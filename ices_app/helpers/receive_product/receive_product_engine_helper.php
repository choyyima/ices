<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Receive_Product_Engine {
    public static $prefix_id = 'receive_product';
    public static $prefix_method = '';
    public static $module_type_list;
    public static $status_list;
    
    function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$prefix_method = self::$prefix_id;
        self::$module_type_list = array(
            array('val'=>'purchase_invoice','label'=>'Purchase Invoice'),
            array('val'=>'refill_subcon_work_order','label'=>'Refill - '.Lang::get('Subcon Work Order')),
        );
        
        self::$status_list = array(
            //<editor-fold defaultstate="collapsed">
            array(
                'val'=>''
                ,'label'=>''
                ,'method'=>self::$prefix_method.'_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add'),array('val'=>'Receive Product'),array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'process'
                ,'label'=>'PROCESS'
                ,'method'=>self::$prefix_method.'_process'
                ,'next_allowed_status'=>array('done','X')
                ,'default'=>true
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update'),array('val'=>'Receive Product'),array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'done'
                ,'label'=>'DONE'
                ,'method'=>self::$prefix_method.'_done'
                ,'next_allowed_status'=>array('X')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update'),array('val'=>'Receive Product'),array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'X'
                ,'label'=>'CANCELED'
                ,'method'=>self::$prefix_method.'_canceled'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Cancel'),array('val'=>'Receive Product'),array('val'=>'success')
                    )
                )
            )
            //</editor-fold>
        );
        //</editor-fold>
    }
    
    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'receive_product/'
            ,'receive_product_engine'=>'receive_product/receive_product_engine'
            ,'receive_product_renderer' => 'receive_product/receive_product_renderer'                
            ,'ajax_search'=>get_instance()->config->base_url().'receive_product/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'receive_product/data_support/'                
        );

        return json_decode(json_encode($path));
    }

    public static function receive_product_exists($id){
        $result = false;
        $db = new DB();
        $q = '
            select 1 
            from receive_product 
            where status > 0 && id = '.$db->escape($id).'
        ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
    }


    public static function validate($method,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('receive_product/receive_product_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()
        );
        $success = 1;
        $msg = array();
        
        $receive_product = isset($data['receive_product'])?$data['receive_product']:null;
        $receive_product_id = isset($receive_product['id'])?
            Tools::_str($receive_product['id']):'';
        $warehouse_to = isset($data['warehouse_to'])?Tools::_arr($data['warehouse_to']):null;
        $warehouse_from = isset($data['warehouse_from'])?Tools::_arr($data['warehouse_from']):null;
        $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):array();
        $product = isset($data['product'])? Tools::_arr($data['product']):array();

        $db = new DB();
        switch($method){
            case 'receive_product_add':
                //<editor-fold defaultstate="collapsed">
                $warehouse_from_id = isset($warehouse_from['warehouse_id'])?
                    $warehouse_from['warehouse_id']:'';
                $warehouse_to_id = isset($warehouse_to['warehouse_id'])?
                    $warehouse_to['warehouse_id']:'';
                $receive_product_type = isset($receive_product['receive_product_type'])?
                    Tools::_str($receive_product['receive_product_type']):'';
                $receive_product_date = isset($receive_product['receive_product_date'])?
                    Tools::_date($receive_product['receive_product_date']):'';
                $reference_product_list = Receive_Product_Data_Support::reference_product_list_get($receive_product_type, $reference_id);
                //<editor-fold defaultstate="collapsed" desc="major validation">
                
                
                if(!SI::type_match('Receive_Product_Engine',$receive_product_type)){
                    $success = 0;
                    $msg[]='Mismatch Module Type';
                    break;
                }

                $store_id = isset($receive_product['store_id'])?$receive_product['store_id']:'';
                $q = 'select 1 from store where status>0 and id ='.$db->escape($store_id);
                if(! count($db->query_array_obj($q))> 0){
                    $success = 0;
                    $msg[] = Lang::get("Store").' '.Lang::get("Empty",true,false);
                }                   

                $ref_exists = false;
                switch($receive_product_type){
                    case 'purchase_invoice':
                        $q = '
                            select 1
                            from purchase_invoice pi
                            where pi.id = '.$db->escape($reference_id).'
                                and pi.purchase_invoice_status = "invoiced"
                                and pi.status > 0 
                        ';
                        if(count($db->query_array($q))> 0){
                            $ref_exists = true;
                        }
                        break;
                    case 'refill_subcon_work_order':
                        $q = '
                            select 1
                            from refill_subcon_work_order rswo
                            where rswo.id = '.$db->escape($reference_id).'
                                and rswo.refill_subcon_work_order_status = "done"
                                and rswo.status > 0 
                        ';
                        if(count($db->query_array($q))>0){
                            $ref_exists = true;
                        }
                        break;

                }
                
                if(!$ref_exists){
                    $success = 0;
                    $msg[] = 'Reference'.' '.Lang::get('Empty',true,false);                    
                }
                
                switch($receive_product_type){
                    case 'purchase_invoice':
                        if(!count($db->fast_get('warehouse',array('id'=>$warehouse_from_id,'code'=>'WS')))>0){
                            $success = 0;
                            $msg[] = Lang::get("Warehouse From").' '.Lang::get("Empty",true,false);
                        }
                        break;
                }
                
                if(!Warehouse_Engine::is_type('BOS',$warehouse_to_id)){
                    $success = 0;
                    $msg[] = Lang::get("Warehouse To").' '.Lang::get("Empty",true,false);
                }
                
                if(!count($product)>0){
                    $success = 0;
                    $msg[] = Lang::get('Product ').Lang::get('empty');
                }
                
                if($success === 0) break;
                //</editor-fold>
                     
                $reference_date = Tools::_date('');
                switch($receive_product_type){
                    case 'purchase_invoice':
                        get_instance()->load->helper('purchase_invoice/purchase_invoice_data_support');
                        $purchase_invoice = Purchase_Invoice_Data_Support::purchase_invoice_get($reference_id);
                        $reference_date = $purchase_invoice['purchase_invoice_date'];
                        break;
                }
                if(strtotime($receive_product_date) <= strtotime($reference_date)){
                    $success = 0;
                    $msg[] = Lang::get('Receive Product Date').' '.Lang::get('invalid');
                }
                
                
                //<editor-fold defaultstate="collapsed" desc="Product Validation">
                foreach($product as $idx=>$row){
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
                        $rf_max_available_qty = isset($row2['max_available_qty'])?
                            Tools::_str($row2['max_available_qty']):'';
                        
                        if($rf_product_type === $product_type
                            && $rf_product_reference_type === $product_reference_type
                            && $rf_product_reference_id === $product_reference_id
                            && $rf_product_id === $product_id
                            && $rf_unit_id === $unit_id
                            && Tools::_float($qty)<= Tools::_float($rf_max_available_qty)
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
                
                //</editor-fold>
                break;
            case 'receive_product_process':
            case 'receive_product_done':
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'receive_product',
                        'module_name'=>'Receive Product',
                        'module_engine'=>'Receive_Product_Engine',
                    ),
                    $receive_product
                );
                $success = $temp_result['success'];
                $msg = array_merge($msg,$temp_result['msg']);

                if($success!==1) break;


                break;
            case 'receive_product_canceled':
                $temp_result = Validator::validate_on_cancel(
                    array(
                        'module'=>'receive_product',
                        'module_name'=>'Receive Product',
                        'module_engine'=>'Receive_Product_Engine',
                    ),
                    $receive_product
                );
                $success = $temp_result['success'];
                $msg = array_merge($msg,$temp_result['msg']);
                if($success !== 1) break;

                
                break;
            default:
                $success = 0;
                $msg[] = 'Unknown Validation Method';
                break;

        }
        $result['msg'] = $msg;
        $result['success'] = $success;
        return $result;
        //</editor-fold>
    }

    public static function adjust($action,$data=array()){
        //<editor-fold defaultstate="collapsed">

        $db = new DB();
        $result = array();
        
        $modid = User_Info::get()['user_id'];
        $moddate = Tools::_date('');
        switch($action){
            case 'receive_product_add':
                //<editor-fold defaultstate="collapsed">
                $receive_product_data = $data['receive_product'];
                $reference_id = $data['reference_id'];
                $warehouse_to_data  = $data['warehouse_to'];
                $warehouse_from_data  = $data['warehouse_from'];
                $product_data = $data['product'];
                
                $receive_product_type = $receive_product_data['receive_product_type'];
                
                $result['receive_product'] = array(
                    'code'=>''
                    ,'store_id'=>$receive_product_data['store_id']
                    ,'receive_product_date'=>Tools::_date($receive_product_data['receive_product_date'],'Y-m-d H:i:s')
                    ,'receive_product_type'=>$receive_product_data['receive_product_type']
                    ,'receive_product_status'=>SI::status_default_status_get('Receive_Product_Engine')['val']
                    ,'notes'=>isset($receive_product_data['notes'])?Tools::empty_to_null(Tools::_str($receive_product_data['notes'])):null
                    ,'modid'=>$modid
                    ,'moddate'=>$moddate
                );
                
                $result['receive_product_product'] = array();
                foreach($product_data as $idx=>$product){
                    $result['receive_product_product'][] = array(
                        'reference_type'=>$product['reference_type'],
                        'reference_id'=>$product['reference_id'],
                        'product_type'=>$product['product_type'],
                        'product_id'=>$product['product_id'],
                        'unit_id'=>$product['unit_id'],
                        'qty'=>$product['qty'],
                    );
                    
                    
                }
                
                $result['receive_product_warehouse_from'] = array(
                    'warehouse_id'=>$warehouse_from_data['warehouse_id'],
                );

                $result['receive_product_warehouse_to'] = array(
                    'warehouse_id'=>$warehouse_to_data['warehouse_id'],
                );

                switch($receive_product_type){
                    case'purchase_invoice':
                        $result['purchase_invoice_receive_product'] = array(
                            'purchase_invoice_id'=>$reference_id
                        );
                        break;
                    case 'refill_subcon_work_order':
                        $result['rswo_rp'] = array(
                            'refill_subcon_work_order_id' => $reference_id
                        );
                        break;
                }
                //</editor-fold>
                break;

            case 'receive_product_done':
            case 'receive_product_process':
                //<editor-fold defaultstate="collapsed">
                $receive_product = $data['receive_product']; 
                $receive_product_status = '';

                switch($action){
                    case 'receive_product_done':
                        $receive_product_status='done';
                        break;
                    case 'receive_product_process':
                        $receive_product_status='process';
                        break;
                }
                $result['receive_product'] = array(
                    'notes'=>isset($receive_product_data['notes'])?Tools::empty_to_null(Tools::_str($receive_product_data['notes'])):null
                    ,'receive_product_status'=>$receive_product_status
                );
                //</editor-fold>
                break;
            case 'receive_product_canceled':
                //<editor-fold defaultstate="collapsed">
                $receive_product = $data['receive_product'];

                $result['receive_product'] = array(
                    'cancellation_reason'=>isset($receive_product['cancellation_reason'])?$receive_product['cancellation_reason']:''
                    ,'receive_product_status'=>'X'
                    ,'notes'=>isset($receive_product_data['notes'])?Tools::empty_to_null(Tools::_str($receive_product_data['notes'])):null
                );
                //</editor-fold>
                break;
        }

        return $result;
        //</editor-fold>
    }

    function receive_product_add($db, $final_data){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $freceive_product = $final_data['receive_product'];
        $freceive_product_product = $final_data['receive_product_product'];
        $freceive_product_warehouse_from = $final_data['receive_product_warehouse_from'];
        $freceive_product_warehouse_to = $final_data['receive_product_warehouse_to'];
        $fpurchase_invoice_receive_product = isset($final_data['purchase_invoice_receive_product'])?
            Tools::_arr($final_data['purchase_invoice_receive_product']):array();
        $frswo_rp = isset($final_data['rswo_rp'])?
            Tools::_arr($final_data['rswo_rp']):array();
        
        $store_id = $freceive_product['store_id'];
        $modid = User_Info::get()['user_id'];
        $moddate = Tools::_date('');
        $receive_product_type = $freceive_product['receive_product_type'];
        $receive_product_id = '';
        $reference_id = '';
        
        switch($receive_product_type){
            case 'purchase_invoice':
                $reference_id = $final_data['purchase_invoice_receive_product']['purchase_invoice_id'];
                get_instance()->load->helper('purchase_invoice/purchase_invoice_engine');
                break;
            case 'refill_subcon_work_order':
                $reference_id = $final_data['rswo_rp']['refill_subcon_work_order_id'];
                get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_engine');
                break;
        }
        
        $freceive_product['code'] = SI::code_counter_store_get($db,$store_id, 'receive_product');
        if(!$db->insert('receive_product',$freceive_product)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $receive_product_code = $freceive_product['code'];

        if($success == 1){                                
            $receive_product_id = $db->fast_get('receive_product'
                    ,array('code'=>$receive_product_code))[0]['id'];
            $result['trans_id']=$receive_product_id; 
        }
        
        if($success === 1){
            foreach($freceive_product_product as $idx=>$product){
                $product['receive_product_id'] = $receive_product_id;
                if(!$db->insert('receive_product_product',$product)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
                $temp_result = array('success'=>1,'msg'=>array());
                switch($receive_product_type){
                    case 'purchase_invoice':
                        $temp_product = array(
                            'reference_id'=>$product['reference_id'],
                            'qty'=>-1*$product['qty'],
                        );
                        $temp_result = Purchase_Invoice_Engine::movement_outstanding_qty_add(
                                $db, 
                                $reference_id, 
                                $temp_product
                        );
                        break;
                    case 'refill_subcon_work_order':
                        $rswo_id = $frswo_rp['refill_subcon_work_order_id'];
                        $product_reference_id = $product['reference_id'];
                        $product_type = $product['product_type'];
                        $product_id = $product['product_id'];
                        $unit_id = $product['unit_id'];
                        $qty = -1*Tools::_float($product['qty']);

                        $temp_result = Refill_Subcon_Work_Order_Engine::movement_outstanding_qty_add(
                            $db
                            ,'expected_product_result'
                            ,$rswo_id
                            ,$product_reference_id
                            ,$qty
                        );
                        break;
                        
                }
                $success = $temp_result['success'];
                $msg = array_merge($msg,$temp_result['msg']);
                if($success !== 1) break;
            }
        }
        
        if($success === 1){
            $freceive_product_warehouse_from['receive_product_id'] = $receive_product_id;
            if(!$db->insert('receive_product_warehouse_from',$freceive_product_warehouse_from)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        if($success === 1){
            $freceive_product_warehouse_to['receive_product_id'] = $receive_product_id;
            if(!$db->insert('receive_product_warehouse_to',$freceive_product_warehouse_to)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        if($success == 1){
            $temp_res = SI::status_log_add($db,
                'receive_product',
                $receive_product_id,
                $freceive_product['receive_product_status']
            );

            $success = $temp_res['success'];
            if($success !== 1){
                $msg = array_merge($msg, $temp_res['msg']);
            }                
        }

        if($success === 1){
            switch($receive_product_type){
                case 'purchase_invoice':                    
                    $fpurchase_invoice_receive_product['receive_product_id']=$receive_product_id;
                    if(!$db->insert('purchase_invoice_receive_product'
                        ,$fpurchase_invoice_receive_product)){
                        $msg[] = $db->_error_message();
                        $db->trans_rollback();                                
                        $success = 0;
                    }
                    break;
                case 'refill_subcon_work_order':
                    $frswo_rp['receive_product_id']=$receive_product_id;

                    if(!$db->insert('rswo_rp'
                        ,$frswo_rp)){
                        $msg[] = $db->_error_message();
                        $db->trans_rollback();                                
                        $success = 0;
                    }
                    break;
            }
        }


        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function receive_product_process($db, $final_data ,$id){
        //<editor-fold defaultstate="collapsed" >
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $freceive_product = array_merge($final_data['receive_product'],array("modid"=>$modid,"moddate"=>$moddate));


        $receive_product = array();
        $q = '
            select t1.*
            from receive_product t1
            where t1.id = '.$db->escape($id).'
        ';
        $receive_product = $db->query_array($q)[0];

        if(!$db->update('receive_product',$freceive_product,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'receive_product',
                $id,$freceive_product['receive_product_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }


        $result['trans_id']=$id;

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }
    
    function receive_product_done($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('receive_product/receive_product_data_support');
        get_instance()->load->helper('product_stock_engine');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $freceive_product = $final_data['receive_product'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $receive_product_id = $id;
        
        $receive_product_product = Receive_Product_Data_Support::receive_product_product_get($receive_product_id);
        $receive_product = Receive_Product_Data_Support::receive_product_get($receive_product_id);
        $warehouse_from = Receive_Product_Data_Support::warehouse_from_get($receive_product_id);
        $warehouse_to = Receive_Product_Data_Support::warehouse_to_get($receive_product_id);
        
        $receive_product_status_old = $receive_product['receive_product_status'];
        $receive_product_type = $receive_product['receive_product_type'];
        
        if($success === 1){
            if($receive_product_type === 'purchase_invoice'){
                get_instance()->load->helper('purchase_invoice/purchase_invoice_engine');
            }
            else if($receive_product_type==='refill_subcon_work_order'){
                get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_engine');
                get_instance()->load->helper('refill_work_order/refill_work_order_engine');
            }
        }
        
        if(!$db->update('receive_product',$freceive_product,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'receive_product',
                $id,$freceive_product['receive_product_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        
        
        
        if($success === 1 && $receive_product_status_old !=='done'){

            foreach($receive_product_product as $idx=>$product){

                $product_id = $product['product_id'];
                $unit_id = $product['unit_id'];
                $qty = Tools::_float($product['qty']);
                $product_type = $product['product_type'];
                $warehouse_id = $warehouse_to['id'];
                $description = 'Receive Product: '.$receive_product['code'].' '.SI::status_get('Receive_Product_Engine',
                        $freceive_product['receive_product_status'])['label'];

                switch($product_type){
                    case 'registered_product':
                        $stock_result = Product_Stock_Engine::stock_good_add(
                            $db,
                            $warehouse_id,
                            $product_id,
                            $qty,
                            $unit_id,
                            $description,
                            $moddate
                        );

                        $success = $stock_result['success'];
                        $msg=array_merge($msg,$stock_result['msg']);   

                        break;
                    case 'refill_work_order_product':
                        $temp_result = Refill_Work_Order_Engine::product_stock_add(
                            $db,
                            $product_id,
                            $qty
                        );
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);

                        if($success === 1){
                            $temp_result = Refill_Work_Order_Engine::product_status_set($db, $product_id, 'waiting_for_confirmation');
                            $success = $temp_result['success'];
                            $msg = array_merge($msg, $temp_result['msg']);
                        }

                    break;
                }


                if($success!== 1)    break;


            }
        }
        

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function receive_product_canceled($db, $final_data,$receive_product_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('receive_product/receive_product_data_support');
        get_instance()->load->helper('product_stock_engine');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$receive_product_id);
        $success = 1;
        $msg = array();

        $freceive_product = $final_data['receive_product'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $receive_product_id = $receive_product_id;
        
        $receive_product_product = Receive_Product_Data_Support::receive_product_product_get($receive_product_id);
        $receive_product = Receive_Product_Data_Support::receive_product_get($receive_product_id);
        $warehouse_from = Receive_Product_Data_Support::warehouse_from_get($receive_product_id);
        $warehouse_to = Receive_Product_Data_Support::warehouse_to_get($receive_product_id);
        
        $receive_product_status_old = $receive_product['receive_product_status'];
        $receive_product_type = $receive_product['receive_product_type'];
        
        if($success === 1){
            if($receive_product_type === 'purchase_invoice'){
                get_instance()->load->helper('purchase_invoice/purchase_invoice_engine');
            }
            else if($receive_product_type==='refill_subcon_work_order'){
                get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_engine');
                get_instance()->load->helper('refill_work_order/refill_work_order_engine');
            }
        }
        
        if(!$db->update('receive_product',$freceive_product,array("id"=>$receive_product_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'receive_product',
                $receive_product_id,$freceive_product['receive_product_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        if($success === 1 && $receive_product_status_old ==='done'){
            foreach($receive_product_product as $idx=>$product){
                $product_id = $product['product_id'];
                $unit_id = $product['unit_id'];
                $qty = $product['qty'];
                $warehouse_id = $warehouse_to['id'];
                $product_type = $product['product_type'];
                                
                if($success === 1){
                    switch($product_type){
                        case 'registered_product':
                            $description = 'Receive Product: '.$receive_product['code'].' '.SI::status_get('Receive_Product_Engine',
                            $freceive_product['receive_product_status'])['label'];                    

                            $stock_result = Product_Stock_Engine::stock_good_add(
                                    $db,
                                    $warehouse_id,
                                    $product_id,
                                    -1 * Tools::_float($qty),
                                    $unit_id,
                                    $description,
                                    $moddate
                                );

                            $success = $stock_result['success'];
                            $msg=array_merge($msg,$stock_result['msg']);   
                            break;
                        
                        case 'refill_work_order_product':
                            $temp_result = Refill_Work_Order_Engine::product_stock_add(
                                $db,
                                $product_id,
                                -1 * Tools::_float($qty)
                            );
                            $success = $temp_result['success'];
                            $msg = array_merge($msg,$temp_result['msg']);
                            
                            if($success === 1){
                                $temp_result = Refill_Work_Order_Engine::product_status_set($db, $product_id, 'process');
                                $success = $temp_result['success'];
                                $msg = array_merge($msg, $temp_result['msg']);
                            }
                            
                            break;
                    }
                }
                
                if($success!== 1) break;
                

            }
            
        }
        
        if($success === 1 && $receive_product_status_old !== 'X'){
            //<editor-fold defaultstate="collapsed">
            foreach($receive_product_product as $idx=>$product){  
                $product_reference_id = $product['reference_id'];
                $product_id = $product['product_id'];
                $unit_id = $product['unit_id'];
                $qty = $product['qty'];
                $warehouse_id = $warehouse_to['id'];
                $product_type = $product['product_type'];
                
                switch($receive_product_type){
                    case 'purchase_invoice':
                        //<editor-fold defaultstate="collapsed">
                        get_instance()->load->helper('purchase_invoice/purchase_invoice_engine');
                        $q = '
                            select t1.purchase_invoice_id
                            from purchase_invoice_receive_product t1
                            where t1.receive_product_id = '.$db->escape($receive_product_id).'
                        ';
                        $purchase_invoice_id = $db->query_array($q)[0]['purchase_invoice_id'];
                        $temp_product = array(
                            'reference_id'=>$product_reference_id,
                            'qty'=>$qty,
                        );
                        $temp_result = Purchase_Invoice_Engine::movement_outstanding_qty_add(
                                $db, 
                                $purchase_invoice_id, 
                                $temp_product
                        );
                        $success = $temp_result['success'];
                        $msg = array_merge($msg, $temp_result['msg']);
                        
                        //</editor-fold>
                        break;
                    case 'refill_subcon_work_order':
                        //<editor-fold defaultstate="collapsed">
                        $q = '
                            select rswo_rp.refill_subcon_work_order_id
                            from rswo_rp 
                            where rswo_rp.receive_product_id = '.$db->escape($receive_product_id).'
                        ';
                        $rswo_id = $db->query_array($q)[0]['refill_subcon_work_order_id'];
                        $qty = Tools::_float($qty);

                        $temp_result = Refill_Subcon_Work_Order_Engine::movement_outstanding_qty_add(
                            $db
                            ,'expected_product_result'
                            ,$rswo_id
                            ,$product_reference_id
                            ,$qty
                        );

                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);
                        //</editor-fold>
                        break;
                }
                                
                if($success !== 1) break;
                
            }
            //</editor-fold>
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }
}
?>