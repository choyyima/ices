<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rpt_Sales_Engine {
    public static $prefix_id = 'rpt_sales';
    public static $module_type_list;
    
    static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$module_type_list = array(
            array(
                'val'=>'sales_invoice'
                ,'label'=>Lang::get(array('Sales','Invoice'))
                ,'method'=>'sales_invoice'
            ),
        );
        //</editor-fold>
    }
    
    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'rpt_sales/'
            ,'rpt_sales_engine'=>'rpt_sales/rpt_sales_engine'
            ,'rpt_sales_renderer' => 'rpt_sales/rpt_sales_renderer'
            ,'rpt_sales_data_support' => 'rpt_sales/rpt_sales_data_support'
            ,'ajax_search'=>get_instance()->config->base_url().'rpt_sales/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'rpt_sales/data_support/'
            ,'form_render'=>get_instance()->config->base_url().'rpt_sales/form_render/'
        );

        return json_decode(json_encode($path));
    }

     
    



}
?>
