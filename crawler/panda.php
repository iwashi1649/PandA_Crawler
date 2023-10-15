<?php
$here = dirname(__FILE__).'/';
$json = file_get_contents($here."./settings.json");
$settings = json_decode($json, true);
$cookie = "";
$files_path = $here."./files.json";
$files;

function Login($username, $password) //ログインしてcookieを取得する
{
    global $cookie;
    global $settings;
    global $here;
    $cookie = "{$settings['cookiePath']}{$username}.cookie";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($curl, CURLOPT_URL, "https://panda.ecs.kyoto-u.ac.jp/cas/login?service=https%3A%2F%2Fpanda.ecs.kyoto-u.ac.jp%2Fsakai-login-tool%2Fcontainer");
    $page1 = curl_exec($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);
    if ($info['url'] == "https://panda.ecs.kyoto-u.ac.jp/portal") 
    {
        //すでにログインできてる
        //echo('すでにログイン済み');
        return true;
    }

    //ログイン画面が表示されているなら"lt"の値を取得
    $pattern = '/<input type="hidden" name="lt" value="([^"]*)"/';
    if (preg_match($pattern, $page1, $result)) {
        $lt = $result[1];
    } else {
        echo ('failed to get lt ' . PHP_EOL."\n");
        return false;
    }

    //ログイン
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($curl, CURLOPT_URL, "https://panda.ecs.kyoto-u.ac.jp/cas/login?service=https%3A%2F%2Fpanda.ecs.kyoto-u.ac.jp%2Fsakai-login-tool%2Fcontainer");
    $post = array(
        '_eventId' => 'submit',
        'execution' => 'e1s1',
        'lt' => $lt,
        'password' => $password,
        'username' => $username
    );
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
    $page1 = curl_exec($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);
    if($info['url'] == "https://panda.ecs.kyoto-u.ac.jp/portal")
    {
        //ログイン成功
        return true;
    }
    else
    {
        //ログイン失敗
        return false;
    }
}
function GetSites()
{
    global $cookie;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($curl, CURLOPT_URL, "https://panda.ecs.kyoto-u.ac.jp/direct/site.json?_limit=999");
    $json = curl_exec($curl);
    curl_close($curl);
    $arr = json_decode($json,true);
    if ($arr === NULL)
    {
        return;
    }
    return $arr['site_collection'];
}
function DownloadContent($siteID)   //サイトの全てのリソースをチェックして，新しいファイルはダウンロード
{
    echo("checking {$siteID}\n");
    global $cookie;
    global $here;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($curl, CURLOPT_URL, "https://panda.ecs.kyoto-u.ac.jp/direct/content/site/{$siteID}.json");
    $json = curl_exec($curl);
    curl_close($curl);
    $arr = json_decode($json,true);
    if ($arr === NULL)
    {
        return;
    }
    
    //ローカルのjsonを取得，比較
    global $files_path;
    global $files;
    $json = file_get_contents($files_path);
    $files = json_decode($json,true);
    foreach($arr['content_collection'] as $data)
    {
        if($data['modifiedDate'] != GetModifiedDate($siteID,$data['url']))
        {
            echo("downloading {$data['title']}\n");
            $files['lastModified'] = date("Y/m/d H:i:s");
            DownloadFile($siteID,$data);
        }
    }
    $files[$siteID] = $arr['content_collection'];
    $files['lastChekDate']=date("Y/m/d H:i:s");
    file_put_contents($files_path, json_encode($files));
}

function DownloadFile($siteID,$content)
{
    global $cookie;
    global $settings;
    global $here;
    $container = str_replace('/content/group/',$settings['folder'],$content['container']);
    if($content['type'] == "collection")
    {
        if($container == $settings['folder'])
        {
            mkdir($here."{$settings['folder']}{$siteID}");
            copy($here."./file_viewer.php",$here."{$settings['folder']}{$siteID}/index.php");
        }
        else
        {
            mkdir($here.$container.$content['title']);
            copy($here."./file_viewer.php",$here.$container.$content['title']."/index.php");
        }
    }
    else
    {
        $title = str_replace("https://panda.ecs.kyoto-u.ac.jp/access".$content['container'],"",urldecode($content['url']));
        $title = urldecode($title);
        $out = fopen($here.$container.$title, 'wb'); 
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $content['url']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($curl, CURLOPT_FILE, $out); 
        curl_exec($curl);
        curl_close($curl);
        fclose($out); 
    }
}

function GetModifiedDate($siteID,$url)  //ローカルのfiles.jsonからcontainerとtitleが一致するコンテンツのmodifiedDateを取得する
{
    global $files;
    if(!isset($files[$siteID])) return "";
    foreach($files[$siteID] as $data)
    {
        if($data['url'] == $url)
        {
            return $data['modifiedDate'];
        }
    }
    return "";
}

?>