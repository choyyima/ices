<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class App_Notification_Parent extends MY_ICES_Controller {
    function __construct(){
        parent::__construct();
    }
    
    function index(){
        
    }
    
    function notification_get(){
        $post = $this->input->post();
        $data = Tools::_arr(json_decode($post,TRUE));
        $result = array('response'=>array());
        $response =array();
        foreach($data as $idx=>$module){
            
            $controller = isset($module['controller'])?Tools::_str($module['controller']):'';
            $method = isset($module['method'])?Tools::_str($module['method']):'';
            $user_id = User_Info::get()['user_id'];
            
            if(Security_Engine::get_controller_permission(ICES_Engine::$app['val'],$user_id, $controller, $method)){
                try{
                    $class_name = Tools::class_name_get($controller).'_Data_Support';
                    get_instance()->load->helper(ICES_Engine::$app['app_base_dir'].'/'.$controller.'/'.$class_name);
                    if(method_exists($class_name, $method)){
                        $temp_result = $class_name::$method();
                        if(count($temp_result['response'])>0){
                            $response[] = $temp_result['response'];
                        }
                    }
                }
                catch(Exception $e){
                   
                }
            }
        }
        
        $result['response'] = $response;
        echo json_encode($result);
    }
}

?>