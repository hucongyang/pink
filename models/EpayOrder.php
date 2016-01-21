<?php

/**
 * This is the model class for table "epay_order".
 *
 * The followings are the available columns in table 'epay_order':
 * @property integer $id
 * @property integer $uid
 * @property string $r0_Cmd
 * @property integer $r1_Code
 * @property string $p1_MerId
 * @property string $p2_Order
 * @property double $p3_Amt
 * @property string $p4_FrpId
 * @property string $p5_CardNo
 * @property double $p6_confirmAmount
 * @property double $p7_realAmount
 * @property integer $p8_cardStatus
 * @property string $error
 * @property string $p9_MP
 * @property double $pb_BalanceAmt
 * @property double $pc_BalanceAct
 * @property integer $status
 * @property integer $addtime
 */
class EpayOrder extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return EpayOrder the static model class
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
		return 'epay_order';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('uid', 'required'),
			array('uid, r1_Code, p8_cardStatus, status, addtime', 'numerical', 'integerOnly'=>true),
			array('p3_Amt, p6_confirmAmount, p7_realAmount, pb_BalanceAmt, pc_BalanceAct', 'numerical'),
			array('r0_Cmd, p1_MerId, error, p9_MP', 'length', 'max'=>100),
			array('p2_Order', 'length', 'max'=>30),
			array('p4_FrpId', 'length', 'max'=>10),
			array('p5_CardNo', 'length', 'max'=>35),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, uid, r0_Cmd, r1_Code, p1_MerId, p2_Order, p3_Amt, p4_FrpId, p5_CardNo, p6_confirmAmount, p7_realAmount, p8_cardStatus, error, p9_MP, pb_BalanceAmt, pc_BalanceAct, status, addtime', 'safe', 'on'=>'search'),
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
			'r0_Cmd' => 'R0 Cmd',
			'r1_Code' => 'R1 Code',
			'p1_MerId' => 'P1 Mer',
			'p2_Order' => 'P2 Order',
			'p3_Amt' => 'P3 Amt',
			'p4_FrpId' => 'P4 Frp',
			'p5_CardNo' => 'P5 Card No',
			'p6_confirmAmount' => 'P6 Confirm Amount',
			'p7_realAmount' => 'P7 Real Amount',
			'p8_cardStatus' => 'P8 Card Status',
			'error' => 'Error',
			'p9_MP' => 'P9 Mp',
			'pb_BalanceAmt' => 'Pb Balance Amt',
			'pc_BalanceAct' => 'Pc Balance Act',
			'status' => 'Status',
			'addtime' => 'Addtime',
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
		$criteria->compare('uid',$this->uid);
		$criteria->compare('r0_Cmd',$this->r0_Cmd,true);
		$criteria->compare('r1_Code',$this->r1_Code);
		$criteria->compare('p1_MerId',$this->p1_MerId,true);
		$criteria->compare('p2_Order',$this->p2_Order,true);
		$criteria->compare('p3_Amt',$this->p3_Amt);
		$criteria->compare('p4_FrpId',$this->p4_FrpId,true);
		$criteria->compare('p5_CardNo',$this->p5_CardNo,true);
		$criteria->compare('p6_confirmAmount',$this->p6_confirmAmount);
		$criteria->compare('p7_realAmount',$this->p7_realAmount);
		$criteria->compare('p8_cardStatus',$this->p8_cardStatus);
		$criteria->compare('error',$this->error,true);
		$criteria->compare('p9_MP',$this->p9_MP,true);
		$criteria->compare('pb_BalanceAmt',$this->pb_BalanceAmt);
		$criteria->compare('pc_BalanceAct',$this->pc_BalanceAct);
		$criteria->compare('status',$this->status);
		$criteria->compare('addtime',$this->addtime);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}