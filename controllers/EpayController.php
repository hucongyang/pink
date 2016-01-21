<?php

class EpayController extends ApiPublicController {
    //===================卡类充值相关=================//

    /**
     * 充值callback处理
     */
    public function actionPayCallback() {
        $p2_Order = Yii::app()->request->getParam('p2_Order');
        $p5_CardNo = Yii::app()->request->getParam('p5_CardNo');
        $r1_Code = Yii::app()->request->getParam('r1_Code');
        $p8_cardStatus = Yii::app()->request->getParam('p8_cardStatus');
        $p3_Amt = Yii::app()->request->getParam('p3_Amt');

        $paymodel = PayOrder::model()->findByPk($p2_Order - 200000000000);
        $result = $paymodel->pay_result;
        if (!$paymodel) {
            exit;
        }

        if ($result == 0) {
            $epay = new Epay();
            if ($p3_Amt != $paymodel->total_fee) {
                $paymodel->remark = "回调金额与订单不符";
                $paymodel->pay_result = -2;
            } else {

                if ($r1_Code == 1) {
                    $paymodel->order_id = $p2_Order - 200000000000;
                    $paymodel->pay_result = 1;
                } else {
                    if ($p8_cardStatus == 1) {
                        $paymodel->pay_result = -1;
                    } else {
                        $paymodel->pay_result = $p8_cardStatus;
                    }
                    $paymodel->remark = $epay->getError($p8_cardStatus);
                }
            }
            $paymodel->total_fee = $p3_Amt;
            $paymodel->time_update = time();

            //获取对应用户的手机号
            $mobile = User::model()->getUserMobile($paymodel->uid);
            if ($paymodel->save()) {

                //易付通支付详情记录
                $epaymodel = $this->loadModel(array("p2_Order" => $p2_Order));

                if ($r1_Code == 1) {
                    $epaymodel->status = 1;

                    //更新用户账户金币
                    $income = $p3_Amt * Yii::app()->params['coinratio'];
                    $coins = PayOrder::model()->addCoins($paymodel->uid, $income);

                    //更新推广员的收入
                    TgPremote::model()->insertTgIncome($paymodel->uid, $p3_Amt);

                    //添加收入记录
                    $coinhistory = new Coinshistory();
                    $coinhistory->Uid = $paymodel->uid;
                    $coinhistory->Coins = $income;
                    $coinhistory->Type = 8;
                    $coinhistory->CallId = 0;
                    $coinhistory->datetime = time();
                    $coinhistory->save();

                    //短信通知用户充值结果
                    $this->sendSMS($mobile, "石榴裙温馨提醒：尊敬的" . $mobile . "用户，您好！您卡号为" . $p5_CardNo . "的充值卡充值成功！充值金额为" . $p3_Amt . "元。", 3);
                } else {
                    $epaymodel->status = 2;
                    $epaymodel->p8_cardStatus = $p8_cardStatus;
                    $epaymodel->error = $epay->getError($p8_cardStatus);

                    //短信通知用户充值结果
                    $this->sendSMS($mobile, "石榴裙温馨提醒：尊敬的" . $mobile . "用户，您好！您卡号为" . $p5_CardNo . "的充值卡充值失败，请重新充值。", 3);
                }
                $epaymodel->r1_Code = $r1_Code;
                $epaymodel->p7_realAmount = Yii::app()->request->getParam('p7_realAmount');
                $epaymodel->pb_BalanceAmt = Yii::app()->request->getParam('pb_BalanceAmt');
                $epaymodel->pc_BalanceAct = Yii::app()->request->getParam('pc_BalanceAct');

                if ($coins)
                    echo "success";
            } else {
                print_r($paymodel->errors);
            }
        }
    }

    /**
     * 支付接口
     */
    public function actionEpay() {
        $uid = Yii::app()->request->getParam('uid') ? Yii::app()->request->getParam('uid') : $this->_exit($this->_error['20007']);  //用户id
        $status = Yii::app()->request->getParam('status') ? Yii::app()->request->getParam('status') : $this->_exit($this->_error['20066']);    //类型
        $amt = Yii::app()->request->getParam('amt') ? Yii::app()->request->getParam('amt') : $this->_exit($this->_error['20075']);           //卡面额
        $oid = PayOrder::model()->insertOrder($uid, $amt, 0, $status);

        if ($oid > 0) {
            $sp_billno = 200000000000 + $oid; //易付通2开头

            $cardno = Yii::app()->request->getParam('cardno') ? Yii::app()->request->getParam('cardno') : $this->_exit($this->_error['20072']);  //卡类型
            $cardCode = Yii::app()->request->getParam('card') ? Yii::app()->request->getParam('card') : $this->_exit($this->_error['20073']);    //卡号
            $pwd = Yii::app()->request->getParam('pwd') ? Yii::app()->request->getParam('pwd') : $this->_exit($this->_error['20074']);           //卡密码;

            $epay = new Epay();
            $params['p0_Cmd'] = $epay->p0_Cmd;
            $params['p1_MerId'] = $epay->p1_MerId;
            $params['p2_Order'] = $sp_billno;
            $params['p3_Amt'] = $amt;
            $params['p4_verifyAmt'] = $epay->p4_verifyAmt;
            $params['p5_Pid'] = 'getconins';
            $params['p6_Pcat'] = 'epay';
            $params['p7_Pdesc'] = 'epay';
            $params['p8_Url'] = 'http://122.227.43.176/index.php/epay/paycallback/';

            $params['pa_MP'] = '467epay';
            $params['pa7_cardAmt'] = $amt;
            $params['pa8_cardNo'] = $cardCode;
            $params['pa9_cardPwd'] = $pwd;
            $params['pd_FrpId'] = $cardno;
            $params['pr_NeedResponse'] = '1';
            $params['pz_userId'] = $epay->p1_MerId;
            $params['pz1_userRegTime'] = $epay->p1_MerId;
            $hmac = $epay->getReqHmacString($params, $epay->key);

            $params['hmac'] = $hmac;

            $response = $epay->curlData($params);

            if (isset($response['r1_Code']) && $response['r1_Code'] == 1) {
                //充值成功进行己方的订单记录动作，在callback中进行校验，如果支付成功，修改状态改为1
                $epaymodel = new EpayOrder();

                $epaymodel->uid = $uid;
                $epaymodel->r0_Cmd = $params['p0_Cmd'];
                $epaymodel->p1_MerId = $params['p1_MerId'];
                $epaymodel->p2_Order = $params['p2_Order'];
                $epaymodel->p3_Amt = $params['p3_Amt'];
                $epaymodel->p4_FrpId = $params['pd_FrpId'];
                $epaymodel->p4_FrpId = $params['pd_FrpId'];
                $epaymodel->p5_CardNo = $params['pa8_cardNo'];
                $epaymodel->p6_confirmAmount = $params['pa7_cardAmt'];
                $epaymodel->p9_MP = $params['pa_MP'];
                $epaymodel->addtime = time();

                $epaymodel->save();

                $this->_exit($epaymodel->p2_Order - 200000000000, 20000);
            } else {
                $this->_exit($response['rq_ReturnMsg']);
            }
        }
    }

    /**
     * 轮询校验回调结果
     */
    public function actionCheck() {
        $orderid = Yii::app()->request->getParam('orderid') ? Yii::app()->request->getParam('orderid') : $this->_exit($this->_error['20069']);  //订单号

        $paymodel = PayOrder::model()->findByPk($orderid);
        if (!$paymodel) {
            $this->_exit($this->_error['20070'], '20070');
        } else {
            if ($paymodel->pay_result == 1) {
                $this->_exit($this->_error['20000'], '20000');
            } elseif ($paymodel->pay_result == 0) {
                $this->_exit($this->_error['20071'], '20071');
            } else {
                $this->_exit($paymodel->remark, '20068');
            }
        }
    }

    /**
     *  发送验证码
     */
    public function sendSMS($mobile, $message, $type = 3) {
        $data = array();
        $url = 'http://mobile.5173.com/MobileAPI/SendSingleMessage';
        $data['m_clientIP'] = '192.168.2.161';
        $data['m_sign'] = MD5('taohua192.168.2.161');
        $data['category'] = '7701';
        $data['mobile'] = $mobile;
        $data['content'] = $message;
        $output = Yii::app()->curl->post($url, $data);

        $ob = json_decode($output);

        //发送短信，记录发送日志
        $log['sendtime'] = date("Y-m-d H:i:s");
        $log['mobile'] = $mobile;
        $log['type'] = $type;
        $log['ResultNo'] = $ob->ResultNo;
        $log['message'] = $ob->ResultDescription;

        Smslog::model()->addLog($log);

        return $ob->ResultNo;
    }

    public function loadModel($order_id) {
        $model = EpayOrder::model()->findByAttributes($order_id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

}