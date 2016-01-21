<?php

class CallController extends ApiPublicController {

    public function actionIndex() {
        if (!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'], '20007');
        if (!isset($_POST['ruid']) || !$_POST['ruid']) $this->_exit($this->_error['20012'], '20012');
        if(!isset($_POST['mac']) || !$_POST['mac']) $this->_exit($this->_error['20028'],'20028');
        $uid = trim($_POST['uid']);
        $ruid = trim($_POST['ruid']);
        $mac = trim($_POST['mac']);
        $token = substr(md5($uid.$mac), 0, 16);
        $r = User::model()->haveToken($uid);
        if($r['value'] != $token) $this->_exit($this->_error['20061'],'20061');
        $ustatus = User::model()->userStatus($ruid);
        if ($ustatus == 2 || $ustatus == 0) $this->_exit($this->_error['20060'], '20060', $ustatus, 'status');
        $res = Callhistory::model()->userCallStatus($uid);
        if ($res) $this->_exit($this->_error['20035'], '20035', $res, 'status');
        $resTo = Callhistory::model()->userCallStatus($ruid);
        if ($resTo) $this->_exit($this->_error['20078'], '20078', $res, 'status');
        $from = Callhistory::model()->getMobile($uid);
        $mobile = $from['Mobile'];
        $to = Callhistory::model()->getMobile($ruid);
        $toMobile = $to['Mobile'];
        if (!$mobile) $this->_exit($this->_error['20001'], '20001');
        if (!$toMobile) $this->_exit($this->_error['20001'], '20001');
        $coins = Callhistory::model()->getCoins($uid);
        $price = Callhistory::model()->getPrice($ruid);
        if (!empty($coins)) $HaveUse = $coins['Coins'] + $coins['FreeCoins'] + $coins['WinCoins'];
        if ($price == 0 && $to['Sex'] == 0) $this->_exit($this->_error['20037'], '20037');
        if ($price == 0 && $to['Sex'] == 1) $m = 1;
        else $m = floor($HaveUse / 100 / $price);
        $minute = $m * 10;
        $record = array();
        $record['uid'] = $uid;
        $record['price'] = $price;
        $record['phone'] = $mobile;
        $record['mobile'] = $toMobile;
        $record['callstatus'] = 0;
        $reId = Callhistory::model()->setCallRecord($record, $ruid, $m*60);
        $data = array();
        $url = 'http://open.guoling.com/datasync.php';
        $data['brand_id'] = 'jhzy2';
        $data['key'] = 'jhzy1234asdf';
        $data['uid'] = $reId;
        $data['balance'] = $minute;
        $data['phone'] = $mobile;
        $data['type'] = '01';
        $data['sign'] = md5($data['brand_id'] . '&' . $data['key'] . '&' . $reId . '&' . $data['phone']);
        $output = Yii::app()->curl->post($url, $data);
        $output = explode(': ', $output);
        if ($output[1] == 0) {
            $calldata = array();
            $callurl = 'http://open.guoling.com/call.php';
            $calldata['brand_id'] = 'jhzy2';
            $calldata['key'] = 'jhzy1234asdf';
            $calldata['uid'] = $reId;
            $calldata['phone'] = $toMobile;
            $calldata['call'] = $mobile;
            $calldata['type'] = '0';
            $calldata['sign'] = md5($calldata['brand_id'] . '&' . $calldata['key'] . '&' . $reId . '&' . $calldata['phone'] . '&' . $calldata['call']);
            $out = Yii::app()->curl->post($callurl, $calldata);
            $out = explode(': ', $out);
            if ($out[1] == 0) {
                Callhistory::model()->changeCallStatus($uid);
                Callhistory::model()->changeCallStatus($ruid);
                $this->_exit($this->_error['20000'], '20000', $reId, 'callid');
            }
            if ($out[1] == -5)
                $this->_exit($this->_error['20038'], '20038');
        }
        $this->_exit($this->_error['20036'], '20036');
    }

    public function actionSend_bill() {
        $key = 'jhzy1234asdf';
        $data = array();
        $data['call_id'] = trim(Yii::app()->request->getParam('call_id'));
        $data['brand_id'] = trim(Yii::app()->request->getParam('brand_id'));
        $data['uid'] = trim(Yii::app()->request->getParam('uid'));
        $data['called'] = trim(Yii::app()->request->getParam('called'));
        $data['start_time'] = strtotime(trim(Yii::app()->request->getParam('start_time')));
        $data['end_time'] = strtotime(trim(Yii::app()->request->getParam('end_time')));
        $data['call_time'] = trim(Yii::app()->request->getParam('call_time'));
        $data['field_fee'] = trim(Yii::app()->request->getParam('field_fee'));
        $data['agent_fee'] = trim(Yii::app()->request->getParam('agent_fee'));
        $data['sign'] = trim(Yii::app()->request->getParam('sign'));
        $cid = Callhistory::model()->isCallId($data['call_id']);
        $s = implode(',', $data);
        if ($cid > 0)
            exit('-3');
        $sign = md5($data['call_id'] . '&' . $data['brand_id'] . '&' . $data['uid'] . '&' . $data['called'] . '&' . $key);
        if ($data['sign'] != $sign)
            exit('-2');
        if (!empty($data)) {
            $record = Callhistory::model()->getToMobile($data['uid']);
            Callhistory::model()->changeCallFree($record['uid']);
            Callhistory::model()->changeCallFree($record['ruid']);
            if (preg_match('/' . $record['phone'] . '/', $data['called'])) {
                if($record['realtime'] < $data['call_time']) $popu = ceil($record['realtime'] / 60);
                else $popu = ceil($data['call_time'] / 60);
                $data['consumption'] = $popu * $record['price'] * 100; 
                $win =  $data['consumption']  * (1-Yii::app()->params['allcoins']);//减掉收取平台费
                Callhistory::model()->updateCallRecord($data);//更新通话记录表
                $proxy = Callhistory::model()->isProxyUser($record['ruid']);
                if($proxy > 0) {
                    $UserWin = $win * (1-Yii::app()->params['agent']);//减掉代理商提成=用户收入
                    $AgentWin = $win * Yii::app()->params['agent'];//代理商收入
                    $userRes = Callhistory::model()->addWinCoins($record['ruid'], $UserWin);
                    if($userRes) Callhistory::model()->setCoinsHistory($record['ruid'], $UserWin, 10, $data['uid']);
                    $proxyRes = Callhistory::model()->addWinCoins($proxy, $AgentWin);
                    if($proxyRes) Callhistory::model()->setCoinsHistory($proxy, $AgentWin, 11, $data['uid']);
                } else {
                    $userRes= Callhistory::model()->addWinCoins($record['ruid'], $win);
                    if($userRes) Callhistory::model()->setCoinsHistory($record['ruid'], $win, 10, $data['uid']);
                }
                Callhistory::model()->updateCoins($data['consumption'], $record['uid']);//更新主叫金币
                Callhistory::model()->setCoinsHistory($record['uid'], -$data['consumption'], 14, $data['uid']);
                UserInfo::model()->changPopu($record['ruid'], $popu);//更新人气
            } elseif ($data['call_time'] == 0 && $record['callstatus'] == 0) {
                Callhistory::model()->insertCallRecord($data);
            }
            $res = Callhistory::model()->setCallInfomation($data);
            if ($res) exit('0');
        }
        exit('-1');
    }

    public function actionBack() {
        if (!isset($_POST['callid']) || !$_POST['callid']) $this->_exit($this->_error['20040'], '20040');
        $cid = trim($_POST['callid']);
        $res = Callhistory::model()->getCallStatus($cid);
        if ($res >= 0) $this->_exit($this->_error['20000'], '20000', $res, 'status');
    }
    /**
     * 测试台湾电话
     */
    public function actionTest(){
        $data = array();
        $url = 'http://open.guoling.com/datasync.php';
        $data['brand_id'] = 'jhzy2';
        $data['key'] = 'jhzy1234asdf';
        $data['uid'] = '123456789';
        $data['balance'] = 100;
        $data['phone'] = '13918231466';
        $data['type'] = '01';
        $data['sign'] = md5($data['brand_id'] . '&' . $data['key'] . '&123456789&' . $data['phone']);
        $output = Yii::app()->curl->post($url, $data);
        $output = explode(': ', $output);
        if ($output[1] == 0) {
            $calldata = array();
            $callurl = 'http://open.guoling.com/call.php';
            $calldata['brand_id'] = 'jhzy2';
            $calldata['key'] = 'jhzy1234asdf';
            $calldata['uid'] = '123456789';
            $calldata['phone'] = '13918231466';//13918231466
            $calldata['call'] = '00886928230771';
            $calldata['type'] = '0';
            $calldata['sign'] = md5($calldata['brand_id'] . '&' . $calldata['key'] . '&123456789&' . $calldata['phone'] . '&' . $calldata['call']);
            $out = Yii::app()->curl->post($callurl, $calldata);
            $out = explode(': ', $out);
            if ($out[1] == 0) {
                $this->_exit($this->_error['20000'], '20000', '123456789', 'callid');
            }
            if ($out[1] == -5)
                $this->_exit($this->_error['20038'], '20038');
        }
    }

}