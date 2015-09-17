<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payment_Type_Data_Support{
    static function payment_type_get($payment_id){
        //<editor-fold defaultstate="collapsed">
        $result = null;
        $db = new DB();
        $q = '
            select *
            from payment_type
            where payment_type.id = '.$db->escape($payment_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
    
    public static function payment_type_code_get($payment_type_id){
        $result = null;
        $db = new DB();
        $rs = $db->fast_get('payment_type',array('id'=>$payment_type_id));
        if(count($rs)>0) $result = $rs[0]['code'];
        return $result;
    }
    
}
?>