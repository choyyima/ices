<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Product_Category_Data_Support {
   
    public static function rpc_rpm_cu_active_count($rpc_rpm_cu_arr){
        //<editor-fold defaultstate="collapsed">
        $result = 0;
        $db = new DB();
        $rpc_rpm_cu_q = '';
        foreach($rpc_rpm_cu_arr as $idx=>$rpc_rpm_cu){
            $rpc_id = isset($rpc_rpm_cu['refill_product_category_id'])?
                Tools::_str($rpc_rpm_cu['refill_product_category_id']):'';
            $rpm_id = isset($rpc_rpm_cu['refill_product_medium_id'])?
                Tools::_str($rpc_rpm_cu['refill_product_medium_id']):'';
            $capacity_unit_id = isset($rpc_rpm_cu['capacity_unit_id'])?
                Tools::_str($rpc_rpm_cu['capacity_unit_id']):'';
            $rpc_rpm_cu_q .= ($rpc_rpm_cu_q ==='')?
                ('select '.$db->escape($rpc_id).' rpc_id, '.$db->escape($rpm_id).' rpm_id,'.$db->escape($capacity_unit_id).' capacity_unit_id'):
                (' union all select '.$db->escape($rpc_id).', '.$db->escape($rpm_id).','.$db->escape($capacity_unit_id).' ');
        }
        $q = '
            select count(1) count_result
            from rpc_rpm_cu t1
                inner join (
                   '.$rpc_rpm_cu_q.'
                ) t2 on t1.refill_product_category_id = t2.rpc_id
                and t1.refill_product_medium_id = t2.rpm_id
                and t1.capacity_unit_id = t2.capacity_unit_id
                inner join refill_product_category t3 
                    on t3.id = t1.refill_product_category_id 
                        and t3.refill_product_category_status = "active"
                        and t3.status>0
                inner join refill_product_medium t4 
                    on t4.id = t1.refill_product_medium_id 
                        and t4.refill_product_medium_status = "active"
                        and t4.status>0
                inner join unit t5 
                    on t5.id = t1.capacity_unit_id 
                        and t5.status>0
        ';
        $result = $db->query_array($q)[0]['count_result'];
        return $result;
        //</editor-fold>
    }
}
?>