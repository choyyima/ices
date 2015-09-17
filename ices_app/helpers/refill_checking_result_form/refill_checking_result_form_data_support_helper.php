<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Checking_Result_Form_Data_Support {

    public static function rcrf_get($id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select rcrf.*
            from refill_checking_result_form rcrf
            where rcrf.status>0 
                and rcrf.id = '.$db->escape($id).'

        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
    
    public static function rcrf_product_get($rcrf_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        
        $q = '
            select distinct rcrfp.*,
                rwop.product_marking_code,
                rwop.qty,
                rwop.qty_stock,
                rpc.id rpc_id,
                rpc.code rpc_code,
                rpc.name rpc_name,
                rpm.id rpm_id,
                rpm.code rpm_code,
                rpm.name rpm_name,
                u.id capacity_unit_id,
                u.name capacity_unit_name,
                u.code capacity_unit_code,
                rwop.capacity,
                rwop.estimated_amount,
                rwop.product_info_merk,
                rwop.product_info_type
                
            from rcrf_product rcrfp
                inner join refill_work_order_product rwop on rcrfp.product_id = rwop.id
                inner join refill_product_category rpc on rwop.refill_product_category_id = rpc.id
                inner join refill_product_medium rpm on rwop.refill_product_medium_id = rpm.id
                inner join unit u on rwop.capacity_unit_id = u.id
            where rcrfp.refill_checking_result_form_id = '.$db->escape($rcrf_id).'
        ';
        
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $t_rcrfp = $rs;            
            foreach($t_rcrfp as $idx=>$row){
                $t_rcrfp[$idx]['rcrf_product_recondition_cost'] = array();
                $t_rcrfp[$idx]['rcrf_product_sparepart_cost'] = array();
                $q = '
                    select rcrfprc.*
                    from rcrf_product_recondition_cost rcrfprc
                    where rcrfprc.rcrf_product_id = '.$db->escape($row['id']).'
                ';
                $rs2 = $db->query_array($q);
                $t_rcrfpcr = array();
                if(count($rs2)>0){
                    $t_rcrfpcr = $rs2;
                }
                $t_rcrfp[$idx]['rcrf_product_recondition_cost'] = $t_rcrfpcr;                
                
                $q = '
                    select distinct t1.*
                        ,p.code product_code
                        ,p.name product_name
                        ,u.code unit_code
                    from rcrf_product_sparepart_cost t1
                    left outer join product p 
                        on t1.product_id = p.id
                        and t1.product_type = "registered_product"
                    left outer join unit u
                        on t1.unit_id = u.id                    
                    where t1.rcrf_product_id = '.$db->escape($row['id']).'
                ';
                $rs2 = $db->query_array($q);
                $t_rcrfpsc = array();
                if(count($rs2)>0){
                    foreach($rs2 as $idx2=>$row2){
                        $rs2[$idx2]['product_text'] = SI::html_tag('strong',$row2['product_code'])
                            .' '.$row2['product_name'];
                        $rs2[$idx2]['unit_text'] = SI::html_tag('strong',$row2['unit_code']);
                        
                    }
                    $t_rcrfpsc = $rs2;
                }
                $t_rcrfp[$idx]['rcrf_product_sparepart_cost'] = $t_rcrfpsc;
                
            }
            $result = $t_rcrfp;
        }
        return $result;
        //</editor-fold>
    }
    
    public function refill_checking_result_form_get($id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*,
                t2.code customer_code,
                t2.name customer_name,
                t1.outstanding_amount
            from refill_checking_result_form t1
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
    
    public static function product_marking_code_search($lookup_data){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        get_instance()->load->helper('product/product_data_support');
        get_instance()->load->helper('product/product_engine');
        get_instance()->load->helper('product_stock_engine');
        
        $result = array();
        
        $t_rwo_product = Refill_Work_Order_Data_Support::rwo_product_search($lookup_data,array('rwo_product_status'=>'waiting_for_confirmation'));
        $rwo_product = array();
        for($i = 0;$i<count($t_rwo_product);$i++){
            if(Tools::_float($t_rwo_product[$i]['qty_stock'])>Tools::_float('0')){
                $t_product = $t_rwo_product[$i];
                $t_product['product_reference_type'] = 'refill_work_order_product';
                $t_product['product_reference_id'] = $t_rwo_product[$i]['id'];
                $t_product['product_type'] = 'refill_work_order_product';
                $t_product['product_id'] = $t_product['rwo_product_id'];
                $t_product['text'] = $t_product['product_marking_code'];
                $t_product['product_info'] = 
                    Product_Data_Support::product_type_get('refill_work_order_product')['label']
                    .' - '.$t_product['product_info_merk']
                    .' '.$t_product['product_info_type']
                    .' '.$t_product['rpc_code']
                    .' '.$t_product['rpm_code']
                    .' '.Tools::thousand_separator($t_product['capacity'])
                    .' '.$t_product['capacity_unit_code']
                    ;
                $t_product['product_img'] = '';
                for($j = 0;$j<count($t_product['unit']);$j++){
                    $t_product['unit'][$j]['stock_qty'] = $t_product['qty_stock'];
                }
                
                $rwo_product[] = $t_product;
            }
        }
        $result = array_merge($result,$rwo_product);
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
    
    public static function product_marking_code_dependency_get($product_type, $product_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product_price_list/product_price_list_data_support');
        $result = array();
        $product_sparepart_cost = array();
        $db = new DB();
        //<editor-fold defaultstate="collapsed" desc="Product Additional">
        //Please aware of product reference multiple usage
        if($product_type === 'refill_work_order_product'){
            $q = '
                select distinct rswop.id rswop_id
                    ,p.id product_id
                    ,p.code product_code
                    ,p.name product_name
                    ,u.id unit_id
                    ,u.code unit_code
                    ,u.name unit_name
                    ,rswop.qty - rswop.movement_outstanding_qty sent_qty
                    ,0 amount
                from refill_subcon_work_order rswo
                inner join rswo_product rswop on rswo.id = rswop.refill_subcon_work_order_id
                inner join product p 
                    on rswop.product_id = p.id and rswop.product_type = "registered_product"
                inner join unit u
                    on rswop.unit_id = u.id 
                where rswo.refill_subcon_work_order_status != "X"
                    and rswo.status > 0
                    and rswop.product_reference_type = "refill_work_order_product"
                    and rswop.product_reference_id = '.$db->escape($product_id).'
                    and rswop.qty - rswop.movement_outstanding_qty <> 0
            ';
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                $rspl = Refill_Checking_Result_Form_Data_Support::refill_sparepart_price_list_get();
                $ppl_id = count($rspl)>0?$rspl['id']:'';;
                $t_prd = array();
                foreach($rs as $idx=>$row){
                    $t_prd[] = array(
                        'product_id'=>$row->product_id,
                        'unit_id'=>$row->unit_id,
                        'qty'=>$row->sent_qty
                    );
                }
                $t_product_price_list = Product_Price_List_Data_Support::price_get($ppl_id,$t_prd,true);
                
                foreach($rs as $idx=>$row){
                    $row->reference_type = 'rswo_product';
                    $row->reference_id = $row->rswop_id;
                    $row->product_type = 'registered_product';
                    $row->product_text = $row->product_code.' '.$row->product_name;
                    $row->unit_text = $row->unit_code;
                    
                    foreach($t_product_price_list as $idx2=>$row2){
                        if($row->product_id === $row2['product_id']
                            && $row->unit_id === $row2['unit_id']
                            && $row->sent_qty === $row2['qty']
                        ){
                            $row->amount = Tools::_float($row2['qty']) * Tools::_float($row2['amount']);
                        }
                    }
                }
                $rs = json_decode(json_encode($rs),true);
                $product_sparepart_cost = $rs;
            }
        }
        //</editor-fold>
        
        $result['product_sparepart_cost'] = $product_sparepart_cost;
        return $result;
        //</editor-fold>
    }
    
    public static function refill_sparepart_price_list_get(){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select *
            from product_price_list ppl
            where ppl.status > 0
            and ppl.product_price_list_status = "active"
            and ppl.is_refill_sparepart_price_list = 1
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs[0];
        return $result;
        //</editor-fold>
    }
    
}
?>