<?php
namespace zouhuigang\alilog;
require_once("aliyun-sls-sdk/Log_Autoload.php");

//https://www.kancloud.cn/ifu-dev/tp5_learn/178066
class Log{
    protected $config = [
        'time_format' => 'Y-m-d H:i:s',
        'file_size' => 2097152,
        'path' => '',
        'endpoint' => 'cn-hangzhou.sls.aliyuncs.com',
        'accessKeyId' => '',
        'accessKey' => '',
        'project' => '',
        'logstore'=>'',
        'topic'=>''
    ];
    protected $client;

    // 实例化并传入参数
    public function __construct($config = [])
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }

        $endpoint = $this->config['endpoint']; // 选择与上面步骤创建Project所属区域匹配的Endpoint
        $accessKeyId = $this->config['accessKeyId'];        // 使用你的阿里云访问秘钥AccessKeyId
        $accessKey = $this->config['accessKey'];             // 使用你的阿里云访问秘钥AccessKeySecret
        $this->client = new \Aliyun_Log_Client($endpoint, $accessKeyId, $accessKey);
    }


    private function putLogs($contents) {

        $project = $this->config['project'];                  // 上面步骤创建的项目名称
        $logstore = $this->config['logstore'];
        $topic = $this->config['topic'];
        
        $logItem = new \Aliyun_Log_Models_LogItem();
        $logItem->setTime(time());
        $logItem->setContents($contents);
        $logitems = array($logItem);
        $request = new \Aliyun_Log_Models_PutLogsRequest($project, $logstore, 
                $topic, null, $logitems);
    
        try {
            $response = $this->client->putLogs($request);
            //echo "日志上传成功";
        } catch (Aliyun_Log_Exception $ex) {
            //var_dump($ex);
        } catch (Exception $ex) {
            //var_dump($ex);
        }
    }

    /*
    写入日志 key:INFO,ERROR等
    msg:文本
    stype:保存日志的类型,0本地和远程都保存，1只保存本地，2只保存远程
    */
    public function write_log($key,$msg,$stype=0){
            $stype=(int)$stype;
            if($stype==1){
                $this->write_log_to_local($key,$msg);
            }else if($stype==2){
                $this->write_log_to_aliyun($key,$msg);
            }else{
                $this->write_log_to_aliyun($key,$msg);
                $this->write_log_to_local($key,$msg);
            }
           
    }


    //写入日志到远程
    private function write_log_to_aliyun($key,$msg){
        $nkey=strtoupper($key);
        $contents[$nkey]=$msg;    
        $this->putLogs($contents);
        return true;
    }

    //写入日志到本地
    private function write_log_to_local($key,$msg){
        $nkey=strtoupper($key);
        $content=$nkey.':'.$msg;
        $destination = $this->config['path'] . date('y_m_d') . '.log';
        return error_log( date ( "[YmdHis]" ) ."\t" . $content . "\r\n", 3, $destination);
    }

}