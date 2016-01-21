<?php

class SearchController extends ApiPublicController
{
    /**
     * 查看用户列表
     */
	public function actionList()
	{
	    if(!isset($_POST['uid']) || $_POST['uid']=='') $this->_exit($this->_error['20007'],'20007');
	    if(!isset($_POST['lng'])) $this->_exit($this->_error['20008'],'20008');
	    if(!isset($_POST['lat'])) $this->_exit($this->_error['20009'],'20009');
	    if(!isset($_POST['page']) || !$_POST['page']) $this->_exit($this->_error['20019'],'20019');
	    if(!isset($_POST['search'])) $this->_exit($this->_error['20020'],'20020');
	    if(!isset($_POST['order']) || !$_POST['order']) $this->_exit($this->_error['20021'],'20021');
	    if(!isset($_POST['mac']) || !$_POST['mac']) $this->_exit($this->_error['20028'],'20028');
        $search = array();
        $search['num'] = 24;
        $search['page'] = trim($_POST['page']);
        $search['uid'] = trim($_POST['uid']);
        $search['lng'] = trim($_POST['lng']);
        $search['lat'] = trim($_POST['lat']);
        $search['search'] = trim($_POST['search']);// 0.女 1.男 2.全部
        $search['order'] =  trim($_POST['order']);//1.距离 2.金币,3.人气 4.最新注册
        if($search['uid'] != 0){
            $mac = trim($_POST['mac']);
            $token = substr(md5($search['uid'].$mac), 0, 16);
            $r = User::model()->haveToken($search['uid']);
            if($r['value'] != $token) $this->_exit($this->_error['20061'],'20061');
        }
        if($search['uid'] == 0 &&($search['lng'] == 0 || $search['lat'] == 0) && $search['order'] == 1) $this->_exit($this->_error['20033'],'20033');
        $pro = UserInfo::model()->getProxyIds($search['uid']);
        $res = UserInfo::model()->getUserIds($search);
        if($res) {
            $uid='';
            foreach ($pro as $x=>$y){
                $uid = $y['uid'];
            }
            if($uid) {
                $proxy = array();
                foreach ($res as $key=>$value) {
                    foreach ($pro as $x=>$y) {
                        $proxy[] = $y['uid'];
                    }
                    if(in_array($res[$key]['uid'],$proxy)) unset($res[$key]);
                }
                $res = array_merge($pro,$res);
            }
	        foreach ($res as $k=>$v) {
	            if($v['uid'] == $search['uid']) continue;
	            $data = UserInfo::model()->getUserInfo($v['uid'],$search);
                if(isset($data['Photo'])) {
                    $data['Photo'] = self::HOST_HTTP_HEAD_IMG . str_replace('\\', '/', $data['Photo']);
    	            unset($v['uid']);
    	            $row[] = array_merge($v,$data);
                }
	        }
	        UserInfo::model()->changeCoord($search['uid'],  $search['lat'], $search['lng']);
	        $this->_exit($this->_error['20000'],'20000',$row,'list');
        }
        $this->_exit($this->_error['21000'],'21000');
	}

	/**
	 *  关注用户列表
	 */
	public function actionRelationList(){
	    if(!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'],'20007');
	    if(!isset($_POST['page']) || !$_POST['page']) $this->_exit($this->_error['20019'],'20019');
	    if(!isset($_POST['lng'])) $this->_exit($this->_error['20008'],'20008');
	    if(!isset($_POST['lat'])) $this->_exit($this->_error['20009'],'20009');
	    if(!isset($_POST['order']) || !$_POST['order']) $this->_exit($this->_error['20021'],'20021');
	    if(!isset($_POST['mac']) || !$_POST['mac']) $this->_exit($this->_error['20028'],'20028');
	    $uid = $_POST['uid'];
	    $page = trim($_POST['page']);
	    $limit = 24;
	    $lng = trim($_POST['lng']);
	    $lat = trim($_POST['lat']);
	    $order = trim($_POST['order']);
	    $mac = trim($_POST['mac']);
	    $token = substr(md5($uid.$mac), 0, 16);
	    $r = User::model()->haveToken($uid);
	    if($r['value'] != $token) $this->_exit($this->_error['20061'],'20061');
	    $list = UserInfo::model()->getRelationUserInfo($uid, $page, $limit, $lat, $lng,$order);
	    if($list) {
	        foreach($list as $k => $v) {
	            $list[$k]['Photo'] = self::HOST_HTTP_HEAD_IMG . str_replace('\\', '/', $v['Photo']);
	        }
	        $push = UserInfo::model()->pushTrends($uid);
	        foreach($push as $k => $v) {
	            $push[$k] = self::HOST_HTTP_HEAD_IMG . str_replace('\\', '/', $v);
	        }
	        $record = UserInfo::model()->getRecord($uid);
	        foreach($record as $k => $v) {
                $record[$k] = self::HOST_HTTP_HEAD_IMG . str_replace('\\', '/', $record[$k]['photo']);
	        }
	        $message = UserInfo::model()->getMessage();
	        $list = array('list'=>$list,'push'=>$push,'record'=>$record,'message'=>$message);
	        UserInfo::model()->changeCoord($uid, $lat, $lng);
	        $this->_exit($this->_error['20000'],'20000',$list,'relist');
	    }
	    if($list==false) $this->_exit($this->_error['20022'],'20022');
	    $this->_exit($this->_error['21000'],'21000');
	}
	/**
	 *  列表/关注 用户详细信息
	 */
	public function actionRows(){
	    if(!isset($_POST['uid']) || $_POST['uid'] =='') $this->_exit($this->_error['20007'],'20007');
	    if(!isset($_POST['ruid']) || !$_POST['ruid']) $this->_exit($this->_error['20012'],'20012');
	    if(!isset($_POST['page']) || !$_POST['page']) $this->_exit($this->_error['20019'],'20019');
	    $uid = trim($_POST['uid']);
	    $ruid = trim($_POST['ruid']);
	    $page = trim($_POST['page']);
	    $limit = 10;
	    $row = UserInfo::model()->UserInformation($uid, $ruid,$page,$limit);
	    if($row) {
	        if($page == 1) {
                $row['Photo'] = $row['Photo'] ? self::HOST_HTTP_HEAD_IMG . str_replace('\\', '/', $row['Photo']) : $row['Photo'];
                $row['bgimg'] = $row['bgimg'] ? self::HOST_HTTP_BG_IMG . str_replace('\\', '/', $row['bgimg']) : $row['bgimg'];
                $row['Voice'] =  $row['Voice'] ? self::HOST_HTTP_SOUND . str_replace('\\', '/', $row['Voice']) : $row['Voice'];
                //$row['coins'] = $row['coins'] / 100;
    	        foreach ($row['trends'] as $key=>$value) {
    	            $row['trends'][$key]['image'] = $value['image'] ? self::HOST_HTTP_TRENDS . str_replace('\\', '/', $value['image']) : $value['image'];
    	        }
	        }else{
	            foreach ($row as $key=>$value) {
	                $row[$key]['image'] = self::HOST_HTTP_TRENDS . str_replace('\\', '/', $value['image']);
	            }	            
	        }
	        $this->_exit($this->_error['20000'],'20000',$row,'rows');
	    }
	    $this->_exit($this->_error['21000'],'21000');
	}
	/**
	 * 用户动态列表
	 */
	public function actionRelationTrendsList(){
	    if(!isset($_POST['uid']) || $_POST['uid'] =='') $this->_exit($this->_error['20007'],'20007');
	    if(!isset($_POST['page']) || !$_POST['page']) $this->_exit($this->_error['20019'],'20019');
	    $uid = trim($_POST['uid']);
	    $page = trim($_POST['page']);
	    $limit = 5;
	    $row = UserInfo::model()->relationTrends($uid,$page,$limit);
	    if($row) {
	        foreach ($row as $key=>$value) {
	            $row[$key]['image'] = self::HOST_HTTP_TRENDS . str_replace('\\', '/', $value['image']);
	            $row[$key]['photo'] = self::HOST_HTTP_HEAD_IMG . str_replace('\\', '/', $value['photo']);
	        } 
	        $num = UserInfo::model()->getCommentNum($uid);
	        $row = array('row'=>$row,'num'=>$num);
	        UserInfo::model()->trendsTime($uid);
	        UserInfo::model()->insertCommentTime($uid);
	        $this->_exit($this->_error['20000'],'20000',$row,'rows'); 
	    }
	    $this->_exit($this->_error['21000'],'21000');
	}
	/**
	 * 评论列表
	 */
	public function actionCommentList(){
	    if(!isset($_POST['uid']) || $_POST['uid'] =='') $this->_exit($this->_error['20007'],'20007');
	    if(!isset($_POST['page']) || !$_POST['page']) $this->_exit($this->_error['20019'],'20019');
	    $uid = trim($_POST['uid']);
	    $page = trim($_POST['page']);
	    $limit = 5;
	    $row = UserInfo::model()->commentInfo($uid,$page,$limit);
	    if($row) {
	        foreach($row as $k=>$v){
	            $row[$k]['image'] = self::HOST_HTTP_TRENDS . str_replace('\\', '/', $v['image']);
	            $row[$k]['photo'] = self::HOST_HTTP_HEAD_IMG . str_replace('\\', '/', $v['photo']);
	        }
	        UserInfo::model()->updateCommentTime($uid);
	        $this->_exit($this->_error['20000'],'20000',$row,'rows');
	    }
	    $this->_exit($this->_error['21000'],'21000');
	}
	/**
	 * 最近通话记录列表
	 */
	public function actionContactList(){
	    if(!isset($_POST['uid']) || $_POST['uid'] =='') $this->_exit($this->_error['20007'],'20007');
	    if(!isset($_POST['page']) || !$_POST['page']) $this->_exit($this->_error['20019'],'20019');
	    $uid = trim($_POST['uid']);
	    $page = trim($_POST['page']);
	    $limit = 10;
	    $row = UserInfo::model()->getRecords($uid,$page,$limit);
	    if($row) {
	        foreach($row as $k=>$v){
	            $row[$k]['Photo'] = self::HOST_HTTP_HEAD_IMG . str_replace('\\', '/', $v['Photo']);
	            $row[$k]['time'] = $row[$k]['stime'];
	            unset($row[$k]['phone']);
	            unset($row[$k]['mobile']);
	            unset($row[$k]['stime']);
	        }
	        $this->_exit($this->_error['20000'],'20000',$row,'rows');
	    }
	    $this->_exit($this->_error['21000'],'21000');
	}
	/**
	 * 消息列表
	 */
	public function actionMsgList(){
	    if(!isset($_POST['page']) || !$_POST['page']) $this->_exit($this->_error['20019'],'20019');
	    $page = trim($_POST['page']);
	    $limit = 10;
	    $message = UserInfo::model()->getMessages($page, $limit);
	    if($message) $this->_exit($this->_error['20000'],'20000',$message,'rows');
	    $this->_exit($this->_error['21000'],'21000');
	}
	/**
	 * 消息详情
	 */
// 	public function actionMsgInfo(){
// 	    if(!isset($_POST['id']) || !$_POST['id']) $this->_exit($this->_error['20062'],'20062');
// 	    $id = trim($_POST['id']);
// 	    $info = UserInfo::model()->getMessageInfo($id);
// 	    if($info) $this->_exit($this->_error['20000'],'20000',$info,'message');
// 	    $this->_exit($this->_error['21000'],'21000');
// 	}
}