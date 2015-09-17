<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$my_param = array(
    'file_path'=>APPPATH.'controllers/ices/common_ajax_listener.php',
    'src_class'=>'Common_Ajax_Listener',
    'src_extends_class'=>'',
    'dst_class'=>'Common_Ajax_Listener_Parent',
    'dst_extends_class'=>'',
);
$my_content = my_load_and_rename_class($my_param);

class Common_Ajax_Listener extends Common_Ajax_Listener_Parent{
    function __construct() {
        parent::__construct();
    }
}
?>