<?php

/**
 * 共用函数辅助类
 * @auth stuart.shi
 * @create 2013-08-20
 */
class Func {

    /**
     * 生成验证码
     * @param type $sessionName
     * @param type $width
     * @param type $height
     */
    static function validatePic($sessionName = "defaultSess", $width = 0, $height = 0) {
        require(Yii::app()->basePath . '/components/Validate.php');
        $validateImage = new Validate();

        if ($width != 0) {
            $validateImage->set(array("width" => $width));
        }
        if ($height != 0) {
            $validateImage->set(array("height" => $height));
        }

        $code = $validateImage->getRandChar();

        Yii::app()->session[$sessionName] = strtolower($code);
        return $validateImage->genImage();
    }

    /**
     * 生成验证码2
     * @param type $sessionName
     * @param type $width
     * @param type $height
     */
    static function validatePic2($sessionName = "defaultSess", $width = 0, $height = 0) {
        require(Yii::app()->basePath . '/components/SecurImage.php');
        $validateImage = new Securimage();
        $validateImage->use_gd_font = true;
        $validateImage->gd_font_file = Yii::app()->basePath."/components/font/crass.gdf";
        $validateImage->draw_lines = false;
        $validateImage->arc_linethrough = false;
        $validateImage->sess_name = $sessionName;
        if ($width != 0) {
            $validateImage->image_width = $width;
        }
        if ($height != 0) {
            $validateImage->image_height = $height;
        }
        $code = $validateImage->createCode();
        Yii::app()->session[$sessionName] = strtolower($code);

        $validateImage->show();
    }

    /**
     * 校验验证码
     * @param type $code
     * @param type $sessionName
     * @return type
     */
    static function checkValidateCode($code, $sessionName = 'defaultSess') {
        return $code == Yii::app()->session[$sessionName];
    }

    /**
     * 获取随机字符串
     */
    static function simpleRand($num = 1) {
        if (intval($num) < 1) {
            $num = 1;
        }
        $str = 'abcdefghijklmnopqrstuvwxyz1234567890';
        $r = '';
        for ($i = 0; $i < $num; $i++) {
            $tmp = rand(0, strlen($str) - 1);
            $r .= $str {$tmp};
        }
        return $r;
    }

    /**
     * 判断是否是邮件地址
     *
     * @param $address
     */
    static function isEmail($address) {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $address)) ? false : true;
    }

    /**
     * 判断是否mobile phone num
     * @param type $phone
     * @return type
     */
    static function isMobile($phone) {
        return (!preg_match("/^1[1-9]\d{9}$/", $phone)) ? false : true;
    }

    /**
     * 获取IP
     * @return type
     */
    static function getIP() {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = "0";
        return($ip);
    }

    /**
     * 数字转ip
     * @param unknown_type $get
     * @return string
     */
    static function intToIP($intip) {
        $intip += 0;
        $s0 = ($intip >> 0) & 0x000000ff;
        $s1 = ($intip >> 8) & 0x000000ff;
        $s2 = ($intip >> 16) & 0x000000ff;
        $s3 = ($intip >> 24) & 0x000000ff;
        return $s3 . '.' . $s2 . '.' . $s1 . '.' . $s0;
    }

    /**
     * ip转数字
     * @param unknown_type $get
     * @return string
     */
    static function ipToInt($ip) {
        $aIP = explode('.', $ip);
        $iIP = ($aIP [0] << 24) | ($aIP [1] << 16) | ($aIP [2] << 8) | $aIP [3];
        if ($iIP < 0) {
            $iIP += 4294967296;
        }
        return $iIP;
    }

    /**
     * 截取字符串
     * @param unknown_type $string
     * @param unknown_type $length
     * @param unknown_type $dot
     * @param unknown_type $charset
     */
    static function cutString($string, $length, $dot = "...", $charset = 'utf-8') {
        if (strlen($string) <= $length) {
            return $string;
        }
        $string = str_replace(array('　', ' ', '&', '"', '<', '>'), array('', '', '&', '"', '<', '>'), $string);
        $strcut = '';
        if (strtolower($charset) == 'utf-8') {
            $n = $tn = $noc = 0;
            while ($n < strlen($string)) {
                $t = ord($string [$n]);
                if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $tn = 1;
                    $n++;
                    $noc++;
                } elseif (194 <= $t && $t <= 223) {
                    $tn = 2;
                    $n += 2;
                    $noc += 2;
                } elseif (224 <= $t && $t < 239) {
                    $tn = 3;
                    $n += 3;
                    $noc += 2;
                } elseif (240 <= $t && $t <= 247) {
                    $tn = 4;
                    $n += 4;
                    $noc += 2;
                } elseif (248 <= $t && $t <= 251) {
                    $tn = 5;
                    $n += 5;
                    $noc += 2;
                } elseif ($t == 252 || $t == 253) {
                    $tn = 6;
                    $n += 6;
                    $noc += 2;
                } else {
                    $n++;
                }
                if ($noc >= $length) {
                    break;
                }
            }
            if ($noc > $length) {
                $n -= $tn;
            }
            $strcut = substr($string, 0, $n);
        } else {
            for ($i = 0; $i < $length; $i++) {
                $strcut .= ord($string [$i]) > 127 ? $string [$i] . $string [++$i] : $string [$i];
            }
        }
        return $strcut . $dot;
    }

    /**
     * 返回收入类型
     * @param type $typeid
     * @return type
     */
    static function getInType($typeid) {
        return Yii::app()->params['intypes'][$typeid];
    }

    /**
     * 返回指定月份的起始和终止时间戳（默认上个月）
     */
    static function startAndEnd($month = 0) {
        if ($month == 0) {
            $year = date('Y');
            $mon = date('m') - 1;
        } else {
            $year = date('Y', strtotime($month));
            $mon = date('m', strtotime($month));
        }

        $m = date('Y-m-d', mktime(0, 0, 0, $mon, 1, $year));
        $start = strtotime($m);
        $end = strtotime(date('Y-m-d', mktime(0, 0, 0, $mon, date('t', strtotime($m)), $year)));
        return array("start" => $start, "end" => $end);
    }

    /**
     * 返回指定月份的起始和终止时间戳（默认上个月）
     */
    static function curWeek() {
        $monday = mktime(0, 0, 0, date("m", strtotime("last Monday")), date("d", strtotime("last Monday")), date("Y", strtotime("last Monday")));
        $sunday = mktime(0, 0, 0, date("m", strtotime("next Sunday")), date("d", strtotime("next Sunday")), date("Y", strtotime("next Sunday")));
        return array("monday" => $monday, "sunday" => $sunday);
    }

    static function dayTime($str = "") {
        if ($str != "") {
            $date = strtotime($str);
        } else {
            $date = time();
        }
        $year = date("Y", $date);
        $month = date("m", $date);
        $day = date("d", $date);
        $dayBegin = mktime(0, 0, 0, $month, $day, $year); //当天开始时间戳
        $dayEnd = mktime(23, 59, 59, $month, $day, $year); //当天结束时间戳
        return array("start" => $dayBegin, "end" => $dayEnd);
    }

    static function lastMonth() {
        return date('Y-m', mktime(0, 0, 0, date('m') - 1, 1, date('Y')));
    }

    /**
     * 获取分页数据
     * @return \CPagination
     */
    static function getPageLinks($config) {
        $pages = new CPagination($config['totalCount']);
        $pages->pageSize = $config['pagesize'];
        $pages->route = $config['route'];

        $pages->applyLimit(new CDbCriteria());

        return $pages;
    }

    static function curlData($method = "POST", $data) {
        if (empty($data)) {
            return null;
        }
        $params = array();
        foreach ($data as $key => $v) {
            $params[] = $key . "=" . $v;
        }
        $params = implode("&", $params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://www.lxvoip.com/sendSMS.php');
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $data = curl_exec();
        curl_close($ch);
    }

}

?>