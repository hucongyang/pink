<?php

class UserController extends ApiPublicController {

    /**
     *  验证码和手机号码
     */
    public function actionCreate() {
        if (!isset($_POST['mobile']) || !$_POST['mobile'])
            $this->_exit($this->_error['20001'], '20001');
        $mobile = trim($_POST['mobile']);
        if (!$this->isMobile($mobile))
            $this->_exit($this->_error['20002'], '20002');
        $res = User::model()->isMobile($mobile);
        if ($res)
            $this->_exit($this->_error['20003'], '20003');

        $codeInfo = User::model()->getVerifyInfo($mobile);
        if ($codeInfo && $codeInfo['ValidTime'] > time()) {
            $this->sendCode($mobile, $codeInfo['VerifyCode']);
            $this->_exit($this->_error['20000'], '20000');
        } else {
            User::model()->deleteCode($mobile);
            $PostCode = $this->getPostCode(); //随机生成的验证码
            $time = time() + 300;
            $send = $this->sendCode($mobile, $PostCode);
            //发送成功以后把验证码写到数据库
            if (!$send) {
                $r = User::model()->insertPostCode($mobile, $PostCode, $time);
                if ($r < 0)
                    $this->_exit($this->_error['21000'], '21000');
                $this->_exit($this->_error['20000'], '20000');
            }
        }
    }

    /**
     *  注册新用户
     */
    public function actionRegest() {
        if (!isset($_POST['mobile']) || !$_POST['mobile'])
            $this->_exit($this->_error['20001'], '20001');
        if (!isset($_POST['postcode']) || !$_POST['postcode'])
            $this->_exit($this->_error['20004'], '20004');
        $mobile = trim($_POST['mobile']);
        $verifycode = trim($_POST['postcode']);
        $verify = User::model()->getVerifyInfo($mobile);
        if ($verifycode != $verify['VerifyCode'])
            $this->_exit($this->_error['20005'], '20005');
        if ($verify['ValidTime'] < time()) {
            User::model()->deleteCode($mobile);
            $this->_exit($this->_error['20006'], '20006');
        }
        $id = User::model()->create($mobile);
        if ($id) {
            $res = User::model()->add($id);
            if ($res['userinfo'] > 0 && $res['usercoins'] > 0 && $res['coord'] > 0 && $res['visit'] > 0) {
                User::model()->deleteCode($mobile);
                $status = User::model()->setStatus($id);
                if ($status)
                    $this->_exit($this->_error['20000'], '20000', $id, 'uid');
            }
            $this->_exit($this->_error['21000'], '21000');
        }
        $this->_exit($this->_error['21000'], '21000');
    }

    /**
     *  完善用户信息
     */
    public function actionGetInfo() {
        if (!isset($_POST['uid']) || !$_POST['uid'])
            $this->_exit($this->_error['20007'], '20007');
        if (!isset($_POST['lng']) || !$_POST['lng'])
            $this->_exit($this->_error['20008'], '20008');
        if (!isset($_POST['lat']) || !$_POST['lat'])
            $this->_exit($this->_error['20009'], '20009');
        if (!isset($_POST['mac']) || !$_POST['mac'])
            $this->_exit($this->_error['20028'], '20028');
        if (!isset($_POST['iostoken']))
            $this->_exit($this->_error['20034'], '20034');
        $params = array();
        $data = $_POST['pic'] ? trim($_POST['pic']) : file_get_contents("php://input");
        $params['nickname'] = $_POST['nickname'] ? trim($_POST['nickname']) : '';
        $params['sex'] = $_POST['sex'] ? trim($_POST['sex']) : '0';
        $params['age'] = $_POST['age'] ? trim($_POST['age']) : '';
        $params['uid'] = trim($_POST['uid']);
        $params['lng'] = trim($_POST['lng']);
        $params['lat'] = trim($_POST['lat']);
        if ($data) {
            $ms = date('Ym', time());
            $path = Yii::app()->basePath . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'head_img' . DIRECTORY_SEPARATOR . $ms . DIRECTORY_SEPARATOR;

            $imgName = MD5(time() . $this->getPostCode()) . '.jpg';
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
            $newFilePath = $path . $imgName;
            $newFile = fopen($newFilePath, 'w');
            fwrite($newFile, $data);
            fclose($newFile);
            $fileName = $ms . DIRECTORY_SEPARATOR . $imgName;
            include Yii::app()->basePath . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'ResizeImage.php';
            $m = new ResizeImage();
            $m->ResizeImg($newFilePath, '120', '120', '0', $path . '120x120-' . $imgName);
            $m->ResizeImg($newFilePath, '80', '80', '0', $path . '80x80-' . $imgName);
        } else {
            $fileName = '';
        }
        $params['pic'] = trim($fileName);
        $mac = trim($_POST['mac']);
        $params['iostoken'] = $_POST['iostoken'] ? str_replace(' ', '', trim($_POST['iostoken'])) : '';
        $params['defaultcoins'] = 200;
        $token = substr(md5($params['uid'] . $mac), 0, 16);
        $time = time() + 7 * 24 * 3600;
        $res = User::model()->updateUser($params);
        if ($res['user'] || $res['coord'] || $res['userinfo'] || $res['usercoins']) {
            $r = User::model()->haveToken($params['uid']);
            if ($r) {
                if ($r['time'] < time()) {
                    User::model()->loginOut($r['uid']);
                    $this->_exit($this->_error['20010'], '20010');
                }
                $this->_exit($this->_error['20000'], '20000', $r['value'], 'token');
            } else {
                $stat = User::model()->insertToken($params['uid'], $token, $time);
                if ($stat)
                    $this->_exit($this->_error['20000'], '20000', $token, 'token');
            }
        }
        $this->_exit($this->_error['21000'], '21000');
    }

    /**
     *  自动登录
     */
    public function actionAutoLogin() {
        if (!isset($_POST['token']) || !$_POST['token'])
            $this->_exit($this->_error['20011'], '20011');
        $token = trim($_POST['token']);
        $row = User::model()->isToken($token);
        if ($row) {
            if ($row['time'] > time()) {
                $this->_exit($this->_error['20000'], '20000', $row['uid'], 'uid');
            }
            User::model()->deleteToken($row['uid'], $token);
            $this->_exit($this->_error['20010'], '20010');
        }
        $this->_exit($this->_error['20011'], '20011');
    }

    /**
     *  判断用户密码是否设置
     */
    public function actionIsPassword() {
        if (!isset($_POST['uid']) || !$_POST['uid'])
            $this->_exit($this->_error['20007'], '20007');
        $uid = $_POST['uid'];
        $res = User::model()->isSetPassWord($uid);
        if ($res)
            $this->_exit($this->_error['20000'], '20000', '1', 'password');
        $this->_exit($this->_error['20000'], '20000', '0', 'password');
    }

    /**
     * 设置新密码
     */
    public function actionSetPassword() {
        if (!isset($_POST['uid']) || !$_POST['uid'])
            $this->_exit($this->_error['20007'], '20007');
        if (!isset($_POST['password']) || !$_POST['password'])
            $this->_exit($this->_error['20023'], '20023');
        $uid = $_POST['uid'];
        $password = md5(trim($_POST['password']));
        $result = User::model()->changePassword($uid, $password);
        if ($result)
            $this->_exit($this->_error['20000'], '20000');
        $this->_exit($this->_error['21000'], '21000');
    }

    /**
     * 修改密码 
     */
    public function actionChangePassword() {
        if (!isset($_POST['uid']) || !$_POST['uid'])
            $this->_exit($this->_error['20007'], '20007');
        if (!isset($_POST['oldpass']) || !$_POST['oldpass'])
            $this->_exit($this->_error['20024'], '20024');
        if (!isset($_POST['newpass']) || !$_POST['newpass'])
            $this->_exit($this->_error['20025'], '20025');
        $uid = $_POST['uid'];
        $old = $_POST['oldpass'];
        $new = MD5($_POST['newpass']);
        $res = User::model()->isSetPassWord($uid);
        if ($res != MD5($old))
            $this->_exit($this->_error['20026'], '20026');
        $result = User::model()->changePassword($uid, $new);
        if ($result >= 0)
            $this->_exit($this->_error['20000'], '20000');
        $this->_exit($this->_error['21000'], '21000');
    }

    /**
     * 发送验证码
     */
    public function actionSendCode() {
        if (!isset($_POST['mobile']) || !$_POST['mobile'])
            $this->_exit($this->_error['20001'], '20001');
        $mobile = trim($_POST['mobile']);
        if (!$this->isMobile($mobile))
            $this->_exit($this->_error['20002'], '20002');
        $res = User::model()->isMobile($mobile);
        if ($res <= 0)
            $this->_exit($this->_error['20039'], '20039');
        $verify = User::model()->getVerifyInfo($mobile);
        if ($verify && $verify['ValidTime'] > time()) {
            $this->sendCode($mobile, $verify['VerifyCode'], 2);
            $this->_exit($this->_error['20000'], '20000');
        } else {
            User::model()->deleteCode($mobile);
            $PostCode = $this->getPostCode(); //随机生成的验证码
            $time = time() + 300;
            $send = $this->sendCode($mobile, $PostCode, 2);
            if (!$send) {
                $r = User::model()->insertPostCode($mobile, $PostCode, $time);
                if ($r < 0)
                    $this->_exit($this->_error['21000'], '21000');
                $this->_exit($this->_error['20000'], '20000');
            }
        }
    }

    /**
     * 判断验证码是否正确
     */
    public function actionVerifyCode() {
        if (!isset($_POST['mobile']) || !$_POST['mobile'])
            $this->_exit($this->_error['20001'], '20001');
        if (!isset($_POST['postcode']) || !$_POST['postcode'])
            $this->_exit($this->_error['20004'], '20004');
        $mobile = trim($_POST['mobile']);
        $verifycode = trim($_POST['postcode']);
        $verify = User::model()->getVerifyInfo($mobile);
        if ($verifycode != $verify['VerifyCode'])
            $this->_exit($this->_error['20005'], '20005');
        if ($verify['ValidTime'] < time()) {
            User::model()->deleteCode($mobile);
            $this->_exit($this->_error['20006'], '20006');
        }
        User::model()->deleteCode($mobile);
        $this->_exit($this->_error['20000'], '20000');
    }

    /**
     * 找回密码/设置新密码
     */
    public function actionSetNewPassword() {
        if (!isset($_POST['mobile']) || !$_POST['mobile'])
            $this->_exit($this->_error['20001'], '20001');
        if (!isset($_POST['newpass']) || !$_POST['newpass'])
            $this->_exit($this->_error['20025'], '20025');
        $mobile = trim($_POST['mobile']);
        $new = md5(trim($_POST['newpass']));
        $uid = User::model()->getUserId($mobile);
        if ($uid) {
            User::model()->changePassword($uid, $new);
            $this->_exit($this->_error['20000'], '20000', $uid, 'uid');
        }
        $this->_exit($this->_error['21000'], '21000');
    }

    /**
     *  登录接口
     */
    public function actionLogin() {
        if (!isset($_POST['mobile']) || !$_POST['mobile'])
            $this->_exit($this->_error['20001'], '20001');
        if (!isset($_POST['password']) || !$_POST['password'])
            $this->_exit($this->_error['20023'], '20023');
        if (!isset($_POST['mac']) || !$_POST['mac'])
            $this->_exit($this->_error['20028'], '20028');
        if (!isset($_POST['iostoken']))
            $this->_exit($this->_error['20034'], '20034');
        $mobile = trim($_POST['mobile']);
        $password = md5(trim($_POST['password']));
        $mac = trim($_POST['mac']);
        $iostoken = $_POST['iostoken'] ? str_replace(' ', '', trim($_POST['iostoken'])) : '';
        $uid = User::model()->isMobile($mobile);
        if (!$uid)
            $this->_exit($this->_error['20027'], '20027');
        $pass = User::model()->Login($mobile);
        if ($pass != $password)
            $this->_exit($this->_error['20026'], '20026');
        $token = substr(md5($uid . $mac), 0, 16);
        $time = time() + 7 * 24 * 3600;
        User::model()->setIosToken($uid, $iostoken);
        $r = User::model()->haveToken($uid);
        if (empty($r))
            User::model()->insertToken($uid, $token, $time);
        User::model()->updateToken($uid, $token, $time);
        $this->_exit($this->_error['20000'], '20000', array('uid' => $uid, 'token' => $token), 'data');
    }

    /**
     * 登出接口
     */
    public function actionLoginOut() {
        if (!isset($_POST['uid']) || !$_POST['uid'])
            $this->_exit($this->_error['20007'], '20007');
        $uid = trim($_POST['uid']);
        $res = User::model()->loginOut($uid);
        if ($res)
            $this->_exit($this->_error['20000'], '20000');
    }

    /**
     * 添加推广号
     */
    public function actionAddPremote() {
        if (!isset($_POST['uid']) || !$_POST['uid'])
            $this->_exit($this->_error['20007'], '20007');
        if (!isset($_POST['tuid']) || !$_POST['tuid'])
            $this->_exit($this->_error['20063'], '20063');

        $params = array();
        $params['tuid'] = intval($_POST['tuid']);
        $params['uid'] = trim($_POST['uid']);
        $tuid = $params['tuid'] - Yii::app()->params['tgbase'];

        $user = User::model()->findByPk($params['uid']);
        $params['regtime'] = $user['RegisterTime'];

        //判断推广号是否存在
        $u = User::model()->findByPk($tuid);
        if (!$u) {
            $this->_exit($this->_error['20064'], '20064');
        }
        //判断记录是否已存在
        $pro = TgPremote::model()->findOne($params['uid'], $tuid);
        if (!empty($pro)) {
            $this->_exit($this->_error['20065'], '20065');
        }

        if (TgPremote::model()->addPremote($params)) {
            //添加推广号的奖励(此处奖励新用户，推广员的奖励统一由推广员系统后台脚本发放)
            $incoins = Yii::app()->params['profl'] * Yii::app()->params['coinratio'];
            $usercoin = Usercoins::model()->findByPk($params['uid']);
            $usercoin->FreeCoins = $usercoin->FreeCoins + $incoins;
            $usercoin->save();

            //添加收入记录
            $inhistory = new Coinshistory();
            $inhistory->Uid = $params['uid'];
            $inhistory->Coins = Yii::model()->params['profl'];
            $inhistory->Type = 2;
            $inhistory->CallId = 0;
            $inhistory->datetime = time();
            $inhistory->save();

            $this->_exit($this->_error['20000'], '20000');
        } else {
            $this->_exit($this->_error['21000'], '21000');
        }
    }

}
