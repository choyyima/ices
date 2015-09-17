<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Sales_Prospect_Data_Support {
        
        
        public static function sales_prospect_get($sales_prospect_id){
            //<editor-fold defaultstate="collapsed">
            $result = array();
            $db = new DB();
            $q = '
                select *
                from sales_prospect t1
                where t1.id = '.$db->escape($sales_prospect_id).'
                    and t1.status>0
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0) $result = $rs[0];
            return $result;
            //</editor-fold>
        }
        
        public static function sales_prospect_info_get($sales_prospect_id){
            //<editor-fold defaultstate="collapsed">
            $result = array();
            $db = new DB();
            $q = '
                select t1.*
                    ,t2.code sales_inquiry_by_code
                    ,t2.name sales_inquiry_by_name
                from sales_prospect_info t1
                    inner join sales_inquiry_by t2 on t1.sales_inquiry_by_id = t2.id
                where t1.sales_prospect_id = '.$db->escape($sales_prospect_id).'
                    
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0) $result = $rs[0];
            return $result;
            //</editor-fold>
        }
        
        public static function sales_prospect_product_get($sales_prospect_id){
            //<editor-fold defaultstate="collapsed">
            $result = array();
            $db = new DB();
            $q = '
                select 
                    t1.*
                    ,t2.code product_code
                    ,t2.name product_name
                    ,t3.code unit_code
                    ,t2.notes product_notes
                    ,t2.additional_info product_additional_info
                from sales_prospect_product t1
                    inner join product t2 on t1.product_id = t2.id
                    inner join unit t3 on t1.unit_id = t3.id
                where t1.sales_prospect_id = '.$db->escape($sales_prospect_id).'
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0){
                
                $result = $rs;
            }
            
            return $result;
            //</editor-fold>
        }
        
        function additional_cost_get($sales_prospect_id){
            //<editor-fold defaultstate="collapsed">
            $result = array();
            $db = new DB();
            $rs = $db->fast_get('sales_prospect_additional_cost',array('sales_prospect_id'=>$sales_prospect_id));
            if(count($rs)>0) $result = $rs;
            return $result;
            //</editor-fold>
        }
    }
?>