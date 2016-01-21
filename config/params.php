<?php

// this contains the application parameters that can be maintained via GUI
return array(
    'SEO_TITLE' => "首页",
    //提现兑换比例
    'ratio'=> '1',
    //充值列表
    'priceList' => array(
        array("coin"=>300, 'money'=>300),
        array("coin"=>200, 'money'=>200),
        array("coin"=>100, 'money'=>100),
        array("coin"=>50, 'money'=>50),
        array("coin"=>30, 'money'=>30),
        array("coin"=>10, 'money'=>10),
    ),
    //充值对应参数
    'payList' => array(
        '300' => 0.01,
        '200' => 0.01,
        '100' => 0.01,
        '50'  => 0.01,
        '30'  => 0.01,
        '10'  => 0.01,
    ),
    //前后台石榴币比例
    'coinratio' => 100,
    //添加推广号奖励
    'profl' => 5,
    //推广号基数(10000)
    'tgbase' => 10000,
    'coins' => 1,
    'allcoins' => 0.5,
    'agent' =>0.3,
    //充值返利比例
    "payfl" => 0.1,
);
