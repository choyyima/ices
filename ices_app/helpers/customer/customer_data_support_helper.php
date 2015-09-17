<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Data_Support{
    
    public static function customer_active_search($lookup_data){
        $result = array();
        $db = new DB();
        $q = '
            select t1.*
            from customer t1
            where t1.status>0 and t1.customer_status = "A"
                and(
                    t1.code like '.$db->escape('%'.Tools::_str($lookup_data).'%').'
                    or t1.name like '.$db->escape('%'.Tools::_str($lookup_data).'%').'
                    or t1.phone like '.$db->escape('%'.Tools::_str($lookup_data).'%').'
                    or t1.phone2 like '.$db->escape('%'.Tools::_str($lookup_data).'%').'
                    or t1.phone3 like '.$db->escape('%'.Tools::_str($lookup_data).'%').'
                    or t1.bb_pin like '.$db->escape('%'.Tools::_str($lookup_data).'%').'
                    or t1.email like '.$db->escape('%'.Tools::_str($lookup_data).'%').'
                )
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs;
        return $result;
    }
    
    public static function customer_get($customer_id){
        //<editor-fold defaultstate="collapsed">
            $result = array();
            $db = new DB();
            $q = '
                select *
                from customer t1
                where t1.id = '.$db->escape($customer_id).'
                    and t1.status>0
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0) $result = $rs[0];
            return $result;
            //</editor-fold>
    }
    
    public static function customer_get_by_field($field_name, $value){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $rs = $db->fast_get('customer', array($field_name=>$value));
        
        if(count($rs)>0){
            $t_customer_id = $rs[0]['id'];
            $result = Customer_Data_Support::customer_get($t_customer_id);
            
        }
        return $result;
        //</editor-fold>
    }
}
?>