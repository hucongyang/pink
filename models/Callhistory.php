<?php

/**
 * This is the model class for table "callhistory".
 *
 * The followings are the available columns in table 'callhistory':
 * @property integer $Id
 * @property integer $FromUserId
 * @property integer $ToUserId
 * @property string $FromMobile
 * @property string $ToMobile
 * @property string $FromBeginTime
 * @property string $ToBeginTime
 * @property string $FromEndTime
 * @property string $ToEndTime
 * @property integer $FromSeconds
 * @property integer $ToSeconds
 * @property integer $CallSeconds
 */
class Callhistory extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Callhistory the static model class
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
		return 'callhistory';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('FromUserId, ToUserId, FromMobile, ToMobile, ToSeconds', 'required'),
			array('FromUserId, ToUserId, FromSeconds, ToSeconds, CallSeconds', 'numerical', 'integerOnly'=>true),
			array('FromMobile, ToMobile', 'length', 'max'=>20),
			array('FromBeginTime, ToBeginTime, FromEndTime, ToEndTime', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('Id, FromUserId, ToUserId, FromMobile, ToMobile, FromBeginTime, ToBeginTime, FromEndTime, ToEndTime, FromSeconds, ToSeconds, CallSeconds', 'safe', 'on'=>'search'),
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
			'Id' => 'ID',
			'FromUserId' => 'From User',
			'ToUserId' => 'To User',
			'FromMobile' => 'From Mobile',
			'ToMobile' => 'To Mobile',
			'FromBeginTime' => 'From Begin Time',
			'ToBeginTime' => 'To Begin Time',
			'FromEndTime' => 'From End Time',
			'ToEndTime' => 'To End Time',
			'FromSeconds' => 'From Seconds',
			'ToSeconds' => 'To Seconds',
			'CallSeconds' => 'Call Seconds',
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

		$criteria->compare('Id',$this->Id);
		$criteria->compare('FromUserId',$this->FromUserId);
		$criteria->compare('ToUserId',$this->ToUserId);
		$criteria->compare('FromMobile',$this->FromMobile,true);
		$criteria->compare('ToMobile',$this->ToMobile,true);
		$criteria->compare('FromBeginTime',$this->FromBeginTime,true);
		$criteria->compare('ToBeginTime',$this->ToBeginTime,true);
		$criteria->compare('FromEndTime',$this->FromEndTime,true);
		$criteria->compare('ToEndTime',$this->ToEndTime,true);
		$criteria->compare('FromSeconds',$this->FromSeconds);
		$criteria->compare('ToSeconds',$this->ToSeconds);
		$criteria->compare('CallSeconds',$this->CallSeconds);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	/**
	 * 获取自己的电话号码
	 * @param int $uid
	 * @return Ambigous <mixed, string, unknown>
	 */
	public function getMobile($uid){
	    return $this->dbConnection->createCommand()
    	    ->select('Mobile,Sex')
    	    ->from('user')
    	    ->where('UserId=:uid',array(':uid'=>$uid))
    	    ->queryRow();
	}
	/**
	 * 获取金币数量
	 * @param int $uid
	 */
	public function getCoins($uid){
	    return $this->dbConnection->createCommand()
	    ->select('*')
	    ->from('usercoins')
	    ->where('UserId=:uid',array(':uid'=>$uid))
	    ->queryRow();	    
	}
	/**
	 * 获取价格
	 * @param int $uid
	 * @return Ambigous <mixed, string, unknown>
	 */
	public function getPrice($uid){
	    return $this->dbConnection->createCommand()
    	    ->select('Price')
    	    ->from('userinfo')
    	    ->where('UserId=:uid',array(':uid'=>$uid))
    	    ->queryScalar();
	}
	/**
	 * 修改用户通话状态 - 通话中
	 * @param int $uid
	 * @return number
	 */
    public function changeCallStatus($uid){
        return $this->dbConnection->createCommand()
            ->update('userinfo',
                array(
                    'CallStatus' => 1
                ),
                'UserId=:UserId', array(':UserId'=>$uid)
            );
    }
	/**
	 * 查看通话状态
	 * @param int $uid
	 * @return Ambigous <mixed, string, unknown>
	 */
	public function userCallStatus($uid){
	    return $this->dbConnection->createCommand()
    	    ->select('CallStatus')
    	    ->from('userinfo')
    	    ->where('UserId=:uid',array(':uid'=>$uid))
    	    ->queryScalar();	    
	}
	/**
	 * 修改用户通话状态 - 空闲中
	 * @param int $uid
	 * @return number
	 */
	public function changeCallFree($uid){
	    return $this->dbConnection->createCommand()
	        ->update('userinfo',
	            array(
                    'CallStatus' => 0
	            ),
	            'UserId=:UserId', array(':UserId'=>$uid)
	    );
	}
	/**
	 * 判断Cid是否存在
	 * @param int $cid
	 * @return Ambigous <mixed, string, unknown>
	 */
	public function isCallId($cid){
	    return $this->dbConnection->createCommand()
    	    ->select('count(id) as n')
    	    ->from($this->tableName())
    	    ->where('call_id=:cid',array(':cid'=>$cid))
    	    ->queryScalar();
	}
	/**
	 * 插入回调记录
	 * @param unknown_type $data
	 * @return number
	 */
	public function setCallInfomation($data){
	    $res = $this->dbConnection->createCommand()
	    ->insert($this->tableName(),
            array(
                'call_id'=>$data['call_id'],
                'uid'=>$data['uid'],
                'called'=>$data['called'],
                'start_time'=>$data['start_time'],
                'end_time'=>$data['end_time'],
                'call_time'=>$data['call_time'],
                'field_fee'=>$data['field_fee'],
                'agent_fee'=>$data['agent_fee']
            )
	    );
	    return $res;
	}	
	/**
	 * 插入通话记录
	 * @param unknown_type $data
	 * @return string
	 */
	public function setCallRecord($data,$ruid,$time){
	    $this->dbConnection->createCommand()
            ->insert('callrecord',
                array(
                    'uid'        => $data['uid'],
                    'ruid'       => $ruid,
                    'price'      => $data['price'],
                    'phone'      => $data['phone'],
                    'mobile'     => $data['mobile'],
                    'realtime'   => $time,
                    'callstatus' => $data['callstatus'],
                    'datetime'   => time()
                )
            );
	    return $this->dbConnection->lastInsertID;
	}
	/**
	 * 查询uid,单价,被叫电话
	 * @param unknown_type $id
	 * @return mixed
	 */
	public function getToMobile($id){
	    return $this->dbConnection->createCommand()
    	    ->select('uid,ruid,price,phone,mobile,realtime,callstatus')
    	    ->from('callrecord')
    	    ->where('id=:id',array(':id'=>$id))
    	    ->queryRow();	    
	}
	/**
	 * 更新通话记录
	 * @param unknown_type $data
	 */
	public function updateCallRecord($data){
	    $this->dbConnection->createCommand()
    	    ->update('callrecord',
    	            array(
                         'call_id'=>$data['call_id'],
                         'stime'=>$data['start_time'],
                         'etime'=>$data['end_time'],
                         'calltime'=>$data['call_time'],
                         'consumption'=>$data['consumption'],
    	                 'callstatus'=> 1,
	                     'datetime'=>time()
    	            ),
    	            'id=:uid', array(':uid'=>$data['uid'])
    	    );
	}	
	/**
	 * 记录拒接通话记录
	 * @param unknown_type $data
	 */
	public function insertCallRecord($data){
	    $this->dbConnection->createCommand()
	    ->update('callrecord',
	            array(
	                    'call_id'=>$data['call_id'],
	                    'stime'=>$data['start_time'],
	                    'etime'=>$data['end_time'],
	                    'calltime'=>$data['call_time'],
	                    'consumption'=> 0,
	                    'callstatus'=> 2,
	                    'datetime'=>time()
	            ),
	            'id=:uid', array(':uid'=>$data['uid'])
	    );
	}
	/**
	 * 更新主叫用户金币
	 * @param int $coins
	 * @param int $uid
	 */
	public function updateCoins($coins,$uid){
	    $db = $this->dbConnection->createCommand();
        $res= $db->select('Coins,FreeCoins,WinCoins')
        	     ->from('usercoins')
        	     ->where('UserId=:uid',array(':uid'=>$uid))
        	     ->queryRow();
	    if($res['FreeCoins'] >= $coins) {
	        $num = $res['FreeCoins'] - $coins;
	        $db->update('usercoins',array('FreeCoins'=>$num),'UserId=:uid', array(':uid'=>$uid));
	    }
	    if($res['FreeCoins'] < $coins && $res['Coins'] + $res['FreeCoins'] >= $coins) {
	        $num = $res['Coins'] + $res['FreeCoins'] - $coins;
	        $db->update('usercoins',array('Coins'=>$num,'FreeCoins'=>0),'UserId=:uid', array(':uid'=>$uid));
	    } 
	    if($res['Coins'] + $res['FreeCoins'] < $coins && $res['Coins'] + $res['FreeCoins'] +  $res['WinCoins'] >= $coins) {
	        $num = $res['Coins'] + $res['FreeCoins'] +  $res['WinCoins'] - $coins;
	        $db->update('usercoins',array('Coins'=>0,'FreeCoins'=>0,'WinCoins'=>$num),'UserId=:uid', array(':uid'=>$uid));
	    }
	}
	/**
	 * 更新被叫用户金币
	 * @param int $uid
	 * @param int $coins
	 */
	public function addWinCoins($uid,$coins){
	    $db = $this->dbConnection->createCommand();
	    $res = $db->select('WinCoins')
        	    ->from('usercoins')
        	    ->where('UserId=:uid',array(':uid'=>$uid))
        	    ->queryScalar();
	    $num = $res + $coins;
	    return $db->update('usercoins',array('WinCoins'=>$num),'UserId=:uid', array(':uid'=>$uid));
	} 

	/**
	 * 获取电话状态
	 * @param unknown_type $cid
	 * @return Ambigous <mixed, string, unknown>
	 */
	public function getCallStatus($cid){
	    return $this->dbConnection->createCommand()
	    ->select('callstatus')
	    ->from('callrecord')
	    ->where('id=:id',array(':id'=>$cid))
	    ->queryScalar();	    
	}
	/**
	 * 检查是否是代理用户
	 * @param int $uid
	 * @return Ambigous <mixed, string, unknown>
	 */
	public function isProxyUser($uid){
	    return $this->dbConnection->createCommand()
    	    ->select('aid')
    	    ->from('agent_relation')
    	    ->where('uid=:uid',array(':uid'=>$uid))
    	    ->queryScalar();
	}
	/**
	 * 插入金币记录
	 * @param unknown_type $uid
	 * @param unknown_type $coins
	 * @param unknown_type $type
	 * @param unknown_type $cid
	 */
	public function setCoinsHistory($uid,$coins,$type,$cid){
	    $this->dbConnection->createCommand()
	        ->insert('coinshistory',
	            array(
	                    'Uid'       => $uid,
	                    'Coins'     => $coins,
	                    'Type'      => $type,
	                    'CallId'    => $cid,
	                    'datetime'  => time()
	            ));
	}
}