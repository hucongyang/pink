<?php

/**
 * This is the model class for table "withdraw".
 *
 * The followings are the available columns in table 'withdraw':
 * @property integer $id
 * @property string $uid
 * @property string $deposit
 * @property integer $status
 * @property string $datetime
 */
class Withdraw extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Withdraw the static model class
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
		return 'withdraw';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, uid', 'required'),
			array('id, status', 'numerical', 'integerOnly'=>true),
			array('uid, deposit, datetime', 'length', 'max'=>11),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, uid, deposit, status, datetime', 'safe', 'on'=>'search'),
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
			'id' => 'ID',
			'uid' => 'Uid',
			'deposit' => 'Deposit',
			'status' => 'Status',
			'datetime' => 'Datetime',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('uid',$this->uid,true);
		$criteria->compare('deposit',$this->deposit,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('datetime',$this->datetime,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	/**
	 * 获取银行卡绑定状态
	 * @param int $uid
	 * @return Ambigous <mixed, string, unknown>
	 */
	public function getBindStatus($uid){
	    $bank = $this->dbConnection->createCommand()
	                ->select('count(id)')
	                ->from('userbank')
	                ->where('uid=:uid and status=:status',array(':uid'=>$uid,':status' => 1))
	                ->queryScalar();
	    $with = $this->dbConnection->createCommand()
    	    ->select('count(id)')
    	    ->from($this->tableName())
    	    ->where('uid=:uid',array(':uid'=>$uid))
    	    ->queryScalar();
	    if($bank > 0 && $with > 0) return $bank;
	    if($bank == 0 && $with > 0) return $bank;
	    if($bank == 0 && $with == 0) return 2;
	}
	/**
	 * 获取绑定银行卡信息
	 * @param int $uid
	 * @return mixed
	 */
	public function getBindInfo($uid){
	    return $this->dbConnection->createCommand()
	    ->select('*')
	    ->from('userbank')
	    ->where('uid=:uid and status=:status',array(':uid' => $uid,':status' => 1))
	    ->queryRow();
	}
	/**
	 * 插入绑定信息
	 * @param array $data
	 * @return unknown
	 */
	public function insertCardInfo($data){
	    $res = $this->dbConnection->createCommand()
	    ->insert('userbank',
            array(
                'uid'=>$data['uid'],
                'name'=>$data['name'],
                'city'=>$data['city'],
                'bank'=>$data['bank'],
                'bname'=>$data['bname'],
                'bcard'=>$data['bcard'],
                'datetime'=> time(),
            )
	    );
	    return $res;
	}
	/**
	 * 插入提现订单信息
	 * @param array $data
	 * @return number
	 */
	public function insertOrderInfo($data){
	    $this->dbConnection->createCommand()
    	    ->insert($this->tableName(),
                array(
                    'uid'=>$data['uid'],
                    'name'=>$data['name'],
                    'city'=>$data['city'],
                    'bank'=>$data['bank'],
                    'bname'=>$data['bname'],
                    'bcard'=>$data['bcard'],
                    'coins'=>$data['coins'],
                    'money'=>$data['money'],
                    'datetime'=> time(),
                )
    	    );
	    return $this->dbConnection->lastInsertID;
	}
	/**
	 * 获取石榴币信息
	 * @param int $uid
	 * @return mixed
	 */
	public function getCoinsInfo($uid){
	    return $this->dbConnection->createCommand()
    	    ->select('WinCoins')
    	    ->from('usercoins')
    	    ->where('UserId=:UserId',array(':UserId'=>$uid))
    	    ->queryScalar();
	}
// 	/**
// 	 * 插入提现石榴币类型记录
// 	 * @param int $uid
// 	 * @param int $wid
// 	 * @param int $coins
// 	 * @param int $wincoins
// 	 * @return number
// 	 */
// 	public function insertRecord($uid,$wid,$coins,$wincoins){
// 	    return $this->dbConnection->createCommand()
//     	    ->insert('coinsrecord',
//                 array(
//                     'uid'        => $uid,
//                     'wid'        => $wid,
//                     'coins'      => $coins,
//                     'wincoins'   => $wincoins,
//                     'datetime'   => time(),
//                     'updatetime'   => time(),
//                 ));	    
// 	}
	/**
	 * 金币操作
	 * @param int $uid
	 * @param int $coins
	 * @return number
	 */
	public function coinsOperate($uid,$coins){
	    return $this->dbConnection->createCommand()
            ->update('usercoins',
                array('WinCoins'=>$coins),
                    'UserId=:uid', array(':uid'=>$uid));
	}
	/**
	 * 获取银行信息
	 * @return Ambigous <multitype:, mixed>
	 */
	public function bankInfo(){
	    return $this->dbConnection->createCommand()
	        ->select('code,bankname')
	        ->from('bank')
        	->queryAll();
	}
}