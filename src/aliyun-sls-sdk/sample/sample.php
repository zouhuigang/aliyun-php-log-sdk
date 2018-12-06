<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

require_once realpath(dirname(__FILE__) . '/../Log_Autoload.php');

function putLogs(Aliyun_Log_Client $client, $project, $logstore) {
    $topic = 'TestTopic';
    
    $time=date("Y-m-d H:i:s",time());
    $contents = array( // key-value pair
        'zouhuigang'=>$time.'测试一把日志，看看是咋样的情况'
    );
    $logItem = new Aliyun_Log_Models_LogItem();
    $logItem->setTime(time());
    $logItem->setContents($contents);
    $logitems = array($logItem);
    $request = new Aliyun_Log_Models_PutLogsRequest($project, $logstore, 
            $topic, null, $logitems);
    
    try {
        $response = $client->putLogs($request);
        //echo "日志上传成功";
        print_r($response);
    } catch (Aliyun_Log_Exception $ex) {
        //var_dump($ex);
        echo "日志上传异常";
    } catch (Exception $ex) {
        //var_dump($ex);
        echo "日志上传异常2";
    }
}

function listLogstores(Aliyun_Log_Client $client, $project) {
    try{
        $request = new Aliyun_Log_Models_ListLogstoresRequest($project);
        $response = $client->listLogstores($request);
        //var_dump($response);
    } catch (Aliyun_Log_Exception $ex) {
        //var_dump($ex);
    } catch (Exception $ex) {
       // var_dump($ex);
    }
}


function listTopics(Aliyun_Log_Client $client, $project, $logstore) {
    $request = new Aliyun_Log_Models_ListTopicsRequest($project, $logstore);
    
    try {
        $response = $client->listTopics($request);
       // var_dump($response);
    } catch (Aliyun_Log_Exception $ex) {
        //var_dump($ex);
    } catch (Exception $ex) {
        //var_dump($ex);
    }
}

//查询日志分布情况询（注意，要查询日志，必须保证已经创建了索引，PHP SDK 不提供该接口，请在控制台创建）
//https://help.aliyun.com/document_detail/29074.html?spm=a2c4g.11186623.6.969.30668bdctFSsWm
//开启日志索引步骤:https://help.aliyun.com/document_detail/90732.html?spm=a2c4g.11186623.6.710.5f258bdcn4smAB
function getLogs(Aliyun_Log_Client $client, $project, $logstore) {
    $topic = 'TestTopic';
    $from = time()-3600*4;
    $to = time();
    $request = new Aliyun_Log_Models_GetLogsRequest($project, $logstore, $from, $to, $topic, '', 100, 0, False);
    
    $logList=array();


    try {
        $response = $client->getLogs($request);
        //print_r($response);die;
        foreach($response -> getLogs() as $log)
        {
            //echo  $log -> getTime()."</br>";
            foreach($log -> getContents() as $key => $value){
                echo  $key.":".$value."</br>";
              
            }
            //print "</br>";
        }

    } catch (Aliyun_Log_Exception $ex) {
        print_r($ex);
    } catch (Exception $ex) {
        print_r($ex);
    }
}

function getHistograms(Aliyun_Log_Client $client, $project, $logstore) {
    $topic = 'TestTopic';
    $from = time()-3600;
    $to = time();
    $request = new Aliyun_Log_Models_GetHistogramsRequest($project, $logstore, $from, $to, $topic, '');
    
    try {
        $response = $client->getHistograms($request);
        //var_dump($response);
    } catch (Aliyun_Log_Exception $ex) {
        //var_dump($ex);
    } catch (Exception $ex) {
       // var_dump($ex);
    }
}
function listShard(Aliyun_Log_Client $client,$project,$logstore){
    $request = new Aliyun_Log_Models_ListShardsRequest($project,$logstore);
    try
    {
        $response = $client -> listShards($request);
        print_r($response);
    } catch (Aliyun_Log_Exception $ex) {
        print_r($ex);
    } catch (Exception $ex) {
        print_r($ex);
    }
}
function batchGetLogs(Aliyun_Log_Client $client,$project,$logstore)
{
    $listShardRequest = new Aliyun_Log_Models_ListShardsRequest($project,$logstore);
    $listShardResponse = $client -> listShards($listShardRequest);
    foreach($listShardResponse-> getShardIds()  as $shardId)
    {
        $getCursorRequest = new Aliyun_Log_Models_GetCursorRequest($project,$logstore,$shardId,null, time() - 60);
        $response = $client -> getCursor($getCursorRequest);
        $cursor = $response-> getCursor();
        $count = 100;
        while(true)
        {
            $batchGetDataRequest = new Aliyun_Log_Models_BatchGetLogsRequest($project,$logstore,$shardId,$count,$cursor);
            var_dump($batchGetDataRequest);
            $response = $client -> batchGetLogs($batchGetDataRequest);
            if($cursor == $response -> getNextCursor())
            {
                break;
            }
            $logGroupList = $response -> getLogGroupList();
            foreach($logGroupList as $logGroup)
            {
                print ($logGroup->getCategory());

                foreach($logGroup -> getLogsArray() as $log)
                {
                    foreach($log -> getContentsArray() as $content)
                    {
                        print($content-> getKey().":".$content->getValue()."\t");
                    }
                    print("\n");
                }
            }
            $cursor = $response -> getNextCursor();
        }
    }
}
function deleteShard(Aliyun_Log_Client $client,$project,$logstore,$shardId)
{
    $request = new Aliyun_Log_Models_DeleteShardRequest($project,$logstore,$shardId);
    try
    {
        $response = $client -> deleteShard($request);
        //var_dump($response);
    }catch (Aliyun_Log_Exception $ex) {
        //var_dump($ex);
    } catch (Exception $ex) {
        //var_dump($ex);
    }
}
function mergeShard(Aliyun_Log_Client $client,$project,$logstore,$shardId)
{
    $request = new Aliyun_Log_Models_MergeShardsRequest($project,$logstore,$shardId);
    try
    {
        $response = $client -> mergeShards($request);
        //var_dump($response);
    }catch (Aliyun_Log_Exception $ex) {
        //var_dump($ex);
    } catch (Exception $ex) {
        //var_dump($ex);
    }
}
function splitShard(Aliyun_Log_Client $client,$project,$logstore,$shardId,$midHash)
{
    $request = new Aliyun_Log_Models_SplitShardRequest($project,$logstore,$shardId,$midHash);
    try
    {
        $response = $client -> splitShard($request);
        //var_dump($response);
    }catch (Aliyun_Log_Exception $ex) {
        //var_dump($ex);
    } catch (Exception $ex) {
        //var_dump($ex);
    }
}
$endpoint = '';
$accessKeyId = '';
$accessKey = '';
$project = '';
$logstore = '';
$token = "";

$client = new Aliyun_Log_Client($endpoint, $accessKeyId, $accessKey,$token);


//上传日志
//putLogs($client, $project, $logstore);


//获取日志-测试
getLogs($client, $project, $logstore);

//批量获取日志
//batchGetLogs($client,$project,$logstore);

//listShard($client,$project,$logstore);
// mergeShard($client,$project,$logstore,82);
// deleteShard($client,$project,$logstore,21);
// splitShard($client,$project,$logstore,84,"0e000000000000000000000000000000");
// putLogs($client, $project, $logstore);
// listShard($client,$project,$logstore);
// batchGetLogs($client,$project,$logstore);
// listLogstores($client, $project);
// listTopics($client, $project, $logstore);
// getHistograms($client, $project, $logstore);
// getLogs($client, $project, $logstore);
