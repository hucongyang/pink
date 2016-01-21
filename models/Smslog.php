<?php

/**
 * This is the model class for table "Smslog".
 *
 * 统计短信日志
 * 
 * 
 * The followings are the available columns in table 'Smslog':
 * @property string $id
 * @property string $smscount
 */
class Smslog extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Trends the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'smslog201308';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('sendtime, mobile, type, resultno', 'required'),
            array('mobile', 'length', 'max' => 11),
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
            'id' => 'Id',
            'sendtime' => 'Sendtime',
            'mobile' => 'Mobile',
            'type' => 'Type',
            'resultno' => 'ResultNo',
            'message' => 'Message',
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

        $criteria->compare('id', $this->id, true);
        $criteria->compare('sendtime', $this->sendtime, true);
        $criteria->compare('mobile', $this->mobile, true);
        $criteria->compare('type', $this->type, true);
        $criteria->compare('resultno', $this->resultno, true);
        $criteria->compare('message', $this->message, true);
        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }

    /**
     * 插入短信记录日志
     * @param type $log
     * @return type
     */
    public function addLog($log) {
        $month = date("Ym");
        if ($month != "201308") {
            $sql = "create table if not exists smslog" . $month . " like smslog201308;";
            $tableRes = $this->dbConnection->createCommand($sql)->query();
        }
        $res = $this->dbConnection->createCommand()
                ->insert('smslog' . $month, $log);

        //发送成功，月汇总加1
        if ($res && ($log['ResultNo'] == 0)) {
            $this->monthCount();
        }

        return $res;
    }

    /**
     * 月统计
     * @param type $log
     * @return type
     */
    public function monthCount() {
        $month = date("Y-m");
        $record = $this->dbConnection->createCommand()
                ->select('*')
                ->from('smscount')
                ->where('month = :month ', array(':month' => $month))
                ->queryRow();
        //对应月份已存在，直接更新统计数
        if ($record) {
            $res = $this->dbConnection->createCommand()
                    ->update('smscount', array("smscount" => new CDbExpression('smscount+1')), 'month=:month', array(':month' => $month)
            );
            //对应月份不存在，添加对应月份的记录
        } else {
            $res = $this->dbConnection->createCommand()
                    ->insert('smscount', array("month" => $month, "smscount" => 1));
        }
        return $res;
    }

}