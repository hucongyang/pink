<?php

/**
 * This is the model class for table "tg_premote".
 *
 * The followings are the available columns in table 'tg_premote':
 * @property integer $id
 * @property integer $uid
 * @property integer $tuid
 * @property integer $called
 * @property integer $paid
 * @property integer $regtime
 */
class TgPremote extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return TgPremote the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'tg_premote';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('uid, tuid, called, paid, regtime', 'numerical', 'integerOnly' => true),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, uid, tuid, called, paid, regtime', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'uid' => 'Uid',
            'tuid' => 'Tuid',
            'called' => 'Called',
            'paid' => 'Paid',
            'regtime' => 'Regtime',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('uid', $this->uid);
        $criteria->compare('tuid', $this->tuid);
        $criteria->compare('called', $this->called);
        $criteria->compare('paid', $this->paid);
        $criteria->compare('regtime', $this->regtime);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * 新加推广关系
     * @param type $data
     * @return type
     */
    public function addPremote($data) {
        $res = $this->dbConnection->createCommand()
                ->insert($this->tableName(), $data);
        return $res;
    }

    /**
     * 返回推广关系
     * @param type $uid
     * @param type $tuid
     * @return null
     */
    public function findOne($uid, $tuid) {
        if (!$uid || !$tuid) {
            return null;
        }
        $res = $this->dbConnection->createCommand()
                ->select('id')
                ->from($this->tableName())
                ->where('uid=:uid and tuid=:tuid', array(':uid' => $tuid, ":tuid" => $uid))
                ->queryRow();
        return $res;
    }

    /**
     * 充值后，插入推广员的收入记录
     * @param type $tuid(推广用户)
     * @param type $income(充值金额)
     * @return null
     */
    public function insertTgIncome($tuid, $income) {
        if (!$tuid) {
            return null;
        }
        //根据推广用户返回推广员
        $tgy = $this->dbConnection->createCommand()
                ->select('uid')
                ->from($this->tableName())
                ->where('tuid=:tuid', array(':tuid' => $tuid))
                ->queryRow();

        if (!empty($tgy)) {
            //写入推广收入日志
            $this->dbConnection->createCommand()
                    ->insert('tg_log', array('uid' => $tgy['uid'], 'tuid' => $tuid, 'incoins' => $income, 'type' => 2, 'addtime' => time()));

            //更新推广员的收入
            $income = $income * Yii::app()->params['payfl'] * Yii::app()->params['coinratio'];
            $paysql = "update usercoins set TgCoins = TgCoins+" . $income . ", WinCoins = WinCoins+" . $income . " where UserId = '" . $tgy['uid'] . "'";
            $this->db->execute($paysql);

            //添加收入记录
            $coinhistory = new Coinshistory();
            $coinhistory->Uid = $tgy['uid'];
            $coinhistory->Coins = $income * Yii::app()->params['payfl'] * Yii::app()->params['coinratio'];
            $coinhistory->Type = 4;
            $coinhistory->CallId = 0;
            $coinhistory->datetime = time();
            $coinhistory->save();

            //更新对应推广关系的充值状态
            $sql = "update tg_premote set paid=1 where tuid ='" . $tuid . "'";
            return $this->db->execute($sql);
        }
    }

}