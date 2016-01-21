<?php

/**
 *  接口公用方法
 * @author Latsu
 *
 */
class ApiPublicController extends Controller {

    const HOST_HTTP_HEAD_IMG = 'http://122.227.43.176/uploads/head_img/';
    const HOST_HTTP_BG_IMG = 'http://122.227.43.176/uploads/bg_img/';
    const HOST_HTTP_SOUND = 'http://122.227.43.176/uploads/sound/';
    const HOST_HTTP_PHOTO = 'http://122.227.43.176/uploads/photo/';
    const HOST_HTTP_TRENDS = 'http://122.227.43.176/uploads/trends/';
    const HOST_HTTP_BG_SOUND = 'http://122.227.43.176/uploads/bgsound/';

    public $_error;

    public function __construct() {
        require_once 'ErrorCode.php';
        $this->_error = $ErrorCode;
    }

    /**
     *  验证手机号码
     */
    public function isMobile($mobile) {
        if (preg_match("/^1[3|5|8]\d{9}$/", $mobile)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *  生成验证码
     */
    public function getPostCode() {
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= rand(0, 9);
        }
        return $code;
    }

    /**
     *  页面返回JSON数据
     */
    public function _exit($msg, $resultType = '', $param = '', $paramName = '') {
        if ($param == '')
            exit(json_encode(array('msg' => $msg, 'res' => $resultType)));
        exit(json_encode(array('msg' => $msg, 'res' => $resultType, $paramName => $param)));
    }

    /**
     *  计算字符串长度
     * @param string $str
     * @return int num
     */
    public function strlen_str($str) {
        $len = strlen($str);
        $i = 0;
        while ($i < $len) {
            if (preg_match("/^[" . chr(0xa1) . "-" . chr(0xff) . "]+$/", $str[$i])) {
                $i+=2;
            } else {
                $i+=1;
            }
        }
        return $i;
    }

    public function ios_push($iosToken, $message, $badge) {
        $passphrase = '1234';
        $ctx = stream_context_create();
        //如果在Windows的服务器：
        $pem = dirname(__FILE__) . '/' . 'dev_ps.pem';
        stream_context_set_option($ctx, 'ssl', 'local_cert', $pem);
        //linux 的服务器直接写pem的路径即可
        //stream_context_set_option($ctx, 'ssl', 'local_cert', 'dev_ps.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        //$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
        //这个是沙盒测试地址
        $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        if (!$fp) exit("链接失败: $err $errstr" . PHP_EOL);
        //echo '已连接到 APNS' . PHP_EOL;
        $body['aps'] = array(
            'alert' => $message,
            'badge' => $badge,
            'sound' => 'default'
        );
        $payload = json_encode($body);
        $msg = chr(0) . pack('n', 32) . pack('H*', $iosToken) . pack('n', strlen($payload)) . $payload;
        $result = fwrite($fp, $msg, strlen($msg));
        print_r($body['aps']);
        fclose($fp);
    }

    /**
     *  发送验证码
     */
    public function sendCode($mobile, $PostCode, $type = 1) {

        //发送手机验证码,调用接口, 传入手机号码和验证码
        $data = array();
        $url = 'http://mobile.5173.com/MobileAPI/SendSingleMessage';
        $data['m_clientIP'] = '192.168.2.161';
        $data['m_sign'] = MD5('taohua192.168.2.161');
        $data['category'] = '7701';
        $data['mobile'] = $mobile;
        $data['content'] = '尊敬的用户你好, 你的验证码是' . $PostCode . '【石榴裙】';
        $output = Yii::app()->curl->post($url, $data);

        $ob = json_decode($output);

        //发送短信，记录发送日志
        $log['sendtime'] = date("Y-m-d H:i:s");
        $log['mobile'] = $mobile;
        $log['type'] = $type;
        $log['ResultNo'] = $ob->ResultNo;
        $log['message'] = $ob->ResultDescription;
        
        Smslog::model()->addLog($log);

        return $ob->ResultNo;
    }
}