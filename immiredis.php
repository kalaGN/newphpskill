<?php
/**
 *
 * 此文件用于redis 缓存迁移
 * Created by Afei.
 * User: infobird
 * Date: 2018/11/29/0029
 * Time: 19:04:29
 */

error_reporting(0);
date_default_timezone_set("PRC");
//连接redisTK数据库信息 需要备份的redis 密码明文
$FROM_IP = '127.0.0.1';//change
$FROM_PORT = 6379;
$FROM_DB = 1;//change
$FROM_PWD = '123456';//

//连接redis数据库信息  备份至redis 密码明文
$TO_IP = '127.0.0.1';//
$TO_PORT = 6379;
$TO_DB = 4;//
$TO_PWD = '123456';//


//var_dump($_SERVER['argv']);
if (count($_SERVER['argv']) != 2) {
    echo 'argv error.';
    exit;
}


$ar = $_SERVER['argv'];

$nowtime = date('Y-m-d H:i:s');
echo "start at :" . $nowtime . "     ";

//存储匹配的key 真人话术后缀匹配 不需改动
$keymatchlist = array(
    "agent_autoplay_switch",
    "modellist",
    "modelinfo",
    "talktaginfo",
    "agent_talk"
);

$fromredis = new redis();
$fromredis->connect($FROM_IP, $FROM_PORT);
if ($FROM_PWD) {
    $fromredis->auth($FROM_PWD);
}
$fromredis->select($FROM_DB);
if (empty($fromredis)) {
    echo "from redis connnect error!";
    exit;
}
$toredis = new redis();
$toredis->connect($TO_IP, $TO_PORT);
if ($TO_PWD) {
    $toredis->auth($TO_PWD);
}
$toredis->select($TO_DB);

if (empty($toredis)) {
    echo "from redis connnect error!";
    exit;
}
//迁移操作
if ($ar[1] == 'immi') {
    foreach ($keymatchlist as $key) {
        //后缀匹配
        $key = "infobird*" . $key;
        $keylist = $fromredis->keys($key);
        if (!empty($keylist)) {
            foreach ($keylist as $truekey) {
                $info = $fromredis->hGetAll($truekey);
                if ($info) {
                    file_put_contents('TKimmilog.txt', $truekey . "\r\n" . "data:" . json_encode($info) . "\r\n", FILE_APPEND);
                    $re = $toredis->hMset($truekey, $info);
                    if (!$re) {
                        file_put_contents('TKerrorlog.txt', $truekey . "\r\n" . "data:" . json_encode($info) . "\r\n", FILE_APPEND);
                    }
                }
            }
        }
    }
    $nowtime = date('Y-m-d H:i:s');
    echo "end in:" . $nowtime . '   if not exsit TKerrorlog.txt, redis immi success!';
    exit;
}elseif ($ar[1]=="del"){
    exit;
    $fromredis = $toredis;
    foreach ($keymatchlist as $key) {
        //后缀匹配
        $key = "infobird*" . $key;
        $keylist = $fromredis->keys($key);
        if (!empty($keylist)) {
            foreach ($keylist as $truekey) {
                $info = $fromredis->hGetAll($truekey);
                if ($info) {
                    file_put_contents('TKdellog.txt', $truekey . "\r\n" . "data:" . json_encode($info) . "\r\n", FILE_APPEND);
                    foreach ($info as $field => $val){
                        $delre =  $fromredis->hDel($truekey,$field);
                        if (!$delre) {
                            file_put_contents('TKdelerrorlog.txt', $truekey . "\r\n" . "data:" . json_encode($info) . "\r\n", FILE_APPEND);
                        }else{
                            file_put_contents('TKdellog.txt', $truekey . "\r\n" . "data:" . json_encode($info) . "\r\n", FILE_APPEND);

                        }
                    }

                }
            }
        }
    }
    $nowtime = date('Y-m-d H:i:s');
    echo "end in:" . $nowtime . '   if not exsit TKdelerrorlog.txt, redis del success!';
    exit;
}
