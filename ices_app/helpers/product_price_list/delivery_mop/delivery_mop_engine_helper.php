<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Delivery_MOP_Engine {
        
        public static function path_get(){
            $path = array(
                'index'=>get_instance()->config->base_url().'product_price_list/'
                ,'delivery_mop_mixed_engine' => 'product_price_list/delivery_mop/delivery_mop_mixed_engine'
                ,'delivery_mop_separated_engine' => 'product_price_list/delivery_mop/delivery_mop_separated_engine'
                ,'delivery_mop_renderer' => 'product_price_list/delivery_mop/delivery_mop_renderer'
                ,'ajax_search'=>get_instance()->config->base_url().'product_price_list/ajax_search/'
                ,'data_support'=>get_instance()->config->base_url().'product_price_list/data_support/'
                
            );
            
            return json_decode(json_encode($path));
        }
        
        public static function delete($id){
            $db = new DB();
            $db->query('update product_price_list_delivery_mop set status = 0 where id = '.$db->escape($id));
            
        }
        
        
        
    }
?>