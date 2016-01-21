<?php

/**
 * This is the model class for table "userinfo".
 *
 * The followings are the available columns in table 'userinfo':
 * @property integer $UserId
 * @property string $Sign
 * @property integer $Evaluate
 * @property integer $Price
 * @property integer $TotalCallSeonds
 * @property string $Photo
 * @property string $bgimg
 * @property string $Voice
 * @property integer $CallStatus
 */
class UserInfo extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return UserInfo the static model class
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
		return 'userinfo';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('UserId', 'required'),
			array('UserId, Evaluate, Price, TotalCallSeonds, CallStatus', 'numerical', 'integerOnly'=>true),
			array('Sign, Photo, bgimg, Voice', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('UserId, Sign, Evaluate, Price, TotalCallSeonds, Photo, bgimg, Voice, CallStatus', 'safe', 'on'=>'search'),
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
			'Sign' => 'Sign',
			'Evaluate' => 'Evaluate',
			'Price' => 'Price',
			'TotalCallSeonds' => 'Total Call Seonds',
			'Photo' => 'Photo',
			'bgimg' => 'Bgimg',
			'Voice' => 'Voice',
			'CallStatus' => 'Call Status',
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
		$criteria->compare('Sign',$this->Sign,true);
		$criteria->compare('Evaluate',$this->Evaluate);
		$criteria->compare('Price',$this->Price);
		$criteria->compare('TotalCallSeonds',$this->TotalCallSeonds);
		$criteria->compare('Photo',$this->Photo,true);
		$criteria->compare('bgimg',$this->bgimg,true);
		$criteria->compare('Voice',$this->Voice,true);
		$criteria->compare('CallStatus',$this->CallStatus);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	public function getProxyIds($uid) {
	    $sql = "select ag.uid from tg_premote as tg left join agent_relation as ag on tg.uid = ag.aid where tuid =".$uid;
	    return Yii::app()->db->createCommand($sql)->queryAll();
	}
	
	
	public function getCoord($seach,$latmin,$latmax,$lngmin,$lngmax,$limit,$num,$uid){
	    switch ($seach) {
	        case 0 :
	            $sql = "select c.uid,c.lat,c.lng from coord as c left join user as u on c.uid = u.UserId where c.lat >=" .
	                    $latmin . " and c.lat <=" . $latmax . " and c.lng >=" . $lngmin ." and c.lng <=" . $lngmax . " and u.Sex = 0 and u.ustatus = 1 and c.uid not in (select uid from userrelation where ruid = ".$uid." and relation = 2 ) ".
	                            " limit " . $limit . "," .$num;
	                    break;
	        case 1 :
	            $sql = "select c.uid,c.lat,c.lng from coord as c left join user as u on c.uid = u.UserId where c.lat >=" .
	                    $latmin . " and c.lat <=" . $latmax . " and c.lng >=" . $lngmin ." and c.lng <=" . $lngmax . " and u.Sex = 1 and u.ustatus = 1 and c.uid not in (select uid from userrelation where ruid = ".$uid." and relation = 2 )".
	                            " limit " . $limit . "," . $num;
	                    break;
	        case 2 :
	            $sql = "select c.uid,c.lat,c.lng from coord as c left join user as u on c.uid = u.UserId where c.lat >=" .
	                    $latmin . "and c.lat <=" . $latmax . " and c.lng >=" . $lngmin ." and c.lng <=" . $lngmax . " and u.ustatus = 1 and c.uid not in (select uid from userrelation where ruid = ".$uid." and relation = 2 )".
	                            " limit " . $limit . "," . $num;
	                    break;
	    }
	    return Yii::app()->db->createCommand($sql)->queryAll();	    
	}
    /**
     * 返回附近用户ids
     * @param array $search
     * @return number|array ids
     */
	public function getUserIds($search){
	    $limit = $search['num'] * ($search['page'] - 1);
	    switch ($search['order']) {
	       
	        case 1 :
	            $gap = 0.001;
	            $search['lngMax'] = $search['lng'] + $gap;
	            $search['lngMin'] = $search['lng'] - $gap;
	            $search['latMax'] = $search['lat'] + $gap;
	            $search['latMin'] = $search['lat'] - $gap;
	            $res = $this->getCoord($search['search'], $search['latMin'], $search['latMax'], $search['lngMin'], $search['lngMax'], $limit, $search['num'], $search['uid']);
	            $num = count($res);
	            while($num < $search['num'] && $gap <= 0.1) {
	                $gap = $gap + 0.001;
	                $search['lngMax'] = $search['lng'] + $gap;
	                $search['lngMin'] = $search['lng'] - $gap;
	                $search['latMax'] = $search['lat'] + $gap;
	                $search['latMin'] = $search['lat'] - $gap;
	                $res = $this->getCoord($search['search'], $search['latMin'], $search['latMax'], $search['lngMin'], $search['lngMax'], $limit, $search['num'], $search['uid']);
	                $num = count($res);
	            }
	            if(!empty($res)){
    	            foreach ($res as $k=>$v){
    	                $res[$k]['coord'] = $this->GetDistance($search['lat'], $search['lng'], $v['lat'], $v['lng']);
    	                unset($res[$k]['lat']);
    	                unset($res[$k]['lng']);
    	            }
    	            foreach ($res as $k=>$v) {
    	                $coord[$k] = $v['coord'];
    	            }
    	            array_multisort($coord,SORT_ASC,SORT_NUMERIC,$res);
	            }
	            return $res;
	            break;
	        case 2 :
	            switch ($search['search']) {
                    case 0 :
                        $sql = 'select uc.UserId,uc.Coins+uc.FreeCoins+uc.WinCoins as num from usercoins as uc left join user as u on uc.UserId = u.UserId where u.ustatus = 1 and u.Sex = 0 and uc.UserId not in (select uid from userrelation where ruid = '.$search['uid'].' and relation = 2 ) order by num desc limit ' . $limit . ',' . $search['num'];
	                    break;
                    case 1 :
                        $sql = 'select uc.UserId,uc.Coins+uc.FreeCoins+uc.WinCoins as num from usercoins as uc left join user as u on uc.UserId = u.UserId where u.ustatus = 1 and u.Sex = 1 and uc.UserId not in (select uid from userrelation where ruid = '.$search['uid'].' and relation = 2 ) order by num desc limit ' . $limit . ',' . $search['num'];
                        break;
                    case 2 :
	                    $sql = 'select UserId,Coins+FreeCoins+WinCoins as num from usercoins where ustatus = 1  and UserId not in (select uid from userrelation where ruid = '.$search['uid'].' and relation = 2 ) order by num desc limit ' . $limit . ',' . $search['num'];
	                    break;
	             }
	            $res = Yii::app()->db->createCommand($sql)->queryAll();
	            foreach ($res as $k=>$v){
	                $res[$k]['uid'] = $v['UserId'];
	                unset($res[$k]['UserId']);
	                unset($res[$k]['num']);
	            }
	            return $res;
	            break;
	        case 3 :
	            switch ($search['search']) {
	                case 0 :
	                    $sql = 'select ui.UserId,ui.popu+ui.addpopu as popu from userinfo as ui left join user as u on ui.UserId = u.UserId where ui.ustatus = 1 and u.Sex = 0 and ui.UserId not in (select uid from userrelation where ruid = '.$search['uid'].' and relation = 2 ) order by ui.popu desc limit ' . $limit . ',' . $search['num'];
	                    break;
	                case 1 :
	                    $sql = 'select ui.UserId,ui.popu+ui.addpopu as popu from userinfo as ui left join user as u on ui.UserId = u.UserId where ui.ustatus = 1 and u.Sex = 1 and ui.UserId not in (select uid from userrelation where ruid = '.$search['uid'].' and relation = 2 ) order by ui.popu desc limit ' . $limit . ',' . $search['num'];
	                    break;
	                case 2 :
	                    $sql = 'select UserId,popu+addpopu as popu from userinfo where ustatus = 1  and UserId not in (select uid from userrelation where ruid = '.$search['uid'].' and relation = 2 ) order by popu desc limit ' . $limit . ',' . $search['num'];
	                    break;
	            }
	            $res = Yii::app()->db->createCommand($sql)->queryAll();
	            foreach ($res as $k=>$v){
	                $res[$k]['uid'] = $v['UserId'];
	                unset($res[$k]['UserId']);
	            }
	            return $res;
	            break;
	        case 4 :
	            switch ($search['search']) {
	                case 0 :
	                    $sql = 'select u.UserId,u.RegisterTime from user as u left join userinfo as ui on u.UserId = ui.UserId where u.ustatus = 1 and u.Sex = 0 and u.UserId not in (select uid from userrelation where ruid = '.$search['uid'].' and relation = 2 ) order by u.RegisterTime desc limit ' . $limit . ',' . $search['num'];
	                    break;
	                case 1 :
	                    $sql = 'select u.UserId,u.RegisterTime from user as u left join userinfo as ui on u.UserId = ui.UserId where u.ustatus = 1 and u.Sex = 1 and u.UserId not in (select uid from userrelation where ruid = '.$search['uid'].' and relation = 2 ) order by u.RegisterTime desc limit ' . $limit . ',' . $search['num'];
	                    break;
	                case 2 :
	                    $sql = 'select UserId,RegisterTime from user where ustatus = 1  and UserId not in (select uid from userrelation where ruid = '.$search['uid'].' and relation = 2 ) order by RegisterTime desc limit ' . $limit . ',' . $search['num'];
	                    break;
	            }
	            $res = Yii::app()->db->createCommand($sql)->queryAll();
	            foreach ($res as $k=>$v){
	                $res[$k]['uid'] = $v['UserId'];
	                unset($res[$k]['UserId']);
	            }
	            return $res;
	            break;
	    }
	}
	 
	/**
	 * 获取用户信息
	 * @param array $ids
	 * @param array $search
	 * @return array
	 */
	public function getUserInfo($ids,$search){
        switch ($search['order']) {
            case 1 :
	            $sql = "select u.UserId,u.NickName,u.Age,u.Sex,info.Sign,info.Price,info.Photo,info.popu+info.addpopu as popu,coins.Coins+coins.FreeCoins+coins.WinCoins as coins from user as u left join userinfo as info on u.UserId = info.UserId left join usercoins as coins on u.UserId = coins.UserId  where u.UserId = $ids";
	            $res = Yii::app()->db->createCommand($sql)->queryRow();
	            break;
            case 2 :
                $sql = "select u.UserId,u.NickName,u.Age,u.Sex,info.Sign,info.Price,info.Photo,info.popu+info.addpopu as popu,coins.Coins+coins.FreeCoins+coins.WinCoins as coins,c.lat,c.lng from user as u left join userinfo as info on u.UserId = info.UserId left join usercoins as coins on u.UserId = coins.UserId left join coord as c on u.UserId = c.uid where u.UserId = $ids";
                $res = Yii::app()->db->createCommand($sql)->queryRow();
                if($res) {
                    if($search['lat'] != 0 && $search['lng'] != 0) {
                        $res['coord'] = $this->GetDistance($search['lat'], $search['lng'], $res['lat'], $res['lng']);
                        unset($res['lat']);
                        unset($res['lng']);
                    } else {
                        $res['coord'] = 0;
                    }
                }
                break;
            case 3 :
                $sql = "select u.UserId,u.NickName,u.Age,u.Sex,info.Sign,info.Price,info.Photo,info.popu+info.addpopu as popu,coins.Coins+coins.FreeCoins+coins.WinCoins as coins,c.lat,c.lng from user as u left join userinfo as info on u.UserId = info.UserId left join usercoins as coins on u.UserId = coins.UserId left join coord as c on u.UserId = c.uid  where u.UserId = $ids";
                $res = Yii::app()->db->createCommand($sql)->queryRow();
                if($res) {
                    if($search['lat'] != 0 && $search['lng'] != 0) $res['coord'] = $this->GetDistance($search['lat'], $search['lng'], $res['lat'], $res['lng']);
                    else $res['coord'] = 0;
                }
                unset($res['lat']);
                unset($res['lng']);
                break;
            case 4 :
                $sql = "select u.UserId,u.NickName,u.Age,u.Sex,info.Sign,info.Price,info.Photo,info.popu+info.addpopu as popu,coins.Coins+coins.FreeCoins+coins.WinCoins as coins,c.lat,c.lng from user as u left join userinfo as info on u.UserId = info.UserId left join usercoins as coins on u.UserId = coins.UserId left join coord as c on u.UserId = c.uid  where u.UserId = $ids";
                $res = Yii::app()->db->createCommand($sql)->queryRow();
                if($res) {
                    if($search['lat'] != 0 && $search['lng'] != 0) $res['coord'] = $this->GetDistance($search['lat'], $search['lng'], $res['lat'], $res['lng']);
                    else $res['coord'] = 0;
                }
                unset($res['lat']);
                unset($res['lng']);
                break;
        }
	    return $res;
	}
	/**
	 * 更新用户坐标
	 * @param int $uid
	 * @param float $lat
	 * @param float $lng
	 */
	public function changeCoord($uid,$lat,$lng){
	    $this->dbConnection->createCommand()
	    ->update('coord', array(
	            'lng' => $lng,
	            'lat' => $lat,
	            'lasttime' => time()
	        ), 'uid=:uid', array(':uid' =>$uid));
	}	
	function rad($d)
	{
	    return $d * 3.1415926535898 / 180.0;
	}
	//计算两点坐标之间的距离 单位 千米
	function GetDistance($latUser, $lngUser, $latToUser, $lngToUser)
	{
	    $EARTH_RADIUS = 6378.137;
	    $radLatUser = $this->rad($latUser);
	    $radLatToUser = $this->rad($latToUser);
	    $a = $radLatUser - $radLatToUser;
	    $b = $this->rad($lngUser) - $this->rad($lngToUser);
	    $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLatUser)*cos($radLatToUser)*pow(sin($b/2),2)));
	    $s = $s * $EARTH_RADIUS;
	    $s = round($s * 10000) / 10000;
	    return $s;
	}
	
	/**
	 * 获取用户详细信息
	 * @param int $uid
	 * @param int $ruid
	 * @return array
	 */
	public function UserInformation($uid,$ruid,$page,$limit){
	    if($page ==1) {
    	    if($uid != 0){
    	        $usersql = 'select lat,lng from coord where uid='.$uid;
    	        $user = Yii::app()->db->createCommand($usersql)->queryRow();
    	    }
    	    else{
    	        $user = null;
    	    }
    	    $resql = 'select lat,lng from coord where uid='.$ruid;
    	    $re = Yii::app()->db->createCommand($resql)->queryRow();
    	    if(($user || $uid == 0) && $re) {
    	        $sql = 'select u.UserId,u.NickName,u.Sex,u.Age,ui.Sign,ui.Price,ui.Photo,ui.bgimg,ui.stime,ui.Voice,ui.popu+ui.addpopu as popu,uc.Coins+FreeCoins+uc.WinCoins as coins '.
                	        'from user as u left join userinfo as ui on u.UserId = ui.UserId '.
        	                       'left join usercoins as uc on u.UserId = uc.UserId where u.UserId = '.$ruid;
    	        $res = Yii::app()->db->createCommand($sql)->queryRow();
    	        $res['retype'] = $this->getReType($uid, $ruid);
    	        if($uid == 0){
    	            $res['coord'] = -1;// 未知距离
    	        }
    	        else{
    	            $res['coord'] = $this->GetDistance($user['lat'], $user['lng'], $re['lat'], $re['lng']);
    	        }
    	        $trendsql = 'select * from trends where uid = ' . $ruid . ' order by datetime desc' . ' limit 0,' . $limit;
    	        
    	        $trends = Yii::app()->db->createCommand($trendsql)->queryAll();
    	        foreach ($trends as $key => $value) {
    	            $com = 'select * from comment where tid = '.$value['tid'].' order by datetime desc';
    	            $trends[$key]['comment'] =  Yii::app()->db->createCommand($com)->queryAll();
    	            foreach($trends[$key]['comment'] as $k=>$v){
    	                $trends[$key]['comment'][$k]['nickname'] = $this->getNickname($trends[$key]['comment'][$k]['uid']);
    	            }
    	            $praise = 'select uid from praise where tid = '.$value['tid'].' order by datetime desc limit 5';
    	            $num = 'select count(uid) from praise where tid = '.$value['tid'];
    	            $trends[$key]['praise'] =  Yii::app()->db->createCommand($praise)->queryAll();
    	            $trends[$key]['praise_num'] =  Yii::app()->db->createCommand($num)->queryScalar();
    	            foreach($trends[$key]['praise'] as $k=>$v){
    	                $trends[$key]['praise'][$k]['nickname'] = $this->getNickname($trends[$key]['praise'][$k]['uid']);
    	            }
    	        }
    	        $res['trends'] = $trends;
    	        $res['relation'] = $this->getRelationNum($ruid);
    	    }
	    }else{
	        $p = $limit * ($page -1);
	        $trendsql = 'select * from trends where uid = ' . $ruid . ' order by datetime desc' . ' limit ' . $p . ',' . $limit;
	        $res = Yii::app()->db->createCommand($trendsql)->queryAll();
	        foreach ($res as $key => $value) {
	            $com = 'select * from comment where tid = '.$value['tid'].' order by datetime desc';
	            $res[$key]['comment'] =  Yii::app()->db->createCommand($com)->queryAll();
	            foreach($res[$key]['comment'] as $k=>$v){
	                $res[$key]['comment'][$k]['nickname'] = $this->getNickname($res[$key]['comment'][$k]['uid']);
	            }
	            $praise = 'select uid from praise where tid = '.$value['tid'].' order by datetime desc limit 5';
	            $num = 'select count(uid) from praise where tid = '.$value['tid'];
	            $res[$key]['praise'] =  Yii::app()->db->createCommand($praise)->queryAll();
	            $res[$key]['praise_num'] =  Yii::app()->db->createCommand($num)->queryScalar();
	            foreach($res[$key]['praise'] as $k=>$v){
	                $res[$key]['praise'][$k]['nickname'] = $this->getNickname($res[$key]['praise'][$k]['uid']);
	            }
	        }
	    }
	    return $res;
	}
	public function relationTrends($uid,$page,$limit){
	    $usersql = 'select ruid from userrelation where uid='.$uid;
	    $rids = Yii::app()->db->createCommand($usersql)->queryColumn();
	    $ids = implode(',', $rids);
	    $p = $limit * ($page -1);
	    $trendsql = 'select * from trends where uid in (' . $ids . ') order by datetime desc limit ' . $p . ',' . $limit;
	    $res = Yii::app()->db->createCommand($trendsql)->queryAll();
	    foreach ($res as $key => $value) {
	        $com = 'select * from comment where tid = '.$value['tid'].' order by datetime desc';
	        $res[$key]['comment'] =  Yii::app()->db->createCommand($com)->queryAll();
	        foreach($res[$key]['comment'] as $k=>$v){
	            $res[$key]['comment'][$k]['nickname'] = $this->getNickname($res[$key]['comment'][$k]['uid']);
	        }
	        $praise = 'select uid from praise where tid = '.$value['tid'].' order by datetime desc limit 5';
	        $num = 'select count(uid) from praise where tid = '.$value['tid'];
	        $res[$key]['praise'] =  Yii::app()->db->createCommand($praise)->queryAll();
	        $res[$key]['praise_num'] =  Yii::app()->db->createCommand($num)->queryScalar();
	        foreach($res[$key]['praise'] as $k=>$v){
	            $res[$key]['praise'][$k]['nickname'] = $this->getNickname($res[$key]['praise'][$k]['uid']);
	        }
	        $res[$key]['nickname'] = $this->getNickname($value['uid']);
	        $photo = 'select Photo from userinfo where UserId='.$value['uid'];
	        $res[$key]['photo'] =  Yii::app()->db->createCommand($photo)->queryScalar();
	    }
	    return $res;
	}
	
	/**
	 * 记录查看评论时间
	 * @param int $uid
	 */
	public function insertCommentTime($uid){
	    $res = $this->dbConnection->createCommand()
    	    ->select('count(id)')
    	    ->from('commenttime')
    	    ->where('uid = :uid ',array(':uid'=>$uid))
    	    ->queryScalar();
        if($res==0) {
            $this->dbConnection->createCommand()
                ->insert('commenttime',
                    array(
                        'uid'=>$uid,
                        'times'=>time()
                    ));
        }
    }
	/**
	 * 更新查看评论时间
	 * @param int $uid
	 */
	public function updateCommentTime($uid){
	    $res = $this->dbConnection->createCommand()
    	    ->select('count(id)')
    	    ->from('commenttime')
    	    ->where('uid = :uid ',array(':uid'=>$uid))
    	    ->queryScalar();
	    if($res) {
	        $this->dbConnection->createCommand()
	            ->update('commenttime',
	                array(
                        'times' => time()
	                ),'uid=:uid', array(':uid'=>$uid));
	    }
	}
	/**
	 * 获取最新更新动态评论的用户信息
	 * @param int $uid
	 * @return multitype:Ambigous <mixed, string, unknown>
	 */
	public function getCommentNum($uid){
	    $res = $this->dbConnection->createCommand()
    	    ->select('times')
    	    ->from('commenttime')
    	    ->where('uid = :uid ',array(':uid'=>$uid))
    	    ->queryScalar();
	    if($res){
	        $rows =$this->dbConnection->createCommand()
	        ->select('tid')
	        ->from('trends')
	        ->where('uid = :uid ',array(':uid'=>$uid))
	        ->queryColumn();
	        $ids = implode(',', $rows);
	        if($ids) {
    	        $sql = 'select count(id) from comment where tid in ('.$ids.') and datetime > ' . $res;
    	        $res = Yii::app()->db->createCommand($sql)->queryScalar();
    	        return $res;
	        }
	        return 0;
	    }
	}
	/**
	 * 获取评论内容
	 * @param int $uid
	 * @param int $page
	 * @param int $limit
	 */
	public function commentInfo($uid,$page,$limit){
	    $tids =$this->dbConnection->createCommand()
    	    ->select('tid')
    	    ->from('trends')
    	    ->where('uid = :uid ',array(':uid'=>$uid))
    	    ->queryColumn();
	    $ids = implode(',', $tids);
	    $p = $limit * ($page -1);
	    $sql = 'select * from comment where tid in ('.$ids.') order by datetime desc limit ' . $p . ',' . $limit;;
	    $res = Yii::app()->db->createCommand($sql)->queryAll();
	    foreach($res as $k=>$v) {
	        $res[$k]['nickname'] = $this->getNickname($v['uid']);
	        $res[$k]['image'] = $this->getTrendsImg($v['tid']);
	        $res[$k]['photo'] = $this->getPhoto($v['uid']);
	    }
	    return $res;
	}
	public function getPhoto($uid){
	    $sql = 'select Photo from userinfo where UserId = ' . $uid;
	    return Yii::app()->db->createCommand($sql)->queryScalar();
	}
	public function getTrendsImg($tid){
	    return $this->dbConnection->createCommand()
	    ->select('image')
	    ->from('trends')
	    ->where('tid = :tid ',array(':tid'=>$tid))
	    ->queryScalar();	    
	}
	/**
	 * 获取用户昵称
	 * @param unknown_type $uid
	 * @return Ambigous <mixed, string, unknown>
	 */
	public function getNickname($uid){
	    return $this->dbConnection->createCommand()
	    ->select('NickName')
	    ->from('user')
	    ->where('UserId = :UserId',array(':UserId'=>$uid))
	    ->queryScalar();	    
	}
	/**
	 * 获取关注状态
	 * @param int $uid
	 * @param int $ruid
	 * @return Ambigous <mixed, string, unknown>
	 */
	public function getReType($uid,$ruid){
	     
	    return $this->dbConnection->createCommand()
	    ->select('count(id) as n')
	    ->from('userrelation')
	    ->where('uid = :uid AND ruid = :ruid AND relation = :relation',array(':uid'=>$uid,':ruid'=>$ruid,':relation'=>1))
	    ->queryScalar();
	}
// 	/**
// 	 * 统计访问人数
// 	 * @param unknown_type $uid
// 	 */
// 	public function visitNum($uid){
//         $sql = "update `visit` set `num` = `num`+1, `datetime` = ".time()." where `uid`=".$uid;
// 		$this->dbConnection->createCommand($sql)->query();
// 	}
// 	/**
// 	 * 获取访问量
// 	 * @param unknown_type $uid
// 	 * @return Ambigous <mixed, string, unknown>
// 	 */
// 	public function getVisitNum($uid){
// 	    return $this->dbConnection->createCommand()
//     	    ->select('num')
//     	    ->from('visit')
//     	    ->where('uid = :uid ',array(':uid'=>$uid))
//     	    ->queryScalar();	    
// 	}
	/**
	 * 关注/取消关注
	 * @param int $uid
	 * @param int $ruid
	 * @return number
	 */
	public function setRelation($uid,$ruid){
	    
	    $n = $this->dbConnection->createCommand()
    	    ->select('count(id) as n')
    	    ->from('userrelation')
    	    ->where('uid = :uid AND ruid = :ruid AND relation = :relation',array(':uid'=>$uid,':ruid'=>$ruid,':relation'=>1))
    	    ->queryScalar();
	    if($n==0) {
            $res['res'] = $this->dbConnection->createCommand()
                ->insert('userrelation',
                    array(
                        'uid'=>$uid,
                        'ruid'=>$ruid,
                        'relation'=>1,
                        'datetime'=>time()
                        )
                    );
            $res['rel'] = '1';

	    } else {
	        $res['res'] = $this->dbConnection->createCommand()->delete('userrelation','uid = :uid AND ruid = :ruid',array(':uid'=>$uid,':ruid'=>$ruid));
	        $res['rel'] = '0';
	    }
	    return $res;
    }
    /**
     *  获取关注数量
     * @param unknown_type $uid
     * @return Ambigous <mixed, string, unknown>
     */
    public function getRelationNum($uid){
        return $this->dbConnection->createCommand()
            ->select('count(ruid)')
            ->from('userrelation')
            ->where('uid = :uid AND relation = :relation',array(':uid'=>$uid,':relation'=>1))
            ->queryScalar();        
    }
    /**
     * 获取粉丝数量
     * @param unknown_type $uid
     * @return Ambigous <mixed, string, unknown>
     */
//     public function getFansNum($uid){
//         return $this->dbConnection->createCommand()
//             ->select('count(uid) as n')
//             ->from('userrelation')
//             ->where('ruid = :ruid AND relation = :relation',array(':ruid'=>$uid,':relation'=>1))
//             ->queryScalar();
//     }
    /**
     * 举报用户
     * @param int $uid
     * @param int $ruid
     * @param int $type
     * @return number
     */
    public function setReport($uid,$ruid,$content){
        return $this->dbConnection->createCommand()
            ->insert('reportlist',
                array(
                    'uid'=>$uid,
                    'ruid'=>$ruid,
                    'content'=>$content,
                    'datetime'=>time()
                    )
                );
    }
    /**
     * 举报动态
     * @param int $uid
     * @param int $ruid
     * @param int $type
     * @return number
     */
    public function setReportTrends($uid,$ruid,$tid,$content){
        return $this->dbConnection->createCommand()
            ->insert('reporttrends',
                array(
                    'uid'=>$uid,
                    'ruid'=>$ruid,
                    'tid'=>$tid,
                    'content'=>$content,
                    'datetime'=>time()
                )
        );
    }
    /**
     * 拉黑名单
     * @param int $uid
     * @param int $ruid
     * @return number
     */
    public function setBlack($uid,$ruid){
         
        $n = $this->dbConnection->createCommand()
        ->select('count(id) as n')
        ->from('userrelation')
        ->where('uid = :uid AND ruid = :ruid AND relation = :relation',array(':uid'=>$uid,':ruid'=>$ruid,':relation'=>2))
        ->queryScalar();
        if($n==0) {
            $res = $this->dbConnection->createCommand()
            ->insert('userrelation',
                array(
                    'uid'=>$uid,
                    'ruid'=>$ruid,
                    'relation'=>2,
                    'datetime'=>time()
                ));
            return $res;
        } else {
            return 0;
        }
    }
    /**
     * 赞/取消赞
     * @param int $uid
     * @param int $ruid
     * @return number
     */
    public function setPraise($tid,$uid,$cuid){
         
        $n = $this->dbConnection->createCommand()
        ->select('count(tid) as n')
        ->from('praise')
        ->where('tid = :tid AND uid = :uid AND cuid = :cuid AND type = :type',array(':tid'=>$tid,':uid'=>$uid,':cuid'=>$cuid,':type'=>1))
        ->queryScalar();
        if($n==0) {
            $res = $this->dbConnection->createCommand()
            ->insert('comments',
                array(
                    'tid'=>$tid,
                    'uid'=>$uid,
                    'cuid'=>$cuid,
                    'type'=>1,
                    'datetime'=>time()
                )
            );
            return $res;
        } else {
            $this->dbConnection->createCommand()->delete('comments','tid = :tid AND uid = :uid AND cuid = :cuid AND type = :type',array(':tid'=>$tid,':uid'=>$uid,':cuid'=>$cuid,':type'=>1));
            return 0;
        }
    }
    /**
     * 获取关注用户列表
     * @param int $uid
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getRelationUserInfo($uid,$page,$limit,$lat,$lng,$order){
        $p = ($page - 1) * $limit;
        $sql = 'select ruid from userrelation where uid = ' . $uid . ' and relation = 1 order by datetime desc limit ' . $p . ',' . $limit;
        $rids = Yii::app()->db->createCommand($sql)->queryColumn();
        if($rids) {
            $res = array();
            if($lat != 0 && $lng != 0) {
                foreach($rids as $k=>$v ){
                    $info = "select u.UserId,u.NickName,u.Age,u.Sex,info.Sign,info.Price,info.Photo,info.popu+info.addpopu as popu,coins.Coins+coins.FreeCoins+coins.WinCoins as coins,c.lat,c.lng from user as u left join userinfo as info on u.UserId = info.UserId left join usercoins as coins on u.UserId = coins.UserId left join coord as c on u.UserId = c.uid where u.UserId = $v";
                    $res[$k] = Yii::app()->db->createCommand($info)->queryRow();
                    $res[$k]['coord'] = $this->GetDistance($lat, $lng, $res[$k]['lat'], $res[$k]['lng']);
                    unset($res[$k]['lat']);
                    unset($res[$k]['lng']);
                }
            } else {
                foreach($rids as $k=>$v ){
                    $info = "select u.UserId,u.NickName,u.Age,u.Sex,info.Sign,info.Price,info.Photo,info.popu+info.addpopu as popu,coins.Coins+coins.FreeCoins+coins.WinCoins as coins,c.lat,c.lng from user as u left join userinfo as info on u.UserId = info.UserId left join usercoins as coins on u.UserId = coins.UserId left join coord as c on u.UserId = c.uid where u.UserId = $v";
                    $res[$k] = Yii::app()->db->createCommand($info)->queryRow();
                    $res[$k]['coord'] = 0;
                    unset($res[$k]['lat']);
                    unset($res[$k]['lng']);
                }
            }
            switch($order) {
                case 1:
                    foreach ($res as$k => $v) {
                        $coord[$k] = $v['coord'];
                    }
                    array_multisort($coord,SORT_ASC,SORT_NUMERIC,$res);
                    break;
                case 2:
                    foreach ($res as$k => $v) {
                        $coins[$k] = $v['coins'];
                    }
                    array_multisort($coins,SORT_DESC,SORT_NUMERIC,$res);
                    break;
                case 3:
                    foreach ($res as$k => $v) {
                        $popu[$k] = $v['popu'];
                    }
                    array_multisort($popu,SORT_DESC,SORT_NUMERIC,$res);
                    break;
            }
            return $res;
        }else{
            return false;
        }
    }
    /**
     * 获取最新更新动态的用户信息
     * @param int $uid
     * @return multitype:Ambigous <mixed, string, unknown>
     */
    public function pushTrends($uid){
        $res = $this->dbConnection->createCommand()
            ->select('times')
            ->from('trendstime')
            ->where('uid = :uid ',array(':uid'=>$uid))
            ->queryScalar();
        if($res){
            $rows =$this->dbConnection->createCommand()
                ->select('ruid')
                ->from('userrelation')
                ->where('uid = :uid ',array(':uid'=>$uid))
                ->queryColumn();
            $ids = implode(',', $rows);
            $sql = 'select  distinct uid from trends where uid in ('.$ids.') and datetime > ' . $res . ' order by datetime desc limit 5';
            $res = Yii::app()->db->createCommand($sql)->queryAll();
            $heads = array();
            foreach ($res as $k => $v) {
                $sql = 'select Photo from userinfo where UserId = ' . $v['uid'];
                $heads[] = Yii::app()->db->createCommand($sql)->queryScalar();
            }
        }else{
            $rows =$this->dbConnection->createCommand()
            ->select('ruid')
            ->from('userrelation')
            ->where('uid = :uid ',array(':uid'=>$uid))
            ->queryColumn();
            $ids = implode(',', $rows);
            $sql = 'select  distinct uid from trends where uid in ('.$ids.') order by datetime desc limit 5';
            $res = Yii::app()->db->createCommand($sql)->queryAll();
            $heads = array();
            foreach ($res as $k => $v) {
                $sql = 'select Photo from userinfo where UserId = ' . $v['uid'];
                $heads[] = Yii::app()->db->createCommand($sql)->queryScalar();
            }
        }
        return $heads;
    }
    /**
     * 通过uid获取手机号码
     * @param int $uid
     * @return Ambigous <mixed, string, unknown>
     */
    public function getMobile($uid){
        $sql = 'select Mobile from user where UserId = ' . $uid;
        return Yii::app()->db->createCommand($sql)->queryScalar();
    }
    /**
     *  通过手机号码获取uid
     * @param string $mobile
     * @return id <tring>
     */
    public function getUid($mobile) {
        return $this->dbConnection->createCommand()
            ->select('UserId')
            ->from('user')
            ->where('Mobile = :Mobile', array(':Mobile' => $mobile))
            ->queryScalar();
    }
    /**
     * 获取最近联系人
     * @param int $uid
     * @return Ambigous <string, multitype:, mixed, unknown>
     */
    public function getRecord($uid){
        $mobile = $this->getMobile($uid);
        $sql = 'select distinct p from (select p from  ( select phone as p,stime as t from callrecord where mobile='. $mobile .'  union select mobile as p,stime as t from callrecord where phone='. $mobile .') x order by t desc) y limit 5';
        $res = Yii::app()->db->createCommand($sql)->queryAll();
        foreach ($res as $k=>$v) {
            $uid = $this->getUid($v['p']);
            if($uid){
                $photo = 'select Photo from userinfo where UserId = '.$uid;
                $res[$k]['photo'] = Yii::app()->db->createCommand($photo)->queryScalar();
            }else{
                $res[$k]['photo'] = '';
            }
            unset($res[$k]['m']);
        }
        return $res;
    }
    /**
     * 通话记录信息
     * @param int $uid
     * @param int $page
     * @param int $limit
     * @return multitype:multitype:
     */
    public function getRecords($uid,$page,$limit){
        $p = ($page - 1) * $limit;
        $mobile = $this->getMobile($uid);
        $sql = 'select phone,mobile,stime from callrecord where phone='. $mobile .' or mobile='. $mobile .' order by stime desc limit ' . $p . ',' . $limit;
        $res = Yii::app()->db->createCommand($sql)->queryAll();
        $info = array();
        $result = array();
        $ruid = '';
        foreach ($res as $k=>$v) {
            if($mobile==$v['phone']) $ruid = $this->getUid($v['mobile']);
            if($mobile==$v['mobile']) $ruid = $this->getUid($v['phone']);
            if($ruid) {
                $sql = 'select u.UserId,u.NickName,u.Sex,ui.Price,ui.Photo,ui.popu+ui.addpopu as popu,uc.Coins+FreeCoins+uc.WinCoins as coins '.
                       'from user as u left join userinfo as ui on u.UserId = ui.UserId '.
                       'left join usercoins as uc on u.UserId = uc.UserId where u.UserId = '.$ruid;
                $info = Yii::app()->db->createCommand($sql)->queryRow();
                if($uid != 0){
                    $usersql = 'select lat,lng from coord where uid='.$uid;
                    $user = Yii::app()->db->createCommand($usersql)->queryRow();
                }else{
                    $res[$k]['coord'] = -1;
                }
                $resql = 'select lat,lng from coord where uid='.$ruid;
                $re = Yii::app()->db->createCommand($resql)->queryRow();
                
                $res[$k]['coord'] = $this->GetDistance($user['lat'], $user['lng'], $re['lat'], $re['lng']);
                $res[$k]['stime'] = $v['stime'];
                $result[] = array_merge($info,$res[$k]);  
            }
        }
        return $result;
    }
    /****************************************************************************************************************/
    /**
     * 消息处理
     * @return mixed
     */
    public function getMessage(){
        return $this->dbConnection->createCommand()
            ->select('*')
            ->from('systemmsg')
            ->order('datetime desc')
            ->limit(1)
            ->queryRow();
    }
    public function getMessages($page,$limit){
        $p = ($page - 1) * $limit;
        return $this->dbConnection->createCommand()
            ->select('id,title,content,datetime')
            ->from('systemmsg')
            ->order('datetime DESC')
            ->limit($limit,$p)
            ->queryAll();
    }
    public function getMessageInfo($id){
        return $this->dbConnection->createCommand()
            ->select('*')
            ->from('systemmsg')
            ->where('id=:id',array(':id'=>$id))
            ->queryRow();
    }    
    /******************************************************************************************************************/
    /**
     * 记录查看动态时间
     * @param int $uid
     */
    public function trendsTime($uid){
        $db = $this->dbConnection->createCommand();
        $res = $db->select('count(id)')
                ->from('trendstime')
                ->where('uid = :uid ',array(':uid'=>$uid))
                ->queryScalar();
        if($res==0) {
            $db->insert('trendstime',
                array(
                    'uid'=>$uid,
                    'times'=>time()
                )
            );
        }else{
            $db->update('trendstime',
                array(
                    'times' => time()
                ),
                'uid=:uid', array(':uid'=>$uid)
            );            
        }
    }    
    /**
     * 获取粉丝用户列表
     * @param int $uid
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getFansUserInfo($uid,$page,$limit,$lat,$lng){
        $p = ($page - 1) * $limit;
        $sql = 'select uid from userrelation where ruid = ' . $uid . ' and relation = 1 order by datetime desc limit ' . $p . ',' . $limit;
        $rids = Yii::app()->db->createCommand($sql)->queryColumn();
        if($rids) {
            $res = array();
            foreach($rids as $k=>$v ){
                $info = "select u.UserId,u.NickName,u.Age,u.Sex,info.Sign,info.Price,info.Photo,info.popu+info.addpopu as popu,coins.Coins+coins.FreeCoins+coins.WinCoins as coins,c.lat,c.lng from user as u left join userinfo as info on u.UserId = info.UserId left join usercoins as coins on u.UserId = coins.UserId left join coord as c on u.UserId = c.uid where u.UserId = $v";
                $res[$k] = Yii::app()->db->createCommand($info)->queryRow();
                $res[$k]['coord'] = $this->GetDistance($lat, $lng, $res[$k]['lat'], $res[$k]['lng']);
                unset($res[$k]['lat']);
                unset($res[$k]['lng']);
            }
            return $res;
        }else{
            return false;
        }
    }
    /**
     * 获取1张图片
     * @param int $uid
     * @return Ambigous <mixed, string, unknown>
     */
    public function getOnePhoto($uid){
        $res = $this->dbConnection->createCommand()
            ->select('url')
            ->from('album')
            ->where('uid = :uid ',array(':uid'=>$uid))
            ->order('datetime DESC')
            ->limit(1)
            ->queryRow();
            return $res['url'];
    }
	/**
	 * 获取用户个人信息
	 * @param int $uid
	 * @return array
	 */
	public function getUserCenter($uid,$page,$limit){
	    if($page == 1) {
    	    $sql = 'select u.UserId,u.NickName,u.Age,u.Sex,u.ustatus,info.Sign,info.Price,info.Photo,info.stime,info.Voice,info.bgimg,info.popu+info.addpopu as popu,coins.Coins+coins.FreeCoins+coins.WinCoins as coins,c.lat,c.lng from user as u left join userinfo as info on u.UserId = info.UserId left join usercoins as coins on u.UserId = coins.UserId left join coord as c on u.UserId = c.uid where u.UserId ='.$uid;
    	    $res = Yii::app()->db->createCommand($sql)->queryRow();
    	    $trendsql = 'select * from trends where uid = ' . $uid . ' order by datetime desc' . ' limit 0,' . $limit;
    	    $trends = Yii::app()->db->createCommand($trendsql)->queryAll();
    	    foreach ($trends as $key => $value) {
    	        $com = 'select * from comment where tid = '.$value['tid'].' order by datetime desc';
    	        $trends[$key]['comment'] =  Yii::app()->db->createCommand($com)->queryAll();
    	        foreach($trends[$key]['comment'] as $k=>$v){
    	            $trends[$key]['comment'][$k]['nickname'] = $this->getNickname($trends[$key]['comment'][$k]['uid']);
    	        }
    	        $praise = 'select uid from praise where tid = '.$value['tid'].' order by datetime desc limit 5';
    	        $num = 'select count(uid) from praise where tid = '.$value['tid'];
    	        
    	        $trends[$key]['praise'] =  Yii::app()->db->createCommand($praise)->queryAll();
    	        $trends[$key]['praise_num'] = Yii::app()->db->createCommand($num)->queryScalar();
    	        foreach($trends[$key]['praise'] as $k=>$v){
    	            $trends[$key]['praise'][$k]['nickname'] = $this->getNickname($trends[$key]['praise'][$k]['uid']);
    	        }
    	    }
    	    $res['trends'] = $trends;
    	    $res['relation'] = $this->getRelationNum($uid);
	    }else{
	        $p = $limit * ($page -1);
	        $sql = 'select * from trends where uid = ' . $uid . ' order by datetime desc' . ' limit ' . $p . ',' . $limit;
	        $res = Yii::app()->db->createCommand($sql)->queryAll();
	        foreach ($res as $key => $value) {
	            $com = 'select * from comment where tid = '.$value['tid'].' order by datetime desc';
	            $res[$key]['comment'] =  Yii::app()->db->createCommand($com)->queryAll();
	            foreach($res[$key]['comment'] as $k=>$v){
	                $res[$key]['comment'][$k]['nickname'] = $this->getNickname($res[$key]['comment'][$k]['uid']);
	            }
	            $praise = 'select uid from praise where tid = '.$value['tid'].' order by datetime desc limit 5';
	            $num = 'select count(uid) from praise where tid = '.$value['tid'];
	            $res[$key]['praise'] =  Yii::app()->db->createCommand($praise)->queryAll();
	            $res[$key]['praise_num'] = Yii::app()->db->createCommand($num)->queryScalar();
	            foreach($res[$key]['praise'] as $k=>$v){
	                $res[$key]['praise'][$k]['nickname'] = $this->getNickname($res[$key]['praise'][$k]['uid']);
	            }
	        }	        
	    }
	    return $res;
	}	
	
	/**
	 * 获取用户个人声音url
	 * @param int $uid
	 * @return array
	 */
	public function getUserVoice($uid){
	    $sql = 'select Voice from userinfo where UserId ='.$uid;
	    $res = Yii::app()->db->createCommand($sql)->queryScalar();
	    return $res;
	}	
	
	/**
	 * 获取查找用户信息
	 * @param array $ids
	 * @param array $search
	 * @return array
	 */
	public function findUser($uid,$lat,$lng){

        $sql = "select u.UserId,u.NickName,u.Age,u.Sex,info.Sign,info.Price,info.Photo,nfo.popu+info.addpopu as popu,coins.Coins+coins.FreeCoins+coins.WinCoins as coins,c.lat,c.lng from user as u left join userinfo as info on u.UserId = info.UserId left join usercoins as coins on u.UserId = coins.UserId left join coord as c on u.UserId = c.uid where u.UserId = $uid";
        $res = Yii::app()->db->createCommand($sql)->queryRow();
        if($res){
            $res['coord'] = $this->GetDistance($lat, $lng, $res['lat'], $res['lng']);
            unset($res['lat']);
            unset($res['lng']);
        }
	    return $res;
	}
	/**
	 * 设置聊天价格
	 * @param int $uid
	 * @param int $price
	 * @return number
	 */	
	public function setPrice($uid,$price){
	    return $this->dbConnection->createCommand()
    	    ->update($this->tableName(),
                array(
                    'Price' => $price
                ),
                'UserId=:UserId', array(':UserId'=>$uid)
    	    );	    
	}
	/**
	 * 更新人气
	 * @param int $uid
	 * @param int $pupo
	 * @return number
	 */
	public function changPopu($uid,$popu){
	    $db = $this->dbConnection->createCommand();
	    $res = $db->select('popu')
	    ->from($this->tableName())
	    ->where('UserId=:uid',array(':uid'=>$uid))
	    ->queryScalar();
	    $num = $res + $popu;
	    return $db->update($this->tableName(),array('popu'=>$num),'UserId=:uid', array(':uid'=>$uid));	    
	}
}