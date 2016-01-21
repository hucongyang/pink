<?php

/**
 * This is the model class for table "usercoins".
 *
 * The followings are the available columns in table 'usercoins':
 * @property integer $UserId
 * @property integer $Coins
 * @property integer $FreeCoins
 * @property integer $WinCoins
 * @property integer $TgCoins
 * @property integer $InCoins
 * @property integer $ustatus
 * @property integer $type
 * @property string $datetime
 */
class Usercoins extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Usercoins the static model class
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
		return 'usercoins';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('UserId, Coins, FreeCoins, WinCoins, InCoins', 'required'),
			array('UserId, Coins, FreeCoins, WinCoins, TgCoins, InCoins, ustatus, type', 'numerical', 'integerOnly'=>true),
			array('datetime', 'length', 'max'=>11),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('UserId, Coins, FreeCoins, WinCoins, TgCoins, InCoins, ustatus, type, datetime', 'safe', 'on'=>'search'),
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
			'UserId' => 'User',
			'Coins' => 'Coins',
			'FreeCoins' => 'Free Coins',
			'WinCoins' => 'Win Coins',
			'TgCoins' => 'Tg Coins',
			'InCoins' => 'In Coins',
			'ustatus' => 'Ustatus',
			'type' => 'Type',
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

		$criteria->compare('UserId',$this->UserId);
		$criteria->compare('Coins',$this->Coins);
		$criteria->compare('FreeCoins',$this->FreeCoins);
		$criteria->compare('WinCoins',$this->WinCoins);
		$criteria->compare('TgCoins',$this->TgCoins);
		$criteria->compare('InCoins',$this->InCoins);
		$criteria->compare('ustatus',$this->ustatus);
		$criteria->compare('type',$this->type);
		$criteria->compare('datetime',$this->datetime,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}