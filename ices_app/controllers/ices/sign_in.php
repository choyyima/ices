<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Sign_In extends MY_Extended_Controller {
    function __construct(){
        parent::__construct();
        ICES_Engine::app_set('ices');
    }
    
    function index(){
        //<editor-fold defaultstate="collapsed">
        $result = SI::result_format_get();
        $success = 0;
        $msg = array();
        $response = array();
        $post = json_decode($this->input->post(),true);
        if($post){
            $app_name = isset($post['app_name'])?Tools::_str($post['app_name']):'';
            $username = isset($post['username'])?Tools::_str($post['username']):'';
            $password = isset($post['password'])?Tools::_str($post['password']):'';
            
            $user_id = Security_Engine::get_user_id($username,$password);
            $username_pwd_match = 0;
            
            if($user_id>0){
                User_Info::set($user_id);
                $response['user_info'] = User_Info::get();
                $username_pwd_match = 1;
                $controller = 'dashboard';
                $method = 'index';
                $is_auth = Security_Engine::get_controller_permission($app_name,User_Info::get()['user_id'],$controller,$method);
                if($is_auth){
                    $app = SI::type_get('ICES_Engine', $app_name,'$app_list');
                    $response['app_url'] = $app['app_default_url'];
                    $success = 1;
                }
                else{
                    $success = 0;
                    $msg = 'You are not allowed to open '.SI::type_get('ICES_Engine', $app_name, '$app_list')['text'];
                }
            }
            else{
                $success = 0;
                $msg = 'Invalid username / password';
            }
            $response['username_pwd_match'] = $username_pwd_match;
        }
        else{         
            $success = 0;
            $msg = 'Empty Login Data';
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function sign_out(){
        Security_Engine::sign_out();
        redirect(get_instance()->config->base_url());
    }
    
}

?>