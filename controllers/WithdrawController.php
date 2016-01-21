<?php

class WithdrawController extends ApiPublicController
{
    /**
     * 查看绑定状态
     */
	public function actionBindCard()
	{
	    if(!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'],'20007');
	    $uid = trim(Yii::app()->request->getPost('uid'));
	    $res = Withdraw::model()->getBindStatus($uid);
	    if($res) $this->_exit($this->_error['20000'], '20000', $res, 'status');
	    $this->_exit($this->_error['20076'], '20076', 'status');
	}
	/**
	 * 已经绑定, 返回绑定信息
	 */
	public function actionBindInfo(){
	    if(!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'],'20007');
	    $uid = trim($_POST['uid']);
	    $res = Withdraw::model()->getBindInfo($uid);
	    if(!empty($res)) $this->_exit($this->_error['20000'], '20000', $res, 'info');
	}
	/**
	 * 提交提现订单
	 */
	public function actionOrder(){
	    if(!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'],'20007');
	    if(!isset($_POST['name']) || !$_POST['name']) $this->_exit($this->_error['20041'],'20041');
	    if(!isset($_POST['city']) || !$_POST['city']) $this->_exit($this->_error['20042'],'20042');
	    if(!isset($_POST['bank']) || !$_POST['bank']) $this->_exit($this->_error['20043'],'20043');
	    if(!isset($_POST['bname']) || !$_POST['bname']) $this->_exit($this->_error['20044'],'20044');
	    if(!isset($_POST['bcard']) || !$_POST['bcard']) $this->_exit($this->_error['20045'],'20045');
	    if(!isset($_POST['coins']) || !$_POST['coins']) $this->_exit($this->_error['20047'],'20047');
	    if(!isset($_POST['totalcoin'])) $this->_exit($this->_error['20050'],'20050');
	    $data = array();
	    $data['uid'] = trim($_POST['uid']);
	    $data['name'] = trim($_POST['name']);
	    $data['city'] = trim($_POST['city']);
	    $data['bank'] = trim($_POST['bank']);
	    $data['bname'] = trim($_POST['bname']);
	    $data['bcard'] = trim($_POST['bcard']);
	    $data['coins'] = trim($_POST['coins']);
	    $data['totalcoin'] = trim($_POST['totalcoin']);
	    if($data['coins'] > $data['totalcoin']) $this->_exit($this->_error['20051'],'20051');
	    if($data['coins'] % 10 != 0) $this->_exit($this->_error['20048'],'20048');
	    $data['money'] =  $data['coins']; //比例暂时1:1
	    $status = Withdraw::model()->getBindStatus($data['uid']);
	    if($status==2) $res = Withdraw::model()->insertCardInfo($data);
	    else $res=1;
	    $wid = Withdraw::model()->insertOrderInfo($data);
	    if($res && $wid) {
	        $wcoins = Withdraw::model()->getCoinsInfo($data['uid']);
	        $coins = $wcoins - $data['coins'] * 100;
	        //冻结提款金币
            $res = Withdraw::model()->coinsOperate($data['uid'], $coins);
            if($res) $this->_exit($this->_error['20000'], '20000');
	    }
	    $this->_exit($this->_error['20049'], '20049');
	}
	/**
	 * 返回银行信息
	 */
	public function actionBankInfo(){
	    $row =  Withdraw::model()->bankInfo();
	    if($row) $this->_exit($this->_error['20000'], '20000',$row,'bank');
	}
}