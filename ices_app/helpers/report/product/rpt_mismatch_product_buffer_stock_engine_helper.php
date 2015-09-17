<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Rpt_Mismatch_Product_buffer_stock_Engine {
        public static function path_get(){
            $path = array(
                'index'=>get_instance()->config->base_url().'rpt_mismatch_product_buffer_stock/'
                ,'rpt_mismatch_product_buffer_stock_engine'=>'report/product/rpt_mismatch_product_buffer_stock_engine'
                ,'ajax_search'=>get_instance()->config->base_url().'rpt_mismatch_product_buffer_stock_engine/ajax_search/'
                
            );
            
            return json_decode(json_encode($path));
        }
        
        
    }
?>
