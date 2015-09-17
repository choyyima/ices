<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Delivery_Order_Data_Support {
    
    static function delivery_order_get($id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.* 
            
                ,t2.contact_name warehouse_to_contact_name 
                ,t2.address warehouse_to_address
                ,t2.phone warehouse_to_phone
                ,t3.code warehouse_to_code
                ,t3.name warehouse_to_name
                ,t3.address warehouse_to_address

                ,t5.code warehouse_from_code
                ,t5.name warehouse_from_name
                ,t5.address warehouse_from_address

            from delivery_order t1
                inner join delivery_order_warehouse_to t2 on t1.id = t2.delivery_order_id
                inner join warehouse t3 on t2.warehouse_id = t3.id
                inner join delivery_order_warehouse_from t4 on t1.id = t4.delivery_order_id
                inner join warehouse t5 on t5.id = t4.warehouse_id
            where t1.status>0 
                and t1.id = '.$db->escape($id).'

        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        
        return $result;
        //</editor-fold>
    }
    
    static function delivery_order_product_get($delivery_order_id){
        get_instance()->load->helper('product/product_engine');
        get_instance()->load->helper('product/product_data_support');
        
        $result = array();
        $db = new DB();
        //<editor-fold defaultstate="collapsed" desc="Registered Product">

        $q = '
            select 
                t1.reference_type,
                t1.reference_id,
                t1.qty,
                t2.id product_id,
                t2.code product_code,
                t2.name product_name,
                t3.id unit_id,
                t3.code unit_code,
                t3.name unit_name,
                t1.product_type
                ,rwop_product_reference.product_marking_code rwop_product_reference_product_marking_code
                
            from delivery_order_product t1
                inner join product t2 on t1.product_id = t2.id
                inner join unit t3 on t1.unit_id = t3.id
                
                left outer join rswo_product rswop
                    on t1.reference_type = "rswo_product"
                    and t1.reference_id = rswop.id
                left outer join refill_work_order_product rwop_product_reference
                    on rswop.product_reference_type = "refill_work_order_product"
                    and rswop.product_reference_id = rwop_product_reference.id
                
            where t1.delivery_order_id = '.$db->escape($delivery_order_id).'
                and t1.product_type = "registered_product"
        ';
        $rs_product = $db->query_array($q);
        if(count($rs_product)>0){
            for($i = 0;$i<count($rs_product);$i++){
                $rs_product_id = $rs_product[$i]['product_id'];
                $rs_product[$i]['product_img'] = Product_Engine::img_get($rs_product_id);
                $rs_product[$i]['qty'] = $rs_product[$i]['qty'];
                $rs_product[$i]['product_text'] = SI::html_tag('strong',$rs_product[$i]['product_code'])
                    .' '.Product_Data_Support::product_type_get($rs_product[$i]['product_type'])['label']
                    .' - '.$rs_product[$i]['product_name']
                    ;
                $rs_product[$i]['unit_text'] = SI::html_tag('strong',$rs_product[$i]['unit_code'])
                    .' '.$rs_product[$i]['unit_name']
                    ;
                
                if(!is_null($rs_product[$i]['rwop_product_reference_product_marking_code'])){
                    $rs_product[$i]['product_text'].= ' Ref:'.SI::html_tag('strong',$rs_product[$i]['rwop_product_reference_product_marking_code']);
                }
                
            }
            $result = array_merge($result,$rs_product);
        }
        //</editor-fold>

        //<editor-fold defaultstate="collapsed" desc="Refill Work Order Product">
        $q = '
            select dop.*,
                rpc.code rpc_code,
                rpm.code rpm_code,
                u.code unit_code,
                u.name unit_name,
                cu.code cu_code,
                rwop.product_marking_code,
                rwop.capacity rwop_capacity
            from delivery_order_product dop
                inner join refill_work_order_product rwop on dop.product_id = rwop.id
                inner join refill_product_category rpc on rpc.id = rwop.refill_product_category_id
                inner join refill_product_medium rpm on rpm.id = rwop.refill_product_medium_id
                inner join unit u on dop.unit_id = u.id
                inner join unit cu on rwop.capacity_unit_id = cu.id
            where dop.delivery_order_id = '.$db->escape($delivery_order_id).'
                and dop.product_type = "refill_work_order_product"
        ';
        $rs_product = $db->query_array($q);
        if(count($rs_product)>0){
            for($i = 0;$i<count($rs_product);$i++){
                $rs_product_id = $rs_product[$i]['product_id'];                            
                $rs_product[$i]['qty'] = $rs_product[$i]['qty'];
                $rs_product[$i]['product_text'] = SI::html_tag('strong',$rs_product[$i]['product_marking_code'])
                    .' '.Product_Data_Support::product_type_get($rs_product[$i]['product_type'])['label']
                    .' - '.$rs_product[$i]['rpc_code']
                    .' '.$rs_product[$i]['rpm_code']
                    .' '.Tools::thousand_separator($rs_product[$i]['rwop_capacity'])
                    .' '.$rs_product[$i]['cu_code']
                    ;
                $rs_product[$i]['unit_text'] = SI::html_tag('strong',$rs_product[$i]['unit_code'])
                    .' '.$rs_product[$i]['unit_name']
                    ;
            }
            $result = array_merge($result,$rs_product);
        }
        //</editor-fold>
        
        
        return $result;
    }
    
    static function warehouse_from_get($id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select dowf.*
            from delivery_order_warehouse_from dowf
            where dowf.delivery_order_id = '.$db->escape($id).'

        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        
        return $result;
        //</editor-fold>
    }
    static function warehouse_to_get($id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select dowt.*
            from delivery_order_warehouse_to dowt            
            where dowt.delivery_order_id = '.$db->escape($id).'

        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        
        return $result;
        //</editor-fold>
    }
    
    static function notification_delivery_order_not_done_get(){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_simple/rpt_simple_data_support');
        $result = array('response'=>null);
        $response = null;
        $temp_result = Rpt_Simple_Data_Support::report_table_delivery_order_not_done();        
        if($temp_result['info']['data_count']>0){
            $response = array(
                'icon'=>App_Icon::html_get('fa fa-truck')
                ,'href'=>get_instance()->config->base_url().'rpt_simple/index/delivery_order/not_done'
                ,'msg'=>' '.($temp_result['info']['data_count']).' '.Lang::get('delivery order',true,false,false,true).' - '.Lang::get(array('not done yet'),true,false,false,true).''
            );
        }
        $result['response'] = $response;
        return $result;
        //</editor-fold>
    }
    
    static function reference_search($lookup_data){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $lookup_str = $db->escape('%'.$lookup_data.'%');
        $limit = 10;
        //<editor-fold defaultstate="collapsed" desc="RMA">
        $q = '
            select distinct t1.id id
                ,t1.code code
                ,t3.id supplier_id
                ,t3.name supplier_name
            from rma t1    
                inner join rma_supplier t2 on t1.id = t2.rma_id
                inner join supplier t3 on t3.id = t2.supplier_id
            where t1.rma_status = "O"
                and t1.status>0
                and t1.rma_type = "purchase_invoice"
                and (
                    t1.code like '.$lookup_str.'
                        or t3.name like '.$lookup_str.'
                )
            limit 0,'.$limit.'

        ';
        $rs = $db->query_array($q);
        for($i = 0;$i<count($rs);$i++){
            $rs[$i]['reference_type'] = 'rma';
            $rs[$i]['reference_code'] = $rs[$i]['code'];
            $rs[$i]['text'] = ''
                    .$rs[$i]['code']
                    .' <span class="pull-right">'
                    .' Supplier: <strong>'.$rs[$i]['supplier_name'].'</strong>'
                    .'</span>'
                ;
        }
        $result = array_merge($result,$rs);
        //</editor-fold>
        
        //<editor-fold desc="Refill Subcon Work Order" defaultstate="collapsed">
        $q = '
            select distinct rswo.id id
                ,rswo.code rswo_code
                ,rswo.refill_subcon_work_order_date rswo_date
            from refill_subcon_work_order rswo
                inner join rswo_product rswop on rswo.id = rswop.refill_subcon_work_order_id
            where rswo.status > 0 
                and rswo.code like '.$lookup_str.'
                and rswo.refill_subcon_work_order_status = "done"
                and rswop.movement_outstanding_qty > 0
            order by rswo.moddate desc
            limit '.$limit.'
        ';
        
        $rs = $db->query_array($q);
        for($i = 0;$i<count($rs);$i++){
            $rs[$i]['reference_type'] = 'refill_subcon_work_order';            
            $rs[$i]['text'] = SI::html_tag('strong',$rs[$i]['rswo_code'])
                .' '.$rs[$i]['rswo_date']
            ;
        }
        
        $result = array_merge($result, $rs);
        //</editor-fold>
        
        //</editor-fold>
        return $result;
    }
    
    static function reference_detail_get($reference_type,$reference_id,$delivery_order_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('delivery_order_final/delivery_order_final_engine');
        $dof_path = Delivery_Order_Final_Engine::path_get();
        $result = array();
        $db = new DB();
        switch($reference_type){
            case 'sales_invoice':
                $delivery_order_final = Delivery_Order_Data_Support::delivery_order_final_get($delivery_order_id);
                                   
                $result = array(
                    array('id'=>'type','label'=>'Type: ','val'=>SI::type_get('delivery_order_engine', $reference_type)['label']),
                    array('id'=>'dof_code','label'=>Lang::get('Delivery Order Final').': ',
                        'val'=>SI::html_tag('a',$delivery_order_final['code'],array('target'=>'_blank','href'=>$dof_path->index.'view/'.$delivery_order_final['id']))
                    ),
                );
                     
                
                break;
            case 'refill_subcon_work_order':
                get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_engine');
                get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_data_support');
                $path = Refill_Subcon_Work_Order_Engine::path_get();
                $rswo = Refill_Subcon_Work_Order_Data_Support::rswo_get($reference_id);
                
                $result = array(
                    array('id'=>'type','label'=>'Type: ','val'=>SI::type_get('delivery_order_engine', $reference_type)['label']),
                    array('id'=>'rswo_code','label'=>Lang::get(array('Refill',' - ','Subcon Work Order')).': ',
                        'val'=>SI::html_tag('a',$rswo['code'],array('target'=>'_blank','href'=>$path->index.'view/'.$rswo['id']))
                    ),
                );
                break;
        }
        return $result;
        //</editor-fold>
    }
    
    static function warehouse_to_list_get($reference_type,$reference_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        
        switch($reference_type){
            case 'refill_subcon_work_order':
                $temp_warehouse_to = Warehouse_Engine::refill_subcon_get();
                get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_data_support');
                $rswo = Refill_Subcon_Work_Order_Data_Support::rswo_get($reference_id);
                foreach($temp_warehouse_to as $idx=>$row){
                    $row['contact_name'] = isset($rswo['refill_subcon_name'])?$rswo['refill_subcon_name']:'';
                    $row['address'] = isset($rswo['refill_subcon_address'])?$rswo['refill_subcon_address']:'';
                    $row['phone'] = isset($rswo['refill_subcon_phone'])?$rswo['refill_subcon_phone']:'';
                    $row['warehouse_type_text'] = SI::html_tag('strong',$row['warehouse_type_code']).' '.$row['warehouse_type_name'];
                    $row['text'] = SI::html_tag('strong',$row['code']).' '.$row['name'];            
                    $result[] = $row;
                }
                break;
        }
        
        
        
        return $result;
        //</editor-fold>
    }
    
    static function reference_product_list_get($ref_type, $ref_id, $warehouse_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product/product_engine');
        get_instance()->load->helper('product/product_data_support');
        get_instance()->load->helper('product_stock_engine');
        $result = array();
        
        $db = new DB();
        
        switch($ref_type){
            case 'refill_subcon_work_order':
                //<editor-fold defaultstate="collapsed">
                
                $q = ' 
                    select distinct rswop.*
                        ,u.code unit_code
                        ,u.name unit_name
                        ,p.code reg_product_code
                        ,p.name reg_product_name
                        ,rwop.product_marking_code
                        ,rwop.capacity
                        ,rwop.qty_stock rwop_qty_stock
                        ,rpc.code rpc_code
                        ,rpm.code rpm_code
                        ,cu.code cu_code
                        ,rwop_product_reference.product_marking_code rwo_product_reference_product_marking_code
                    from rswo_product rswop
                        inner join unit u on rswop.unit_id = u.id 
                        left outer join product p on rswop.product_id = p.id and rswop.product_type = "registered_product"
                        
                        left outer join refill_work_order_product rwop 
                            on rswop.product_id = rwop.id 
                            and rswop.product_type = "refill_work_order_product"
                        left outer join refill_product_category rpc on rpc.id = rwop.refill_product_category_id
                        left outer join refill_product_medium rpm on rpm.id = rwop.refill_product_medium_id
                        left outer join unit cu on cu.id = rwop.capacity_unit_id
                        
                        left outer join refill_work_order_product rwop_product_reference
                            on rswop.product_reference_type = "refill_work_order_product"
                            and rswop.product_reference_id = rwop_product_reference.id
                            
                    where rswop.refill_subcon_work_order_id = '.$db->escape($ref_id).'
                    order by rswop.product_type desc , product_id
                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['reference_type'] = "rswo_product";
                    $rs[$i]['reference_id'] = $rs[$i]['id'];
                    
                    if($rs[$i]['product_type'] === 'registered_product'){
                        $rs[$i]['stock_qty'] = Product_Stock_Engine::
                            stock_sum_get('stock_sales_available',$rs[$i]['product_id'],$rs[$i]['unit_id'],array($warehouse_id));
                        $rs[$i]['product_img'] = Product_Engine::img_get($rs[$i]['product_id']);
                        $rs[$i]['product_text'] = SI::html_tag('strong',$rs[$i]['reg_product_code'])
                            .' '.Product_Data_Support::product_type_get($rs[$i]['product_type'])['label']
                            .' - '.$rs[$i]['reg_product_name'];
                    }
                    else if ($rs[$i]['product_type'] === 'refill_work_order_product'){
                        $rs[$i]['stock_qty'] = $rs[$i]['rwop_qty_stock'];
                        $rs[$i]['product_text'] = SI::html_tag('strong',$rs[$i]['product_marking_code'])
                            .' '.Product_Data_Support::product_type_get($rs[$i]['product_type'])['label']
                            .' - '.$rs[$i]['rpc_code']
                            .' '.$rs[$i]['rpm_code']
                            .' '.$rs[$i]['capacity']
                            .' '.$rs[$i]['cu_code']
                        ;
                    }
                    
                    if(!is_null(Tools::empty_to_null($rs[$i]['product_reference_type']))){
                        if($rs[$i]['product_reference_type'] === 'refill_work_order_product'){
                            $rs[$i]['product_text'].=' Ref:'.'<strong>'.$rs[$i]['rwo_product_reference_product_marking_code'].'</strong>';
                        }
                    }
                    $rs[$i]['unit_text'] = SI::html_tag('strong',$rs[$i]['unit_code'])
                        .' '.$rs[$i]['unit_name'];
                    $rs[$i]['ordered_qty'] = $rs[$i]['qty'];
                    $rs[$i]['outstanding_qty'] = $rs[$i]['movement_outstanding_qty'];
                }
                $result = $rs;
                //</editor-fold>
                break;
        }
        return $result;
        //</editor-fold>
    }
    
    static function rswo_get($delivery_order_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select refill_subcon_work_order_id
            from rswo_do
            where delivery_order_id = '.$db->escape($delivery_order_id).'
        ';
        
        $rs = $db->query_array($q);
        if(count($rs)>0){
            get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_data_support');
            $result = Refill_Subcon_Work_Order_Data_Support::rswo_get($rs[0]['refill_subcon_work_order_id']);
        }
        
        return $result;
        //</editor-fold>
    }
    
    static function delivery_order_final_get($delivery_order_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('delivery_order_final/delivery_order_final_data_support');
        $result = array();
        $db = new DB();
        $q = '
            select dofdo.delivery_order_final_id
            from delivery_order_final_delivery_order dofdo
            where dofdo.delivery_order_id = '.$db->escape($delivery_order_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $dof_id = $rs[0]['delivery_order_final_id'];
            $result = Delivery_Order_Final_Data_Support::delivery_order_final_get($dof_id);
            
        }
        return $result;
        //</editor-fold>
    }
}
?>