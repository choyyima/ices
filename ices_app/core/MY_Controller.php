<?php
class MY_Extended_Controller extends CI_Controller{
    //<editor-fold defaultstate="collapsed">
    function __construct() {
        parent::__construct();
        header('Cache-Control: no-cache, no-store, must-revalidate'); 
        header('Pragma: no-cache'); 
        header('Expires: 0');
        
        self::limit_connection();
        
    }
        
    function limit_connection(){
        $limit = get_instance()->config->config['MY_']['limit_connection'];
        $num_of_real_request = get_instance()->session->userdata('num_of_real_request') !== (null||false)?
                get_instance()->session->userdata('num_of_real_request'):0;
        $num_of_real_request+=1;
        $last_min_request = get_instance()->session->userdata('last_min_request') !== null &&
                get_instance()->session->userdata('last_min_request') !== false
                ?
                get_instance()->session->userdata('last_min_request'):Date('Y-m-d H:i:s');

        $date_diff =strtotime(Date('Y-m-d H:i:s')) - strtotime($last_min_request); 

        if($date_diff>60){
            $last_min_request = Date('Y-m-d H:i:s');
            $num_of_real_request = 1;
        }


        get_instance()->session->set_userdata(array("num_of_real_request"=>$num_of_real_request));
        get_instance()->session->set_userdata(array("last_min_request"=>$last_min_request));
        if($limit !== 0)
        if($num_of_real_request>$limit){die('Are you trying to flood our server?');}

    }
    //</editor-fold>
}

class MY_ICES_Controller extends MY_Extended_Controller{
    //<editor-fold defaultstate="collapsed">
    
    function __construct()
    { 
        parent::__construct();
        $app = ICES_Engine::$app; 
        $ices = SI::type_get('ICES_Engine', 'ices', '$app_list');
        
        set_time_limit(get_instance()->config->config['MY_']['controller_time_limit']);
        
        User_Info::component_security_set();
        $user_info = User_Info::get();
        $redirect = false;
        $redirect_page = get_instance()->config->base_url();
        
        if($user_info['user_id']  == '')
        {
            User_Info::flush();
            $redirect=true;
        }
        else if (strtolower($user_info['role'])!='root'){
            
            $user = User_Info::get();
            $method_name = 'index';
            $cotroller_name = '';
            
            if(get_instance()->uri->segment(2)!=false){
                $cotroller_name = get_instance()->uri->segment(2);
            }
            if(get_instance()->uri->segment(3)!=false){
                $method_name = get_instance()->uri->segment(3);
            }
            
            $valid = Security_Engine::get_controller_permission($app['val'],$user_info['user_id'],$cotroller_name,$method_name);
            
            $redirect = $valid?false:true;
            $redirect_page = get_instance()->config->base_url().'no_permission';
        }
        
        if(!$redirect){                
            $is_timeout = Security_Engine::is_timeout();
            if($is_timeout){
                User_Info::flush();
                $redirect=true;
                $redirect_page = get_instance()->config->base_url();
            }
        }
        
        if($redirect){
            if(!get_instance()->input->post()){
                redirect($redirect_page);
            }
            else{
                echo json_encode(array('success'=>0,'msg'=>'Unauthorized user for this request '.current_url()));
                die();
            }
        }
        
        get_instance()->load->helper($ices['app_base_dir'].'app_access_time/app_access_time_engine');
        if(!App_Access_Time_Engine::u_group_allowed($app['val']) && User_Info::get()['role']!== 'ROOT'){            
            redirect($ices['app_base_url'].'app_access_time_invalid/');
        }
        
        
    }
    //</editor-fold>
}


class MY_Job_Controller extends CI_Controller{
    
    //<editor-fold defaultstate="collapsed">    
    function __construct()
    {
        parent::__construct();  
        
        set_time_limit(get_instance()->config->config['MY_Job']['controller_time_limit']);
        $post = json_decode($this->input->post(),TRUE);
        $password  = isset($post['password'])?(is_string($post['password'])?$post['password']:''):'';
        
        if($password !== get_instance()->config->config['MY_Job']['password']){
            echo 'Wrong Password';
            die();
        }
        else{
            get_instance()->load->helper('app_job/app_job_engine');
        }
        
    }
    //</editor-fold>
}
?>
