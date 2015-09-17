<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mf_Work_Process_Data_Support{
    
    
    public static function mf_work_process_get($id){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = null;
        $q = '
            select *
            from mf_work_process
            where id = '.$db->escape($id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
    
    public static function mfwp_info_get($mf_work_process_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
           select mfwpi.*
           from mfwp_info mfwpi
           where mfwpi.mf_work_process_id = '.$db->escape($mf_work_process_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
    
    public static function mfwp_worker_get($mf_work_process_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
           select mfwpw.*
           from mfwp_worker mfwpw
           where mfwpw.mf_work_process_id = '.$db->escape($mf_work_process_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function reference_get($mf_work_process_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $mf_work_process = self::mf_work_process_get($mf_work_process_id);
        if(count($mf_work_process)>0){
            $q = '
                select mfwo.*
                from mf_work_order mfwo
                where mfwo.id = '.$db->escape($mf_work_process['reference_id']).'
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0){
                $result = array(
                    'id'=>$rs[0]['id'],
                    'reference_type'=>$rs[0]['mf_work_order_type'],
                    'text'=>SI::html_tag('strong',$rs[0]['code'])
                );
            }
            
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function mfwp_expected_result_product_get($mf_work_process_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $mf_work_process_db = Mf_Work_Process_Data_Support::mf_work_process_get($mf_work_process_id);
        if(count($mf_work_process_db)>0){
            $mf_work_process_type = $mf_work_process_db['mf_work_process_type'];
           
            if(in_array($mf_work_process_type,array('normal','bad_stock_transform','good_stock_transform'))){
                //<editor-fold defaultstate="collapsed">
                 $q = '
                    select distinct mfwperp.*
                        ,p.code product_code
                        ,p.name product_name
                        ,u.code unit_code
                        ,u.name unit_name
                        ,bom.code bom_code
                        ,bom.name bom_name
                    from mfwp_expected_result_product mfwperp
                    inner join product p 
                        on mfwperp.product_id = p.id 
                        and  mfwperp.product_type = "registered_product"
                    inner join unit u
                        on mfwperp.unit_id = u.id
                    left outer join bom 
                        on mfwperp.bom_id = bom.id
                    where mfwperp.mf_work_process_id = '.$db->escape($mf_work_process_id).'
                ';
                
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $result = $rs;
                }
                //</editor-fold>
            }
            
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function mfwp_component_product_get($mf_work_process_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $mf_work_process_db = Mf_Work_Process_Data_Support::mf_work_process_get($mf_work_process_id);
        if(count($mf_work_process_db)>0){
            $mf_work_process_type = $mf_work_process_db['mf_work_process_type'];
           
            if(in_array($mf_work_process_type,array('normal','bad_stock_transform','good_stock_transform'))){
                //<editor-fold defaultstate="collapsed">
                 $q = '
                    select mfwpcp.*
                        ,p.code product_code
                        ,p.name product_name
                        ,u.code unit_code
                        ,u.name unit_name
                    from mfwp_component_product mfwpcp
                        left outer join product p on mfwpcp.product_id = p.id 
                            and mfwpcp.product_type ="registered_product"
                        left outer join unit u on mfwpcp.unit_id = u.id
                    where mfwpcp.mf_work_process_id = '.$db->escape($mf_work_process_id).'
                ';
                
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $result = $rs;
                }
                //</editor-fold>
            }
            
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function mfwp_result_product_get($mf_work_process_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $mf_work_process_db = Mf_Work_Process_Data_Support::mf_work_process_get($mf_work_process_id);
        if(count($mf_work_process_db)>0){
            $mf_work_process_type = $mf_work_process_db['mf_work_process_type'];
           
            if(in_array($mf_work_process_type,array('normal','bad_stock_transform','good_stock_transform'))){
                //<editor-fold defaultstate="collapsed">
                 $q = '
                    select mfwprp.*
                        ,p.code product_code
                        ,p.name product_name
                        ,u.code unit_code
                        ,u.name unit_name
                    from mfwp_result_product mfwprp
                        left outer join product p on mfwprp.product_id = p.id 
                            and mfwprp.product_type ="registered_product"
                        left outer join unit u on mfwprp.unit_id = u.id
                    where mfwprp.mf_work_process_id = '.$db->escape($mf_work_process_id).'
                ';
                
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $result = $rs;
                }
                //</editor-fold>
            }
            
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function mfwp_scrap_product_get($mf_work_process_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $mf_work_process_db = Mf_Work_Process_Data_Support::mf_work_process_get($mf_work_process_id);
        if(count($mf_work_process_db)>0){
            $mf_work_process_type = $mf_work_process_db['mf_work_process_type'];
           
            if(in_array($mf_work_process_type,array('normal','bad_stock_transform','good_stock_transform'))){
                //<editor-fold defaultstate="collapsed">
                 $q = '
                    select mfwpsp.*
                        ,p.code product_code
                        ,p.name product_name
                        ,u.code unit_code
                        ,u.name unit_name
                    from mfwp_scrap_product mfwpsp
                        left outer join product p on mfwpsp.product_id = p.id 
                            and mfwpsp.product_type ="registered_product"
                        left outer join unit u on mfwpsp.unit_id = u.id
                    where mfwpsp.mf_work_process_id = '.$db->escape($mf_work_process_id).'
                ';
                
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $result = $rs;
                }
                //</editor-fold>
            }
            
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function mf_work_process_exists($id=""){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from mf_work_process 
                where status > 0 && id = '.$db->escape($id).'
            ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function sir_get($mf_work_process_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('sir/sir_data_support');
        $result = array();
        $t_sir = SIR_Data_Support::sir_by_reference_get('mf_work_process','free_rules',$mf_work_process_id);
        if(count($t_sir)>0){
            $result = array(
                'id'=>$t_sir['id']
            );
        }
        return $result;
        //</editor-fold>
    }
    
    public static function reference_search($lookup_str=''){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        get_instance()->load->helper('mf_work_order/mf_work_order_data_support');
        $rs = Mf_Work_Order_Data_Support::mf_work_order_search($lookup_str,array('mf_work_order_status'=>'approved'));
        
        if(count($rs) > 0){
            foreach($rs as $i=>$row){
                $rs[$i]['reference_type'] = $rs[$i]['mf_work_order_type'];
            }
            $result = $rs;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function reference_detail_get($reference_type, $reference_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        switch($reference_type){
            case 'normal':
            case 'good_stock_transform':
            case 'bad_stock_transform':
                get_instance()->load->helper('mf_work_order/mf_work_order_data_support');
                get_instance()->load->helper('mf_work_order/mf_work_order_engine');
                $temp_data = Mf_Work_Order_Data_Support::mf_work_order_get($reference_id);
                if(count($temp_data)>0){
                    $mfwo_info = Mf_Work_Order_Data_Support::mfwo_info_get($reference_id);
                    $result = array(
                        array('id'=>'type','label'=>'Type: ','val'=>SI::type_get('mf_work_order_engine', $temp_data['mf_work_order_type'])['label']),
                        array('id'=>'mf_work_order_date','label'=>'Date: ','val'=>Tools::_date($temp_data['mf_work_order_date'],'F d, Y H:i:s')),
                        array('id'=>'start_date_plan','label'=>Lang::get(array('Start','Date','Plan')).': ','val'=>Tools::_date($mfwo_info['start_date_plan'],'F d, Y H:i:s')),
                        array('id'=>'end_date_plan','label'=>Lang::get(array('End','Date','Plan')).': ','val'=>Tools::_date($mfwo_info['end_date_plan'],'F d, Y H:i:s')),
                        array('id'=>'approver','label'=>'Approver: ','val'=>$mfwo_info['approver']),
                    );
                }
                break;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function available_expected_result_product_get($reference_id,$warehouse_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        get_instance()->load->helper('mf_work_order/mf_work_order_data_support');
        get_instance()->load->helper('bom/bom_data_support');
        get_instance()->load->helper('product_stock_engine');
        $mfwo  = Mf_Work_Order_Data_Support::mf_work_order_get($reference_id);
        $rs_mfwoop = Mf_Work_Order_Data_Support::available_ordered_product_get($reference_id);
        if(count($rs_mfwoop)>0){
            $t_result = array();
            foreach($rs_mfwoop as $i=>$row){
                $t_result[] = array(
                    'reference_type'=>'mfwo_ordered_product'
                    ,'reference_id'=>$row['id']
                    ,'product_img'=>  $row['product_type'] === 'registered_product'?
                        Product_Engine::img_get($row['product_id']):''
                    ,'product_type'=>$row['product_type']
                    ,'product_id'=>$row['product_id']
                    ,'product_code'=>$row['product_code']
                    ,'product_name'=>$row['product_name']
                    ,'unit_id'=>$row['unit_id']
                    ,'unit_code'=>$row['unit_code']
                    ,'unit_name'=>$row['unit_name']
                    ,'ordered_qty'=>$row['qty']
                    ,'outstanding_qty'=>$row['outstanding_qty']
                    ,'max_qty'=>0
                    ,'bom_id'=>$row['bom_id']
                    ,'bom_code'=>$row['bom_code']
                    ,'bom_name'=>$row['bom_name']
                );
            }
            $t_result = json_decode(json_encode($t_result));
            foreach($t_result as $i=>$row){
                if(
                    $row->reference_type === 'mfwo_ordered_product' 
                    && Tools::empty_to_null($row->bom_id) === null
                    && $row->product_type === 'registered_product'
                ){
                    if($mfwo['mf_work_order_type'] === 'good_stock_transform'){
                        $stock_qty = Product_Stock_Engine::stock_sum_get(
                            'stock_bad',$row->product_id,$row->unit_id
                            ,array($warehouse_id)
                        );
                        $row->max_qty = Tools::_float($stock_qty) < Tools::_float($row->outstanding_qty) ?
                                $stock_qty:$row->outstanding_qty;
                    }
                    else if($mfwo['mf_work_order_type'] === 'bad_stock_transform'){
                        $stock_qty = Product_Stock_Engine::stock_sum_get(
                            'stock_sales_available',$row->product_id,$row->unit_id
                            ,array($warehouse_id)
                        );
                        $row->max_qty = Tools::_float($stock_qty) < Tools::_float($row->outstanding_qty) ?
                            $stock_qty:$row->outstanding_qty;
                    }
                }
                else if(
                    $row->reference_type === 'mfwo_ordered_product' 
                    && Tools::empty_to_null($row->bom_id) !== null
                ){
                    $stock_qty = BOM_Data_Support::bom_from_component_stock_sum_get(
                        $row->bom_id,array($warehouse_id)
                    );
                    $row->max_qty = Tools::_float($stock_qty) < Tools::_float($row->outstanding_qty) ?
                        $stock_qty:$row->outstanding_qty;
                }
            }
            
            $t_result = json_decode(json_encode($t_result),true);
            $result = $t_result;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function available_component_product_get($module_type, $warehouse_id,
    $expected_result_product){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        get_instance()->load->helper('product_stock_engine');
        get_instance()->load->helper('mf_work_process/mf_work_process_engine');
        $db = new DB();
        if($module_type === 'normal'){
            //<editor-fold defaultstate="collapsed">
            $t_result = array();
            $t_result = json_decode(json_encode($t_result));
            //<editor-fold defaultstate="collapsed" desc="BOM Product">
            get_instance()->load->helper('bom/bom_data_support');

            foreach($expected_result_product as $i=>$row){
                $bom_id = isset($row['bom_id'])?Tools::_str($row['bom_id']):'';
                $expected_product_result_qty = isset($row['qty'])?
                    Tools::_str(Tools::_float(Tools::_str($row['qty']))):
                    '0';
                $t_bom_result_product = BOM_Data_Support::result_product_get($bom_id);
                if(count($t_bom_result_product)>0
                    && Tools::_float($expected_product_result_qty)> Tools::_float('0')
                ){
                    $t_bom_component_product = BOM_Data_Support::component_product_get(
                        $bom_id,array($warehouse_id)
                    );
                    foreach($t_bom_component_product as $i2=>$row2){
                        $row_target = null;
                        foreach($t_result as $i3=>$row3){
                            if(
                                $row3->product_type === $row2['product_type']
                                && $row3->product_id === $row2['product_id']
                                && $row3->unit_id === $row2['unit_id']
                            ){
                                $row_target = $row3;
                            }
                        }
                        
                        if($row_target === null){
                            $t_result[] = json_decode(json_encode(array(
                                'product_type'=>$row2['product_type'],
                                'product_id'=>$row2['product_id'],
                                'product_code'=>$row2['product_code'],
                                'product_name'=>$row2['product_name'],
                                'unit_id'=>$row2['unit_id'],
                                'unit_code'=>$row2['unit_code'],
                                'unit_name'=>$row2['unit_name'],
                                'qty'=>'0',
                                'stock_location' => 
                                SI::type_get(
                                    'mf_work_process_engine',
                                    'normal',
                                    '$module_type_list'
                                )['default_component_product_stock_location'],
                                'stock_location_text' => SI::type_get('mf_work_process_engine', 'good_stock','$stock_location_list')['label'],
                                'warehouse_stock_qty'=>Product_Stock_Engine::stock_sum_get(
                                        'stock_sales_available',                                     
                                        $row2['product_id'],
                                        $row2['unit_id'],
                                        array($warehouse_id)
                                ),
                            )));
                            $row_target = $t_result[count($t_result)-1];
                            
                        }
                        $row_target->qty += Tools::_str(
                            (Tools::_float($expected_product_result_qty) 
                            * Tools::_float($row2['qty']))
                            / Tools::_float($t_bom_result_product['qty'])
                        );
                    }
                }
            }
            //</editor-fold>
            $t_result = json_decode(json_encode($t_result),true);
            $result = $t_result;
            //</editor-fold>
        }
        else if (in_array($module_type,array('bad_stock_transform','good_stock_transform'))){
            //<editor-fold defaultstate="collapsed">
            $t_result = array();
            
            //<editor-fold defaultstate="collapsed" desc="Registered Product">
            $q_prod = 'select -1 product_id, -1 unit_id, 0 qty';
            
            foreach($expected_result_product as $i=>$row){
                $product_type = isset($row['product_type'])?Tools::_str($row['product_type']):'';
                $product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
                $unit_id = isset($row['unit_id'])?Tools::_str($row['unit_id']):'';
                $qty = isset($row['qty'])?Tools::_str($row['qty']):'';
                if($product_type ==='registered_product'){
                    $q_prod.=' union select '.$db->escape($product_id).','.$db->escape($unit_id).','.$db->escape($qty);
                }
            }
            $q = '
                select distinct
                    "registered_product" product_type
                    ,p.id product_id
                    ,p.name product_name
                    ,p.code product_code
                    ,u.id unit_id
                    ,u.code unit_code
                    ,u.name unit_name
                    ,tp.qty qty
                from product p
                    inner join product_unit pu on p.id = pu.product_id
                    inner join unit u on u.id = pu.unit_id
                    inner join ('.$q_prod.') tp 
                        on tp.product_id = p.id
                        and tp.unit_id = u.id
                where p.status > 0 and p.product_status = "active"
                    and u.status > 0
            ';
            $rs_pu = $db->query_array($q);
            if(count($rs_pu)>0){
                $rs_pu = json_decode(json_encode($rs_pu));
                foreach($rs_pu as $i=>$row){
                    $row->warehouse_stock_qty = '0';
                    if($module_type ==='good_stock_transform'){
                        $row->stock_location = 
                        SI::type_get(
                            'mf_work_process_engine',
                            $module_type,
                            '$module_type_list'
                        )['default_component_product_stock_location'];
                        $row->stock_location_text = SI::type_get('mf_work_process_engine','bad_stock','$stock_location_list')['label'];
                        $row->warehouse_stock_qty = Product_Stock_Engine::stock_sum_get('stock_bad',$row->product_id,$row->unit_id,array($warehouse_id));
                    }
                    else if ($module_type === 'bad_stock_transform'){
                        $row->stock_location = SI::type_get(
                            'mf_work_process_engine',
                            $module_type,
                            '$module_type_list'
                        )['default_component_product_stock_location'];
                        $row->stock_location_text = SI::type_get('mf_work_process_engine','good_stock','$stock_location_list')['label'];
                        $row->warehouse_stock_qty = Product_Stock_Engine::stock_sum_get('stock_sales_available',$row->product_id,$row->unit_id,array($warehouse_id));
                    }
                }
                $rs_pu = json_decode(json_encode($rs_pu));
                $t_result = array_merge($t_result,$rs_pu);
            }
            //</editor-fold>
            $t_result = json_decode(json_encode($t_result),true);
            $result = $t_result;
            //</editor-fold>
        }

        return $result;
        //</editor-fold>
    }
    
    public static function result_product_get($mf_work_process_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select brp.*,
                p.code product_code,
                p.name product_name,
                u.code unit_code,
                u.name unit_name
                
            from mf_work_process_result_product brp 
                inner join product p on brp.product_id = p.id
                inner join unit u on brp.unit_id = u.id
            where brp.mf_work_process_id = '.$db->escape($mf_work_process_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function available_result_product_get($mf_work_process_id){
        //<editor-fold defaultstate="collapsed">
        //GROUP BY QUERY IS VERY IMPORTANT JUST IN CASE THERE IS MORE THAN ONE REFERENCE TYPE 
        $result = array();
        $db = new DB();
        $mf_work_process = self::mf_work_process_get($mf_work_process_id);
        $q = '
            select
                mfwperp.product_type product_type,
                p.id product_id,
                p.code product_code,
                p.name product_name,
                u.id unit_id,
                u.code unit_code,
                u.name unit_name,
                 sum(mfwperp.qty) qty
            from mfwp_expected_result_product mfwperp 
                inner join product p 
                    on mfwperp.product_id = p.id and mfwperp.product_type = "registered_product"
                inner join unit u on mfwperp.unit_id = u.id
            where mfwperp.mf_work_process_id = '.$db->escape($mf_work_process_id).'
                group by mfwperp.product_type, p.id, u.id
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $rs = json_decode(json_encode($rs));
            foreach($rs as $i=>$row){
                $mf_work_process_type = $mf_work_process['mf_work_process_type'];
                $row->stock_location = SI::type_get(
                    'mf_work_process_engine',
                    $mf_work_process_type,
                    '$module_type_list'
                )['default_result_product_stock_location'];
            }
            $rs = json_decode(json_encode($rs),true);
            $result = $rs;
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function available_scrap_product_get($mf_work_process_id){
        //<editor-fold defaultstate="collapsed">
        //GROUP BY QUERY IS VERY IMPORTANT JUST IN CASE THERE IS MORE THAN ONE REFERENCE TYPE 
        $result = array();
        $db = new DB();
        $mf_work_process = self::mf_work_process_get($mf_work_process_id);
        $q = '
            select
                t1.*,
                p.id product_id,
                p.code product_code,
                p.name product_name,
                u.id unit_id,
                u.code unit_code,
                u.name unit_name
                
            from mfwp_component_product t1 
                inner join product p 
                    on t1.product_id = p.id and t1.product_type = "registered_product"
                inner join unit u on t1.unit_id = u.id
            where t1.mf_work_process_id = '.$db->escape($mf_work_process_id).'

        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $rs = json_decode(json_encode($rs));
            foreach($rs as $i=>$row){
                $row->max_qty = $row->qty;
            }
            $rs = json_decode(json_encode($rs),true);
            $result = $rs;
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function product_search($lookup_str,$warehouse_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        get_instance()->load->helper('product/product_data_support');
        get_instance()->load->helper('product/product_engine');
        get_instance()->load->helper('product_stock_engine');
        $trs = Product_Data_Support::registered_product_search($lookup_str,array('product_status'=>'active'));
        if(count($trs)>0){
            $trs = json_decode(json_encode($trs));
            foreach($trs as $i=>$row){
                $row->id = $row->product_id;
                $row->text = SI::html_tag('strong',$row->product_code)
                    .' '.$row->product_name;
                foreach($row->unit as $i2=>$row2){
                    $row2->id = $row2->unit_id;
                    $row2->text = SI::html_tag('strong',$row2->unit_code)
                    .' '.$row2->unit_name;
                    $row2->warehouse_stock_qty = Product_Stock_Engine::stock_sum_get(
                        'stock_sales_available',$row->product_id,$row2->unit_id
                        ,array($warehouse_id)

                    );
                }
                $row->product_img = Product_Engine::img_get($row->product_id);
                $row->product_type = 'registered_product';
                
            }
            $trs = json_decode(json_encode($trs),true);
            $result = $trs;
        }
        return $result;
        //</editor-fold>
    }
    
    
    
}
?>