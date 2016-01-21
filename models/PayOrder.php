<?php

/**
 * This is the model class for table "pay_order".
 *
 * The followings are the available columns in table 'pay_order':
 * @property string $order_id
 * @property string $uid
 * @property string $total_fee
 * @property integer $pay_result
 * @property integer $pay_status
 * @property string $time_create
 * @property string $time_update
 * @property string $remark
 */
class PayOrder extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return PayOrder the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'pay_order';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('pay_result', 'required'),
			array('pay_result, pay_status', 'numerical', 'integerOnly'=>true),
			array('uid, total_fee, time_create, time_update', 'length', 'max'=>11),
			array('remark', 'length', 'max'=>200),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('order_id, uid, total_fee, pay_result, pay_status, time_create, time_update, remark', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'order_id' => 'Order',
			'uid' => 'Uid',
			'total_fee' => 'Total Fee',
			'pay_result' => 'Pay Result',
			'pay_status' => 'Pay Status',
			'time_create' => 'Time Create',
			'time_update' => 'Time Update',
			'remark' => 'Remark',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('order_id',$this->order_id,true);
		$criteria->compare('uid',$this->uid,true);
		$criteria->compare('total_fee',$this->total_fee,true);
		$criteria->compare('pay_result',$this->pay_result);
		$criteria->compare('pay_status',$this->pay_status);
		$criteria->compare('time_create',$this->time_create,true);
		$criteria->compare('time_update',$this->time_update,true);
		$criteria->compare('remark',$this->remark,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	/**
	 * 生成sign
	 * @param array $params
	 * @return string
	 */
	public function createSign($params) {
	    $sign = '';
	    ksort($params);
	    foreach($params as $key => $value) {
	        if('' != $value && 'sign' != $key) {
	            $sign .= $key . '=' . $value . '&';
	        }
	    }
	    $sign .= 'key=' . $params['key'];
	    $sign = strtoupper(md5($sign));
        return $sign;
	}
	
	public function getSign($params) {
	    $sign = '';
	    ksort($params);
	    foreach($params as $key => $value) {
	        if('' != $value && 'sign' != $key && 'key' != $key) {
	            $sign .= $key . '=' . $value . '&';
	        }
	    }
	    $sign .= 'key=' . $params['key'];
	    $sign = strtoupper(md5($sign));
	    return $sign;
	}
    /**
     * 添加充值订单信息
     * @param int $uid
     * @param int $status
     * @return string
     */	
	public function insertOrder($uid,$fee,$coins,$status){
	    $this->dbConnection->createCommand()
	        ->insert($this->tableName(),
	            array(
                    'uid'            => $uid,
                    'total_fee'      => $fee,
                    'total_coins'    => $coins,
                    'pay_status'     => $status,
                    'time_create'    => time()
	            ));
	    return $this->dbConnection->lastInsertID;
	}
	/**
	 * 查询充值金额
	 * @param unknown_type $oid
	 */
	public function getOrder($oid){
	    return $this->dbConnection->createCommand()
    	    ->select('total_fee,total_coins')
    	    ->from($this->tableName())
    	    ->where('order_id=:oid',array(':oid'=>$oid))
    	    ->queryRow();
	}
	/**
	 * 插入回调记录
	 * @param unknown_type $uid
	 * @param unknown_type $data
	 */
	public function insertTenpayLog($uid,$data) {
	    $this->dbConnection->createCommand()
	        ->insert('tenpay_log',
	            array(
                    'uid'            => $uid,
                    'ver'            => $data['ver'],
                    'charset'        => $data['charset'],
                    'pay_result'     => $data['pay_result'],
                    'pay_info'       => $data['pay_info'],
                    'transaction_id' => $data['transaction_id'],
                    'sp_billno'      => $data['sp_billno'],
                    'total_fee'      => $data['total_fee'],
                    'fee_type'       => $data['fee_type'],
                    'bargainor_id'   => $data['bargainor_id'],
                    'attach'         => $data['attach'],
                    'sign'           => $data['sign'],
                    'bank_type'      => $data['bank_type'],
                    'bank_billno'    => $data['bank_billno'],
                    'time_end'       => $data['time_end'],
                    'purchase_alias' => $data['purchase_alias'],
                    'datetime'       => time()
	            ));
	}
    /**
     * 更新充值订单信息
     * @param array $data
     * @return number
     */
    public function updateOrder($data){
        return $this->dbConnection->createCommand()
            ->update($this->tableName(),
                array(
                    'pay_result'    => $data['pay_result'],
                    'time_update'   => time(),
                    'remark'        => $data['pay_info']
                ),'order_id=:oid', array(':oid'=>$data['sp_billno']));
    }
    /**
     * 获取用户id
     * @param int $oid
     * @return Ambigous <mixed, string, unknown>
     */
    public function getOrderUid($oid){
        return $this->dbConnection->createCommand()
            ->select('uid')
            ->from($this->tableName())
            ->where('order_id=:id',array(':id'=>$oid))
            ->queryScalar();
    }
    /**
     * 更新用户账户石榴币
     * @param unknown_type $uid
     * @param unknown_type $coins
     * @return number
     */
    public function addCoins($uid,$coins){
        $db = $this->dbConnection->createCommand();
        $res = $db->select('Coins')
            ->from('usercoins')
            ->where('UserId=:uid',array(':uid'=>$uid))
            ->queryScalar();
        $num = $res + $coins;
        return $db->update('usercoins',array('Coins'=>$num),'UserId=:uid', array(':uid'=>$uid));
    }
}