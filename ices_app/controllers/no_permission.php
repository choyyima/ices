<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class No_Permission extends MY_Extended_Controller {
        
    public function index(){
        get_instance()->load->view('no_permission');
        
    }
    
}
?>