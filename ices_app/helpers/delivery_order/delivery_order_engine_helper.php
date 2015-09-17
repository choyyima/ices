<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Delivery_Order_Engine {
    public static $prefix_id = 'delivery_order';
    public static $prefix_method = '';
    public static $module_type_list;        
    public static $status_list;

    function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$prefix_method = self::$prefix_id;

        self::$module_type_list = array(
            //<editor-fold defaultstate="collapsed">
            array('val'=>'sales_invoice','label'=>'Sales Invoice'),
            array('val'=>'rma','label'=>'Return Merchandise Authorization'),
            array('val'=>'refill_subcon_work_order','label'=>'Refill - '.Lang::get('Subcon Work Order')),
            //</editor-fold>
        );
        self::$status_list =  array(
            //<editor-fold defaultstate="collapsed">
            array(
                'val'=>''
                ,'label'=>''
                ,'method'=>self::$prefix_method.'_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add'),array('val'=>'Delivery Order'),array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'process'
                ,'label'=>'PROCESS'
                ,'method'=>self::$prefix_method.'_process'
                ,'default'=>true
                ,'next_allowed_status'=>array('done','X')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update'),array('val'=>'Delivery Order'),array('val'=>'success')
                    )
                )
            )
            ,array(
                'val'=>'done'
                ,'label'=>'DONE'
                ,'method'=>self::$prefix_method.'_done'
                ,'next_allowed_status'=>array('X')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update'),array('val'=>'Delivery Order'),array('val'=>'success')
                    )
                )

            )
            ,array(
                'val'=>'X'
                ,'label'=>'CANCELED'
                ,'method'=>self::$prefix_method.'_canceled'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update'),array('val'=>'Delivery Order'),array('val'=>'success')
                    )
                )
            )
            //</editor-fold>
        );

        //</editor-fold>
    }



    public static function delivery_order_exists($id){
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from delivery_order 
                where status > 0 && id = '.$db->escape($id).'
            ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
    }

    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'delivery_order/'
            ,'delivery_order_engine'=>'delivery_order/delivery_order_engine'
            ,'delivery_order_rma_engine'=>'delivery_order/delivery_order_rma_engine'
            ,'delivery_order_sales_pos_engine'=>'delivery_order/delivery_order_sales_pos_engine'
            ,'delivery_order_print'=>'delivery_order/delivery_order_print'
            ,'delivery_order_renderer' => 'delivery_order/delivery_order_renderer'
            ,'ajax_search'=>get_instance()->config->base_url().'delivery_order/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'delivery_order/data_support/'

        );

        return json_decode(json_encode($path));
    }


    public static function validate($method,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('delivery_order/delivery_order_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        
        $reference = isset($data['reference'])?$data['reference']:null;
        $delivery_order = isset($data['delivery_order'])?$data['delivery_order']:null;
        $do_product = isset($data['delivery_order_product'])? $data['delivery_order_product']: null;
        $warehouse_to = isset($data['warehouse_to'])?$data['warehouse_to']:null;
        $warehouse_from = isset($data['warehouse_from'])?$data['warehouse_from']:null;
        $do_type = '';
        
        $db = new DB();
        $delivery_order_id = isset($delivery_order['id'])?
            $delivery_order['id']:'';
        switch($method){
            case self::$prefix_method.'_add':
                get_instance()->load->helper('product_stock_engine');
                //<editor-fold defaultstate="collapsed">
                $do_type = isset($delivery_order['delivery_order_type'])?
                    Tools::_str($delivery_order['delivery_order_type']):'';
                $reference_id = isset($reference['reference_id'])?
                    Tools::_str($reference['reference_id']):'';
                $delivery_order_date = Tools::_date(isset($delivery_order['delivery_order_date'])?
                    Tools::_str($delivery_order['delivery_order_date']):'','Y-m-d H:i:s');
                $store_id = isset($delivery_order['store_id'])?
                    Tools::_str($delivery_order['store_id']):'';
                $warehouse_from_id = isset($warehouse_from['warehouse_id'])?
                    Tools::_str($warehouse_from['warehouse_id']):'';
                $warehouse_to_id = isset($warehouse_to['warehouse_id'])?
                    Tools::_str($warehouse_to['warehouse_id']):'';
                $reference_product_list = Delivery_Order_Data_Support::reference_product_list_get($do_type, $reference_id, $warehouse_from_id);
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
                
                if(!SI::type_match('delivery_order_engine', $do_type)){
                    $success = 0;
                    $msg[] = 'Reference empty';
                }
                
                $q = 'select 1 from store where status>0 and id ='.$db->escape($store_id);
                if(count($db->query_array_obj($q)) == 0){
                    $success = 0;
                    $msg[] = "Store Empty";                    
                }                   

                $q = 'select 1 from warehouse where status>0 and id = '.$db->escape($warehouse_from_id).'';
                if(count($db->query_array_obj($q)) === 0){
                    $success = 0;
                    $msg[] = Lang::get("Warehouse From",true,true,false,false,true).' '.Lang::get('empty',true,false);
                    
                }
                
                $q = 'select 1 from warehouse where status>0 and id = '.$db->escape($warehouse_to_id).'';
                if(count($db->query_array_obj($q)) === 0){
                    $success = 0;
                    $msg[] = Lang::get("Warehouse To").' '.Lang::get("empty",true,false);
                    
                }
                
                if(strtotime($delivery_order_date) < strtotime(Tools::_date('','Y-m-d H:i:s'))){
                    $success = 0;
                    $msg[] = Lang::get(array("Delivery Order","Date")).' '
                        .Lang::get('must be greater than',true,false,false,true).' '.Tools::_date('','F d, Y H:i:s');

                }
                
                if(!count($do_product)> 0){
                    $success = 0;
                    $msg[] = Lang::get('Product').' '.Lang::get('empty',true,false);
                }
                
                if($success !== 1) break;
                
                //</editor-fold>
                
                //<editor-fold defaultstate="collapsed" desc="Reference">
                if(!in_array($do_type,array('refill_subcon_work_order'))){
                    $success = 0;
                    $msg[] = 'Reference Type invalid';
                }
                else if($do_type === 'refill_subcon_work_order'){
                    $q = '
                        select 1 
                        from refill_subcon_work_order rswo
                        where rswo.status>0 
                            and rswo.id = '.$db->escape($reference_id).'
                            and rswo.refill_subcon_work_order_status = "done"
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs) == 0){
                        $success = 0;
                        $msg[] = 'Reference invalid';
                    }
                }
                //</editor-fold>
                
                //<editor-fold defaultstate="collapsed" desc="Product Validation">
                
                //<editor-fold defaultstate="collapsed" desc="Product in DB">
                foreach($do_product as $idx=>$row){
                    $product_reference_type = isset($row['reference_type'])?
                        Tools::_str($row['reference_type']):'';
                    $product_reference_id = isset($row['reference_id'])?
                        Tools::_str($row['reference_id']):'';
                    $product_type = isset($row['product_type'])?
                        Tools::_str($row['product_type']):'';
                    $product_id = isset($row['product_id'])?
                        Tools::_str($row['product_id']):'';
                    $unit_id = isset($row['unit_id'])?
                        Tools::_str($row['unit_id']):'';
                    $qty = isset($row['qty'])?
                        Tools::_str($row['qty']):'';
                    
                    $product_valid = false;
                    
                    foreach($reference_product_list as $idx2=>$row2){
                        $rf_product_reference_type = isset($row2['reference_type'])?
                            Tools::_str($row2['reference_type']):'';
                        $rf_product_reference_id = isset($row2['reference_id'])?
                            Tools::_str($row2['reference_id']):'';
                        $rf_product_type = isset($row2['product_type'])?
                            Tools::_str($row2['product_type']):'';
                        $rf_product_id = isset($row2['product_id'])?
                            Tools::_str($row2['product_id']):'';
                        $rf_unit_id = isset($row2['unit_id'])?
                            Tools::_str($row2['unit_id']):'';
                        $rf_stock_qty = isset($row2['stock_qty'])?
                            Tools::_str($row2['stock_qty']):'';
                        
                        if($rf_product_type === $product_type
                            && $rf_product_reference_type === $product_reference_type
                            && $rf_product_reference_id === $product_reference_id
                            && $rf_product_id === $product_id
                            && $rf_unit_id === $unit_id
                            && Tools::_float($qty)<= Tools::_float($rf_stock_qty)
                            && Tools::_float($qty) > Tools::_float(0)
                        ){
                            $product_valid = true;
                            break;
                        }
                        
                    }
                    
                    if(!$product_valid){
                        $success = 0;
                        $msg[] = 'Product'.' '.Lang::get('invalid');
                    }
                    
                    if($success !== 1) break;
                }
                //</editor-fold>
                
                //<editor-fold defaultstate="collapsed" desc="Product Duplicate">
                foreach($do_product as $idx=>$row){
                    $product_reference_type = isset($row['reference_type'])?
                        Tools::_str($row['reference_type']):'';
                    $product_reference_id = isset($row['reference_id'])?
                        Tools::_str($row['reference_id']):'';
                    $product_type = isset($row['product_type'])?
                        Tools::_str($row['product_type']):'';
                    $product_id = isset($row['product_id'])?
                        Tools::_str($row['product_id']):'';
                    $unit_id = isset($row['unit_id'])?
                        Tools::_str($row['unit_id']):'';
                    
                    $product_duplicate = false;
                    
                    foreach($do_product as $idx2=>$row2){
                        $product_reference_type2 = isset($row2['reference_type'])?
                            Tools::_str($row2['reference_type']):'';
                        $product_reference_id2 = isset($row2['reference_id'])?
                            Tools::_str($row2['reference_id']):'';
                        $product_type2 = isset($row2['product_type'])?
                            Tools::_str($row2['product_type']):'';
                        $product_id2 = isset($row2['product_id'])?
                            Tools::_str($row2['product_id']):'';
                        $unit_id2 = isset($row2['unit_id'])?
                            Tools::_str($row2['unit_id']):'';
                        
                        if($idx!== $idx2
                            && $product_type2 === $product_type
                            && $product_reference_type2 === $product_reference_type
                            && $product_reference_id2 === $product_reference_id
                        ){
                            $product_duplicate = true;
                            break;
                        }
                        
                    }
                    
                    if($product_duplicate){
                        $success = 0;
                        $msg[] = 'Product'.' '.Lang::get('duplicate');
                    }
                    
                    if($success !== 1) break;
                }
                //</editor-fold>
                
                //</editor-fold>
                
                //</editor-fold>
                break;
            case self::$prefix_method.'_process':
            case self::$prefix_method.'_done':
                //<editor-fold defaultstate="collapsed">
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'delivery_order',
                        'module_name'=>'Delivery Order',
                        'module_engine'=>'Delivery_Order_Engine',
                    ),
                    $delivery_order
                );
                $success = $temp_result['success'];
                $msg = array_merge($msg,$temp_result['msg']);
                if($success!==1) break;

                $delivery_order_db = $db->fast_get('delivery_order',array('id'=>$delivery_order_id))[0];
                $do_type = $delivery_order_db['delivery_order_type'];
                //</editor-fold>
                break;
            case self::$prefix_method.'_canceled':
                //<editor-fold defaultstate="collapsed">
                $temp_result = Validator::validate_on_cancel(
                    array(
                        'module'=>'delivery_order',
                        'module_name'=>'Delivery Order',
                        'module_engine'=>'Delivery_Order_Engine',
                    ),
                    $delivery_order
                );
                $success = $temp_result['success'];
                $msg = array_merge($msg,$temp_result['msg']);

                if($success !== 1) break;

                $delivery_order_db = $db->fast_get('delivery_order',array('id'=>$delivery_order_id))[0];
                $do_type = $delivery_order_db['delivery_order_type'];
                
                if($do_type ==='sales_invoice'){
                    $success = 0;
                    $msg[] = 'Cancel POS Delivery Order '.Lang::get('failed',true,false).'. '.Lang::get('Use').' Delivery Order Final';
                }
                
                //</editor-fold>
                break;
                
            default:
                $success = 0;
                $msg[] = 'Invalid Method';
                break;
        }
        
        if($success === 1){
            switch($do_type){
                case 'refill_subcon_work_order':
                    $temp_result = self::validate_refill_subcon_work_order($method,$data);
                    $success = $temp_result['success'];
                    $msg = array_merge($msg,$temp_result['msg']);
                    break;
            }
            
        }
                
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }

    public static function validate_refill_subcon_work_order($method,$data){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('delivery_order/delivery_order_data_support');
        get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_data_support');
        $result = array('success'=>1,'msg'=>array());
        $success = 1;
        $msg = array();
        $db = new DB();
        
        $reference = isset($data['reference'])?$data['reference']:null;
        $delivery_order = isset($data['delivery_order'])?$data['delivery_order']:null;
        $do_product = isset($data['delivery_order_product'])? $data['delivery_order_product']: null;
        $warehouse_to = isset($data['warehouse_to'])?$data['warehouse_to']:null;
        $warehouse_from = isset($data['warehouse_from'])?$data['warehouse_from']:null;
        
        $delivery_order_id = Tools::_str($delivery_order['id']);
        $delivery_order_db = Delivery_Order_Data_Support::delivery_order_get($delivery_order_id);
        $rswo_id = '';
        $q = '
            select rswo_do.refill_subcon_work_order_id
            from rswo_do
            where delivery_order_id = '.$db->escape($delivery_order_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $rswo_id = $rs[0]['refill_subcon_work_order_id'];
        }
        $rswo_db = Refill_Subcon_Work_Order_Data_Support::rswo_get($rswo_id);
        
        
        switch($method){
            case self::$prefix_method.'_add':
                //<editor-fold defaultstate="collapsed">
                $reference_id = isset($reference['reference_id'])?
                    Tools::_str($reference['reference_id']):'';
                $warehouse_from_id = isset($warehouse_from['warehouse_id'])?
                    Tools::_str($warehouse_from['warehouse_id']):'';
                $q = '
                    select rswop.*
                    from rswo_product rswop
                    where rswop.refill_subcon_work_order_id = '.$db->escape($reference_id).'
                ';
                $rswo_product = $db->query_array($q);
                
                $reg_p_arr = array();
                $rwo_p_arr = array();

                foreach( $do_product as $key=>$row){
                    //<editor-fold defaultstate="collapsed" desc="group product to registered or rwo product">
                        $reference_type = isset($row['reference_type'])?Tools::_str($row['reference_type']):'';
                        $reference_id = isset($row['reference_id'])?Tools::_str($row['reference_id']):'';
                        $product_type = isset($row['product_type'])?Tools::_str($row['product_type']):'';
                        $product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
                        $unit_id = isset($row['unit_id'])?Tools::_str($row['unit_id']):'';
                        $qty = isset($row['qty'])?Tools::_str($row['qty']):'0';

                        if(Tools::_float($qty)===Tools::_float('0')){
                            $success = 0;
                            $msg[] = 'Qty 0';

                        }
                        $t_p = array('reference_type'=>$reference_type,'reference_id'=>$reference_id,'product_id'=>$product_id,'unit_id'=>$unit_id,'qty'=>$qty,'product_type'=>$product_type);
                        switch($product_type){
                            case 'registered_product':
                                $reg_p_arr[] = $t_p;
                                break;
                            case 'refill_work_order_product':
                                $rwo_p_arr[] = $t_p;
                                break;
                            default:
                                $success = 0;
                                $msg[] = 'Product Type invalid';
                                break;
                        }
                        if($success !== 1) break;

                    //</editor-fold>
                }
                                
                foreach(array_merge($reg_p_arr,$rwo_p_arr) as $idx=>$row
                ){
                    //<editor-fold defaultstate="collapsed" desc="validate product unit is in refill subcon work order">
                    $product_unit_valid = false;

                    foreach($rswo_product as $idx=>$p){
                        if(
                            $p['product_type'] === $row['product_type']
                            && $p['product_id'] === $row['product_id'] 
                            && $p['unit_id'] === $row['unit_id']
                        ){
                            $product_unit_valid = true;
                            break;
                        }
                    }
                    
                    if(!$product_unit_valid){
                        $success = 0;
                        $msg[] = 'Product Unit invalid';
                        break;
                    
                    }
                    //</editor-fold>
                }

                foreach(array_merge($reg_p_arr,$rwo_p_arr) as $idx=>$p){
                    //<editor-fold defaultstate="collapsed" desc="validate product qty vs stock">
                    if($p['product_type'] === 'registered_product'){
                        $product_id = $p['product_id'];
                        $unit_id = $p['unit_id'];
                        $qty = $p['qty'];
                        $total_stock = Product_Stock_Engine::stock_sum_get('stock_sales_available',$product_id,$unit_id,array($warehouse_from_id));
                        
                        if(Tools::_float($qty)>Tools::_float($total_stock)){
                            $success = 0;
                            $msg[] = 'Product Qty higher than stock';
                            break;
                        }
                    }
                    else if($p['product_type'] === 'refill_work_order_product'){
                        foreach($rswo_product as $idx=>$row){
                            if(
                                $p['product_type'] === $row['product_type']
                                && $p['product_id'] === $row['product_id'] 
                                && $p['unit_id'] === $row['unit_id']
                            ){
                                if(Tools::_float($p['qty'])>Tools::_float($row['movement_outstanding_qty'])){
                                    $success = 0;
                                    $msg[] = 'Product Qty higher than stock';
                                    break;
                                }
                            }
                        }
                    }
                    if($success !== 1) break;
                    //</editor-fold>
                }
                
                foreach(array_merge($reg_p_arr,$rwo_p_arr) as $idx=>$row
                ){
                    //<editor-fold defaultstate="collapsed" desc="validate product qty vs refill subcon work order movement outstanding">
                    foreach($rswo_product as $idx=>$p){
                    
                        if( 
                            $p['product_type'] === $row['product_type']
                            && $p['product_id'] === $row['product_id'] 
                            && $p['unit_id'] === $row['unit_id']
                            && $row['reference_type'] === 'rswo_product'
                            && $row['reference_id'] === $p['id']
                        ){
                            $qty = $row['qty'];
                            $mov_outstanding_qty = $p['movement_outstanding_qty'];
                            if(!Tools::_float($qty) > Tools::_float('0')){
                                $success = 0;
                                $msg[] = 'Product Qty'.' '.'0';
                                break;
                            }
                            if($p['product_type'] === 'registered_product'){
                                if(Tools::_float($qty)>Tools::_float($mov_outstanding_qty)){
                                    $success = 0;
                                    $msg[] = 'Product Qty '.Lang::get('lower than').' '.Lang::get('Movement Outstanding Qty');
                                    break;
                                }
                            }
                            else if ($p['product_type'] === 'refill_work_order_product'){
                                if(Tools::_float($qty) !== Tools::_float($mov_outstanding_qty)){
                                    $success = 0;
                                    $msg[] = 'Refill Product Qty '.Lang::get('different from',true,false,false,true).' '.Lang::get('Movement Outstanding Qty',true,true,true) ;
                                    break;
                                }
                            }
                        }

                        if($success !== 1) break;
                    }
                    
                    if($success !== 1) break;
                    //</editor-fold>
                }
                //</editor-fold>
                break;
            case self::$prefix_method.'_process':
            case self::$prefix_method.'_done':
                break;
            case self::$prefix_method.'_canceled':
                $q = '
                    select distinct 1
                    from receive_product rp
                        inner join receive_product_product rpp on rp.id = rpp.receive_product_id
                        inner join rswo_rp on rp.id = rswo_rp.receive_product_id
                        inner join delivery_order_product dop 
                            on dop.product_type = rpp.product_type
                            and dop.product_id = rpp.product_id
                            and dop.unit_id = rpp.unit_id
                    where rswo_rp.refill_subcon_work_order_id = '.$db->escape($rswo_id).'
                        and rp.receive_product_status !="X"
                        and dop.delivery_order_id = '.$db->escape($delivery_order_id).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $success = 0;
                    $msg[] = 'Receive Product '.' '.Lang::get('exists');
                }
                break;
            default:
                $success = 0;
                $msg[] = 'Invalid Method';
                break;
                
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        ///</editor-fold>
    }
    
    public static function adjust($action,$data=array()){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();

        switch($action){
            case 'delivery_order_add':
                //<editor-fold defaultstate="collapsed">
                $delivery_order = $data['delivery_order'];
                $delivery_order_product = $data['delivery_order_product'];
                $warehouse_to  = $data['warehouse_to'];
                $warehouse_from  = $data['warehouse_from'];

                $result['delivery_order_warehouse_to'] = array(
                        'warehouse_id'=>$warehouse_to['warehouse_id']
                        ,'contact_name'=>isset($warehouse_to['contact_name'])?
                                $warehouse_to['contact_name']:''
                        ,'phone'=>isset($warehouse_to['phone'])?
                            preg_replace('/[^0-9]/','',$warehouse_to['phone']):''
                        ,'address'=>isset($warehouse_to['address'])?
                            $warehouse_to['address']:''
                    );

                $result['delivery_order_warehouse_from'] = array(
                    'warehouse_id'=>$warehouse_from['warehouse_id']
                    );

                $result['delivery_order'] = array(
                    'code'=>''
                    ,'store_id'=>$delivery_order['store_id']
                    ,'delivery_order_date'=>Tools::_date($delivery_order['delivery_order_date'],'Y-m-d H:i:s')
                    ,'delivery_order_type'=>$delivery_order['delivery_order_type']
                    ,'delivery_order_status'=>SI::status_default_status_get('delivery_order_engine')['val']
                    ,'notes'=>$delivery_order['notes']
                );

                $result['delivery_order_product'] = array();
                for($i = 0;$i<count($delivery_order_product);$i++){
                    if(floatval($delivery_order_product[$i]['qty'])>0){
                        $result['delivery_order_product'][] = array(
                            'reference_type'=>$delivery_order_product[$i]['reference_type'],
                            'reference_id'=>$delivery_order_product[$i]['reference_id'],
                            'product_type'=>$delivery_order_product[$i]['product_type']
                            ,'product_id'=>$delivery_order_product[$i]['product_id']
                            ,'unit_id'=>$delivery_order_product[$i]['unit_id']
                            ,'qty'=>$delivery_order_product[$i]['qty']
                        );
                    }
                }
                
                if($delivery_order['delivery_order_type'] === 'refill_subcon_work_order'){
                    $result['rswo_do'] = array(
                        'refill_subcon_work_order_id'=>$data['reference']['reference_id']
                    );
                }
                
                //</editor-fold>
                break;

            case 'delivery_order_done':
            case 'delivery_order_process':
                //<editor-fold defaultstate="collapsed">
                $delivery_order = $data['delivery_order']; 
                $delivery_order_status = '';
                $status_list = SI::status_list_get('Delivery_Order_Engine');

                foreach($status_list as $status_idx=>$status){
                    if($status['method'] === $action) $delivery_order_status = $status['val'];
                }

                $result['delivery_order'] = array(
                    'notes'=>isset($delivery_order['notes'])?$delivery_order['notes']:''
                    ,'delivery_order_status'=>$delivery_order_status
                );
                //</editor-fold>
                break;
            case 'delivery_order_canceled':
                //<editor-fold defaultstate="collapsed">
                $delivery_order = $data['delivery_order'];

                $result['delivery_order'] = array(
                    'notes'=>isset($delivery_order['notes'])?$delivery_order['notes']:''
                    ,'cancellation_reason'=>isset($delivery_order['cancellation_reason'])?$delivery_order['cancellation_reason']:''
                    ,'delivery_order_status'=>'X'
                );
                //</editor-fold>
                break;
        }

        return $result;
        //</editor-fold>
    }

    function delivery_order_add($db, $final_data,$id = ''){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $fdelivery_order = $final_data['delivery_order'];
        $fwarehouse_from = $final_data['delivery_order_warehouse_from'];
        $fwarehouse_to = $final_data['delivery_order_warehouse_to'];
        $fdelivery_order_final_delivery_order = isset($final_data['delivery_order_final_delivery_order'])?
            Tools::_arr($final_data['delivery_order_final_delivery_order']):
            array();
        $fdelivery_order_product = $final_data['delivery_order_product'];


        $store_id = $fdelivery_order['store_id'];
        $delivery_order_type = $fdelivery_order['delivery_order_type'];
        $delivery_order_code =  SI::code_counter_store_get($db,$store_id, 'delivery_order');
        $fdelivery_order['code'] = $delivery_order_code;
        if(!$db->insert('delivery_order',$fdelivery_order)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $delivery_order_id = '';

        if($success === 1){                                
            $delivery_order_id = $db->fast_get('delivery_order'
                    ,array('code'=>$delivery_order_code))[0]['id'];
            $result['trans_id']=$delivery_order_id; 
        }

        if($success === 1){
            $fwarehouse_from['delivery_order_id']=$delivery_order_id;
            if(!$db->insert('delivery_order_warehouse_from',$fwarehouse_from)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }

        if($success === 1){
            $fwarehouse_to['delivery_order_id']=$delivery_order_id;
            if(!$db->insert('delivery_order_warehouse_to',$fwarehouse_to)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }

        if($success === 1){
            if($delivery_order_type === 'sales_invoice'){
                get_instance()->load->helper('sales_pos/sales_pos_engine');
            }
            else if($delivery_order_type==='refill_subcon_work_order'){
                get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_engine');
                get_instance()->load->helper('refill_work_order/refill_work_order_engine');
            }
        }
        
        if($success === 1){
            switch($delivery_order_type){
                case 'sales_invoice':
                    $fdelivery_order_final_delivery_order['delivery_order_id'] = $delivery_order_id;
                    if(!$db->insert('delivery_order_final_delivery_order',$fdelivery_order_final_delivery_order)){
                        $msg[] = $db->_error_message();
                        $db->trans_rollback();                                
                        $success = 0;
                    }
                    break;
                case 'refill_subcon_work_order':
                    $frswo_do =$final_data['rswo_do'];
                    $frswo_do['delivery_order_id'] = $delivery_order_id;
                    if(!$db->insert('rswo_do',$frswo_do)){
                        $msg[] = $db->_error_message();
                        $db->trans_rollback();                                
                        $success = 0;
                    }
                    break;
            }
            
        }

        if($success === 1){
            foreach($fdelivery_order_product as $fp_idx=>$fp){
                $fp['delivery_order_id']=$delivery_order_id;
                if(!$db->insert('delivery_order_product',$fp)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }

                $product_type = $fp['product_type'];

                if($success === 1){
                    //<editor-fold defaultstate="collapsed" desc="MOVEMENT OUTSTANDING QTY">
                    switch($delivery_order_type){
                        case 'sales_invoice':
                            if($product_type === 'registered_product'){                                
                                $q = '
                                    select t1.sales_invoice_id
                                    from sales_invoice_delivery_order_final t1
                                    where t1.delivery_order_final_id = '.$db->escape($fdelivery_order_final_delivery_order['delivery_order_final_id']).'
                                ';
                                $sales_invoice_id = $db->query_array($q)[0]['sales_invoice_id'];
                                $temp_si_product = array(
                                    'reference_id'=>$fp['reference_id'],
                                    'qty'=>-1*Tools::_float($fp['qty'])
                                );
                                $temp_result = Sales_Pos_Engine::movement_outstanding_qty_add($db, $sales_invoice_id,$temp_si_product);
                                $success = $temp_result['success'];
                                $msg = array_merge($msg,$temp_result['msg']);
                            }

                            break;
                        case 'refill_subcon_work_order':
                            $rswo_id = $final_data['rswo_do']['refill_subcon_work_order_id'];
                            $p_reference_id = $fp['reference_id'];
                            $qty = -1*Tools::_float($fp['qty']);
                                    
                            $temp_result = Refill_Subcon_Work_Order_Engine::movement_outstanding_qty_add(
                                $db
                                ,'product'
                                ,$rswo_id
                                ,$p_reference_id
                                ,$qty
                            );
                            
                            $success = $temp_result['success'];
                            $msg = array_merge($msg,$temp_result['msg']);
                            break;
                            
                        
                    }
                    //</editor-fold>
                }

                if($success === 1){
                    //<editor-fold defaultstate="collapsed" desc="STOCK">
                    if($product_type === 'registered_product'){
                        $temp_result = Product_Stock_Engine::stock_sales_available_only_add(
                            $db,
                            $fwarehouse_from['warehouse_id'],
                            $fp['product_id'],
                            -1*Tools::_float($fp['qty']),
                            $fp['unit_id'],
                            'Delivery Order: '.$fdelivery_order['code'].' '.SI::status_get('Delivery_Order_Engine',
                                $fdelivery_order['delivery_order_status'])['label'],
                            $moddate
                        );
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);
                    }
                    else if ($product_type === 'refill_work_order_product'){                        
                        $temp_result = Refill_Work_Order_Engine::product_stock_add(
                            $db,
                            $fp['product_id'],
                            -1*Tools::_float($fp['qty'])
                        );
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);
                    }
                    //</editor-fold>
                }

                if($success !== 1) break;

            }
        }

        if($success === 1){
            $temp_res = SI::status_log_add($db,
                'delivery_order',
                $delivery_order_id,
                $fdelivery_order['delivery_order_status']
            );

            $success = $temp_res['success'];

            if($success !== 1){
                $msg = array_merge($msg, $temp_res['msg']);
            }
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function delivery_order_process($db, $final_data ,$id){
        //<editor-fold defaultstate="collapsed" >
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $fdelivery_order = array_merge($final_data['delivery_order'],array("modid"=>$modid,"moddate"=>$moddate));


        $delivery_order = array();
        $q = '
            select t1.*
            from delivery_order t1
            where t1.id = '.$db->escape($id).'
        ';
        $delivery_order = $db->query_array($q)[0];

        if(!$db->update('delivery_order',$fdelivery_order,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'delivery_order',
                $id,$fdelivery_order['delivery_order_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }


        $result['trans_id']=$id;

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function delivery_order_done($db, $final_data ,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('delivery_order_final/delivery_order_final_engine');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $fdelivery_order = array_merge($final_data['delivery_order'],array("modid"=>$modid,"moddate"=>$moddate));


        $delivery_order = array();
        $q = '
            select t1.*
            from delivery_order t1
            where t1.id = '.$db->escape($id).'
        ';
        $delivery_order = $db->query_array($q)[0];

        $delivery_order_status_old = $delivery_order['delivery_order_status'];
        $delivery_order_type = $delivery_order['delivery_order_type'];

        $warehouse_from = array();
        $q = '
            select t3.id warehouse_id, t3.name warehouse_name 
            from delivery_order_warehouse_from t2 
                inner join warehouse t3 on t3.id = t2.warehouse_id
            where t2.delivery_order_id = '.$db->escape($delivery_order['id']).'
        ';
        $warehouse_from = $db->query_array($q)[0];


        $delivery_order_product = array();
        $q = '
            select *
            from delivery_order_product
            where delivery_order_id = '.$db->escape($id).'
        ';
        $delivery_order_product = $db->query_array($q);

        if(!$db->update('delivery_order',$fdelivery_order,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'delivery_order',
                $id,$fdelivery_order['delivery_order_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        if($success === 1 && $delivery_order_status_old !== 'done'){
            //<editor-fold defaultstate="collapsed">
            
            //<editor-fold defaultstate="collapsed" desc="Extra Action to reference module">
            switch($delivery_order_type){
                case 'sales_invoice':
                    //<editor-fold defaultstate="collapsed">
                    $delivery_order_final_id='';
                    $rs_temp = $db->fast_get('delivery_order_final_delivery_order',array('delivery_order_id'=>$id));
                    if (count($rs_temp)>0){
                        $delivery_order_final_id = $rs_temp[0]['delivery_order_final_id'];

                        $q = '
                            select t1.delivery_order_status
                            from delivery_order t1
                                inner join delivery_order_final_delivery_order t2
                                    on t1.id = t2.delivery_order_id
                            where t2.delivery_order_final_id = '.$db->escape($delivery_order_final_id).'
                        ';
                        $rs_do = $db->query_array($q);

                        if(count($rs_do)>0){
                            $all_done = true;
                            foreach($rs_do as $do_idx=>$do){
                                if($do['delivery_order_status'] !== 'done') $all_done = false;
                            }
                            if($all_done){
                                $temp_delivery_order_final = array(
                                    'delivery_order_final'=>array(
                                        'delivery_order_final_status'=>'done',
                                    ),
                                );
                                $temp_result = eval('return Delivery_Order_Final_Engine::'
                                    .'delivery_order_final_done($db,'
                                    .'$temp_delivery_order_final,'
                                    .'$delivery_order_final_id'
                                    .');'
                                .'');

                                $success = $temp_result['success'];
                                $msg = array_merge($msg, $temp_result['msg']);
                            }
                        }
                    }
                    //</editor-fold>
                    break;
                
            }
            //</editor-fold>
            
            //<editor-fold defaultstate="collapsed" desc="Cut down Product Stock">
            if($success == 1){
                foreach($delivery_order_product as $product){
                    $product_id = $product['product_id'];
                    $unit_id = $product['unit_id'];
                    $qty = -1* $product['qty'];
                    $product_type = $product['product_type'];
                    $warehouse_id = $warehouse_from['warehouse_id'];
                    $description = 'Delivery Order: '.$delivery_order['code'].' '.SI::status_get('Delivery_Order_Engine',
                            $fdelivery_order['delivery_order_status'])['label'];
                    get_instance()->load->helper('product_stock_engine');
                    $stock_result = array('success'=>1,'msg'=>array());
                    switch($product_type){
                        case 'registered_product':
                        $stock_result = Product_Stock_Engine::stock_good_only_add(
                                $db,
                                $warehouse_id,
                                $product_id,
                                $qty,
                                $unit_id,
                                $description,
                                $moddate
                            );
                            break;
                    }
                    if($stock_result['success'] == 0){
                        $success = 0;
                        $msg[]=$stock_result['msg'];   
                        $db->trans_rollback();
                        break;
                    } 
                }
            }
            //</editor-fold>
            
            //</editor-fold>
        }

        


        $result['trans_id']=$id;

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function delivery_order_canceled($db, $final_data ,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product_stock_engine');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $delivery_order_id = $id;
        $fdelivery_order = array_merge($final_data['delivery_order'],array("modid"=>$modid,"moddate"=>$moddate));

        $delivery_order = array();
        $q = '
            select t1.*
            from delivery_order t1
            where t1.id = '.$db->escape($id).'
        ';
        $delivery_order = $db->query_array($q)[0];

        $delivery_order_status_old = $delivery_order['delivery_order_status'];
        $delivery_order_type = $delivery_order['delivery_order_type'];

        $warehouse_from = array();
        $q = '
            select t3.id warehouse_id, t3.name warehouse_name 
            from delivery_order_warehouse_from t2 
                inner join warehouse t3 on t3.id = t2.warehouse_id
            where t2.delivery_order_id = '.$db->escape($delivery_order['id']).'
        ';
        $warehouse_from = $db->query_array($q)[0];


        $delivery_order_product = array();
        $q = '
            select *
            from delivery_order_product
            where delivery_order_id = '.$db->escape($id).'
        ';
        $delivery_order_product = $db->query_array($q);

        if(!$db->update('delivery_order',$fdelivery_order,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'delivery_order',
                $id,$fdelivery_order['delivery_order_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        if($success === 1){
            if($delivery_order_type === 'sales_invoice'){
                get_instance()->load->helper('sales_pos/sales_pos_engine');
            }
            else if ($delivery_order_type === 'refill_subcon_work_order'){
                get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_engine');
                get_instance()->load->helper('refill_work_order/refill_work_order_engine');
            }
        }

        if($success === 1){
            foreach($delivery_order_product as $product){
                $product_reference_id = $product['reference_id'];
                $product_type = $product['product_type'];
                $product_id = $product['product_id'];
                $unit_id = $product['unit_id'];
                $qty = $product['qty'];
                $warehouse_from_id = $warehouse_from['warehouse_id'];
                switch($delivery_order_type){
                    case 'sales_invoice':
                        //<editor-fold defaultstate="collapsed">
                        $q = '
                            select t1.sales_invoice_id
                            from sales_invoice_delivery_order_final t1
                                inner join delivery_order_final_delivery_order t2 
                                    on t1.delivery_order_final_id = t2.delivery_order_final_id
                            where t2.delivery_order_id = '.$db->escape($delivery_order_id).'
                        ';
                        $sales_invoice_id = $db->query_array($q)[0]['sales_invoice_id'];
                        $temp_si_product = array(
                            'reference_id'=>$product_reference_id,
                            'qty'=>Tools::_float($qty)
                        );
                        $temp_result = Sales_Pos_Engine::movement_outstanding_qty_add($db, $sales_invoice_id,$temp_si_product);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);

                        
                        //</editor-fold>
                        break;
                        
                    case 'refill_subcon_work_order':
                        //<editor-fold defaultstate="collapsed">
                        $q = '
                            select rswo_do.refill_subcon_work_order_id
                            from rswo_do 
                            where rswo_do.delivery_order_id = '.$db->escape($delivery_order_id).'
                        ';
                        $rswo_id = $db->query_array($q)[0]['refill_subcon_work_order_id'];
                        $qty = Tools::_float($qty);

                        $temp_result = Refill_Subcon_Work_Order_Engine::movement_outstanding_qty_add(
                            $db
                            ,'product'
                            ,$rswo_id
                            ,$product_reference_id
                            ,$qty
                        );

                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);
                        //</editor-fold>
                        break;
                        
                }

                if($success === 1){
                    //<editor-fold desc="warehouse stock adjustment" defaultstate="collapsed">
                    switch($product_type){
                        case 'registered_product':
                            //<editor-fold defaultstate="collapsed">
                            if($delivery_order_status_old === 'done'){

                                $description = 'Delivery Order: '.$delivery_order['code'].' '.
                                    SI::status_get('Delivery_Order_Engine',
                                        $fdelivery_order['delivery_order_status']
                                    )['label'];

                                $temp_result = Product_Stock_Engine::stock_good_add(
                                    $db,
                                    $warehouse_from_id,
                                    $product_id,
                                    $qty,
                                    $unit_id,
                                    $description,
                                    $moddate
                                );
                                $success = $temp_result['success'];
                                $msg = array_merge($msg, $temp_result['msg']);
                                
                            }
                            else if($delivery_order_status_old !== 'done'){
                                $description = 'Delivery Order: '.$delivery_order['code'].' '.
                                    SI::status_get('Delivery_Order_Engine',
                                        $fdelivery_order['delivery_order_status']
                                    )['label'];

                                $temp_result = Product_Stock_Engine::stock_sales_available_only_add(
                                    $db,
                                    $warehouse_from_id,
                                    $product_id,
                                    $qty,
                                    $unit_id,
                                    $description,
                                    $moddate
                                );

                                $success = $temp_result['success'];
                                $msg = array_merge($msg, $temp_result['msg']);
                                
                            }
                            //</editor-fold>
                            break;
                            
                        case'refill_work_order_product':
                            //<editor-fold defaultstate="collapsed">
                            $temp_result = Refill_Work_Order_Engine::product_stock_add(
                                $db,
                                $product_id,
                                Tools::_float($qty)
                            );
                            $success = $temp_result['success'];
                            $msg = array_merge($msg,$temp_result['msg']);
                            //</editor-fold>
                            break;
                    
                        
                    }
                    //</editor-fold>
                }

                if($success !== 1) break;
            }            
        }






        $result['trans_id']=$id;

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }
}
?>
