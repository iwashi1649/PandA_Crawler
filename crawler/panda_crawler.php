<?php
$here = dirname(__FILE__).'/';
require($here."./panda.php");
//settingsの読み込み
$json = file_get_contents($here."./settings.json");
$settings = json_decode($json, true);
$username=$settings['username'];
$password=$settings['password'];
$login = Login($username,$password);
if(!$login)
{
    exit("failed to login\n");
}
$sites = $settings['sites'];
foreach($sites as $data)
{
    DownloadContent($data['siteId']);
}
?>