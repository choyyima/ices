<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Invoice_Engine {
    public static $prefix_id = 'refill_invoice';
    public static $prefix_method;
    public static $status_list;
    public static $module_type_list;
    public static $stock_location_list;

    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$prefix_method = self::$prefix_id;
        
        self::$status_list = array(
            //<editor-fold defaultstate="collapsed">
            array(
                'val'=>''
                ,'label'=>''
                ,'method'=>'refill_invoice_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('Manufacturing - Work Process'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(//label name is used for method name
                'val'=>'invoiced'
                ,'label'=>'INVOICED'
                ,'method'=>'refill_invoice_invoiced'
                ,'next_allowed_status'=>array('X')
                ,'default'=>true
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('Manufacturing - Work Process'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            )
            ,array(
                'val'=>'X'
                ,'label'=>'CANCELED'
                ,'method'=>''
                ,'user_select_next_allowed_status'=>'false'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Cancel')
                        ,array('val'=>Lang::get(array('Manufacturing - Work Process'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ) 
            //</editor-fold>
        );
       
        self::$module_type_list = array(
            //<editor-fold defaultstate="collapsed">
            array(
                'val'=>'refill_work_order','label'=>'Refill - '.Lang::get('Work Order'),
            ),
            
            //</editor-fold>
        );
        
        
        //</editor-fold>
    }
    
    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'refill_invoice/'
            ,'refill_invoice_engine'=>'refill_invoice/refill_invoice_engine'
            ,'refill_invoice_data_support'=>'refill_invoice/refill_invoice_data_support'
            ,'refill_invoice_renderer' => 'refill_invoice/refill_invoice_renderer'
            ,'refill_invoice_print' => 'refill_invoice/refill_invoice_print'
            ,'ajax_search'=>get_instance()->config->base_url().'refill_invoice/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'refill_invoice/data_support/'

        );

        return json_decode(json_encode($path));
    }

    public static function delete_all_related_table_records($db, $refill_invoice_id){
        //<editor-fold defaultstate="collapsed">
        $success = 1;
        $msg = array();
        $result = array('success'=>$success,'msg' => $msg);
        
        $q = 'delete from refill_invoice_component_product where refill_invoice_id = '.$db->escape($refill_invoice_id);
        if(!$db->query($q)){
            $success = 0;
            $msg[] = $db->_error_message();
            $db->trans_rollback();
        }
        
        if($success === 1){
            $q = 'delete from refill_invoice_result_product where refill_invoice_id = '.$db->escape($refill_invoice_id);
            if(!$db->query($q)){
                $success = 0;
                $msg[] = $db->_error_message();
                $db->trans_rollback();
            }
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    

    public static function validate($action,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_invoice/refill_invoice_data_support');
        get_instance()->load->helper('product/product_data_support');
        
        $result = array(
            "success"=>1
            ,"msg"=>array()
        );
        $success = 1;
        $msg = array();
        
        $refill_invoice = isset($data['refill_invoice'])?Tools::_arr($data['refill_invoice']):null;
        $refill_invoice_type = isset($refill_invoice['refill_invoice_type'])?Tools::_str($refill_invoice['refill_invoice_type']):'';
        $refill_invoice_id = Tools::empty_to_null(isset($refill_invoice['id'])?Tools::_str($refill_invoice['id']):'');
        $refill_invoice_db = Refill_Invoice_Data_Support::refill_invoice_get($refill_invoice_id);
        $db = new DB();
        switch($action){
            case self::$prefix_method.'_add':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('mf_work_order/mf_work_order_data_support');
                $reference_id = isset($refill_invoice['reference_id'])?Tools::_str($refill_invoice['reference_id']):'';
                $store_id = isset($refill_invoice['store_id'])?Tools::_str($refill_invoice['store_id']):'';
                $reference_dependency = Refill_Invoice_Data_Support::reference_dependency_get($refill_invoice_type, $reference_id);
                $reference = Tools::_arr($reference_dependency['reference']);
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
                              
                if(!Store_Engine::store_exists($store_id)){
                    $success = 0;
                    $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                }
                
                if(!SI::type_match('Refill_Invoice_Engine',$refill_invoice_type)){
                    $success = 0;
                    $msg[] = Lang::get(array('Module Type','invalid'),true,true,false,false,true);
                }

                if(!count($reference)>0){
                    $success = 0;
                    $msg[] = 'Reference'.' '.Lang::get('invalid');
                }
                
                if($success !== 1) break;
                //</editor-fold>
                
                //<editor-fold defaultstate="collapsed" desc="Reference">
                switch($refill_invoice_type){
                    case 'refill_work_order':
                        if(!in_array($reference['refill_work_order_status'],array('done'))){
                            $success = 0;
                            $msg[] = 'Reference Status'.' '.Lang::get('invalid');
                        }
                        break;
                }
                //</editor-fold>
                
                
                break;
            case self::$prefix_method.'_invoiced':
                //<editor-fold defaultstate="collapsed">
                
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'refill_invoice',
                        'module_name'=>Lang::get('Refill Invoice'),
                        'module_engine'=>'refill_invoice_engine',
                    ),
                    $refill_invoice
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                
                
                //</editor-fold>
                break;
            case self::$prefix_method.'_canceled':
                //<editor-fold defaultstate="collapsed">
                $temp_result = Validator::validate_on_cancel(
                    array(
                        'module'=>'refill_invoice',
                        'module_name'=>Lang::get('Refill Invoice'),
                        'module_engine'=>'refill_invoice_engine',
                    ),
                    $refill_invoice
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                $success = 0;
                $msg[] = 'Cancel Refill Invoice invalid';
                //</editor-fold>
                break;
            default:
                $success = 0;
                $msg[] = 'Invalid Method';
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        
        return $result;
        //</editor-fold>
    }

    public static function adjust($method, $data=array()){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();

        $refill_invoice_data = isset($data['refill_invoice'])?$data['refill_invoice']:array();
        $reference_dependency = Refill_Invoice_Data_Support::reference_dependency_get($refill_invoice_data['refill_invoice_type'], $refill_invoice_data['reference_id']);
        $reference_product = $reference_dependency['reference_product'];
        $reference = $reference_dependency['reference'];
        $refill_invoice_db = Refill_Invoice_Data_Support::refill_invoice_get($refill_invoice_data['id']);

        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        
        switch($method){
            case self::$prefix_method.'_add':
                //<editor-fold defaultstate="collapsed">
                $refill_invoice_type = Tools::_str($refill_invoice_data['refill_invoice_type']);
                
                $refill_invoice = array(
                    'store_id'=>  Tools::_str($refill_invoice_data['store_id']),
                    'refill_invoice_type' => Tools::_str($refill_invoice_data['refill_invoice_type']),
                    'customer_id'=>$reference['customer_id'],
                    'refill_invoice_date'=>Tools::_date('','Y-m-d H:i:s'),
                    'reference_id'=> Tools::_str($refill_invoice_data['reference_id']),
                    'notes' => Tools::empty_to_null(Tools::_str($refill_invoice_data['notes'])),
                    'refill_invoice_status'=>SI::status_default_status_get('refill_invoice_engine')['val'],
                    'grand_total_amount'=>'0',
                    'outstanding_amount'=>'0',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                    'status'=>'1'
                    
                );
                
                $ri_product = array();
                
                
                $grand_total_amount = Tools::_float('0');
                foreach($reference_product as $idx=>$row){
                    $product_recondition_cost = array();
                    $product_sparepart_cost = array();
                    foreach($row['product_recondition_cost'] as $idx2=>$row2){
                        $product_recondition_cost[] = array(
                            'reference_type'=>$row2['reference_type'],
                            'reference_id'=>$row2['reference_id'],
                            'product_recondition_name'=>$row2['product_recondition_name'],
                            'amount'=>$row2['amount'],
                        );
                    }
                    
                    foreach($row['product_sparepart_cost'] as $idx2=>$row2){
                        $product_sparepart_cost[] = array(
                            'reference_type'=>$row2['reference_type'],
                            'reference_id'=>$row2['reference_id'],
                            'product_type'=>$row2['product_type'],
                            'product_id'=>$row2['product_id'],
                            'unit_id'=>$row2['unit_id'],
                            'qty'=>$row2['qty'],
                            'amount'=>$row2['amount'],
                        );
                    }
                    
                    $t_product = array(
                        'product_id'=>$row['id'],
                        'unit_id'=>$row['unit_id'],
                        'qty'=>$row['qty'],
                        'movement_outstanding_qty'=>$row['qty'],
                        'amount'=>$row['amount'],
                        'subtotal'=>Tools::_float($row['amount']) * Tools::_float($row['qty']),
                        'product_recondition_cost'=>$product_recondition_cost,
                        'product_sparepart_cost'=>$product_sparepart_cost,
                    );
                    
                    $ri_product[] = $t_product;
                    
                    $grand_total_amount+= Tools::_float($t_product['amount']);
                }
                
                $refill_invoice['grand_total_amount'] = $grand_total_amount;
                $refill_invoice['outstanding_amount'] = $grand_total_amount;
                
                $result['refill_invoice'] = $refill_invoice;
                $result['ri_product'] = $ri_product;
                
                
                
                //</editor-fold>
                break;

            case self::$prefix_method.'_invoiced':
                //<editor-fold defaultstate="collapsed">
                $refill_invoice = array(
                    'notes' => isset($refill_invoice_data['notes'])?
                        Tools::empty_to_null(Tools::_str($refill_invoice_data['notes'])):null,
                    'refill_invoice_status'=>'done',                    
                );
                
                $result['refill_invoice'] = $refill_invoice;
                
                if($refill_invoice_db['refill_invoice_status'] === 'process'){
                    //<editor-fold defaultstate="collapsed">
                    
                    $ri_info = array(
                        'end_date'=>$datetime_curr,
                        'sir_exists'=>Tools::_bool($ri_info_data['sir_exists'])?'1':'0',
                    );
                    
                    $ri_checker = array(
                        'refill_invoice_id'=>$refill_invoice_db['id'],
                        'name'=>$ri_checker_data['name'],
                    );
                    
                    $ri_result_product = array();
                    foreach($ri_result_product_data as $i=>$row){
                        $ri_result_product[] = array(
                            'refill_invoice_id'=>$refill_invoice_data['id'],
                            'product_type'=>Tools::_str($row['product_type']),
                            'product_id'=>Tools::_str($row['product_id']),
                            'unit_id'=>Tools::_str($row['unit_id']),
                            'qty'=>Tools::_str($row['qty']),
                            'stock_location'=>Tools::_str($row['stock_location']),
                        );
                    }
                    
                    $ri_scrap_product = array();
                    foreach($ri_scrap_product_data as $i=>$row){
                        $ri_scrap_product[] = array(
                            'refill_invoice_id'=>$refill_invoice_data['id'],
                            'product_type'=>Tools::_str($row['product_type']),
                            'product_id'=>Tools::_str($row['product_id']),
                            'unit_id'=>Tools::_str($row['unit_id']),
                            'qty'=>Tools::_str($row['qty']),
                            'stock_location'=>Tools::_str($row['stock_location']),
                        );
                    }
                    
                    $sir = array();
                    if($ri_info['sir_exists'] === '1'){
                        get_instance()->load->helper('sir/sir_engine');
                        $sir = array(
                            'store_id'=>Tools::_str($refill_invoice_db['store_id']),
                            'reference_id'=>$refill_invoice_db['id'],
                            'creator'=>Tools::_str($sir_data['creator']),
                            'description'=>Tools::_str($sir_data['description']),
                            'module_name'=>'refill_invoice',
                            'module_action'=>'free_rules',
                            'sir_date'=>$datetime_curr,
                            'sir_status'=>SI::status_default_status_get('sir_engine')['val'],
                            'modid'=>$modid,
                            'moddate'=>$datetime_curr
                        );
                    }
                    
                    $result['ri_checker'] = $ri_checker;
                    $result['ri_info'] = $ri_info;
                    $result['ri_scrap_product'] = $ri_scrap_product;
                    $result['ri_result_product'] = $ri_result_product;
                    $result['sir'] = $sir;
                    //</editor-fold>
                }
                
                //</editor-fold>
                break;
            case self::$prefix_method.'_canceled':
                //<editor-fold defaultstate="collapsed">
                $refill_invoice = array();

                $refill_invoice = array(
                    'refill_invoice_status'=>'X',
                    'cancellation_reason'=>$refill_invoice_data['cancellation_reason'],
                    'notes'=>isset($refill_invoice_data['notes'])?
                        Tools::empty_to_null(Tools::_str($refill_invoice_data['notes'])):null,
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['refill_invoice'] = $refill_invoice;
                //</editor-fold>
                break;
                
        }        

        return $result;
        //</editor-fold>
    }

    public function refill_invoice_add($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product_stock_engine');
        get_instance()->load->helper('refill_work_order/refill_work_order_engine');
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        get_instance()->load->helper('customer/customer_engine');
        get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_engine');
        
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $frefill_invoice = $final_data['refill_invoice'];
        $fri_product = $final_data['ri_product'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $store_id = $frefill_invoice['store_id'];        
        $refill_invoice_type = $frefill_invoice['refill_invoice_type'];        
        $refill_invoice_id = '';
        $reference_id = $frefill_invoice['reference_id'];
        $grand_total_amount = $frefill_invoice['grand_total_amount'];
        
        $frefill_invoice['code'] = SI::code_counter_store_get($db,$store_id,'refill_invoice');
        
        if(!$db->insert('refill_invoice',$frefill_invoice)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        $refill_invoice_code = $frefill_invoice['code'];

        if($success == 1){
            $refill_invoice_id = $db->fast_get('refill_invoice'
                    ,array('code'=>$refill_invoice_code))[0]['id'];
            $result['trans_id']=$refill_invoice_id; 
        }
        
        if($success == 1){
            $refill_invoice_status_log = array(
                'refill_invoice_id'=>$refill_invoice_id
                ,'refill_invoice_status'=>$frefill_invoice['refill_invoice_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('refill_invoice_status_log',$refill_invoice_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
                
            }
        }
        
        //<editor-fold defaultstate="collapsed" desc="RI Product">
        if($success === 1){
            foreach($fri_product as $i=>$row){
                $row['refill_invoice_id'] = $refill_invoice_id;
                $product_recondition_cost = $row['product_recondition_cost'];
                $product_sparepart_cost = $row['product_sparepart_cost'];
                unset($row['product_recondition_cost']);
                unset($row['product_sparepart_cost']);
                if(!$db->insert('ri_product',$row)){
                    $success = 0;
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();
                }
                
                $ri_product_id = '';
                if($success === 1){                    
                    $q = '
                        select max(id) id
                        from ri_product
                        where ri_product.refill_invoice_id = '.$db->escape($refill_invoice_id).'
                    ';
                    $rs = $db->query_array($q);
                    if(!count($rs)>0){
                        $success = 0;
                        $msg[] = 'Unable to find Refill Invoice Product ID';
                        break;
                    }
                    else{
                        $ri_product_id = $rs[0]['id'];
                    }
                    
                }
                
                if($success === 1){
                    
                    foreach($product_recondition_cost as $i2=>$row2){
                        $row2['ri_product_id'] = $ri_product_id;
                        if(!$db->insert('ri_product_recondition_cost',$row2)){
                            $success = 0;
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();
                        }
                        if($success !== 1) break;
                    }
                }
                
                if($success === 1){
                    
                    foreach($product_sparepart_cost as $i2=>$row2){
                        $row2['ri_product_id'] = $ri_product_id;
                        if(!$db->insert('ri_product_sparepart_cost',$row2)){
                            $success = 0;
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();
                        }
                        if($success !== 1) break;
                    }
                }
                
                if($success !== 1) break;                
            }
        }
        
        //</editor-fold>
        
        if($success === 1 & $refill_invoice_type==='refill_work_order'){
            $temp_result = Refill_Work_Order_Engine::rwo_status_set($db, $reference_id, 'invoiced');
            $success = $temp_result['success'];
            $msg = array_merge($msg,$temp_result['msg']);
        }
        
        if($success === 1){
            $temp_result = Customer_Engine::customer_credit_add($db, $frefill_invoice['grand_total_amount'],
                $frefill_invoice['customer_id']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);

        }
        
        //<editor-fold defaultstate="collapsed" desc="CUSTOMER DEPOSIT">
        if($success === 1){
            switch($refill_invoice_type){
                case 'refill_work_order':
                    $customer_deposit_list = Refill_Work_Order_Data_Support::customer_deposit_get($reference_id,array('customer_deposit_status'=>'invoiced'));
                    $t_grand_total_amount = Tools::_float($grand_total_amount);
                    foreach($customer_deposit_list as $idx=>$row){
                        $outstanding_amount = Tools::_float($row['outstanding_amount']);
                        if(Tools::_float($outstanding_amount)>Tools::_float('0')
                        && Tools::_float($t_grand_total_amount)>Tools::_float('0')){
                            $allocated_amount = min(array(Tools::_float($grand_total_amount),Tools::_float($outstanding_amount)));
                            
                            $t_grand_total_amount = Tools::_float($t_grand_total_amount) - Tools::_float($allocated_amount);
                            $cda_param = array(
                                'store_id'=>$store_id,
                                'customer_deposit_allocation_type'=>'refill_invoice',
                                'customer_deposit_id'=>$row['id'],
                                'allocated_amount'=>$allocated_amount,
                                'customer_deposit_allocation_status'=>SI::type_default_type_get('Customer_Deposit_Allocation_Engine','$status_list')['val'],
                                'refill_invoice_id'=>$refill_invoice_id,
                                'modid'=>$modid,
                                'moddate'=>$moddate,
                            );
                            $temp_result = Customer_Deposit_Allocation_Engine::
                                customer_deposit_allocation_add(
                                    $db,array('customer_deposit_allocation'=>$cda_param)
                                );
                            $success = $temp_result['success'];
                            $msg = array_merge($msg, $temp_result['msg']);

                        }
                        
                        if($success !== 1) break;
                    }
                    break;
                
            }
        }
        //</editor-fold>
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    function refill_invoice_invoiced($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_invoice/refill_invoice_data_support');
        get_instance()->load->helper('product_stock_engine');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $frefill_invoice = $final_data['refill_invoice'];
        
        $refill_invoice_id = $id;
        $refill_invoice_db = Refill_Invoice_Data_Support::refill_invoice_get($refill_invoice_id);
        $ri_info_db = Refill_Invoice_Data_Support::ri_info_get($refill_invoice_id);
        
        $refill_invoice_type = $refill_invoice_db['refill_invoice_type'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('refill_invoice',$frefill_invoice,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'refill_invoice',
                $id,$frefill_invoice['refill_invoice_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function refill_invoice_canceled($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_invoice/refill_invoice_data_support');
        get_instance()->load->helper('refill_work_order/refill_work_order_engine');
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        get_instance()->load->helper('customer/customer_engine');
        get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_engine');
        
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $frefill_invoice = $final_data['refill_invoice'];
        
        $refill_invoice_id = $id;
        $refill_invoice_db = Refill_Invoice_Data_Support::refill_invoice_get($refill_invoice_id);
        $ri_product_db = Refill_Invoice_Data_Support::ri_product_get($refill_invoice_id);
                        
        $refill_invoice_type = $refill_invoice_db['refill_invoice_type'];
        $reference_id = $refill_invoice_db['reference_id'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('refill_invoice',$frefill_invoice,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'refill_invoice',
                $id,$frefill_invoice['refill_invoice_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        
        if($refill_invoice_db['refill_invoice_status']!=='X'){
            if($success === 1){
                get_instance()->load->helper('customer/customer_engine');
                $temp_result = Customer_Engine::customer_credit_add($db,-1*$refill_invoice_db['grand_total_amount'],$refill_invoice_db['customer_id']);
                $success = $temp_result['success'];
                $msg = array_merge($msg,$temp_result['msg']);
            }
            
            if($success === 1 & $refill_invoice_type === 'refill_work_order'){
                $temp_result = Refill_Work_Order_Engine::rwo_status_set($db, $reference_id, 'done');
                $success = $temp_result['success'];
                $msg = array_merge($msg,$temp_result['msg']);
            }
        }
        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

}
?>
