<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Print_Form_Data_Support {
    public static function product_stock_opname_product_list_get($opt = array()){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select 
                distinct 
                ps.id product_subcategory_id,
                ps.code product_subcategory_code,
                ps.name product_subcategory_name,
                p.id product_id,
                p.code product_code,
                p.name product_name,
                u.id unit_id,
                u.code unit_code                    
            from product p 
                inner join product_unit pu on p.id = pu.product_id
                inner join unit u on pu.unit_id = u.id
                left outer join product_subcategory ps on p.product_subcategory_id = ps.id
            where 1 = 1 
                and ps.status > 0
                and p.status > 0
                and u.status > 0
            order by ps.name, p.name asc
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs;
        }
        return $result;
        //</editor-fold>
    }
    
}
?>