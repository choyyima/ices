<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Smart_Search_Data_Support {

    public function customer_detail_get($customer_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*
            from customer t1
            where t1.id = '.$db->escape($customer_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = array(
                    array('id'=>'customer_name','label'=>'Name: ','val'=>$rs[0]['name']),
                    array('id'=>'customer_address','label'=>'Address: ','val'=>$rs[0]['address']),
                    array('id'=>'customer_phone','label'=>'Phone: ','val'=>$rs[0]['phone']),

            );
        }
        return $result;
        //</editor-fold>
    }

    public function refill_work_order_get($id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*,
                t2.code customer_code,
                t2.name customer_name,
                t1.outstanding_amount
            from Refill_Work_Order t1
                inner join customer t2 on t1.customer_id = t2.id
            where t1.id = '.$db->escape($id).'
                and t1.status > 0
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }

    public static function refill_work_order_info_get($refill_work_order_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*, concat(t2.first_name," ",t2.last_name) creator_name
            from refill_work_order_info t1
                inner join user_login t2 on t1.creator_id = t2.id
            where t1.refill_work_order_id = '.$db->escape($refill_work_order_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs[0];
        return $result;
        //</editor-fold>
    }
        
}
?>