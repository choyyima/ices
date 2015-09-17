<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Sales_Receipt_Allocation_Engine {
        public static $prefix_id = 'sales_receipt_allocation';
        public static $prefix_method;
        public static $status_list;
    
        public static $module_type_list; 

        public static function helper_init(){
            //<editor-fold defaultstate="collapsed">
            self::$module_type_list = array(
                array('val'=>'sales_invoice','label'=>'Sales Invoice'),
                array('val'=>'customer_bill','label'=>'Customer Bill'),
            );
            
            self::$status_list  = array(
                //<editor-fold defaultstate="collapsed">
                array(
                    'val'=>''
                    ,'label'=>''
                    ,'method'=>'sales_receipt_allocation_add'
                    ,'next_allowed_status'=>array()
                    ,'msg'=>array(
                        'success'=>array(
                            array('val'=>'Add')
                            ,array('val'=>Lang::get(array('Sales Receipt Allocation'),true,true,false,false,true))
                            ,array('val'=>'success')
                        )
                    )
                ),
                array(//label name is used for method name
                    'val'=>'invoiced'
                    ,'label'=>'INVOICED'
                    ,'method'=>''
                    ,'default'=>true
                    ,'next_allowed_status'=>array('X')
                    ,'msg'=>array(
                        'success'=>array(
                            array('val'=>'Update')
                            ,array('val'=>Lang::get(array('Sales Receipt Allocation'),true,true,false,false,true))
                            ,array('val'=>'success')
                        )
                    )
                )
                ,array(
                    'val'=>'X'
                    ,'label'=>'CANCELED'
                    ,'method'=>'sales_receipt_allocation_canceled'
                    ,'next_allowed_status'=>array()
                    ,'msg'=>array(
                        'success'=>array(
                            array('val'=>'Cancel')
                            ,array('val'=>Lang::get(array('Sales Receipt Allocation'),true,true,false,false,true))
                            ,array('val'=>'success')
                        )
                    )
                )
                //</editor-fold>
            );
            //</editor-fold>
        }
        
        
        public static function path_get(){
            $path = array(
                'index'=>get_instance()->config->base_url().'sales_receipt_allocation/',
                'sales_receipt_allocation_engine'=>'sales_receipt_allocation/sales_receipt_allocation_engine',
                'sales_receipt_allocation_data_support' => 'sales_receipt_allocation/sales_receipt_allocation_data_support',
                'sales_receipt_allocation_renderer' => 'sales_receipt_allocation/sales_receipt_allocation_renderer',
                'ajax_search'=>get_instance()->config->base_url().'sales_receipt_allocation/ajax_search/',
                'data_support'=>get_instance()->config->base_url().'sales_receipt_allocation/data_support/',
            );
            
            return json_decode(json_encode($path));
        }
        
        public static function validate($method,$data=array()){            
            //<editor-fold defaultstate="collapsed">
            $result = array(
                "success"=>1
                ,"msg"=>array()
                
            );
            $success = 1;
            $msg = array();
            $sales_receipt_allocation = isset($data['sales_receipt_allocation'])?$data['sales_receipt_allocation']:null;
            $sales_receipt = isset($data['sales_receipt'])?$data['sales_receipt']:null;
            $reference = isset($data['reference'])?$data['reference']:null;
            switch($method){
                case 'sales_receipt_allocation_add':
                    $db = new DB();
                    
                    $sales_receipt_allocation_type = isset($sales_receipt_allocation['sales_receipt_allocation_type'])?
                            $sales_receipt_allocation['sales_receipt_allocation_type']:'';
                    $allocated_amount = isset($sales_receipt_allocation['allocated_amount'])?
                        Tools::_str($sales_receipt_allocation['allocated_amount']):'0';
                    
                    if(!SI::type_match('Sales_Receipt_Allocation_Engine',$sales_receipt_allocation_type)){
                        $success = 0;
                        $msg[]='Mismatch Module Type';
                        break;
                    }
                    $store_id = isset($sales_receipt_allocation['store_id'])?
                        Tools::_str($sales_receipt_allocation['store_id']):'';                    
                    
                    if(!SI::record_exists('store', array('id'=>$store_id,'status'=>'1'))){
                        $success = 0;
                        $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                    }
                    
                    $sales_receipt_id = isset($sales_receipt['id'])?
                            Tools::_str($sales_receipt['id']):'';
                    $sales_receipt_outstanding_amount = 0;
                    $q = '
                        select t1.*, t1.outstanding_amount
                        from sales_receipt t1
                        where t1.id ='.$sales_receipt_id.' 
                            and (t1.outstanding_amount) >= '.$db->escape($allocated_amount).'
                    ';
                    
                    $sales_receipt_db = array();
                    $rs = $db->query_array($q);
                    if(!count($rs)>0){
                        $success = 0;
                        $msg[] = 'Sales Receipt '.Lang::get('empty',true,false);
                        
                    }
                    else{
                        $sales_receipt_db = $rs[0];
                    }
                    
                    $reference_id = isset($reference['id'])?
                            Tools::_str($reference['id']):'';
                    $reference_outstanding_amount = 0;
                    $reference_exists = false;
                    $reference_customer_id = '';
                    $q = '';
                    switch($sales_receipt_allocation_type){
                        case 'sales_invoice':
                            $q = '
                                select outstanding_amount, customer_id
                                from sales_invoice t1
                                where t1.id = '.$reference_id.' and t1.outstanding_amount >0
                                    and t1.outstanding_amount >= '.$db->escape($allocated_amount).'
                            ';
                            break;
                        case 'customer_bill':
                            $q = '
                                select outstanding_amount, customer_id
                                from customer_bill t1
                                where t1.id = '.$reference_id.' and t1.outstanding_amount >0
                                    and t1.outstanding_amount >= '.$db->escape($allocated_amount).'
                            ';
                            break;
                    }
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        $reference_exists = true;
                        $reference_outstanding_amount = $rs[0]['outstanding_amount'];
                        $reference_customer_id = $rs[0]['customer_id'];
                    }
                    
                    if(!$reference_exists){
                        $success = 0;
                        $msg[] = 'Reference '.Lang::get('empty',true,false);
                        
                    }                   
                    
                    
                    if(floatval($allocated_amount)<=floatval('0')){
                        $success = 0;
                        $msg[] = 'Allocated Amount 0';
                        
                    }
                    
                    if(!(isset($sales_receipt_db['customer_id'])?
                        $sales_receipt_db['customer_id']:'') === $reference_customer_id){
                        $success = 0;
                        $msg[] = 'Customer '.Lang::get('invalid',true,false);
                    }
                                        
                    if($success !== 1) break;
                    
                    
                    break;
                case 'sales_receipt_allocation_canceled':
                    $db = new DB();
                    $temp_result = Validator::validate_on_cancel(
                        array(
                            'module'=>'sales_receipt_allocation',
                            'module_name'=>'Sales Receipt Allocation',
                            'module_engine'=>'Sales_Receipt_Allocation_Engine',
                            'table'=>'sales_receipt_allocation',
                        ),
                        $sales_receipt_allocation
                    );
                    $success = $temp_result['success'];
                    $msg = array_merge($msg, $temp_result['msg']);
                    
                    
                    break;
                default:
                    $success = 0;
                    $msg[] = 'Unknown Method';
                    break;
                    
               
            }
            $result['success'] = $success;
            $result['msg'] = $msg;
            return $result;
            //</editor-fold>
        }
        
        public static function adjust($action,$data=array()){
            //<editor-fold defaultstate="collapsed">
            $db = new DB();
            $result = array();
            $cda = isset($data['sales_receipt_allocation'])?
                Tools::_arr($data['sales_receipt_allocation']):array();
            $cd = isset($data['sales_receipt'])?
                Tools::_arr($data['sales_receipt']):array();
            $reference = isset($data['reference'])?
                Tools::_arr($data['reference']):array();
            switch($action){
                case 'sales_receipt_allocation_add':                    
                    $modid = User_Info::get()['user_id'];
                    $datetime_curr = Date('Y-m-d H:i:s');
                    
                    $cda_type = $cda['sales_receipt_allocation_type'];
                    
                    $sales_receipt_allocation = array();
                    
                    $sales_receipt_allocation = array(
                        'store_id'=>$cda['store_id'],
                        'sales_receipt_allocation_type'=>$cda_type,
                        'sales_receipt_id'=>$cd['id'],
                        'allocated_amount'=>$cda['allocated_amount'],
                        'sales_receipt_allocation_status'=>
                            SI::status_default_status_get('Sales_Receipt_Allocation_Engine')['val'],
                        'modid'=>$modid,
                        'moddate'=>$datetime_curr,
                        
                    );
                    
                    switch($cda_type){
                        case 'sales_invoice':
                            $sales_receipt_allocation['sales_invoice_id'] = $reference['id'];
                            break;
                        case 'customer_bill':
                            $sales_receipt_allocation['customer_bill_id'] = $reference['id'];
                            break;
                        
                    }
                    
                    $result['sales_receipt_allocation'] = $sales_receipt_allocation;                   
                    
                    break;
                case 'sales_receipt_allocation_canceled':
                    $sales_receipt_allocation = array(
                        'sales_receipt_allocation_status'=>'X',
                        'cancellation_reason'=>$cda['cancellation_reason'],
                        
                    );
                    $result['sales_receipt_allocation'] = $sales_receipt_allocation;
                    break;
            }
            
            return $result;
            //</editor-fold>
        }
               
        public function sales_receipt_allocation_add($db,$final_data,$id){
            //<editor-fold defaultstate="collapsed">
            $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
            $success = 1;
            $msg = array();
            
            $fsales_receipt_allocation = $final_data['sales_receipt_allocation'];
            
            $store_id = $fsales_receipt_allocation['store_id'];
            $modid = User_Info::get()['user_id'];
            $moddate = Date('Y-m-d H:i:s');

            $sales_receipt_allocation_id = '';   
            $q = '
                select t1.*
                from sales_receipt t1
                where t1.id = '.$fsales_receipt_allocation['sales_receipt_id'].'
            ';
            $sales_receipt = $db->query_array($q)[0];
            $sales_receipt_allocation_type = $fsales_receipt_allocation['sales_receipt_allocation_type'];
            
            $allocated_amount = Tools::_float($fsales_receipt_allocation['allocated_amount']);
            
            $fsales_receipt_allocation['code'] = SI::code_counter_store_get($db,$store_id, 'sales_receipt_allocation');
            if(!$db->insert('sales_receipt_allocation',$fsales_receipt_allocation)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }

            if($success === 1){
                get_instance()->load->helper('customer/customer_engine');
                $temp_result = Customer_Engine::customer_debit_add($db, -1*$allocated_amount,
                    $sales_receipt['customer_id']);
                $success = $temp_result['success'];
                $msg = array_merge($msg, $temp_result['msg']);

            }
            
            if($success === 1){
                get_instance()->load->helper('customer/customer_engine');
                $temp_result = Customer_Engine::customer_credit_add($db, -1*$allocated_amount,
                    $sales_receipt['customer_id']);
                $success = $temp_result['success'];
                $msg = array_merge($msg, $temp_result['msg']);

            }
            
            if($success === 1){                
                switch($sales_receipt_allocation_type){
                    case'sales_invoice':
                        $q = '
                            update sales_invoice 
                            set outstanding_amount = outstanding_amount - '.$db->escape($allocated_amount).'
                                ,modid='.$db->escape($modid).'
                                ,moddate='.$db->escape($moddate).'
                            where id = '.$db->escape($fsales_receipt_allocation['sales_invoice_id']).'
                        ';
                        break;
                    
                    case'customer_bill':
                        $q = '
                            update customer_bill 
                            set outstanding_amount = outstanding_amount - '.$db->escape($allocated_amount).'
                                ,modid='.$db->escape($modid).'
                                ,moddate='.$db->escape($moddate).'
                            where id = '.$db->escape($fsales_receipt_allocation['customer_bill_id']).'
                        ';
                        break;
                    default:
                        $success = 0;
                        $msg[] = 'Unable to find Reference Outstanding Amount';
                        break;
                }
                if($success === 1){
                    if(!$db->query($q)){
                        $msg[] = $db->_error_message();
                        $db->trans_rollback();                                
                        $success = 0;                
                    }
                }
            }
            
            $sales_receipt_allocation_code = $fsales_receipt_allocation['code'];
            
            if($success == 1){                                
                $sales_receipt_allocation_id = $db->fast_get('sales_receipt_allocation'
                        ,array('code'=>$sales_receipt_allocation_code))[0]['id'];
                $result['trans_id']=$sales_receipt_allocation_id; 
            }
            
            if($success === 1){
                $sales_receipt_id = $fsales_receipt_allocation['sales_receipt_id'];
                $q = '
                    update sales_receipt
                    set outstanding_amount = outstanding_amount - '.$db->escape($fsales_receipt_allocation['allocated_amount']).'
                    where id = '.$db->escape($sales_receipt_id).'
                ';
                if(!$db->query($q)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                }
            }
            
            $result['success'] = $success;
            $result['msg'] = $msg;
            return $result;
            //</editor-fold>
        }
        
        public function sales_receipt_allocation_canceled($db,$final_data,$id){
            //<editor-fold defaultstate="collapsed">
            $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
            $success = 1;
            $msg = array();
            
            $modid = User_Info::get()['user_id'];
            $moddate = Date('Y-m-d H:i:s');
            
            $fsales_receipt_allocation = array_merge($final_data['sales_receipt_allocation'],array("modid"=>$modid,"moddate"=>$moddate));
            $sales_receipt_allocation_id = $id;
            
            $sales_receipt_allocation = array();
            $q = '
                select t1.*
                from sales_receipt_allocation t1
                where t1.id = '.$db->escape($id).'
            ';
            $sales_receipt_allocation = $db->query_array($q)[0];

            $allocated_amount = $sales_receipt_allocation['allocated_amount'];
            $sales_receipt_allocation_status_old = $sales_receipt_allocation['sales_receipt_allocation_status'];
            $sales_receipt_allocation_type = $sales_receipt_allocation['sales_receipt_allocation_type'];
            $sales_receipt_id = $sales_receipt_allocation['sales_receipt_id'];
            $sales_receipt = $db->fast_get('sales_receipt',array('id'=>$sales_receipt_id))[0];
            
            if(!$db->update('sales_receipt_allocation',$fsales_receipt_allocation,
                    array("id"=>$sales_receipt_allocation_id))){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
            
            if($success === 1){
                get_instance()->load->helper('customer/customer_engine');
                $temp_result = Customer_Engine::customer_debit_add($db, $allocated_amount,
                    $sales_receipt['customer_id']);
                $success = $temp_result['success'];
                $msg = array_merge($msg, $temp_result['msg']);

            }
            
            if($success === 1){
                get_instance()->load->helper('customer/customer_engine');
                $temp_result = Customer_Engine::customer_credit_add($db, $allocated_amount,
                    $sales_receipt['customer_id']);
                $success = $temp_result['success'];
                $msg = array_merge($msg, $temp_result['msg']);

            }
            
            if($success == 1){
                $q = '
                    update sales_receipt
                    set outstanding_amount = outstanding_amount + '.$db->escape($allocated_amount).'
                        ,modid = '.$db->escape($modid).'
                        ,moddate = '.$db->escape($moddate).'
                    where id = '.$db->escape($sales_receipt_id).'
                ';
                if(!$db->query($q)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                }
            }
            
            
            
            if($success === 1){
                $q = '';
                switch($sales_receipt_allocation_type){
                    case 'sales_invoice':
                        $q = '
                            update sales_invoice 
                            set outstanding_amount = outstanding_amount + '.$db->escape($allocated_amount).'
                                ,modid='.$db->escape($modid).'
                                ,moddate='.$db->escape($moddate).'
                            where id = '.$db->escape($sales_receipt_allocation['sales_invoice_id']).'
                        ';
                        break;
                    case 'customer_bill':
                        $q = '
                            update customer_bill 
                            set outstanding_amount = outstanding_amount + '.$db->escape($allocated_amount).'
                                ,modid='.$db->escape($modid).'
                                ,moddate='.$db->escape($moddate).'
                            where id = '.$db->escape($sales_receipt_allocation['customer_bill_id']).'
                        ';
                        break;
                    default:
                        $success = 0;
                        $msg[] = 'Unable to find Reference Outstanding Amount';
                        break;
                }
                if($success === 1){
                    if(!$db->query($q)){
                        $msg[] = $db->_error_message();
                        $db->trans_rollback();                                
                        $success = 0;
                    }
                }
                
            }
            
            $result['success'] = $success;
            $result['msg'] = $msg;
            return $result;
            //</editor-fold>
        }
        
    }
?>