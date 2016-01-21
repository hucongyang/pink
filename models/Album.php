<?php

/**
 * This is the model class for table "album".
 *
 * The followings are the available columns in table 'album':
 * @property string $id
 * @property string $tid
 * @property string $uid
 * @property string $url
 * @property string $datetime
 */
class Album extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Album the static model class
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
		return 'album';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('tid, uid, datetime', 'length', 'max'=>11),
			array('url', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, tid, uid, url, datetime', 'safe', 'on'=>'search'),
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
			'tid' => 'Tid',
			'uid' => 'Uid',
			'url' => 'Url',
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
		$criteria->compare('tid',$this->tid,true);
		$criteria->compare('uid',$this->uid,true);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('datetime',$this->datetime,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	/**
	 * 相册 上传图片
	 * @param int $uid
	 * @param str $url
	 * @return number
	 */
	public function insertPhoto($uid,$url){
	    $res = $this->dbConnection->createCommand()
	    ->insert('album',
	            array(
	                    'uid'=>$uid,
	                    'url'=>$url,
	                    'datetime'=>time()
	            )
	    );
	    return $res;
	}
	/**
	 * 添加背景图片地址
	 * @param int $uid
	 * @param string $url
	 * @return number
	 */
	public function updateBgImg($uid,$url){
	    $res = $this->dbConnection->createCommand()
	    ->update('userinfo',
	            array(
	                    'bgimg'       => $url
	            ),
	            'UserId=:UserId', array(':UserId'=> $uid)
	    );
	    return $res;
	}
	/**
	 * 修改头像
	 * @param int $uid
	 * @param string $url
	 * @return number
	 */
	public function updateHeadImg($uid,$url){
	    $res = $this->dbConnection->createCommand()
	    ->update('userinfo',
	            array(
	                    'Photo'       => $url
	            ),
	            'UserId=:UserId', array(':UserId'=> $uid)
	    );
	    return $res;
	}
	/**
	 * 添加背景图片地址
	 * @param int $uid
	 * @param string $url
	 * @return number
	 */
	public function updateSound($uid,$url,$time){
	    $res = $this->dbConnection->createCommand()
	    ->update('userinfo',
	            array(
	                    'stime'       => $time,
	                    'Voice'       => $url
	            ),
	            'UserId=:UserId', array(':UserId'=> $uid)
	    );
	    return $res;
	}
	/**
	 * 获取金币总数
	 * @param int $uid
	 * @return Ambigous <mixed, string, unknown>
	 */
	public function getCoins($uid){
	    $sql = "select Coins+FreeCoins+WinCoins as n from usercoins where UserId =".$uid;
	    return $this->dbConnection->createCommand($sql)->queryScalar();
	}
	/**
	 * 修改昵称
	 * @param int $uid
	 * @param string $url
	 * @return number
	 */
	public function updateNickName($uid,$name){
	    $res = $this->dbConnection->createCommand()
	    ->update('user',
	            array(
	                    'NickName'       => $name
	            ),
	            'UserId=:UserId', array(':UserId'=> $uid)
	    );
	    return $res;
	}
	/**
	 * 修改性别
	 * @param int $uid
	 * @param string $url
	 * @return number
	 */
	public function updateSex($uid,$sex){
	    $res = $this->dbConnection->createCommand()
	    ->update('user',
	            array(
	                    'Sex'       => $sex
	            ),
	            'UserId=:UserId', array(':UserId'=> $uid)
	    );
	    return $res;
	}
	/**
	 * 修改年龄
	 * @param int $uid
	 * @param string $url
	 * @return number
	 */
	public function updateAge($uid,$age){
	    $res = $this->dbConnection->createCommand()
	    ->update('user',
	            array(
	                    'Age'       => $age
	            ),
	            'UserId=:UserId', array(':UserId'=> $uid)
	    );
	    return $res;
	}
	/**
	 * 修改签名
	 * @param int $uid
	 * @param string $url
	 * @return number
	 */
	public function updateSign($uid,$sign){
	    $res = $this->dbConnection->createCommand()
	    ->update('userinfo',
	            array(
	                    'Sign'       => $sign
	            ),
	            'UserId=:UserId', array(':UserId'=> $uid)
	    );
	    return $res;
	}
	/**
	 * 获取相册列表
	 * @param int $uid
	 * @param int $page
	 * @param int $num
	 * @return Ambigous <multitype:, mixed>
	 */
	public function getAlbum($uid,$page,$num){
	    $limit = ($page - 1) * $num;
	    return $this->dbConnection->createCommand()
    	    ->select('*')
    	    ->from('album')
    	    ->where('uid = :uid ',array(':uid'=>$uid))
    	    ->order('datetime DESC')
    	    ->limit($num,$limit)
    	    ->queryAll();
	}
	/**
	 * 用户反馈
	 * @param int $uid
	 * @param str $content
	 * @return number
	 */
	public function feedBack($uid,$content){
	    $res = $this->dbConnection->createCommand()
	    ->insert('feedback',
            array(
                'uid'=>$uid,
                'content'=>$content,
                'datetime'=>time()
            )
	    );
	    return $res;	    
	}
	/**
	 * 检查版本
	 * @param str $version
	 * @return Ambigous <mixed, string, unknown>
	 */
	public function getVersion($version){

        $res = $this->dbConnection->createCommand()
	        ->select('*')
	        ->from('appinfo')
	        ->order('datetime DESC')
	        ->limit(1)
	        ->queryRow();
	    if($res['version'] != $version) return $res;
	    else return '';
	}
}