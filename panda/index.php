<?php
$json = file_get_contents("../crawler/settings.json");
$settings = json_decode($json, true);
$json = file_get_contents("../crawler/files.json");
$files = json_decode($json, true);

//次の講義を取得
$days = ['月','火','水','木','金'];
$today = date('w')-1;
$now = date('H')*60+date('i');
$jigen=0;
$time =[615,720,885,990,1095];
for($i=0;$i<5;$i++)
{
    if($now<$time[$i])
    {
        $jigen = $i+1;
        break;
    }
}
if($jigen==0)
{
    //翌日
    $today+=1;
    $jigen = 1;
    if($today == 5 || $today==6) $today = 0;
}
//$today曜日の$jigen以降の講義を表示させれば良い
for($i = 0;$i<5;$i++)
{
    $j = ($today + $i)%5;
    foreach($settings['sites'] as $data)
    {
        if($data['day'] == $days[$j])
        {
            if($data['period'] >= $jigen)
            {
                $next = $data['siteId'];
                $today = $days[$j];
                $jigen = $data['period'];
                $title = $data['siteTitle'];
                break;
            }
        }
    }
    if(isset($next)) break;
    $jigen = 1;
}

?>
<html>
    <header>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <link rel="stylesheet" type="text/css" href="./styles.css" />
    </header>
    <body>
        <h1>PandA Resource Viewer</h1>
        <h2>Next</h2>
        <a href="<?php echo("./{$next}");?>">
        <div class="next">
                <h3><?php echo($title)?></h3>
                <p><?php echo($today."曜".$jigen)?>限</P>
            </div>
</a>
        <?php
        foreach($days as $day)
        {?>
            <h2><?php echo($day."曜日")?></h2>
            <?php
            foreach($settings['sites'] as $data)
            {
                if($data['day'] == $day)
                {?>
                <a href="<?php echo("./{$data['siteId']}");?>">
                <div class="site">
                <h3><?php echo($data['siteTitle'])?></h3>
                <p><?php echo($data['period'])?>限</P>
            </div>
                </a>
                <?php }
            }
            ?>
        <?php }
        ?>
        <P>
            情報取得:<?php echo($files['lastChekDate']);?>
        </P>
        <P>
            最終更新:<?php echo($files['lastModified']);?>
        </P>
    </body>
</html>