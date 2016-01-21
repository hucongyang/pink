<?php

class PayController extends ApiPublicController
{
    /**
     * 财付通接口
     */
	public function actionTenpay()
	{
	    if(!isset($_POST['uid']) || $_POST['uid']=='') $this->_exit($this->_error['20007'],'20007');
	    if(!isset($_POST['status']) || $_POST['status']=='') $this->_exit($this->_error['20066'],'20066');
	    if(!isset($_POST['fee']) || $_POST['fee']=='') $this->_exit($this->_error['20067'],'20067');
	    $uid = Yii::app()->request->getParam('uid');
	    $status = Yii::app()->request->getParam('status');
	    $coins = Yii::app()->request->getParam('fee');
	    $paylist = Yii::app()->params['payList'];
	    $res = array_key_exists($coins,$paylist);
	    if($res) {
    	    $fee = $paylist[$coins] * 100;
    	    $oid = PayOrder::model()->insertOrder($uid, $fee, $coins*100, $status);
	    }else{
	        $this->_exit($this->_error['20077'],'20077');
	    }
	    if($oid > 0) {
    	    $sp_billno = 100000000000 + $oid;//财付通1开头
    	    $url = "https://wap.tenpay.com/cgi-bin/wappayv2.0/wappay_init.cgi";
    	    $params = array();
    	    $params['ver'] = "2.0";
    	    $params['charset'] = "1";
    	    $params['sp_billno'] = $sp_billno;
    	    $params['total_fee'] = $fee; //金额
    	    $params['bargainor_id'] = "1215765401";
    	    //$params['attach'] = "";
    	    $params['bank_type'] = "0";
    	    $params['desc'] = "这个是测试的商品描述!";
    	    //$params['purchaser_id'] = ''; //买方 财付通账号
    	    $params['fee_type'] = 1;
    	    $params['notify_url'] = "http://122.227.43.176/index.php/pay/notify";
    	    $params['callback_url'] = "http://www.467.com";
    	    //$params['time_start'] = date('YmdHis',time());
    	    //$params['time_expire'] = date('YmdHis',time()+3600);
    	    $params['key'] = "ab124def05mgh7839cd33befg632cg04";
    	    $params['sign'] = PayOrder::model()->createSign($params);
    	    $output = Yii::app()->curl->post($url, $params);
    	    $ob = simplexml_load_string($output);
    	    if($ob->token_id) {
    	        $url = "https://wap.tenpay.com/cgi-bin/wappayv2.0/wappay_gate.cgi?token_id=".$ob->token_id;
    	        $this->_exit($this->_error['20000'], '20000',$url,'url');
    	    }
    	    $this->_exit($this->_error['21000'], '21000');
	    }
	}
	/**
	 * 财付通充值回调
	 */
	public function actionNotify(){
	    $data = array();
	    $data['ver'] = Yii::app()->request->getParam('ver');
	    $data['charset'] = Yii::app()->request->getParam('charset');
	    $data['pay_result'] = Yii::app()->request->getParam('pay_result');
	    $data['pay_info'] = Yii::app()->request->getParam('pay_info');
	    $data['transaction_id'] = Yii::app()->request->getParam('transaction_id');
	    $data['sp_billno'] = Yii::app()->request->getParam('sp_billno');
	    $data['total_fee'] = Yii::app()->request->getParam('total_fee');
	    $data['fee_type'] = Yii::app()->request->getParam('fee_type');
	    $data['bargainor_id'] = Yii::app()->request->getParam('bargainor_id');
	    $data['attach'] = Yii::app()->request->getParam('attach');
	    $data['sign'] = Yii::app()->request->getParam('sign');
	    $data['bank_type'] = Yii::app()->request->getParam('bank_type');
	    $data['bank_billno'] = Yii::app()->request->getParam('bank_billno');
	    $data['time_end'] = Yii::app()->request->getParam('time_end');
	    $data['purchase_alias'] = Yii::app()->request->getParam('purchase_alias');
	    $data['key'] = "ab124def05mgh7839cd33befg632cg04";
	    $sign = PayOrder::model()->getSign($data);
	    if($sign != $data['sign']) exit('fail');
	    if($data['pay_result'] == "0") {
	        $data['sp_billno'] =  $data['sp_billno'] - 100000000000;
	        $rows = PayOrder::model()->getOrder($data['sp_billno']);//获取金额和金币
	        $uid = PayOrder::model()->getOrderUid($data['sp_billno']);//查询UID
	        if($rows['total_fee'] != $data['total_fee']) {
	            PayOrder::model()->insertTenpayLog($uid, $data);//记录错误日志
	            exit('fail');
	        }
	        $order = PayOrder::model()->updateOrder($data);//修改订单信息
	        $coins = PayOrder::model()->addCoins($uid,$rows['total_coins']);//更新用户账户金币
            if($coins) {
                Callhistory::model()->setCoinsHistory($uid, $rows['total_coins'], 12, 0);
                //更新推广员的收入
                TgPremote::model()->insertTgIncome($uid, $rows['total_coins']);
            }
	        if($order && $coins) exit('success');
	    }
	}
}