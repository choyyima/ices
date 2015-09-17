<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Home extends MY_Extended_Controller {
    
    function __construct(){
        parent::__construct();
    }
    
    public function index(){
        $app_base_dir = SI::type_get('ICES_Engine', 'ices','$app_list')['app_base_dir'];
        $this->load->view($app_base_dir.'home/home');
        $this->load->view($app_base_dir.'home/home_js');
    }

    public function data_support($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        $result = SI::result_format_get();
        $data = json_decode($this->input->post(), true);
        $success = 1;
        $msg = array();
        $response = array();
        switch($method){
            case 'is_auth':
                //<editor-fold defaultstate="collapsed">
                $app_name = isset($data['app_name'])?Tools::_str($data['app_name']):'';
                $controller = 'dashboard';
                $method = 'index';
                $is_auth = Security_Engine::get_controller_permission($app_name,User_Info::get()['user_id'],$controller,$method);
                $app_url = '';
                if($is_auth){
                    $app = SI::type_get('Ices_Engine', $app_name, '$app_list');
                    $app_url = $app['app_default_url'];
                }
                $response['is_auth'] = $is_auth;
                $response['app_url'] = $app_url;
                //</editor-fold>
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    
}
?>