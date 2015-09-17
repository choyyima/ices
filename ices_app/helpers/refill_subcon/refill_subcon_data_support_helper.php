<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Subcon_Data_Support{
    
    public static function refill_subcon_exists($id=""){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from refill_subcon 
                where status > 0 && id = '.$db->escape($id).'
            ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function refill_subcon_active_search($lookup_data){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*
            from refill_subcon t1
            where t1.status>0 and t1.refill_subcon_status = "A"
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
        //</editor-fold>
    }
    
    public function product_unit_all_exists($data_arr){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q_product = 'select -1 product_id, -1 unit_id';
        foreach($data_arr as $idx=>$data){
            $product_id = isset($data['product_id'])?Tools::_str($data['product_id']):'';
            $unit_id = isset($data['unit_id'])?Tools::_str($data['unit_id']):'';
            $q_product.=' union all select '.$db->escape($product_id).', '.$db->escape($unit_id);
        }

        $q = '
            select count(1) total
            from refill_work_order_product t1
                inner join ('.$q_product.') tp 
                    on t1.id = tp.product_id and t1.unit_id = tp.unit_id
            where 1 = 1 
        ';
        $total = $db->query_array($q)[0]['total'];
        if(Tools::_int(count($data_arr))===Tools::_int($total)) $result = true;
        return $result;
        //</editor-fold>
    }
    
}
?>