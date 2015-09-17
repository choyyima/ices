<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rpt_Sales_Data_Support{
    /*
    static function product_stock_search($data){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $lookup_str = $db->escape('%'.$data['data'].'%');                
        $config = array(
            'additional_filter'=>array(
                array('key'=>'warehouse_id','query'=>' and t3.id = '),
            ),
            'query'=>array(
                'basic'=>'
                    select * from (                                
                            select distinct t2.code product_code
                                , t2.name product_name
                                , t3.name warehouse_text
                                , concat("<strong>",t2.code,"</strong> ",t2.name) product_text
                                , coalesce(t1.qty,0) product_stock_qty
                                , coalesce(pssa.qty,0) product_stock_sales_available_qty
                                , coalesce(psb.qty,0) product_stock_bad_qty
                                , pu.product_id product_id
                                , t4.code unit_text
                            from product_unit pu
                                cross join warehouse t3 
                                inner join warehouse_type t5 
                                    on t3.warehouse_type_id = t5.id and t5.code = "BOS"
                                inner join product t2 on pu.product_id = t2.id 
                                    and t2.product_status = "active"
                                inner join unit t4 on t4.id = pu.unit_id
                                left outer join product_stock t1 
                                    on pu.product_id = t1.product_id and pu.unit_id = t1.unit_id 
                                    and t3.id = t1.warehouse_id
                                left outer join product_stock_sales_available pssa 
                                    on pu.product_id = pssa.product_id and pu.unit_id = pssa.unit_id 
                                    and t3.id = pssa.warehouse_id
                                left outer join product_stock_bad psb
                                    on pu.product_id = psb.product_id and pu.unit_id = psb.unit_id 
                                    and t3.id = psb.warehouse_id
                                

                            where 1 = 1 and t3.status>0
                ',
                'where'=>'
                    and (
                        t2.code like '.$lookup_str.'

                    )
                ',
                'group'=>'
                    )tfinal
                ',
                'order'=>'order by product_code asc, warehouse_text asc'
            ),
        );
        $temp_result = SI::form_data()->ajax_table_search($config, $data);
        $temp_result = json_decode(json_encode($temp_result));
        foreach($temp_result->data as $i=>$row){
            
        }
        $temp_result = json_decode(json_encode($temp_result),true);
                
        $result = $temp_result;
        return $result;
        //</editor-fold>
    }
    */
}
?>