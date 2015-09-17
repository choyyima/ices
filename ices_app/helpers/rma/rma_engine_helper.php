<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class RMA_Engine {
        
        public static function rma_exists($id){
            $result = false;
            $db = new DB();
            $q = '
                    select 1 
                    from rma 
                    where status > 0 && id = '.$db->escape($id).'
                ';
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                $result = true;
            }
            return $result;
        }
        
        public static function get($id){
            $db = new DB();
            $result = array();
            $q ='
                select *
                from rma
                where id = '.$db->escape($id).'
            ';
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                $result = $rs[0];
            }
            return $result;
        }
        
        public static function path_get(){
            $path = array(
                'index'=>get_instance()->config->base_url().'rma/'
                ,'rma_engine'=>'rma/rma_engine'
                ,'rma_purchase_invoice_engine'=>'rma/rma_purchase_invoice_engine'
                ,'rma_renderer' => 'rma/rma_renderer'
                ,'ajax_search'=>get_instance()->config->base_url().'rma/ajax_search/'
                
            );
            
            return json_decode(json_encode($path));
        }
        
        
        
        
    }
?>
