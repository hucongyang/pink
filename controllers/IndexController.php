<?php

/**
 * 石榴裙首页
 * @author stuart.shi <shizw@5173.com>
 * @create 2013-10-14
 */
class IndexController extends ApiPublicController {

    public $name = "index";

    /**
     * 登录
     */
    public function actionLogin() {
        if (!Yii::app()->request->isPostRequest) {
            //登录错误次数
            if (isset(Yii::app()->session['errCount'])) {
                $logErr = Yii::app()->session['errCount'];
            } else {
                $logErr = 0;
            }
            $this->render("index/login", array("logErr" => $logErr));
        } else {
            $mobile = Yii::app()->request->getParam('username') ? trim(Yii::app()->request->getParam('username')) : $this->_exit($this->_error['20001']);
            $password = Yii::app()->request->getParam('pwd') ? trim(Yii::app()->request->getParam('pwd')) : $this->_exit($this->_error['20023']);
            
            if (isset(Yii::app()->session['errCount'])) {
                $valid = Yii::app()->request->getParam('valid') ? strtolower(Yii::app()->request->getParam('valid')) : $this->_exit($this->_error['20004']);

                //带验证码的登录
                if ($valid != Yii::app()->session['validSess']) {
                    $this->_exit($this->_error['20005']);
                }
            }

            $identity = new UserIdentity($mobile, $password);
            if ($identity->authenticate()) {
                Yii::app()->user->login($identity);
                //登录成功，获取用户基本信息
                $info = User::model()->getUserInfo(Yii::app()->user->id);
                if ($info['Status'] != 0) {
                    $this->_exit($this->_error['20014']);
                    Yii::app()->user->logout();
                } else {
                    //写入session
                    Yii::app()->session['userinfo'] = $info;
                    $this->_exit($this->_error['20000'], 1);
                }
                //$this->redirect(Yii::app()->user->returnUrl);
            } else {
                //记录登录错误次数
                if (isset(Yii::app()->session['errCount'])) {
                    Yii::app()->session['errCount'] = Yii::app()->session['errCount'] + 1;
                } else {
                    Yii::app()->session['errCount'] = 1;
                }
                $this->_exit($this->_error['20026'], 2);
            }
        }
    }

    /**
     * 注销
     */
    public function actionLogout() {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->user->returnUrl);
    }

    /**
     * 推广首页
     */
    public function actionIndex() {
        $user = $premoted = array();
        if (isset(Yii::app()->session['userinfo'])) {
            $user = Yii::app()->session['userinfo'];
        }

        //登录错误次数
        if (isset(Yii::app()->session['errCount'])) {
            $logErr = Yii::app()->session['errCount'];
        } else {
            $logErr = 0;
        }

        $this->render("index/index", array("user" => $user));
    }

    /*     * *********************系列单页********************** */

    public function actionQa() {
        $this->getPage($this->getAction()->id);
    }

    public function actionDisclaimer() {
        $this->getPage($this->getAction()->id);
    }

    public function actionService() {
        $this->getPage($this->getAction()->id);
    }

    public function actionPolicy() {
        $this->getPage($this->getAction()->id);
    }

    public function actionAbout() {
        $this->getPage($this->getAction()->id);
    }

    /**
     * 获取单页内容
     * @param type $action
     */
    protected function getPage($action) {
        $info = TgArticles::model()->getPageByTypeName(Yii::app()->params['pageTypes'][$action]);
        $this->render("article/page", array("info" => $info));
    }

    /**
     * 图形验证码
     */
    public function actionValid() {
        Func::validatePic2("validSess", 100, 26);
    }

}