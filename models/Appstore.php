<?php

/**
 * This is the model class for table "appstore".
 *
 * The followings are the available columns in table 'appstore':
 * @property string $id
 * @property string $uid
 * @property string $certificate
 * @property string $coins
 * @property string $money
 * @property integer $status
 * @property string $datetime
 */
class Appstore extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Appstore the static model class
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
		return 'appstore';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('status', 'numerical', 'integerOnly'=>true),
			array('uid, coins, money, datetime', 'length', 'max'=>11),
			array('certificate', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, uid, certificate, coins, money, status, datetime', 'safe', 'on'=>'search'),
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
			'certificate' => 'Certificate',
			'coins' => 'Coins',
			'money' => 'Money',
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

		$criteria->compare('id',$this->id,true);
		$criteria->compare('uid',$this->uid,true);
		$criteria->compare('certificate',$this->certificate,true);
		$criteria->compare('coins',$this->coins,true);
		$criteria->compare('money',$this->money,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('datetime',$this->datetime,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	/**
	 * 记录内购信息
	 * @param array $data
	 * @return number
	 */
	public function setInside($data){
	    $res = $this->dbConnection->createCommand()
	        ->insert($this->tableName(),
	            array(
                    'uid' => $data['uid'],
                    'certificate' => $data['certificate'],
                    'coins' => $data['coins'],
                    'money' => $data['money'],
                    'datetime' => time()
	            )
	        );
	    return $res;    
	}
	/**
	 * 更改内购状态
	 * @param int $uid
	 * @return number
	 */
	public function changeStatus($uid){
	    return $this->dbConnection->createCommand()
        ->update($this->tableName(),
            array(
                'status' => 1 ,
                'datetime' => time()
            ),
            'uid=:uid', array(':uid'=>$uid)
	    );
	}
	/**
	 * 金币操作
	 * @param int $uid
	 * @param int $coins
	 * @return number
	 */
	public function coinsOperate($uid,$coins){
	    $c = $this->dbConnection->createCommand()
    	    ->select('Coins')
    	    ->from('usercoins')
    	    ->where('UserId=:uid',array(':uid'=>$uid))
    	    ->queryScalar();
	    $coins = $c + $coins;
	    return $this->dbConnection->createCommand()
	        ->update('usercoins',
	            array(
	                    'Coins' => $coins
	            ),
	            'UserId=:UserId', array(':UserId'=>$uid)
	        );
	}
	public function changeBadge($uid,$token){
	    return $this->dbConnection->createCommand()
	    ->update('pushmsg',
	            array(
	                'badge' => 0
	            ),
	            'uid=:uid AND iostoken=:iostoken', array(':uid'=>$uid,':iostoken'=>$token)
	    );	    
	}
	public function getIostoken($uid){
	    return $this->dbConnection->createCommand()
    	    ->select('iostoken')
    	    ->from('user')
    	    ->where('UserId=:uid',array(':uid'=>$uid))
    	    ->queryScalar();
	}
}