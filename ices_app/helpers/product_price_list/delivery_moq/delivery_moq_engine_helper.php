<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Delivery_MOQ_Engine {
        
        public static function path_get(){
            $path = array(
                'index'=>get_instance()->config->base_url().'product_price_list/'
                ,'delivery_moq_mixed_engine' => 'product_price_list/delivery_moq/delivery_moq_mixed_engine'
                ,'delivery_moq_separated_engine' => 'product_price_list/delivery_moq/delivery_moq_separated_engine'
                ,'delivery_moq_renderer' => 'product_price_list/delivery_moq/delivery_moq_renderer'
                ,'ajax_search'=>get_instance()->config->base_url().'product_price_list/ajax_search/'
                ,'data_support'=>get_instance()->config->base_url().'product_price_list/data_support/'
                
            );
            
            return json_decode(json_encode($path));
        }
        
        public static function delete($id){
            $db = new DB();
            $db->query('update product_price_list_delivery_moq set status = 0 where id = '.$db->escape($id));
            
        }
        
        
        
    }
?>