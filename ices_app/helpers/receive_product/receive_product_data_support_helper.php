<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Receive_Product_Data_Support{
    public static function receive_product_get($id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*
            from receive_product t1
            where t1.id = '.$db->escape($id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
    
    public static function receive_product_product_get($id){
        //<editor-fold defaultstate="collapsed">
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
            from receive_product_product t1
                inner join product t2 on t1.product_id = t2.id
                inner join unit t3 on t1.unit_id = t3.id
            where t1.receive_product_id = '.$db->escape($id).'
                and t1.product_type="registered_product"
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
            }
            $result = $rs_product;
        }
        //</editor-fold>
        
        //<editor-fold defaultstate="collapsed" desc="Refill Work Order Product">
        $q = '
            select rpp.*,
                rpc.code rpc_code,
                rpm.code rpm_code,
                u.code unit_code,
                u.name unit_name,
                cu.code cu_code,
                rwop.product_marking_code,
                rwop.capacity rwop_capacity
            from receive_product_product rpp
                inner join refill_work_order_product rwop on rpp.product_id = rwop.id
                inner join refill_product_category rpc on rpc.id = rwop.refill_product_category_id
                inner join refill_product_medium rpm on rpm.id = rwop.refill_product_medium_id
                inner join unit u on rpp.unit_id = u.id
                inner join unit cu on rwop.capacity_unit_id = cu.id
            where rpp.receive_product_id = '.$db->escape($id).'
                and rpp.product_type = "refill_work_order_product"
        ';
        $rs_product = $db->query_array($q);
        if(count($rs_product)>0){
            for($i = 0;$i<count($rs_product);$i++){
                $rs_product_id = $rs_product[$i]['product_id'];
                $rs_product[$i]['product_img'] = '';
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
        //</editor-fold>
    }
    
    public static function reference_search($lookup_data){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $lookup_str = $db->escape('%'.$lookup_data.'%');
        $limit = 10;
        $db = new DB();
        $q = '
            select distinct t1.id id
                ,t1.code code
                ,t1.purchase_invoice_date
            from purchase_invoice t1
                inner join purchase_invoice_product t2 on t1.id = t2.purchase_invoice_id
            where t1.purchase_invoice_status != "X"
                and t1.status>0
                and (
                    t1.code like '.$lookup_str.'                                
                )
                and t2.movement_outstanding_qty>0
            order by t1.purchase_invoice_date desc
            limit '.$limit.'

        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['reference_type'] = 'purchase_invoice';
                $rs[$i]['text'] = ''
                        .'<strong>'.$rs[$i]['code'].'</strong>'
                        .' '
                        .Tools::_date($rs[$i]['purchase_invoice_date'],'F d, Y H:i:s')
                    ;
            }
            $result = $rs;
        }
        
        $q = '
            select distinct rswo.*
            from refill_subcon_work_order rswo
                inner join refill_subcon rs on rswo.refill_subcon_id = rs.id
                inner join rswo_expected_product_result rswoepr on rswoepr.refill_subcon_work_order_id = rswo.id
            where rswo.refill_subcon_work_order_status = "done"
                and rswoepr.movement_outstanding_qty > 0
            and (
                rswo.code like '.$lookup_str.'
                or rs.code like '.$lookup_str.'
                or rs.name like '.$lookup_str.'
            )
            order by rswo.id desc
            limit '.$limit.'
        ';
        $rs = $db->query_array($q);
        if(count ($rs)>0){
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['reference_type'] = 'refill_subcon_work_order';
                $rs[$i]['text'] = ''
                        .'<strong>'.$rs[$i]['code'].'</strong>'
                        .' '
                        .Tools::_date($rs[$i]['refill_subcon_work_order_date'],'F d, Y H:i:s')
                    ;
            }
            $result = array_merge($result,$rs);
        }
        return $result;
        //</editor-fold>
    }
    
    public static function reference_get($receive_product_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = ' select receive_product_type from receive_product where id = '.$db->escape($receive_product_id);
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $receive_product_type = $rs[0]['receive_product_type'];
            switch($receive_product_type){
                case 'purchase_invoice':
                    $q = '
                        select t1.*                        
                        from purchase_invoice t1
                            inner join purchase_invoice_receive_product t2 on t1.id = t2.purchase_invoice_id
                        where t2.receive_product_id = '.$db->escape($receive_product_id).' 
                    ';
                    $rs_pi = $db->query_array($q);
                    if(count($rs_pi)>0){
                        $result = $rs_pi[0];
                    }
                    break;
                case 'refill_subcon_work_order':
                    $q= '
                        select rswo.*
                        from rswo_rp
                            inner join refill_subcon_work_order rswo 
                                on rswo.id = rswo_rp.refill_subcon_work_order_id
                        where rswo_rp.receive_product_id = '.$db->escape($receive_product_id).'
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        $result = $rs[0];
                    }
                    break;
            }
        }
        return $result;
        //</editor-fold>
    }
    
    public static function reference_detail_get($reference_type, $reference_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        switch($reference_type){
            case 'purchase_invoice':
                //<editor-fold defaultstate="collapsed">
                $q = '
                    select *
                    from purchase_invoice t1
                    where t1.id = '.$db->escape($reference_id).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $result = array(
                        array('id'=>'type','label'=>'Type: ','val'=>'Purchase Invoice'),
                        array('id'=>'purchase_invoice_date','label'=>'Purchase Invoice Date: ','val'=>Tools::_date($rs[0]['purchase_invoice_date'],'F d, Y H:i:s')),
                        array('id'=>'grand_total','label'=>'Grand Total: ','val'=>Tools::thousand_separator($rs[0]['grand_total'])),
                        array('id'=>'outstanding_amount','label'=>'Outstanding Amount: ','val'=>Tools::thousand_separator($rs[0]['outstanding_amount'])),
                    );
                }
                //</editor-fold>
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
    
    public static function warehouse_from_list_get($reference_type){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        switch($reference_type){
            case 'purchase_invoice':
                $temp_rs = Warehouse_Engine::supplier_get();
                $warehouse = array(
                    'id'=>$temp_rs[0]['id'],
                    'text'=>SI::html_tag('strong',$temp_rs[0]['code']).' '.$temp_rs[0]['name'],
                    'warehouse_detail'=>self::warehouse_detail_get($temp_rs[0]['id']),
                );
                
                $result[] = $warehouse;
                break;
            case 'refill_subcon_work_order':
                $temp_rs = Warehouse_Engine::refill_subcon_get();
                $warehouse = array(
                    'id'=>$temp_rs[0]['id'],
                    'text'=>SI::html_tag('strong',$temp_rs[0]['code']).' '.$temp_rs[0]['name'],
                    'warehouse_detail'=>self::warehouse_detail_get($temp_rs[0]['id']),
                );
                
                $result[] = $warehouse;
                break;
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function warehouse_to_list_get($reference_type){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        switch($reference_type){
            case 'purchase_invoice':
            case 'refill_subcon_work_order':
                $temp_rs = Warehouse_Engine::BOS_get();
                foreach($temp_rs as $idx=>$row){
                    $result[] = array(
                        'id'=>$row['id'],
                        'text'=>SI::html_tag('strong',$row['code']).' '.$row['name'],
                        'warehouse_detail'=>self::warehouse_detail_get($row['id']),
                    );
                }                
                break;
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function warehouse_detail_get($warehouse_id){
        $result = array();
        $db = new DB();
        $warehouse = Warehouse_Engine::warehouse_get($warehouse_id);
        if(count($warehouse)>0){
            $result = array(
                array('id'=>'type','label'=>'Type: ','val'=>$warehouse['warehouse_type_name'])
            );
        }
        return $result;
    }
    
    public static function reference_product_list_get($reference_type, $reference_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product/product_engine');
        get_instance()->load->helper('product/product_data_support');
        get_instance()->load->helper('product_stock_engine');
        
        $result = array();
        
        switch($reference_type){
            case 'purchase_invoice':
                get_instance()->load->helper('purchase_invoice/purchase_invoice_data_support');
                $temp_product = Purchase_Invoice_Data_Support::purchase_invoice_product_get($reference_id);
                for($i =0;$i<count($temp_product);$i++){
                    $result[] = array(
                        'product_id'=>$temp_product[$i]['product_id'],
                        'reference_type'=>'purchase_invoice_product',
                        'reference_id'=>$temp_product[$i]['id'],
                        'product_type'=>'registered_product',
                        'product_text'=>'<strong>'.$temp_product[$i]['product_code'].'</strong>'
                            .' '.$temp_product[$i]['product_name'],
                        'unit_id'=>$temp_product[$i]['unit_id'],
                        'unit_code'=>$temp_product[$i]['unit_code'],
                        'ordered_qty'=>$temp_product[$i]['qty'],
                        'outstanding_qty'=>$temp_product[$i]['movement_outstanding_qty'],
                        'max_available_qty'=>$temp_product[$i]['movement_outstanding_qty']
                    );
                }
                
                break;
            case 'refill_subcon_work_order':
                //<editor-fold defaultstate="collapsed">
                
                $db = new DB();
                $rswop = array();
                $q = '
                    select product_type, product_id, unit_id, sum(qty) qty
                    from(
                        select distinct dop.id,
                            dop.product_type,
                            dop.product_id,
                            dop.unit_id,
                            dop.qty
                        from delivery_order do
                            inner join delivery_order_product dop on do.id = dop.delivery_order_id
                            inner join rswo_do on do.id = rswo_do.delivery_order_id
                        where rswo_do.refill_subcon_work_order_id = '.$db->escape($reference_id).'
                            and do.delivery_order_status ="done"
                    ) as tf1
                    group by tf1.product_type, tf1.product_id, tf1.unit_id
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $rswop = $rs;
                }
                
                $q = ' 
                    select distinct rswoepr.*
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
                    from rswo_expected_product_result rswoepr
                        inner join unit u on rswoepr.unit_id = u.id 
                        left outer join product p on rswoepr.product_id = p.id and rswoepr.product_type = "registered_product"                        
                        left outer join refill_work_order_product rwop 
                            on rswoepr.product_id = rwop.id 
                            and rswoepr.product_type = "refill_work_order_product"
                        left outer join refill_product_category rpc on rpc.id = rwop.refill_product_category_id
                        left outer join refill_product_medium rpm on rpm.id = rwop.refill_product_medium_id
                        left outer join unit cu on cu.id = rwop.capacity_unit_id
                        
                    where rswoepr.refill_subcon_work_order_id = '.$db->escape($reference_id).'
                    order by rswoepr.product_type desc , product_id
                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['reference_type'] = "rswo_expected_product_result";
                    $rs[$i]['reference_id'] = $rs[$i]['id'];
                    
                    if($rs[$i]['product_type'] === 'registered_product'){
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
                            .' '.Tools::thousand_separator($rs[$i]['capacity'])
                            .' '.$rs[$i]['cu_code']
                        ;
                    }
                    $rs[$i]['unit_text'] = SI::html_tag('strong',$rs[$i]['unit_code'])
                        .' '.$rs[$i]['unit_name'];
                    $rs[$i]['ordered_qty'] = $rs[$i]['qty'];
                    $rs[$i]['outstanding_qty'] = $rs[$i]['movement_outstanding_qty'];
                    
                    $rs[$i]['delivered_qty'] = '0';
                    foreach($rswop as $idx=>$row){
                        if(
                            $row['product_type'] === $rs[$i]['product_type'] &&
                            $row['product_id'] === $rs[$i]['product_id'] &&
                            $row['unit_id'] === $rs[$i]['unit_id']
                        ){
                            $rs[$i]['delivered_qty'] = $row['qty'];
                        }
                    }
                                        
                    $rs[$i]['max_available_qty'] = min(
                        array(
                            $rs[$i]['delivered_qty'],
                            $rs[$i]['movement_outstanding_qty']                            
                        )
                    );
                }
                $result = $rs;
                //</editor-fold>
                break;
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function warehouse_from_get($receive_product_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select warehouse_id 
            from receive_product_warehouse_from 
            where receive_product_id = '.$db->escape($receive_product_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = Warehouse_Engine::warehouse_get($rs[0]['warehouse_id']);
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function warehouse_to_get($receive_product_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select warehouse_id 
            from receive_product_warehouse_to
            where receive_product_id = '.$db->escape($receive_product_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = Warehouse_Engine::warehouse_get($rs[0]['warehouse_id']);
        }
        
        return $result;
        //</editor-fold>
    }
    
    function notification_not_done_get(){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_simple/rpt_simple_data_support');
        $result = array('response'=>null);
        $response = null;        
        $temp_result = Rpt_Simple_Data_Support::report_table_receive_product_not_done();        
        if($temp_result['info']['data_count']>0){
            $response = array(
                'icon'=>App_Icon::html_get('fa fa-truck fa-flip-horizontal')
                ,'href'=>get_instance()->config->base_url().'rpt_simple/index/receive_product/not_done'
                ,'msg'=>' '.($temp_result['info']['data_count']).' receive product - '.Lang::get('not done yet',true,false)
            );
        }
        $result['response'] = $response;
        return $result;
        //</editor-fold>
    }
    
}
?>