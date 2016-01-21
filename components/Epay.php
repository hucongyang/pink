<?php

/*
 * 易付通接口
 * @auth stuart.shi
 */

class Epay {

    public $API_HOST = 'http://pay.e138.com/GateWay/Card/Default_V2.aspx';  //消卡api接口
    //request params
    public $p0_Cmd = "ChargeCardDirect";    //业务类型（非银行卡专业版支付请求固定值“ChargeCardDirect”）
    public $p1_MerId = "13585665865";   //商户编号
    public $p2_Order = "";  //商户订单号
    public $p3_Amt = "";    //支付金额
    public $p4_verifyAmt = true;    //是否较验订单金额
    public $p5_Pid = "getconins";   //产品名称
    public $p6_Pcat = "pay";    //产品类型
    public $p7_Pdesc = "pay";   //产品描述
    public $p8_Url = "";    //商户接收支付成功数据的地址
    public $pa_MP = '467epay';   //商户扩展信息
    public $pa7_cardAmt = "";   //卡面额组
    public $pa8_cardNo = "";    //卡号组
    public $pa9_cardPwd = "";   //卡密组
    public $pd_FrpId = "";  //支付渠道编码
    public $pr_NeedResponse = "1";  //应答机制
    public $pz_userId = ""; //用户ID
    public $pz1_userRegTime = "";   //用户注册时间
    public $hmac = "";  //签名数据
    public $key = '5c9c6ab062794475964695af6cffb58b';   //密钥
    //
    //response params
    public $r0_Cmd = "1";   //业务类型
    public $r1_Code = "";   //提交状态(1表示成功 其它状态表示失败)
    public $r6_Order = "";  //商户订单号
    public $rq_ReturnMsg = "";  //错误代码
    public $hmac_response = "";  //签名数据

    /**
     * 签名函数生成签名串
     */

    function getReqHmacString($basicParams, $merchantKey) {
        $basic = implode("", $basicParams);
        return $this->HmacMd5($basic, $merchantKey);
    }

    /**
     * 加密参数
     * @param type $data
     * @param type $key
     * @return type
     */
    public function HmacMd5($data, $key) {
        $b = 64;
        if (strlen($key) > $b) {
            $key = pack("H*", md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;

        return md5($k_opad . pack("H*", md5($k_ipad . $data)));
    }

    /**
     * 请求
     * @param type $data
     * @param type $method
     * @return null
     */
    public function curlData($data) {
        if (empty($data)) {
            return null;
        }
        $params = array();
        foreach ($data as $key => $v) {
            $params[] = $key . "=" . $v;
        }
        $params = implode("&", $params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->API_HOST . '?' . $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);

        if (!curl_errno($ch)) {
            $return = array();
            $results = explode("&", $result);
            foreach ($results as $k => $v) {
                $vv = explode("=", $v);
                $return[$vv[0]] = $vv[1];
            }
            return $return;
        } else {
            return curl_error($ch);
        }
        curl_close($ch);
    }

    /**
     * 返回卡类列表
     * @return string
     */
    public function getCardCodes($code = "") {
        $cards = array(
//            "JUNNET" => "骏网一卡通",
//            "SNDACARD" => "盛大卡",
            "SZX" => "神州行",
//            "ZHENGTU" => "征途卡",
//            "QQCARD" => "Q币卡",
            "UNICOM" => "联通卡",
//            "JIUYOU" => "久游卡",
//            "YPCARD" => "易付通e卡通",
//            "NETEASE" => "网易卡",
//            "WANMEI" => "完美卡",
//            "SOHU" => "搜狐卡",
            "TELECOM" => "电信卡",
//            "ZONGYOU" => "纵游一卡通",
//            "TIANXIA" => "天下一卡通",
//            "TIANHONG" => "天宏一卡通",
        );
        if ($code == "") {
            return $cards;
        } else {
            return isset($cards[$code]) ? $cards[$code] : "";
        }
    }

    /**
     * 返回错误信息
     * @param type $code
     * @return type
     */
    public function getError($code) {
        $errArr = array(
            "0" => "销卡成功，订单成功",
            "1" => "销卡成功，订单失败",
            "7" => "卡号卡密或卡面额不符合规则",
            "1002" => "本张卡密您提交过于频繁，请您稍后再试",
            "1003" => "不支持的卡类型（比如电信地方卡）",
            "1004" => "密码错误或充值卡无效",
            "1006" => "充值卡无效",
            "1007" => "卡内余额不足",
            "1008" => "余额卡过期（有效期1个月）",
            "1010" => "此卡正在处理中",
            "10000" => "未知错误",
            "2005" => "此卡已使用",
            "2006" => "卡密在系统处理中",
            "2007" => "该卡为假卡",
            "2008" => "该卡种正在维护",
            "2009" => "浙江省移动维护",
            "2010" => "江苏省移动维护",
            "2011" => "福建省移动维护",
            "2012" => "辽宁省移动维护",
            "2013" => "该卡已被锁定",
            "2014" => "系统繁忙，请稍后再试",
            "3001" => "卡不存在",
            "3002" => "卡已使用过",
            "3003" => "卡已作废",
            "3004" => "卡已冻结",
            "3005" => "卡未激活",
            "3006" => "密码不正确",
            "3007" => "卡正在处理中",
            "3101" => "系统错误",
            "3102" => "卡已过期",
        );

        return isset($errArr[$code]) ? $errArr[$code] : "";
    }

}

?> 