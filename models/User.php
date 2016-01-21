<?php

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property integer $UserId
 * @property string $Mobile
 * @property string $Password
 * @property string $NickName
 * @property integer $Sex
 * @property integer $National
 * @property string $RegisterTime
 * @property string $LastLoginTime
 * @property integer $Status
 */
class User extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return User the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'user';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('Mobile, Password, RegisterTime, LastLoginTime', 'required'),
            array('Sex, National, Status', 'numerical', 'integerOnly' => true),
            array('Mobile, Password, NickName', 'length', 'max' => 20),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('UserId, Mobile, Password, NickName, Sex, National, RegisterTime, LastLoginTime, Status', 'safe', 'on' => 'search'),
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
            'UserId' => 'User',
            'Mobile' => 'Mobile',
            'Password' => 'Password',
            'NickName' => 'Nick Name',
            'Sex' => 'Sex',
            'National' => 'National',
            'RegisterTime' => 'Register Time',
            'LastLoginTime' => 'Last Login Time',
            'Status' => 'Status',
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

        $criteria->compare('UserId', $this->UserId);
        $criteria->compare('Mobile', $this->Mobile, true);
        $criteria->compare('Password', $this->Password, true);
        $criteria->compare('NickName', $this->NickName, true);
        $criteria->compare('Sex', $this->Sex);
        $criteria->compare('National', $this->National);
        $criteria->compare('RegisterTime', $this->RegisterTime, true);
        $criteria->compare('LastLoginTime', $this->LastLoginTime, true);
        $criteria->compare('Status', $this->Status);

        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }

    /**
     *  获取插入后用户id
     * @param  $mobile
     * @return uid
     */
    public function create($mobile) {
        $this->dbConnection->createCommand()->insert($this->tableName(), array('Mobile' => $mobile, 'RegisterTime' => time()));
        return Yii::app()->db->getLastInsertID();
    }

    public function add($uid) {
        $res = array();
        $res['userinfo'] = $this->dbConnection->createCommand()->insert('userinfo', array('UserId' => $uid));
        $res['usercoins'] = $this->dbConnection->createCommand()->insert('usercoins', array('UserId' => $uid));
        $res['coord'] = $this->dbConnection->createCommand()->insert('coord', array('uid' => $uid, 'lasttime' => time()));
        $res['visit'] = $this->dbConnection->createCommand()->insert('visit', array('uid' => $uid, 'num' => 0, 'datetime' => time()));
        return $res;
    }

    /**
     *  插入验证码信息
     * @param varchar $mobile
     * @param int $postcode
     * @param int $time
     * @return int
     */
    public function insertPostCode($mobile, $postcode, $time) {
        $id = $this->dbConnection->createCommand()
                ->select('Id')
                ->from('verifycode')
                ->where('Mobile = :Mobile', array(':Mobile' => $mobile))
                ->queryScalar();
        if ($id > 0) {
            $res = $this->dbConnection->createCommand()
                ->update('verifycode', 
                array(
                    'VerifyCode' => $postcode,
                    'ValidTime'  => $time,
                ), 'Mobile=:Mobile', array(':Mobile' => $mobile));
        } else {
            $res = $this->dbConnection->createCommand()
                ->insert('verifycode', 
                array(
                    'Mobile'     => $mobile,
                    'VerifyCode' => $postcode,
                    'ValidTime'  => $time
                ));
        }
        return $res;
    }

    /**
     *  删除已经使用的验证码
     * @param str $mobile
     */
    public function deleteCode($mobile) {
        $this->dbConnection->createCommand()->delete('verifycode', 'Mobile=:Mobile', array(':Mobile' => $mobile));
    }

    /**
     *  获取用户id
     * @param string $mobile
     * @return id <tring>
     */
    public function isMobile($mobile) {
        return $this->dbConnection->createCommand()
                        ->select('UserId')
                        ->from($this->tableName())
                        ->where('Mobile = :Mobile', array(':Mobile' => $mobile))
                        ->queryScalar();
    }
    /**
     *  获取验证码信息
     * @param  $mobile
     * @return array
     */
    public function getVerifyInfo($mobile) {
        return $this->dbConnection->createCommand()
                        ->select('*')
                        ->from('verifycode')
                        ->where('Mobile = :Mobile', array(':Mobile' => $mobile))
                        ->queryRow();
    }

    /**
     *  更新用户信息, 添加当前位置
     * @param array $params
     * @return multitype:number
     */
    public function updateUser($params) {
        $res = array();
        $res['user'] = $this->dbConnection->createCommand()
        ->update($this->tableName(), 
            array(
                'NickName' => $params['nickname'],
                'Sex' => $params['sex'],
                'Age' => $params['age'],
                'LastLoginTime' => time(),
                'Status' => 0,
                'iostoken' => $params['iostoken']
            ), 'UserId=:UserId', array(':UserId' => $params['uid']));
        $res['coord'] = $this->dbConnection->createCommand()
        ->update('coord',
            array(
                'uid' => $params['uid'],
                'lng' => $params['lng'],
                'lat' => $params['lat'],
                'lasttime' => time()
            ), 'uid=:UserId', array(':UserId' => $params['uid']));
        $res['userinfo'] = $this->dbConnection->createCommand()
        ->update('userinfo', 
           array(
                'Photo' => $params['pic']
            ), 'UserId=:UserId', array(':UserId' => $params['uid']));
        $res['usercoins'] = $this->dbConnection->createCommand()
        ->update('usercoins', 
            array(
                'FreeCoins' => $params['defaultcoins'],
                'datetime' => time()
        ), 'UserId=:UserId', array(':UserId' => $params['uid']));
        if ($res['usercoins'] > 0) {
            $this->dbConnection->createCommand()
            ->insert('coinshistory', 
                array(
                    'Uid'      => $params['uid'],
                    'Coins'    => $params['defaultcoins'],
                    'Type'     => 1,
                    'datetime' => time()
                ));
        }
        return $res;
    }

    /**
     * 添加token
     * @param string $token
     * @param int $time
     * @return number
     */
    public function insertToken($uid, $token, $time) {
        return $this->dbConnection->createCommand()
            ->insert('token', array(
                'uid' => $uid,
                'value' => $token,
                'time' => $time
            ));
    }

    public function updateToken($uid, $token, $time) {
        $this->dbConnection->createCommand()
            ->update('token', array(
                'value' => $token,
                'time' => $time
            ), 'uid=:uid', array(':uid' => $uid));
    }

    public function haveToken($uid) {
        return $this->dbConnection->createCommand()
            ->select('*')
            ->from('token')
            ->where('uid = :uid ', array(':uid' => $uid))
            ->queryRow();
    }

    /**
     * 返回Token信息
     * @param string $token
     * @return array
     */
    public function isToken($token) {
        return $this->dbConnection->createCommand()
            ->select('*')
            ->from('token')
            ->where('value = :Token', array(':Token' => $token))
            ->queryRow();
    }

    /**
     *  删除已经过期的Token
     * @param str $mobile
     */
    public function deleteToken($uid,$token) {
        $this->dbConnection->createCommand()->delete('token', 'value=:token', array(':token' => $token));
        $this->dbConnection->createCommand()->update($this->tableName(), array('LastLoginTime'=>time()), 'UserId=:uid', array(':uid' => $uid));
    }

    /**
     *  登录成功 修改用户状态
     */
    public function setStatus($uid) {
        $user = $this->dbConnection->createCommand()->update('user', 
            array('ustatus' => 1), 
                'UserId=:uid', array(':uid' => $uid));
        if ($user) {
            $info = $this->dbConnection->createCommand()->update('userinfo', 
                array('ustatus' => 1), 
                    'UserId=:uid', array(':uid' => $uid)
            );
            $coins = $this->dbConnection->createCommand()->update('usercoins', 
                array('ustatus' => 1), 
                    'UserId=:uid', array(':uid' => $uid)
            );
            if ($info && $coins) return $info;
        }
    }

    /**
     *  退出登录 修改用户状态
     */
    public function loginOut($uid) {
        return $this->dbConnection->createCommand()->delete('token', 'uid=:uid', array(':uid' => $uid));
    }

    /**
     *  离线状态(免打扰)
     */
    public function offLine($uid) {
        $user = $this->dbConnection->createCommand()
            ->update($this->tableName(), 
                array(
                    'ustatus' => 2
                ), 
                'UserId=:uid', array(':uid' => $uid));
        if ($user) {
            $info = $this->dbConnection->createCommand()->update('userinfo', array(
                    'ustatus' => 2
                ), 'UserId=:uid', array(':uid' => $uid));
            $coins = $this->dbConnection->createCommand()->update('usercoins', array(
                    'ustatus' => 2
                ), 'UserId=:uid', array(':uid' => $uid));
            if ($info && $coins) return $info;
        }
    }
    /**
     *  在线状态
     */
    public function onLine($uid) {
       $user = $this->dbConnection->createCommand()
           ->update($this->tableName(),
                    array(
                        'ustatus' => 1
                    ),'UserId=:uid', array(':uid' => $uid));
        if ($user) {
            $info = $this->dbConnection->createCommand()
                ->update('userinfo', array(
                    'ustatus' => 1
                ), 'UserId=:uid', array(':uid' => $uid));
            $coins = $this->dbConnection->createCommand()
                ->update('usercoins', array(
                    'ustatus' => 1
                ), 'UserId=:uid', array(':uid' => $uid));
            if ($info && $coins)
                return true;
        }
    }
    /**
     * 检查密码是否存在
     * @param int $uid
     * @return string password
     */
    public function isSetPassWord($uid) {
        return $this->dbConnection->createCommand()
                ->select('Password')
                ->from($this->tableName())
                ->where('UserId=:uid', array(':uid' => $uid))
                ->queryScalar();
    }
    /**
     * 设置密码/修改密码
     * @param int $uid
     * @param string $newpass
     * @return number
     */
    public function changePassword($uid, $password) {
        return $this->dbConnection->createCommand()
                        ->update($this->tableName(), array(
                            'Password' => $password
                                ), 'UserId=:uid', array(':uid' => $uid)
        );
    }

    /**
     * 获取用户id
     * @param int $mobile
     * @return UserId
     */
    public function getUserId($mobile) {
        return $this->dbConnection->createCommand()
                        ->select('UserId')
                        ->from($this->tableName())
                        ->where('Mobile=:mobile', array(':mobile' => $mobile))
                        ->queryScalar();
    }

    /**
     * 获取用户手机号码
     * @param int $uid
     * @return Ambigous <mixed, string, unknown>
     */
    public function getUserMobile($uid) {
        return $this->dbConnection->createCommand()
                        ->select('Mobile')
                        ->from($this->tableName())
                        ->where('UserId=:uid', array(':uid' => $uid))
                        ->queryScalar();
    }

    /**
     * 判断用户名和密码是否正确
     * @param int $mobile
     * @param str $password
     */
    public function Login($mobile) {
        return $this->dbConnection->createCommand()
                        ->select('Password')
                        ->from($this->tableName())
                        ->where('Mobile=:mobile', array(':mobile' => $mobile))
                        ->queryScalar();
    }

    /**
     * 查看用户状态
     * @param int $mobile
     * @param str $password
     */
    public function userStatus($uid) {
        return $this->dbConnection->createCommand()
                        ->select('ustatus')
                        ->from($this->tableName())
                        ->where('UserId=:uid', array(':uid' => $uid))
                        ->queryScalar();
    }

    /**
     *  登录成功 修改状态/修改iostoken
     */
    public function setIosToken($uid, $token) {
        $user = $this->dbConnection->createCommand()
                ->update($this->tableName(), array(
                    'iostoken' => $token,
                    'LastLoginTime'=>time()
                ), 'UserId=:uid', array(':uid' => $uid));
    }

    /************
     * 消息推送 *
     ************/

    /**
     * 获取IosToken
     * @param int $uid
     * @return Ambigous <mixed, string, unknown>
     */
    public function getIosToken($uid) {
        return $this->dbConnection->createCommand()
            ->select('iostoken')
            ->from($this->tableName())
            ->where('UserId=:uid', array(':uid' => $uid))
            ->queryScalar();
    }

    /**
     * 添加推送信息
     * @param int $uid
     * @param str $token
     */
    public function setBadge($uid, $token) {
        $db = $this->dbConnection->createCommand();
        $res = $db->select('badge')
                ->from('pushmsg')
                ->where('uid=:uid AND iostoken=:token', array(':uid' => $uid, ':token' => $token))
                ->queryScalar();
        if (empty($res)) {
            $db->insert('token', array(
                'uid' => $uid,
                'iostoken' => $token,
                'badge' => 1
            ));
        } else {
            $badge = $res + 1;
            $db->update('pushmsg', array(
                'badge' => $badge
                    ), 'uid=:uid AND iostoken=:token', array(':uid' => $uid, ':token' => $token));
        }
    }

    /**
     * 获取push总数
     * @param int $uid
     * @param str $token
     */
    public function getPushmsg($uid, $token) {
        return $this->dbConnection->createCommand()
                        ->select('*')
                        ->from('pushmsg')
                        ->where('uid=:uid', array(':uid' => $uid))
                        ->queryRow();
    }

    /**
     * 获取用户金币
     * @param int $uid
     * @return coin
     */
    public function getCoinByUid($uid) {
        return $this->dbConnection->createCommand()
                        ->select('(Coins+FreeCoins+WinCoins) as coins')
                        ->from("usercoins")
                        ->where('UserId=:UserId', array(':UserId' => $uid))
                        ->queryRow();
    }
    /**
     * 获取赚取的金币
     * @param int $uid
     * @return coin
     */
    public function getWinCoinByUid($uid) {
        return $this->dbConnection->createCommand()
                        ->select('WinCoins')
                        ->from("usercoins")
                        ->where('UserId=:UserId', array(':UserId' => $uid))
                        ->queryScalar();
    }
    
    /**
     * 更新用户金币信息
     * @param int $uid
     * @param array $data
     * @return number
     */
    public function updateCoinInfo($uid, $data) {
        if(!$uid || empty($data)) {
            return null;
        }
        $res = $this->dbConnection->createCommand()
                ->update('usercoins', $data, 'UserId=:UserId', array(':UserId' => $uid)
        );
        return $res;
    }
}