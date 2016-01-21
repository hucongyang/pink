<?php

class AppstoreController extends ApiPublicController
{
    /**
     * 内购消息处理
     */
    public function actionInside(){
        if(!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'],'20007');
        if(!isset($_POST['coins']) || !$_POST['coins']) $this->_exit($this->_error['20052'],'20052');
        if(!isset($_POST['money']) || !$_POST['money']) $this->_exit($this->_error['20053'],'20053');
        if(!isset($_POST['receipt']) || !$_POST['receipt']) $this->_exit($this->_error['20054'],'20054');
        $data = array();
        $data['uid'] = trim($_POST['uid']);
        $data['coins'] = trim($_POST['coins']);
        $data['money'] = trim($_POST['money']);
        $data['certificate'] = trim($_POST['receipt']);
        $res = Appstore::model()->setInside($data);
        if($res) {
            $receipt = json_encode(array('receipt-data'=>$data['certificate']));
            $url = 'https://sandbox.itunes.apple.com/verifyReceipt';
            $output = Yii::app()->curl->post($url, $receipt);
            $ob = json_decode($output);
            if($ob->status == 0){
                Appstore::model()->changeStatus($data['uid']);
                Appstore::model()->coinsOperate($data['uid'], $data['coins']);
                $this->_exit($this->_error['20000'],'20000');
            }
            $this->_exit($this->_error['20055'],'20055');
        }
        $this->_exit($this->_error['21000'],'21000');
    }
    /**
     * 清空推送消息
     */
    public function actionBadgeEmpty(){
        if(!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'],'20007');
        $uid = trim($_POST['uid']);
        $token = User::model()->getIosToken($uid);
        if($token) {
            Appstore::model()->changeBadge($uid, $token);
            $this->_exit($this->_error['20000'],'20000');
        }
    }
    public function actionPushMessage(){
//         if(!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'],'20007');
//         $uid = trim($_POST['uid']);
        //$iosToken = Appstore::model()->getIostoken($uid);
        $iosToken = '775181128cd802734e2093fcb805678337163d843821cde17bc6334dc23ac644';
        //$iosToken = '533258c8e5f5ad0ddedfc98b91e34030b52c3486e6a652ee566b4dfb190672bd';
        $message = "恭喜:您中奖了, 这是不可能的事情.";
        $badge = 1;
        if($iosToken) $this->ios_push($iosToken, $message, $badge);
    }
}