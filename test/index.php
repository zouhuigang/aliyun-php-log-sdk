<?php

include_once "../src/Log.php";


$config = [
    'time_format' => 'Y-m-d H:i:s',
    'file_size' => 2097152,
    'path' => 'local-log/',
    'endpoint' => '',
    'accessKeyId' => '',
    'accessKey' => '',
    'project' => '',
    'logstore' =>'',// 日志库名称
    'topic'=>'default'
];

$sls = new \zouhuigang\alilog\Log($config);

$log=array(
    "msg"=>["测试传一段json","成功了吗"]
);
$sls->write_log('info',json_encode($log,JSON_UNESCAPED_UNICODE));

$sls->write_log('ERROR',"出错啦",2);
echo "写入日志完成";die;
