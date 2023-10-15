<?php
require('../crawler/panda.php');

if (!isset($_POST['set'])) {
    ?>
    <html>
    <header>
        <meta charset="utf-8">
    </header>

    <body>
        <h1>ユーザー情報の設定</h1>
        <form action="" method="POST">
            <div>
                <label for="username">Username:</label><br>
                <input type="text" id="username" name="username" /><br><br>
            </div>

            <div>
                <label for="pass">Password:</label><br>
                <input type="password" id="pass" name="password" /><br><br>
            </div>
            <input type="hidden" name="set" value="userdata">
            <input type="submit" value="ok">
        </form>
    </body>

    </html>

    <?php
} else if ($_POST['set'] == 'userdata') {
    $login = Login($_POST['username'], $_POST['password']);
    if(!$login)
    {
        exit('ログイン失敗');
    }
    $json = file_get_contents("../crawler/settings.json");
    $settingData = json_decode($json,true);
    $settingData['username'] = $_POST['username'];
    $settingData['password'] = $_POST['password'];
    file_put_contents("../crawler/settings.json", json_encode($settingData));
    $sites = GetSites();
    ?>
        <html>
        <header>
            <meta charset="utf-8">
        </header>

        <body>
        <h1>取得する教科を選択</h1>
        <form action="" method="POST">
            <?php
                foreach($sites as $data)
                {
                $checkbox = "<input type=\"checkbox\" name=\"{$data['entityId']}\" value=\"{$data['entityTitle']}\" />\n
                <label for=\"scales\">{$data['entityTitle']}</label><br>\n";
                echo($checkbox);
                }
            ?>
            <input type="hidden" name="set" value="sites">
            <input type="submit" value="ok">
        </form>

        </body>

        </html>
    <?php
} else if ($_POST['set'] == 'sites') { 
    $json = file_get_contents("../crawler/settings.json");
    $settingData = json_decode($json,true);
    $settingData['sites']=[];
    foreach($_POST as $key => $value)
    {
        if($key != "set")
        {
            $title = preg_replace('/\[.*?\]/',"",$value);//[]内を消去
            preg_match('/(...)(...)\]/',$value,$schedule);
            $day=$schedule[1];
            $num = ["０","１","２","３","４","５","６","７","８","９"]; //全角数字文字列→整数
            for($i = 0; $i<10;$i++)
            {
                if($schedule[2] == $num[$i])
                {
                    $period = $i;
                    break;
                }
            }
            array_push($settingData['sites'],array('siteTitle'=>$title,'siteId'=>$key,'day'=>$day,'period'=>$period));
        }
    }
    file_put_contents("../crawler/settings.json", json_encode($settingData));
    ?>
            <html>
            <header>
                <meta charset="utf-8">
            </header>

            <body>
                <p>
                <?php
                foreach($settingData['sites'] as $data)
                {
                    echo("{$data['siteTitle']}<br>");
                }
                ?>
                を取得リストに設定しました．</p>
            </body>

            </html>
    <?php
} ?>