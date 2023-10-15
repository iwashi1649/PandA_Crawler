<?php
date_default_timezone_set('Asia/Tokyo');
//授業資料の各フォルダにindex.phpとして配置
$files = glob('*');
//var_dump($files);
?>
<html>

<head>
    <style type="text/css">
        @media screen and (min-width:801px) {
            body {
                width: 900px;
                margin: auto;
            }

            .file,
            .folder {
                border-top: solid 1px;
                margin: auto;
                padding: 10 0 10 0;
                width: 800px;
                background-color: rgb(255, 255, 255);
            }
        }

        @media screen and (max-width:800px) {
            body {
                width: 100%;
                margin: auto;
            }

            .file,
            .folder {
                border-top: solid 1px;
                margin: auto;
                padding: 10 0 10 0;
                width: 100%;
                background-color: rgb(255, 255, 255);
            }
        }

        .file:hover,
        .folder:hover {
            background-color: rgb(235, 235, 235);
        }

        .file .filename p::before {

            content: "📄";

        }

        .folder .filename p::before {
            content: "📁";

        }

        .file p,
        .folder p {

            margin: 0;
            margin-left: 1em;
            font-size: 1.05em;
        }

        .filename {
            display: inline-flex;
            width: 70%;
        }

        .date {
            display: inline-flex;
            width: 28%;
        }

        .date p {
            font-size: 1em;
        }

        a {
            color: black;
            text-decoration: none;
        }




        .caption .file {
            color: white;
            background-color: rgb(49, 49, 49);
        }

        .caption .file:hover {
            color: white;
            background-color: rgb(49, 49, 49);
        }

        .caption .filename p::before {
            content: "";
        }
    </style>
</head>
<header>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
</header>

<body>
    <div class="caption">
        <div class="file">
            <div class="filename">
                <p>ファイル名</p>
            </div>
            <div class="date">
                <p>更新日時</p>
            </div>
        </div>


    </div>
    <?php
    foreach ($files as $data) {
        if (is_dir($data)) { ?>

            <a href="<?php echo ("./{$data}") ?>">
                <div class="folder">
                    <div class="filename">
                        <p>
                            <?php echo ($data) ?>
                        </p>
                    </div>
                    <div class="date">
                        <p>
                            <?php echo (date("Y/m/d H:i:s", filemtime($data))) ?>
                        </p>
                    </div>
                </div>
            </a>

        <?php }
    }
    foreach ($files as $data) {
        if (is_file($data) && $data != "index.php") { ?>
            <a href="<?php echo ("./{$data}") ?>">
                <div class="file">
                    <div class="filename">
                        <p>
                            <?php echo ($data) ?>
                        </p>
                    </div>
                    <div class="date">
                        <p>
                            <?php echo (date("Y/m/d H:i:s", filemtime($data))) ?>
                        </p>
                    </div>
                </div>
            </a>
        <?php }
    } ?>
</body>

</html>