<?php
$this->widget('SideBar', array("view" => "articleside", "action" => $this->getAction()->id, "type" => $type, "types" => $types));
?>
<div id="mainContent">
    <div class="newslist">
        <h1>新闻公告 > <?php echo $typeName; ?></h1>
        <!--文章列表-->
        <table class="qaitems">
            <thead>
                <tr>
                    <th style="padding-left: 50px;">标题</th>
                    <th width="80" style="text-align:center;">发布日期</th>
                    <th width="60" style="text-align:center;">浏览数</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($list)) {
                    foreach ($list as $k => $item) {
                        ?>
                        <tr>
                            <td style="padding-left:30px;"><a href="<?php echo Yii::app()->createUrl('article/info/id/' . $item['id']); ?>" target="_blank"><?php echo $item['title']; ?></a></td>
                            <td style="text-align:center;"><span><?php echo date("Y-m-d H:i:s", $item['addtime']); ?></span></td>
                            <td style="text-align:center;"><?php echo $item['pv']?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
        <?php
        //分页widget代码: 
        $this->widget('MyPager', array('pages' => $pages));
        ?>
    </div>
</div>