<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Receive_Product_Engine {
        
        public static function receive_product_exists($id){
            $result = false;
            $db = new DB();
            $q = '
                    select 1 
                    from receive_product 
                    where status > 0 && id = '.$db->escape($id).'
                ';
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                $result = true;
            }
            return $result;
        }
        
        public static function path_get(){
            $path = array(
                'index'=>get_instance()->config->base_url().'receive_product/'
                ,'receive_product_engine'=>'receive_product/receive_product_engine'
                ,'receive_product_purchase_invoice_engine'=>'receive_product/receive_product_purchase_invoice_engine'
                ,'receive_product_rma_engine'=>'receive_product/receive_product_rma_engine'
                ,'receive_product_print'=>'receive_product/receive_product_print'
                ,'receive_product_renderer' => 'receive_product/receive_product_renderer'
                ,'ajax_search'=>get_instance()->config->base_url().'receive_product/ajax_search/'
                ,'data_support'=>get_instance()->config->base_url().'receive_product/data_support/'
                
            );
            
            return json_decode(json_encode($path));
        }
        
        
    }
?>
