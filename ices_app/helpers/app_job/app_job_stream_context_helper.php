<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class App_Job_Stream_Context{
	
    private $opts=array();

    function __construct(){
        $this->opts = array(
            'http' => array(
                    'method'  => 'POST',
                    'content' => '',
                    'header'=>"Content-Type: application/json\r\n".
                            'Connection: close\r\n'
              )
        );
    }

    private function content_set($data, $cfg=array()){
            $temp_data = json_encode($data);
            $base64_encode = isset($cfg['base64_encode'])? $cfg['base64_encode']: true;		
            if($base64_encode) $temp_data = $temp_data = base64_encode($temp_data);		
            $this->opts['http']['content'] = $temp_data;

    }

    function send($url,$data=null,$data_cfg=array()){
            $this->content_set($data,$data_cfg);
            $context = stream_context_create($this->opts);
            $result  = file_get_contents($url, false, $context);
            return $result;
    }
	
}

?>