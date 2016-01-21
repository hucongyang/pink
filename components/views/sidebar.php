<!--用户页功能导航-->
<dl class="sidebar">
    <dt class="first<?php if ($controller == "index") echo ' active' ?>"><a href="<?php echo Yii::app()->createUrl('user/index'); ?>">我的推广首页</a></dt>
    <dt<?php if ($controller == "archieve") echo ' class="active"' ?>><a href="javascript:void(0)">我的业绩</a></dt>
    <dl<?php if ($controller == "archieve") echo ' class="active"' ?>>
        <dd<?php if ($action == "archieve") echo ' class="active"' ?>><em class="myicon"></em><a href="<?php echo Yii::app()->createUrl('user/archieve'); ?>">我的业绩</a></dd>
        <dd class="last<?php if ($action == "withdraw" || $action=="godraw") echo ' active' ?>"><em class="myicon"></em><a href="<?php echo Yii::app()->createUrl('user/withdraw'); ?>">提现</a></dd>
    </dl>
    <dt<?php if ($controller == "set") echo ' class="active"' ?>><a href="javascript:void(0)">个人管理</a></dt>
    <dl<?php if ($controller == "set") echo ' class="active"' ?>>
        <dd<?php if ($action == "setting") echo ' class="active"' ?>><em class="myicon"></em><a href="<?php echo Yii::app()->createUrl('user/setting'); ?>">个人资料</a></dd>
        <dd<?php if ($action == "changepwd") echo ' class="active"' ?>><em class="myicon"></em><a href="<?php echo Yii::app()->createUrl('user/changepwd'); ?>">修改密码</a></dd>
        <dd<?php if ($action == "message") echo ' class="active"' ?>><em class="myicon"></em><a href="<?php echo Yii::app()->createUrl('user/message'); ?>">系统消息</a></dd>
        <dd><em class="myicon"></em><a href="<?php echo Yii::app()->createUrl('index/logout'); ?>">退出账号</a></dd>
    </dl>
</dl>