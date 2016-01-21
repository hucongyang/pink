<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title><?php echo CHtml::encode(Yii::app()->params['SEO_TITLE']); ?> - 石榴裙</title>
        <meta name="keywords" content="<?php echo CHtml::encode(Yii::app()->params['SEO_KEYWORDS']); ?>">
        <meta name="description" content="<?php echo CHtml::encode(Yii::app()->params['SEO_DESCRIPTION']); ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.min.css">
        <link href="<?php echo Yii::app()->request->baseUrl; ?>/js/artdialog/skins/twitter.css"  rel="stylesheet" />
        <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/head.js"></script>
        <script>
            head.js(
            {'jquery':'<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.js'},
            '<?php echo Yii::app()->request->baseUrl; ?>/js/common.js',
            "<?php echo Yii::app()->request->baseUrl; ?>/js/artdialog/artDialog.js",
            function() {

            });
        </script>
    </head>
    <body>
        <!--头部-->
        <div class="header">
            <div class="top">
                <div class="topitem mw">
                    <span class="hometitle"><a href="http://122.227.43.176/premote/467" target="_blank">石榴裙官网</a></span>
                    <div class="logreg">
                        <?php
                        if (isset(Yii::app()->session['userinfo'])) {
                            ?>
                            欢迎您，<a href="<?php echo Yii::app()->createUrl('user/index'); ?>" title="进入个人用户中心"><?php echo Yii::app()->session['userinfo']['NickName']; ?></a>
                            <a href="<?php echo Yii::app()->createUrl('index/logout'); ?>" title="退出登录">注销</a>
                            <?php
                        } else {
                            ?>
                            <a href="<?php echo Yii::app()->request->baseUrl; ?>/index.php/index/login">登录</a>
                            <a href="<?php echo Yii::app()->request->baseUrl; ?>/index.php/index/reg">注册</a>
                            <?php
                        }
                        ?>

                    </div>
                </div>
            </div>

            <div class="nav mw" id="header">
                <div class="logo">
                    <a href="http://122.227.43.176/premote/467" title="<?php echo CHtml::encode(Yii::app()->name); ?>">
                        <img src="<?php echo Yii::app()->request->baseUrl; ?>/image/logo.png" alt="<?php echo CHtml::encode(Yii::app()->name); ?>" />
                    </a>
                </div>
                <?php
                if ($this->getAction()->id != login) {
                    ?>
                    <ul class="menu">
                        <li>
                            <a href="<?php echo Yii::app()->createUrl(''); ?>"<?php if (Yii::app()->controller->name == "index" && $this->getAction()->id == "index") echo ' class="active"' ?>>首页</a>
                        </li>
                        <li>
                            <a href="<?php echo Yii::app()->createUrl('user/index'); ?>"<?php if (Yii::app()->controller->name == "user") echo ' class="active"' ?>>我的推广</a>
                        </li>
                        <li>
                            <a href="<?php echo Yii::app()->createUrl('article/index'); ?>"<?php if (Yii::app()->controller->name == "article" && $this->getAction()->id == "index") echo ' class="active"' ?>>新闻公告</a>
                        </li>
                        <li>
                            <a href="<?php echo Yii::app()->createUrl('index/qa'); ?>"<?php if ($this->getAction()->id == "qa") echo ' class="active"' ?>>推广指南</a>
                        </li>
                    </ul>
                    <?php
                }
                ?>
            </div>

        </div>

        <div class="mainContent mw">
            <?php echo $content; ?>

            <div class="fix"></div>
            <!--footer-->
            <div class="footer">
                <ul class="footnav">
                    <li>
                        <a href="<?php echo Yii::app()->createUrl('kefu/index'); ?>">帮助中心</a>
                    </li>
                    <li>
                        <a href="<?php echo Yii::app()->createUrl('index/service'); ?>">服务条款</a>
                    </li>
                    <li>
                        <a href="<?php echo Yii::app()->createUrl('index/policy'); ?>">隐私政策</a>
                    </li>
                    <li class="last">
                        <a href="<?php echo Yii::app()->createUrl('index/about'); ?>">关于我们</a>
                    </li>
                </ul>
                <div class="footbox">
                    <div class="about">
                        <h2>关于我们</h2>
                        <p>石榴裙是一个为美女帅哥搭建畅聊的平台。用户可以通过LBS搜索附近的TA，并可以通过拨打电话和TA畅聊。</p>
                    </div>
                    <div class="weibo">
                        <img src="<?php echo Yii::app()->request->baseUrl; ?>/image/weibo.png" />
                        <a href="http://weibo.com/u/3650739730" class="icon2 addweibo"></a>
                    </div>
                    <div class="contact">
                        <h2>联系我们</h2>
                        <p>电话：021-62918230</p>
                        <p>传真：021-62918230</p>
                        <p>邮编：200000</p>
                        <p>邮箱: 467app@gmail.com</p>
                    </div>
                </div>
            </div>
        </div>
        <!--QR-->
        <div class="qrbox" id="qrbox" style="display: none;">
            <a class="icon2 jybtn" href="<?php echo Yii::app()->createUrl('kefu/index'); ?>"></a>
            <div class="icon2 kefu"></div>
            <div class="kefunum">62918230</div>
            <div class="qrcode">
                <img src="<?php echo Yii::app()->request->baseUrl; ?>/image/qrcode.png" width="80" />
                关注<br>石榴裙微信
            </div>
            <div class="icon2 addweibo"></div>
        </div>
        <script>
            head.ready(function(){ 
                var head = $("#header");
                var _left = head.offset().left + head.width();
                var _top = head.offset().top + head.height() + 14;
                $("#qrbox").css({left:_left,top:_top}).show();
                
                $(window).resize(function() {
                    var _left = head.offset().left + head.width();
                    var _top = head.offset().top + head.height() + 14;
                    $("#qrbox").css({left:_left,top:_top}).show();
                })
            })
        </script>
    </body>
</html>