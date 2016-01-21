<?php

/**
 * This is the model class for table "tg_articles".
 *
 * The followings are the available columns in table 'tg_articles':
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property integer $type
 * @property string $addtime
 * @property integer $pv
 * @property integer $status
 */
class TgArticles extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return TgArticles the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'tg_articles';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('title, content', 'required'),
            array('type, pv, status', 'numerical', 'integerOnly' => true),
            array('title', 'length', 'max' => 255),
            array('addtime', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, title, content, type, addtime, pv, status', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'category' => array(self::BELONGS_TO, 'TgArticleType', 'type'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'title' => 'Title',
            'content' => 'Content',
            'type' => 'Type',
            'addtime' => 'Addtime',
            'pv' => 'Pv',
            'status' => 'Status',
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
        $criteria->compare('title', $this->title, true);
        $criteria->compare('content', $this->content, true);
        $criteria->compare('type', $this->type);
        $criteria->compare('addtime', $this->addtime, true);
        $criteria->compare('pv', $this->pv);
        $criteria->compare('status', $this->status);

        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }

    /**
     * 所有文章列表
     * @param type $type
     */
    public function getList($limit = 10) {
        $sql = "select a.*,t.type,t.issinglepage from " . $this->tableName() . " as a left join tg_article_type as t on (a.type = t.id) limit " . $limit . "";
        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    /**
     * 获取文章分类By id
     * @return type
     */
    public function getById($id) {
        $res = $this->dbConnection->createCommand()
                ->select('*')
                ->from($this->tableName())
                ->where("id = " . $id)
                ->queryRow();
        return $res;
    }

    /**
     * 获取案例
     * @return type
     */
    public function getAnliById($id) {
        $sql = "select a.*,t.* from " . $this->tableName() . " as a left join tg_article_user as t on (a.id = t.aid) where a.id ='" . $id."'";
        return Yii::app()->db->createCommand($sql)->queryRow();
    }

    /**
     * 文章列表
     * @param type $type
     */
    public function getArticles($type, $limit = 10) {
        $sql = "select a.*,t.type from " . $this->tableName() . " as a left join tg_article_type as t on (a.type = t.id) where a.type ='" . $type . "' limit ".$limit;
        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    /**
     * 文章列表
     * @param type $type
     */
    public function getArticlesByTypeName($typename, $limit = 10, $top = "") {
        $where = "where t.type ='" . $typename."'";
        if($top !== "") {
            $where .= " and top='".$top."'";
        }
        $sql = "select a.*,t.type,u.* from " . $this->tableName() . " as a left join tg_article_type as t on (a.type = t.id) left join tg_article_user u on(u.aid=a.id) ".$where . " limit ".$limit;
        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    /**
     * 文章列表
     * @param type $type
     */
    public function getListCount($type) {
        $sql = "select count(*) from " . $this->tableName()." where type='".$type ."'";
        return Yii::app()->db->createCommand($sql)->queryScalar();
    }

    /**
     * 根据单页类名获取指定文章
     * @param type $type
     */
    public function getPageByTypeName($type) {
        $sql = "select a.*,t.type,t.issinglepage from " . $this->tableName() . " as a left join tg_article_type as t on (a.type = t.id) where t.type ='" . $type . "'";
        return Yii::app()->db->createCommand($sql)->queryRow();
    }

}