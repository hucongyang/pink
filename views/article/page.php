<!--文章详情-->
<?php
if (!empty($info)) {
    ?>
    <div class="pageTitle">
        <img src="<?php echo Yii::app()->request->baseUrl; ?>/image/ibanner.png"/>
        <h1><?php echo $info['title'] ?></h1>
    </div>
    <div class="pageInfo">
        <?php echo $info['content'] ?>
    </div>
    <?php
} else {
    ?>
    <div class="pageNo">未找到指定的内容</div>
    <?php
}
?>