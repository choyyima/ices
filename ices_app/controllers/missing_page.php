<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Missing_Page extends MY_Extended_Controller {
    function index(){
        redirect(get_instance()->config->base_url());
    }
}

?>