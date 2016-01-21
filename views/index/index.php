<link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/js/tip/tip-yellowsimple/tip-yellowsimple.css" type="text/css" />
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/tip/jquery.poshytip.js"></script>

<div class="loginbox">
    <div class="loginform">
        <form id="loginform" action="<?php echo Yii::app()->createUrl('index/login'); ?>" method="post">
            <input type="text" placeholder="输入您的手机号" id="username" name="username" class="ltxt" value="" /><br>
            <input type="password" placeholder="密码" id="password" name="pwd" class="ltxt" value="" /><br>
            <input id="loginBtn" type="button" value="登　录" class="lbtn" onclick="login();" />
        </form>
    </div>
</div>
<script>
//<![CDATA[
    head.js(
            '<?php echo Yii::app()->request->baseUrl; ?>/js/tip/jquery.poshytip.js',
            function() {
                $('#username, #password').poshytip({
                    className: 'tip-yellowsimple',
                    showOn: 'focus',
                    alignTo: 'target',
                    alignX: 'center',
                    alignY: 'top',
                    offsetX: 0,
                    offsetY: 5,
                    showTimeout: 50
                });
            });
            head.ready(function() {
                $("#loginform input").keydown(function(e) {
                    if(e.keyCode == 13) {
                        $("#loginBtn").trigger("click");
                    }
                });
    });
    /**
     * 登录
     */
    function login() {
        if($("input[name=username]").val() == "") {
            $('#username').poshytip('update', '手机号不能为空').focus();
            return false;
        }
        if($("input[name=pwd]").val() == "") {
            $('#password').poshytip('update', '密码不能为空').focus();
            return false;
        }
        $.ajax({
            url:"<?php echo Yii::app()->createUrl('index/login'); ?>",
            type:"post",
            dataType:"json",
            data:$("#loginform").serialize(),
            success:function(json) {
                if(json.status ==1 ) {
                    location.href = '<?php echo Yii::app()->createUrl('user/index'); ?>';
                } else {
                    location.href = '<?php echo Yii::app()->createUrl('index/login'); ?>';
                    //$('#password').poshytip('update', '手机号或密码错误').focus();
                }
            }
        });
    }
    //]]>
</script>