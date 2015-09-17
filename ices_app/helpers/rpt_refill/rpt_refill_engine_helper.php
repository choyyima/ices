<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rpt_Refill_Engine {
    public static $prefix_id = 'rpt_refill';
    public static $module_type_list;
    
    static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$module_type_list = array(
            array(
                'val'=>'refill_invoice'
                ,'label'=>Lang::get(array('Refill','Invoice'))
                ,'method'=>'refill_invoice'
            ),
        );
        //</editor-fold>
    }
    
    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'rpt_refill/'
            ,'rpt_refill_engine'=>'rpt_refill/rpt_refill_engine'
            ,'rpt_refill_renderer' => 'rpt_refill/rpt_refill_renderer'
            ,'rpt_refill_data_support' => 'rpt_refill/rpt_refill_data_support'
            ,'ajax_search'=>get_instance()->config->base_url().'rpt_refill/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'rpt_refill/data_support/'
            ,'form_render'=>get_instance()->config->base_url().'rpt_refill/form_render/'
        );

        return json_decode(json_encode($path));
    }

     
    



}
?>
