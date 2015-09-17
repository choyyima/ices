<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class App_Job extends MY_Job_Controller{
    public function index(){        
        
        $post = json_decode($this->input->post(),TRUE);
        $job = isset($post['job'])?Tools::_arr($post['job']):array();
        foreach($job as $i=>$row){
            $job_name = isset($row['name'])?Tools::_str($row['name']):'';        
            $config = isset($row['config'])?Tools::_arr($row['config']):array();
            
            if(App_Job_Engine::job_exists($job_name)){
                App_Job_Engine::job_start($job_name, $config);
            }
        }
        
        
        
    }
}

?>