<?php

class UcenterController extends ApiPublicController
{
    /**
     * 获取个人信息
     */
    public function actionIndex(){
        if(!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'],'20007');
        if(!isset($_POST['page']) || !$_POST['page']) $this->_exit($this->_error['20019'],'20019');
        if(!isset($_POST['mac']) || !$_POST['mac']) $this->_exit($this->_error['20028'],'20028');
        $uid = trim($_POST['uid']);
        $page = trim($_POST['page']);
        $limit = 10;
        $mac = trim($_POST['mac']);
        $token = substr(md5($uid.$mac), 0, 16);
        $r = User::model()->haveToken($uid);
        if($r['value'] != $token) $this->_exit($this->_error['20061'],'20061');
        $res = UserInfo::model()->getUserCenter($uid,$page,$limit);
        if($res) {
            if($page == 1) {
                $banner = Banner::model()->getBanners();
                if (!empty($banner)) {
                    foreach($banner as $k=>$b) {
                        $banner[$k]['image'] = $b['image'] ? self::HOST_HTTP_BG_IMG . str_replace('\\', '/', $b['image']) : $b['image'];
                    }
                    $res['banner'] = $banner;
                }
                $res['Photo'] = $res['Photo'] ? self::HOST_HTTP_HEAD_IMG . str_replace('\\', '/', $res['Photo']) : $res['Photo'];
                $res['bgimg'] = $res['bgimg'] ? self::HOST_HTTP_BG_IMG . str_replace('\\', '/', $res['bgimg']) : $res['bgimg'];
                $res['Voice'] =  $res['Voice'] ? self::HOST_HTTP_SOUND . str_replace('\\', '/', $res['Voice']) : $res['Voice'];
                
                foreach ($res['trends'] as $key=>$value) {
                    $res['trends'][$key]['image'] = $value['image'] ? self::HOST_HTTP_TRENDS . str_replace('\\', '/', $value['image']) : $value['image'];
                }
            }else{
                foreach ($res as $key=>$value) {
                    $res[$key]['image'] = $value['image'] ? self::HOST_HTTP_TRENDS . str_replace('\\', '/', $value['image']) : $value['image'];
                }    
            }
            $this->_exit($this->_error['20000'],'20000',$res,'row');
        }
        $this->_exit($this->_error['21000'],'21000');
    }
    /**
     * 关注/取消关注
     */
    public function actionRelation(){
        if(!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'],'20007');
        if(!isset($_POST['ruid']) || !$_POST['ruid']) $this->_exit($this->_error['20012'],'20012');
        $uid = trim($_POST['uid']);
        $ruid = trim($_POST['ruid']);
        $res = UserInfo::model()->setRelation($uid, $ruid);
        if($res['res']) $this->_exit($this->_error['20000'],'20000',$res['rel'],'relation');
        $this->_exit($this->_error['21000'],'21000');
    }
    /**
     * 举报用户
     */
    public function actionReport(){
        if(!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'],'20007');
        if(!isset($_POST['ruid']) || !$_POST['ruid']) $this->_exit($this->_error['20012'],'20012');
        if(!isset($_POST['content']) || !$_POST['content']) $this->_exit($this->_error['20013'],'20013');
        $uid = trim($_POST['uid']);
        $ruid = trim($_POST['ruid']);
        $content = trim($_POST['content']);
        $res = UserInfo::model()->setReport($uid, $ruid,$content);
        if($res) $this->_exit($this->_error['20000'],'20000');
        $this->_exit($this->_error['21000'],'21000');
    }
    /**
     * 举报动态
     */
    public function actionReportTrends(){
        if(!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'],'20007');
        if(!isset($_POST['ruid']) || !$_POST['ruid']) $this->_exit($this->_error['20012'],'20012');
        if(!isset($_POST['tid']) || !$_POST['tid']) $this->_exit($this->_error['20057'],'20057');
        if(!isset($_POST['content']) || !$_POST['content']) $this->_exit($this->_error['20013'],'20013');
        $uid = trim($_POST['uid']);
        $ruid = trim($_POST['ruid']);
        $tid = trim($_POST['tid']);
        $content = trim($_POST['content']);
        $res = UserInfo::model()->setReportTrends($uid, $ruid,$tid,$content);
        if($res) $this->_exit($this->_error['20000'],'20000');
        $this->_exit($this->_error['21000'],'21000');
    }
    /**
     * 拉黑名单
     */
    public function actionBlack(){
        if(!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'],'20007');
        if(!isset($_POST['ruid']) || !$_POST['ruid']) $this->_exit($this->_error['20012'],'20012');
        $uid = trim($_POST['uid']);
        $ruid = trim($_POST['ruid']);
        $res = UserInfo::model()->setBlack($uid, $ruid);
        if($res!=0) $this->_exit($this->_error['20000'],'20000',$res,'status');
            else $this->_exit($this->_error['20014'],'20014',$res,'status');
        $this->_exit($this->_error['21000'],'21000');
    }
    /**
     * 查找用户
     */
    public function actionAddRelation(){
	    if(!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'],'20007');
	    if(!isset($_POST['lng']) || !$_POST['lng']) $this->_exit($this->_error['20008'],'20008');
	    if(!isset($_POST['lat']) || !$_POST['lat']) $this->_exit($this->_error['20009'],'20009');
	    $uid = trim($_POST['uid']);
	    $lng = trim($_POST['lng']);
	    $lat = trim($_POST['lat']);
        $res = UserInfo::model()->findUser($uid, $lat, $lng);
        if($res) {
            $res['Photo'] = self::HOST_HTTP_HEAD_IMG . str_replace('\\', '/', $res['Photo']);
            $this->_exit($this->_error['20000'],'20000',$res,'userinfo');
        }
        $this->_exit($this->_error['21000'],'21000');        
    }
    /**
     * 动态评论
     */
    public function actionComment(){
        if(!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'],'20007');
        if(!isset($_POST['tid']) || !$_POST['tid']) $this->_exit($this->_error['20057'],'20057');
        if(!isset($_POST['content']) || !$_POST['content']) $this->_exit($this->_error['20058'],'20058');
        $uid = trim($_POST['uid']);
        $tid = trim($_POST['tid']);
        $content = trim($_POST['content']);
        $nickname = UserInfo::model()->getNickname($uid);
        $res = Trends::model()->addComment($uid, $tid, $content);
        if($res) $this->_exit($this->_error['20000'],'20000',$nickname,'nickname');
        $this->_exit($this->_error['21000'],'21000');
    }
    /**
     *  赞
     */
    public function actionPraise(){
        if(!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'],'20007');
        if(!isset($_POST['tid']) || !$_POST['tid']) $this->_exit($this->_error['20057'],'20057');
        $uid = trim($_POST['uid']);
        $tid = trim($_POST['tid']);
        $nickname = UserInfo::model()->getNickname($uid);
        $res = Trends::model()->addPraise($tid,$uid);
        if($res) $this->_exit($this->_error['20000'],'20000',$nickname,'nickname');
        $this->_exit($this->_error['20059'],'20059');
    }
}