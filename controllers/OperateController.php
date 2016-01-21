<?php

class OperateController extends ApiPublicController {

    /**
     * 上传背景图
     */
    public function actionBgImg() {
        if (!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'], '20007');
        $uid = trim($_POST['uid']);
        $data = $_POST['photo'] ? trim($_POST['photo']) : file_get_contents("php://input");
        if ($data) {
            $ms = date('Ym', time());
            $path = Yii::app()->basePath . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'bg_img' . DIRECTORY_SEPARATOR . $ms . DIRECTORY_SEPARATOR;
            $imgName = MD5(time() . $this->getPostCode()) . '.jpg';
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
            $newFilePath = $path . $imgName;
            $newFile = fopen($newFilePath, 'w');
            fwrite($newFile, $data);
            fclose($newFile);
            $fileName = $ms . DIRECTORY_SEPARATOR . $imgName;
        } else {
            $fileName = '';
        }
        $img = $fileName ? self::HOST_HTTP_BG_IMG . str_replace('\\', '/', $fileName) : '';
        $res = Album::model()->updateBgImg($uid, $fileName);
        if ($res) $this->_exit($this->_error['20000'], '20000', $img, 'bgimg');
        $this->_exit($this->_error['21000'], '21000');
    }

    /**
     * 修改头像
     */
    public function actionHeadImg() {
        if (!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'], '20007');
        $uid = trim($_POST['uid']);
        $data = $_POST['headimg'] ? trim($_POST['headimg']) : file_get_contents("php://input");
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
            $m->ResizeImg($newFilePath, '160', '160', '0', $path . '160x160-' . $imgName);
            $m->ResizeImg($newFilePath, '240', '240', '0', $path . '240x240-' . $imgName);
        } else {
            $fileName = '';
        }
        $img = self::HOST_HTTP_HEAD_IMG . str_replace('\\', '/', $fileName);
        $res = Album::model()->updateHeadImg($uid, $fileName);
        if ($res)  $this->_exit($this->_error['20000'], '20000', $img, 'headimg');
        $this->_exit($this->_error['21000'], '21000');
    }

    /**
     * 上传 声音介绍
     */
    public function actionSound() {
        if (!isset($_POST['uid']) || !$_POST['uid'])  $this->_exit($this->_error['20007'], '20007');
        if (!isset($_POST['stime']) || !$_POST['stime']) $this->_exit($this->_error['20029'], '20029');
        $uid = trim($_POST['uid']);
        $time = trim($_POST['stime']);
        $data = $_POST['sound'] ? trim($_POST['sound']) : file_get_contents("php://input");
        if ($data) {
            $ms = date('Ym', time());
            $path = Yii::app()->basePath . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'sound' . DIRECTORY_SEPARATOR . $ms . DIRECTORY_SEPARATOR;
            $imgName = MD5(time() . $this->getPostCode()) . '.amr';
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
            $newFilePath = $path . $imgName;
            $newFile = fopen($newFilePath, 'w');
            fwrite($newFile, $data);
            fclose($newFile);
            $fileName = $ms . DIRECTORY_SEPARATOR . $imgName;
        } else {
            $fileName = '';
        }
        $res = Album::model()->updateSound($uid, $fileName, $time);
        $soundUrl = self::HOST_HTTP_SOUND . str_replace('\\', '/', UserInfo::model()->getUserVoice($uid));
        if ($res) $this->_exit($this->_error['20000'], '20000', $soundUrl, 'sound');
        $this->_exit($this->_error['21000'], '21000');
    }

    /**
     * 修改昵称
     */
    public function actionNickName() {
        if (!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'], '20007');
        $uid = trim($_POST['uid']);
        $name = trim($_POST['nickname']);
        $res = Album::model()->updateNickName($uid, $name);
        if ($res) $this->_exit($this->_error['20000'], '20000');
        $this->_exit($this->_error['21000'], '21000');
    }

    /**
     * 修改性别
     */
    public function actionSex() {
        if (!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'], '20007');
        $uid = trim($_POST['uid']);
        $sex = trim($_POST['sex']);
        $res = Album::model()->updateSex($uid, $sex);
        if ($res) $this->_exit($this->_error['20000'], '20000');
        $this->_exit($this->_error['21000'], '21000');
    }

    /**
     * 修改年龄
     */
    public function actionAge() {
        if (!isset($_POST['uid']) || !$_POST['uid'])
            $this->_exit($this->_error['20007'], '20007');
        $uid = trim($_POST['uid']);
        $age = trim($_POST['age']);
        $res = Album::model()->updateAge($uid, $age);
        if ($res)
            $this->_exit($this->_error['20000'], '20000');
        $this->_exit($this->_error['21000'], '21000');
    }

    /**
     * 修改签名
     */
    public function actionSign() {
        if (!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'], '20007');
        $uid = trim($_POST['uid']);
        $sign = trim($_POST['sign']);
        $res = Album::model()->updateSign($uid, $sign);
        if ($res) $this->_exit($this->_error['20000'], '20000');
        $this->_exit($this->_error['21000'], '21000');
    }

    /**
     * 设置聊天价格
     */
    public function actionSetPrice() {
        if (!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'], '20007');
        if (!isset($_POST['price'])) $this->_exit($this->_error['20030'], '20030');
        $uid = trim($_POST['uid']);
        $price = trim($_POST['price']);
        $res = UserInfo::model()->setPrice($uid, $price);
        if ($res) $this->_exit($this->_error['20000'], '20000');
        $this->_exit($this->_error['21000'], '21000');
    }
    /**
     *  我的金币
     */
    public function actionCoins() {
        if (!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'], '20007');
        $uid = trim($_POST['uid']);
        $res = Album::model()->getCoins($uid);
        $this->_exit($this->_error['20000'], '20000', $res, 'coins');
    }
    /**
     *  用户反馈
     */
    public function actionFeedback() {
        if (!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'], '20007');
        if (!isset($_POST['content'])) $this->_exit($this->_error['20031'], '20031');
        $uid = trim($_POST['uid']);
        $content = trim($_POST['content']);
        $res = Album::model()->feedBack($uid, $content);
        if ($res) $this->_exit($this->_error['20000'], '20000');
        $this->_exit($this->_error['21000'], '21000');
    }

    /**
     * 版本更新
     */
    public function actionVersion() {
        if (!isset($_POST['version']) || !$_POST['version']) $this->_exit($this->_error['20032'], '20032');
        $version = trim($_POST['version']);
        $res = Album::model()->getVersion($version);
        if ($res) $this->_exit($this->_error['20000'], '20000', $res, 'data');
        $this->_exit($this->_error['20000'], '20000');
    }

    /**
     * 发布动态
     */
    public function actionTrends() {
        if (!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'], '20007');
        if (!(isset($_POST['content']) && isset($_POST['image'])) || !($_POST['image'] || $_POST['content'])) $this->_exit($this->_error['20056'], '20056');
        if (!isset($_POST['area']) || !$_POST['area']) $this->_exit($this->_error['20042'], '20042');
        $uid = trim($_POST['uid']);
        $content = trim($_POST['content']);
        $area = trim($_POST['area']);
        $imgdata = $_POST['image'] ? trim($_POST['image']) : file_get_contents('php://input');
        if ($imgdata) {
            $ms = date('Ym', time());
            $path = Yii::app()->basePath . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'trends' . DIRECTORY_SEPARATOR . $ms . DIRECTORY_SEPARATOR;

            $imgName = MD5(time() . $this->getPostCode()) . '.jpg';
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
            $newFilePath = $path . $imgName;
            $newFile = fopen($newFilePath, 'w');
            fwrite($newFile, $imgdata);
            fclose($newFile);
            $fileName = $ms . DIRECTORY_SEPARATOR . $imgName;
            include Yii::app()->basePath . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'ResizeImage.php';
            $m = new ResizeImage();
            $m->ResizeImg($newFilePath, '230', '230', '0', $path . '230x230-' . $imgName);
            $m->ResizeImg($newFilePath, '120', '120', '0', $path . '120x120-' . $imgName);
        } else {
            $fileName = '';
        }
        $img = trim($fileName);
        $res = Trends::model()->addTrends($uid, $area, $content, $img);
        if ($res) $this->_exit($this->_error['20000'], '20000', array('img' => self::HOST_HTTP_TRENDS . str_replace('\\', '/', $img), 'tid' => $res), 'trends');
        $this->_exit($this->_error['21000'], '21000');
    }

    /**
     * 免打扰设置
     */
    public function actionSetStatus() {
        if (!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'], '20007');
        if (!isset($_POST['status'])) $this->_exit($this->_error['20060'], '20060');
        $status = trim($_POST['status']);
        $uid = trim($_POST['uid']);
        if ($status==1) {
            $res = User::model()->offLine($uid);
            if ($res) $this->_exit($this->_error['20000'], '20000');
        }
        if ($status==2 || $status==0) {
            $res = User::model()->onLine($uid);
            if ($res) $this->_exit($this->_error['20000'], '20000');
        }
    }

    /**
     * 获取背景音乐
     */
    public function actionBgSound() {
        $sound1 = array('name' => '安妮的仙境', 'bgsound' => self::HOST_HTTP_BG_SOUND . 'anni.mp3', 'author' => '班得瑞');
        $sound2 = array('name' => '绿袖子', 'bgsound' => self::HOST_HTTP_BG_SOUND . 'green.mp3', 'author' => '亨利');
        $sound = array($sound1, $sound2);
        $this->_exit($this->_error['20000'], '20000', $sound, 'sound');
    }

    /**
     * 删除动态
     */
    public function actionDeleteTrends() {
        if (!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'], '20007');
        if (!isset($_POST['tid']) || !$_POST['tid']) $this->_exit($this->_error['20057'], '20057');
        $uid = trim($_POST['uid']);
        $tid = trim($_POST['tid']);
        $res = Trends::model()->deleteTrends($uid, $tid);
        if ($res) $this->_exit($this->_error['20000'], '20000');
        $this->_exit($this->_error['21000'], '21000');
    }

    /**
     * 我的金币相关
     */
    public function actionCoinInfo() {
        if (!isset($_POST['uid']) || !$_POST['uid']) $this->_exit($this->_error['20007'], '20007');
        $uid = trim($_POST['uid']);
        //初设充值列表
        $res['priceList'] = Yii::app()->params['priceList'];
        //指定用户的当前金币数
        $coinall = User::model()->getCoinByUid($uid);
        $res['coins'] = $coinall['coins'];
        //可提现金额
        $wincoin = User::model()->getWinCoinByUid($uid);
        $res['money'] = $wincoin;
        $res['ucoins'] = $wincoin;
        $res['freecoins'] = $res['coins'] - $wincoin;
        $this->_exit($this->_error['20000'], '20000', $res, 'coininfo');
    }

}