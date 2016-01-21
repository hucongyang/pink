<!--登录框/登录后用户信息-->
<div >
    <?php
    if (!empty($user)) {
        ?>
        <!--用户基本信息-->
        <div class="loginbox">
            <img src="<?php echo HOST_HTTP_HEAD_IMG . $user['Photo'] ?>" width="160" />
            <h1><?php echo $user['NickName'] ?></h1>
            <span>手机号：<?php echo $user['Mobile']; ?></span><br>
            上个月成功推广：
            <span><b style="color:red;"><?php echo intval($archieve[0]['num']); ?></b>个注册用户</span>　　
            <span><b style="color:red;"><?php echo intval($archieve[1]['num']); ?></b>个通话用户</span>　　
            <span><b style="color:red;"><?php echo intval($archieve[2]['num']); ?></b>个充值用户</span>
            <br>
            <input type="button" onclick="location.href='<?php echo Yii::app()->createUrl('user/index'); ?>'" value="进入我的推广"/>
            <a href="<?php echo Yii::app()->createUrl('index/logout'); ?>">退出</a>
        </div>
        <?php
    } else {
        ?>
        <!--登录框-->
        <div class="loginbox">
            <div class="loginleft">
                <img src="<?php echo Yii::app()->request->baseUrl; ?>/image/login.jpg" alt="" />
            </div>
            <div class="loginbar"></div>
            <div class="loginform">
                <form id="loginform" action="<?php echo Yii::app()->createUrl('index/login'); ?>" method="post">
                    <label>登录账号：</label><input type="text" placeholder="输入您的电话号码" name="username" class="ltxt" value="" /><br>
                    <label>登录密码：</label><input type="password" name="pwd" class="ltxt" value="" /><br>
                    <?php
                    if ($logErr > 0) {
                        ?>
                        <input type="text" name="valid" class="ltxt lsmall" value="" size="4" placeholder="验证码" />
                        <img src="<?php echo Yii::app()->createUrl('index/valid'); ?>" id="vcode" onclick="this.src='<?php echo Yii::app()->createUrl('index/valid'); ?>';" style="cursor:pointer;" title="点击刷新"/></a>
                        <span class="ltip" onclick='$("#vcode").trigger("click");'>看不清换一张</span>
                        <?php
                    }
                    ?>
                    <input id="loginBtn" type="button" value="登　录" class="lbtn" onclick="login();" />
                </form>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<script type="text/javascript">
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
            JBox.alert("用户名不能为空");
            return false;
        }
        if($("input[name=pwd]").val() == "") {
            JBox.alert("密码不能为空");
            return false;
        }
<?php
if ($logErr > 0) {
    ?>
                if($("input[name=valid]").val() == "") {
                    JBox.alert("验证码不能为空");
                    return false;
                }
    <?php
}
?>
        $.ajax({
            url:"<?php echo Yii::app()->createUrl('index/login'); ?>",
            type:"post",
            dataType:"json",
            data:$("#loginform").serialize(),
            success:function(json) {
                if(json.status ==1 ) {
                    location.href = '<?php echo Yii::app()->createUrl('user/index'); ?>';
                } else if (json.status == 2) {
                    JBox.alert(json.msg, function() {
                        location.href = location.href;
                    });
                } else {
                    JBox.alert(json.msg, function() {
                        $("#vcode").trigger("click");
                    });
                }
            }
        });
    }
</script>