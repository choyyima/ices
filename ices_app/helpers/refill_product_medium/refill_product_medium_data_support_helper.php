<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Product_Medium_Data_Support {

    public static function rpm_active_count($rpm_id_arr){
        //<editor-fold defaultstate="collapsed">
        $result = 0;
        $db = new DB();
        $rpm_q = '';
        foreach($rpm_id_arr as $idx=>$rpm_id){
            $rpm_q .= ($rpm_q ==='')?
                ('select '.$db->escape($rpm_id).' id'):' union all select '.$db->escape($rpm_id);
        }
        $q = '
            select count(1) count_result
            from refill_product_medium t1
               inner join (
                   '.$rpm_q.'
               ) t2 on t1.id = t2.id 
            where t1.status>0 
               and t1.refill_product_medium_status = "active"
        ';
        $result = $db->query_array($q)[0]['count_result'];
        return $result;
        //</editor-fold>
     }
        
}
?>