<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class App_Access_Time_Invalid extends MY_Extended_Controller {
        
    public function index(){
        ICES_Engine::app_set('ices');        
        $html = get_instance()->load->view(ICES_Engine::$app['app_base_dir'].'app_access_time_invalid');
    }
    
}
?>