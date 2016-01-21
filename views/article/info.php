<?php
$this->widget('SideBar', array("view" => "articleside", "action" => $this->getAction()->id, "type" => $type, "types" => $types));
?>
<!--文章详情-->
<div id="mainContent">
    <?php
    if (!empty($info)) {
        ?>
        <div class="artTitle">
            <h1><?php echo $info['title'] ?></h1>
            <div><?php echo date("Y-m-d H:i:s", $info['addtime']); ?></div>
        </div>
        <div class="artContent">
            <?php echo $info['content'] ?>
        </div>
        <?php
    } else {
        ?>
        <div class="noContent">未找到指定的内容</div>
        <?php
    }
    ?>
</div>