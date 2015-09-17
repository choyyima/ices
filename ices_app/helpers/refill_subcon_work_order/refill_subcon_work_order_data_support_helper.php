<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Subcon_Work_Order_Data_Support {

    public static function rswo_get($id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select rswo.*
                ,rs.code refill_subcon_code
                ,rs.name refill_subcon_name
                ,rs.address refill_subcon_address
                ,rs.phone refill_subcon_phone
                
            from refill_subcon_work_order rswo
                inner join refill_subcon rs on rswo.refill_subcon_id = rs.id
                
            where rswo.status>0 
                and rswo.id = '.$db->escape($id).'

        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
    
    public static function rswo_product_get($rswo_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        
        $q = '
            select distinct rswop.*,
                p.code registered_product_code,
                p.name registered_product_name,
                u.code unit_code,
                u.name unit_name,
                f_rwop.*,
                f_product_reference_rwop.rwop_product_marking_code product_reference_rwop_product_marking_code
                
            from rswo_product rswop
                inner join unit u on rswop.unit_id = u.id
                left outer join product p 
                    on rswop.product_id = p.id and rswop.product_type = "registered_product"
                left outer join (
                    select rwop.id rwop_product_id,
                        rwop.capacity rwop_capacity,
                        u.code rwop_capacity_unit_code,
                        u.name rwop_capacity_unit_name,
                        rwop.product_marking_code rwop_product_marking_code,
                        rpc.code rwop_rpc_code,
                        rpc.name rwop_rpc_name,
                        rpm.code rwop_rpm_code,
                        rpm.name rwop_rpm_name
                    from refill_work_order_product rwop 
                        inner join refill_product_category rpc on rwop.refill_product_category_id = rpc.id
                        inner join refill_product_medium rpm on rwop.refill_product_medium_id = rpm.id
                        inner join unit u on rwop.capacity_unit_id = u.id
                        
                ) f_rwop
                    on rswop.product_id = f_rwop.rwop_product_id 
                    and rswop.product_type = "refill_work_order_product"
                left outer join (
                    select rwop.id rwop_product_id,
                        rwop.product_marking_code rwop_product_marking_code
                    from refill_work_order_product rwop 
                ) f_product_reference_rwop
                    on rswop.product_reference_id = f_product_reference_rwop.rwop_product_id 
                    and rswop.product_reference_type = "refill_work_order_product"
            where rswop.refill_subcon_work_order_id = '.$db->escape($rswo_id).'
                order by rswop.id
        ';
        
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs;
        return $result;
        //</editor-fold>
    }
    
    public function rswo_expected_product_result_get($rswo_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        
        $q = '
            select distinct rswoepr.*,
                p.code registered_product_code,
                p.name registered_product_name,
                u.code unit_code,
                u.name unit_name,
                f_rwop.*
                
            from rswo_expected_product_result rswoepr
                inner join unit u on rswoepr.unit_id = u.id
                left outer join product p 
                    on rswoepr.product_id = p.id and rswoepr.product_type = "registered_product"
                left outer join (
                    select rwop.id rwop_product_id,
                        rwop.capacity rwop_capacity,
                        u.code rwop_capacity_unit_code,
                        u.name rwop_capacity_unit_name,
                        rwop.product_marking_code rwop_product_marking_code,
                        rpc.code rwop_rpc_code,
                        rpc.name rwop_rpc_name,
                        rpm.code rwop_rpm_code,
                        rpm.name rwop_rpm_name
                    from refill_work_order_product rwop 
                        inner join refill_product_category rpc on rwop.refill_product_category_id = rpc.id
                        inner join refill_product_medium rpm on rwop.refill_product_medium_id = rpm.id
                        inner join unit u on rwop.capacity_unit_id = u.id
                        
                ) f_rwop
                    on rswoepr.product_id = f_rwop.rwop_product_id 
                    and rswoepr.product_type = "refill_work_order_product"                
            where rswoepr.refill_subcon_work_order_id = '.$db->escape($rswo_id).'
        ';
        
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs;
        return $result;
        //</editor-fold>
    }
    
    public function refill_subcon_work_order_get($id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*,
                t2.code customer_code,
                t2.name customer_name,
                t1.outstanding_amount
            from refill_subcon_work_order t1
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
    
    public function product_search($lookup_data){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        get_instance()->load->helper('product/product_data_support');
        get_instance()->load->helper('product/product_engine');
        get_instance()->load->helper('product_stock_engine');
        
        $result = array();
        $registered_product = Product_Data_Support::registered_product_search($lookup_data,array('product_status'=>'active'));
        for($i = 0;$i<count($registered_product);$i++){
            $product_id = $registered_product[$i]['product_id'];            
            $registered_product[$i]['id'] = $registered_product[$i]['product_type'].'#'.$product_id;
            $registered_product[$i]['text'] = SI::html_tag('strong',
                $registered_product[$i]['product_code'])
                .' '.Product_Data_Support::product_type_get('registered_product')['label']
                .' - '.$registered_product[$i]['product_name']
                
                ;
            $registered_product[$i]['product_img'] = Product_Engine::img_get($registered_product[$i]['product_id']);
            for($j = 0;$j<count($registered_product[$i]['unit']);$j++){
                $unit_id = $registered_product[$i]['unit'][$j]['unit_id'];    
                $registered_product[$i]['unit'][$j]['stock_qty'] = 
                    Product_Stock_Engine::stock_sum_get(
                    'stock_sales_available',$product_id,$unit_id,array()
                    
                );
            }
        }
        
        $rwo_product = Refill_Work_Order_Data_Support::rwo_product_search($lookup_data,array('rwo_product_status'=>'ready_to_process'));
        for($i = 0;$i<count($rwo_product);$i++){
            $rwo_product[$i]['id'] = $rwo_product[$i]['product_type'].'#'.$rwo_product[$i]['rwo_product_id'];
            $rwo_product[$i]['product_id'] = $rwo_product[$i]['rwo_product_id'];
            $rwo_product[$i]['rswo_product_reference_req'] = 'no';
            $rwo_product[$i]['text'] = SI::html_tag('strong',
                $rwo_product[$i]['product_marking_code'])
                .' '.Product_Data_Support::product_type_get('refill_work_order_product')['label']
                .' - '.$rwo_product[$i]['rpc_code']
                .' '.$rwo_product[$i]['rpm_code']
                .' '.Tools::thousand_separator($rwo_product[$i]['capacity'])
                .' '.$rwo_product[$i]['capacity_unit_code']
                    
                ;
            $rwo_product[$i]['product_img'] = '';
            for($j = 0;$j<count($rwo_product[$i]['unit']);$j++){
                $rwo_product[$i]['unit'][$j]['stock_qty'] = $rwo_product[$i]['qty_stock'];
            }
            
        }
        $result = array_merge($registered_product,$rwo_product);
        for($i = 0;$i<count($result);$i++){
            for($j = 0;$j<count($result[$i]['unit']);$j++){
                $result[$i]['unit'][$j]['id'] = $result[$i]['unit'][$j]['unit_id'];
                $result[$i]['unit'][$j]['text'] = SI::html_tag('strong',$result[$i]['unit'][$j]['unit_code'])
                    .' '.$result[$i]['unit'][$j]['unit_name'];
            }
        }

        return $result;
        //</editor-fold>
    }
    
    public function product_reference_search($lookup_data){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        get_instance()->load->helper('product/product_data_support');
        get_instance()->load->helper('product/product_engine');
        get_instance()->load->helper('product_stock_engine');
        
        $result = array();
        
        $rwo_product = Refill_Work_Order_Data_Support::rwo_product_search($lookup_data,array('module'=>'refill_subcon_work_order'));
        for($i = 0;$i<count($rwo_product);$i++){
            $rwo_product[$i]['id'] = $rwo_product[$i]['product_type'].'#'.$rwo_product[$i]['rwo_product_id'];
            $rwo_product[$i]['product_id'] = $rwo_product[$i]['rwo_product_id'];
            $rwo_product[$i]['text'] = SI::html_tag('strong',
                $rwo_product[$i]['product_marking_code'])
                .' '.Product_Data_Support::product_type_get('refill_work_order_product')['label']
                .' - '.$rwo_product[$i]['rpc_code']
                .' '.$rwo_product[$i]['rpm_code']
                .' '.Tools::thousand_separator($rwo_product[$i]['capacity'])
                .' '.$rwo_product[$i]['capacity_unit_code']
                    
                ;
            $rwo_product[$i]['product_img'] = '';
        }
        $result = array_merge($rwo_product);
        
        return $result;
        //</editor-fold>
    }
    
}
?>