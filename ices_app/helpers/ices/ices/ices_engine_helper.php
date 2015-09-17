<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ICES_Engine {
    public static $app_list;
    public static $app;
        
    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$app_list = array(
            array(
                'val'=>'ices',
                'text'=>'Integrated Civil Engineering System',
                'app_base_url'=>get_instance()->config->base_url().'ices/',
                'app_base_dir'=>'ices/',
                'app_db_conn_name'=>'ices',
                'app_translate'=>false,
                'app_default_url'=>get_instance()->config->base_url().'ices/dashboard',
                'app_theme'=>'AdminLTE',
                'app_db_lock_name'=>'ices',
                'app_db_lock_limit'=>10,
                'non_permission_controller'=>array()
            ),
            array(
                'val'=>'phone_book',
                'text'=>'Phone Book',
                'app_base_url'=>get_instance()->config->base_url().'phone_book/',
                'app_base_dir'=>'phone_book/',
                'app_db_conn_name'=>'phone_book',
                'app_translate'=>false,
                'app_default_url'=>get_instance()->config->base_url().'phone_book/dashboard',
                'app_theme'=>'AdminLTE',
                'app_db_lock_name'=>'phone_book',
                'app_db_lock_limit'=>10,
                'non_permission_controller'=>array()
            ),
            
        );
        
        self::helper_load();
        $app_name = get_instance()->uri->segment(1);
        self::app_set($app_name);
        //</editor-fold>
    }
    
    public static function helper_load(){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('ices/user_info/user_info');
        get_instance()->load->helper('ices/handy/printer_helper');
        get_instance()->load->helper('ices/handy/excel_helper');
        get_instance()->load->helper('ices/handy/tools');
        get_instance()->load->helper('ices/handy/si/si');
        get_instance()->load->helper('ices/handy/email/email_engine');
        get_instance()->load->helper('ices/handy/email/email_message');
        get_instance()->load->helper('ices/handy/validator');
        get_instance()->load->helper('ices/security/security_engine');
        get_instance()->load->helper('ices/handy/db');
        get_instance()->load->helper('ices/app/app');
        get_instance()->load->helper('ices/app/app_icon');
        get_instance()->load->helper('ices/app_message/app_message_engine');
        get_instance()->load->helper('ices/app/app_message');
        //</editor-fold>
    }
    
    public static function app_set($app_name=''){
        //<editor-fold defaultstate="collapsed">
        $t_app = SI::type_get('ICES_Engine',$app_name,'$app_list');
        if($t_app !== null){
            self::$app = $t_app;
            get_instance()->load->helper(self::$app['app_base_dir'].'lang/lang_helper');
        }
        //<editor-fold>
    }
    
}
?>
