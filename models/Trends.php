<?php

/**
 * This is the model class for table "trends".
 *
 * The followings are the available columns in table 'trends':
 * @property string $tid
 * @property string $uid
 * @property string $content
 * @property string $image
 * @property string $datetime
 */
class Trends extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Trends the static model class
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
		return 'trends';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('datetime', 'required'),
			array('uid, datetime', 'length', 'max'=>11),
			array('image', 'length', 'max'=>100),
			array('content', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('tid, uid, content, image, datetime', 'safe', 'on'=>'search'),
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
			'tid' => 'Tid',
			'uid' => 'Uid',
			'content' => 'Content',
			'image' => 'Image',
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

		$criteria->compare('tid',$this->tid,true);
		$criteria->compare('uid',$this->uid,true);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('image',$this->image,true);
		$criteria->compare('datetime',$this->datetime,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	/**
	 * 发布动态
	 * @param int $uid
	 * @param str $content
	 * @param str $image
	 * @return number
	 */
	public function addTrends($uid,$area,$content,$image){
	    $this->dbConnection->createCommand()
	    ->insert($this->tableName(),
            array(
                'uid'=>$uid,
                'area'=>$area,
                'content'=>$content,
                'image' => $image,
                'datetime'=>time()
            ));
	    return $this->dbConnection->lastInsertID;
	}
	/**
	 * 添加评论
	 * @param int $uid
	 * @param int $tid
	 * @param str $nickname
	 * @param str $content
	 * @return number
	 */
	public function addComment($uid,$tid,$content){
	    return $this->dbConnection->createCommand()
	    ->insert('comment',
            array(
                'uid'=>$uid,
                'tid'=>$tid,
                'content'=>$content,
                'datetime'=>time()
            ));	    
	}
	/**
	 * 赞
	 * @param int $tid
	 * @param int $uid
	 * @param string $nickname
	 * @return number|boolean
	 */
	public function addPraise($tid,$uid){
	    $db = $this->dbConnection->createCommand();
	    $r = $db->select('count(id)')
    	        ->from('praise')
    	        ->where('uid = :uid and tid=:tid',array(':uid'=>$uid,':tid'=>$tid))
    	        ->queryScalar();
	    if($r==0){
	        return $db->insert('praise',
                        array(
                            'tid'=>$tid,
                            'uid'=>$uid,
                            'datetime'=>time()
                     ));
	    }
        return false;
	}
	public function deleteTrends($uid,$tid){
	    $tres = $this->dbConnection->createCommand()->delete($this->tableName(),'uid=:uid and tid=:tid', array(':uid'=>$uid,':tid'=>$tid));
	    if($tres){
	        $this->dbConnection->createCommand()->delete('comment','tid=:tid', array(':tid'=>$tid));
	        $this->dbConnection->createCommand()->delete('praise','tid=:tid', array(':tid'=>$tid));
	        return true;
	    }
	    return true;
	}
}