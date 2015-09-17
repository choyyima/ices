<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class App_Job_Engine{
    
    public static function job_exists($job_name){
        $result = false;
        
        if(file_exists(APPPATH.'helpers/app_job/jobs/'.$job_name.'_helper.php')){
            $result = true;
        }
            
        return $result;
    }
    
    public static function job_log_detail_add($db, $job_id, $job_status){
        //<editor-fold defaultstate="collapsed">
        $moddate = Tools::_date('');
        $db->insert('sys_job_log_detail',array('sys_job_log_id'=>$job_id,'moddate'=>$moddate,'status'=>$job_status));
        
        //</editor-fold>
    }
    
    public static function job_start($job_name, $user_config){
        //<editor-fold defaultstate="collapsed">
        $db = new DB(array('db_name'=>'MY_Job'));
        $success = 1;
        $msg = '';
        $job_status = '';
        if($db->insert('sys_job_log',array('job_name'=>$job_name,'start'=>Date('Y-m-d H:i:s'),'success'=>0))){
            $job_id = $db->query_array('select * from sys_job_log order by id desc limit 1')[0]['id'];
            
            $job_status = 'starting';
            self::job_log_detail_add($db, $job_id, $job_status);            
            
            try{
                get_instance()->load->helper('app_job/jobs/'.$job_name);
                $param = array(
                    'user_config'=>$user_config,
                );
                $temp_result = eval('return '.$job_name.'::start($db, $param);');
                                
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                
            }
            catch(Exception $err){
                $success = 0;
                $msg = $err->getMessage();
            }
            
            $job_status = 'finished';            
            self::job_log_detail_add($db, $job_id, $job_status);
                        
            $db->update('sys_job_log',array('end'=>Date('Y-m-d H:i:s'),'success'=>$success,'msg'=>$msg),array('id'=>$job_id));
        }
        //</editor-fold>
        
    }
    
}
?>