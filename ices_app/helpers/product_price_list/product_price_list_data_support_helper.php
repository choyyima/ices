<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_Price_List_Data_Support {
    
    public static function product_price_list_get_by_code($ppl_code = ''){
        //<editor-fold defaultstate="colllapsed">
        $result = null;
        $db = new DB();
        $q = '
            select *
            from product_price_list ppl
            where ppl.status > 0
            and ppl.code = '.$db->escape($ppl_code).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;        
        //</editor-fold>
    }
    
    public static function price_get($ppl_id,$product_arr = array()){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        
        $q_product = 'select -1 product_id, -1 unit_id, -1 qty';
        foreach($product_arr as $idx=>$row){
            $product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
            $unit_id = isset($row['unit_id'])?Tools::_str($row['unit_id']):'';
            $qty = isset($row['qty'])?Tools::_str($row['qty']):'0';
            
            $q_product.=' union all select '.$db->escape($product_id).' product_id '
                .','.$db->escape($unit_id).' unit_id '
                .','.$db->escape($qty).' qty '
            ;
            
        }
        
        $q = '
            select t1.product_id, t1.unit_id,t1.qty, pplp.amount, max(pplp.min_qty) min_qty
            from 
            ('.$q_product.') t1
            inner join product_price_list_product pplp
            on  pplp.product_id = t1.product_id
                    and pplp.unit_id = t1.unit_id
                    and pplp.product_price_list_id = '.$db->escape($ppl_id).'
                    and pplp.min_qty <= t1.qty

            group by t1.product_id, t1.unit_id,t1.qty, pplp.amount
            
        ';
        
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs;
        return $result;
        //</editor-fold>
    }
    
    
}
?>
