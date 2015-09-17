<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Supplier_Data_Support{
    
    public static function supplier_active_search($lookup_data){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*
            from supplier t1
            where t1.status>0 and t1.supplier_status = "A"
                and(
                    t1.code like '.$db->escape('%'.Tools::_str($lookup_data).'%').'
                    or t1.name like '.$db->escape('%'.Tools::_str($lookup_data).'%').'
                    or t1.phone like '.$db->escape('%'.Tools::_str($lookup_data).'%').'
                    or t1.bb_pin like '.$db->escape('%'.Tools::_str($lookup_data).'%').'
                    or t1.email like '.$db->escape('%'.Tools::_str($lookup_data).'%').'
                )
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs;
        return $result;
        //</editor-fold>
    }
    
    
    public static function supplier_get($id){
        $db = new DB();
        $result = null;
        $q = '
            select *
            from supplier
            where id = '.$db->escape($id).'
                and status > 0
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
    }

}
?>