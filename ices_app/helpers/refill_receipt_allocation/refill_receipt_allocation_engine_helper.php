<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Refill_Receipt_Allocation_Engine {
        public static $prefix_id = 'refill_receipt_allocation';
        public static $prefix_method;
        public static $status_list;
    
        public static $module_type_list; 

        public static function helper_init(){
            //<editor-fold defaultstate="collapsed">
            self::$module_type_list = array(
                array('val'=>'refill_invoice','label'=>'Refill Invoice'),
            );
            
            self::$status_list  = array(
                //<editor-fold defaultstate="collapsed">
                array(
                    'val'=>''
                    ,'label'=>''
                    ,'method'=>'refill_receipt_allocation_add'
                    ,'next_allowed_status'=>array()
                    ,'msg'=>array(
                        'success'=>array(
                            array('val'=>'Add')
                            ,array('val'=>Lang::get(array('Refill Receipt Allocation'),true,true,false,false,true))
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
                            ,array('val'=>Lang::get(array('Refill Receipt Allocation'),true,true,false,false,true))
                            ,array('val'=>'success')
                        )
                    )
                )
                ,array(
                    'val'=>'X'
                    ,'label'=>'CANCELED'
                    ,'method'=>'refill_receipt_allocation_canceled'
                    ,'next_allowed_status'=>array()
                    ,'msg'=>array(
                        'success'=>array(
                            array('val'=>'Cancel')
                            ,array('val'=>Lang::get(array('Refill Receipt Allocation'),true,true,false,false,true))
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
                'index'=>get_instance()->config->base_url().'refill_receipt_allocation/',
                'refill_receipt_allocation_engine'=>'refill_receipt_allocation/refill_receipt_allocation_engine',
                'refill_receipt_allocation_data_support' => 'refill_receipt_allocation/refill_receipt_allocation_data_support',
                'refill_receipt_allocation_renderer' => 'refill_receipt_allocation/refill_receipt_allocation_renderer',
                'ajax_search'=>get_instance()->config->base_url().'refill_receipt_allocation/ajax_search/',
                'data_support'=>get_instance()->config->base_url().'refill_receipt_allocation/data_support/',
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
            $refill_receipt_allocation = isset($data['refill_receipt_allocation'])?$data['refill_receipt_allocation']:null;
            $refill_receipt = isset($data['refill_receipt'])?$data['refill_receipt']:null;
            $reference = isset($data['reference'])?$data['reference']:null;
            switch($method){
                case 'refill_receipt_allocation_add':
                    $db = new DB();
                    
                    $refill_receipt_allocation_type = isset($refill_receipt_allocation['refill_receipt_allocation_type'])?
                            $refill_receipt_allocation['refill_receipt_allocation_type']:'';
                    $allocated_amount = isset($refill_receipt_allocation['allocated_amount'])?
                        Tools::_str($refill_receipt_allocation['allocated_amount']):'0';
                    
                    if(!SI::type_match('Refill_Receipt_Allocation_Engine',$refill_receipt_allocation_type)){
                        $success = 0;
                        $msg[]='Mismatch Module Type';
                        break;
                    }
                    $store_id = isset($refill_receipt_allocation['store_id'])?
                        Tools::_str($refill_receipt_allocation['store_id']):'';                    
                    
                    if(!SI::record_exists('store', array('id'=>$store_id,'status'=>'1'))){
                        $success = 0;
                        $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                    }
                    
                    $refill_receipt_id = isset($refill_receipt['id'])?
                            Tools::_str($refill_receipt['id']):'';
                    $refill_receipt_outstanding_amount = 0;
                    $q = '
                        select t1.*, t1.outstanding_amount
                        from refill_receipt t1
                        where t1.id ='.$refill_receipt_id.' 
                            and (t1.outstanding_amount) >= '.$db->escape($allocated_amount).'
                    ';
                    
                    $refill_receipt_db = array();
                    $rs = $db->query_array($q);
                    if(!count($rs)>0){
                        $success = 0;
                        $msg[] = 'Refill Receipt '.Lang::get('empty',true,false);
                        
                    }
                    else{
                        $refill_receipt_db = $rs[0];
                    }
                    
                    $reference_id = isset($reference['id'])?
                            Tools::_str($reference['id']):'';
                    $reference_outstanding_amount = 0;
                    $reference_exists = false;
                    $reference_customer_id = '';
                    $q = '';
                    switch($refill_receipt_allocation_type){
                        case 'refill_invoice':
                            $q = '
                                select outstanding_amount, customer_id
                                from refill_invoice t1
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
                    
                    if(!(isset($refill_receipt_db['customer_id'])?
                        $refill_receipt_db['customer_id']:'') === $reference_customer_id){
                        $success = 0;
                        $msg[] = 'Customer '.Lang::get('invalid',true,false);
                    }
                                        
                    if($success !== 1) break;
                    
                    
                    break;
                case 'refill_receipt_allocation_canceled':
                    $db = new DB();
                    $temp_result = Validator::validate_on_cancel(
                        array(
                            'module'=>'refill_receipt_allocation',
                            'module_name'=>'Refill Receipt Allocation',
                            'module_engine'=>'Refill_Receipt_Allocation_Engine',
                            'table'=>'refill_receipt_allocation',
                        ),
                        $refill_receipt_allocation
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
            $cda = isset($data['refill_receipt_allocation'])?
                Tools::_arr($data['refill_receipt_allocation']):array();
            $cd = isset($data['refill_receipt'])?
                Tools::_arr($data['refill_receipt']):array();
            $reference = isset($data['reference'])?
                Tools::_arr($data['reference']):array();
            switch($action){
                case 'refill_receipt_allocation_add':                    
                    $modid = User_Info::get()['user_id'];
                    $datetime_curr = Date('Y-m-d H:i:s');
                    
                    $cda_type = $cda['refill_receipt_allocation_type'];
                    
                    $refill_receipt_allocation = array();
                    
                    $refill_receipt_allocation = array(
                        'store_id'=>$cda['store_id'],
                        'refill_receipt_allocation_type'=>$cda_type,
                        'refill_receipt_id'=>$cd['id'],
                        'allocated_amount'=>$cda['allocated_amount'],
                        'refill_receipt_allocation_status'=>
                            SI::status_default_status_get('Refill_Receipt_Allocation_Engine')['val'],
                        'modid'=>$modid,
                        'moddate'=>$datetime_curr,
                        
                    );
                    
                    switch($cda_type){
                        case 'refill_invoice':
                            $refill_receipt_allocation['refill_invoice_id'] = $reference['id'];
                            break;
                        
                        
                    }
                    
                    $result['refill_receipt_allocation'] = $refill_receipt_allocation;                   
                    
                    break;
                case 'refill_receipt_allocation_canceled':
                    $refill_receipt_allocation = array(
                        'refill_receipt_allocation_status'=>'X',
                        'cancellation_reason'=>$cda['cancellation_reason'],
                        
                    );
                    $result['refill_receipt_allocation'] = $refill_receipt_allocation;
                    break;
            }
            
            return $result;
            //</editor-fold>
        }
               
        public function refill_receipt_allocation_add($db,$final_data,$id){
            //<editor-fold defaultstate="collapsed">
            $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
            $success = 1;
            $msg = array();
            
            $frefill_receipt_allocation = $final_data['refill_receipt_allocation'];
            
            $store_id = $frefill_receipt_allocation['store_id'];
            $modid = User_Info::get()['user_id'];
            $moddate = Date('Y-m-d H:i:s');

            $refill_receipt_allocation_id = '';   
            $q = '
                select t1.*
                from refill_receipt t1
                where t1.id = '.$frefill_receipt_allocation['refill_receipt_id'].'
            ';
            $refill_receipt = $db->query_array($q)[0];
            $refill_receipt_allocation_type = $frefill_receipt_allocation['refill_receipt_allocation_type'];
            
            $allocated_amount = Tools::_float($frefill_receipt_allocation['allocated_amount']);
            
            $frefill_receipt_allocation['code'] = SI::code_counter_store_get($db,$store_id, 'refill_receipt_allocation');
            if(!$db->insert('refill_receipt_allocation',$frefill_receipt_allocation)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }

            if($success === 1){
                get_instance()->load->helper('customer/customer_engine');
                $temp_result = Customer_Engine::customer_debit_add($db, -1*$allocated_amount,
                    $refill_receipt['customer_id']);
                $success = $temp_result['success'];
                $msg = array_merge($msg, $temp_result['msg']);

            }
            
            if($success === 1){
                get_instance()->load->helper('customer/customer_engine');
                $temp_result = Customer_Engine::customer_credit_add($db, -1*$allocated_amount,
                    $refill_receipt['customer_id']);
                $success = $temp_result['success'];
                $msg = array_merge($msg, $temp_result['msg']);

            }
            
            if($success === 1){                
                switch($refill_receipt_allocation_type){
                    case'refill_invoice':
                        $q = '
                            update refill_invoice 
                            set outstanding_amount = outstanding_amount - '.$db->escape($allocated_amount).'
                                ,modid='.$db->escape($modid).'
                                ,moddate='.$db->escape($moddate).'
                            where id = '.$db->escape($frefill_receipt_allocation['refill_invoice_id']).'
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
            
            $refill_receipt_allocation_code = $frefill_receipt_allocation['code'];
            
            if($success == 1){                                
                $refill_receipt_allocation_id = $db->fast_get('refill_receipt_allocation'
                        ,array('code'=>$refill_receipt_allocation_code))[0]['id'];
                $result['trans_id']=$refill_receipt_allocation_id; 
            }
            
            if($success === 1){
                $refill_receipt_id = $frefill_receipt_allocation['refill_receipt_id'];
                $q = '
                    update refill_receipt
                    set outstanding_amount = outstanding_amount - '.$db->escape($frefill_receipt_allocation['allocated_amount']).'
                    where id = '.$db->escape($refill_receipt_id).'
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
        
        public function refill_receipt_allocation_canceled($db,$final_data,$id){
            //<editor-fold defaultstate="collapsed">
            $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
            $success = 1;
            $msg = array();
            
            $modid = User_Info::get()['user_id'];
            $moddate = Date('Y-m-d H:i:s');
            
            $frefill_receipt_allocation = array_merge($final_data['refill_receipt_allocation'],array("modid"=>$modid,"moddate"=>$moddate));
            $refill_receipt_allocation_id = $id;
            
            $refill_receipt_allocation = array();
            $q = '
                select t1.*
                from refill_receipt_allocation t1
                where t1.id = '.$db->escape($id).'
            ';
            $refill_receipt_allocation = $db->query_array($q)[0];

            $allocated_amount = $refill_receipt_allocation['allocated_amount'];
            $refill_receipt_allocation_status_old = $refill_receipt_allocation['refill_receipt_allocation_status'];
            $refill_receipt_allocation_type = $refill_receipt_allocation['refill_receipt_allocation_type'];
            $refill_receipt_id = $refill_receipt_allocation['refill_receipt_id'];
            $refill_receipt = $db->fast_get('refill_receipt',array('id'=>$refill_receipt_id))[0];
            
            if(!$db->update('refill_receipt_allocation',$frefill_receipt_allocation,
                    array("id"=>$refill_receipt_allocation_id))){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
            
            if($success === 1){
                get_instance()->load->helper('customer/customer_engine');
                $temp_result = Customer_Engine::customer_debit_add($db, $allocated_amount,
                    $refill_receipt['customer_id']);
                $success = $temp_result['success'];
                $msg = array_merge($msg, $temp_result['msg']);

            }
            
            if($success === 1){
                get_instance()->load->helper('customer/customer_engine');
                $temp_result = Customer_Engine::customer_credit_add($db, $allocated_amount,
                    $refill_receipt['customer_id']);
                $success = $temp_result['success'];
                $msg = array_merge($msg, $temp_result['msg']);

            }
            
            if($success == 1){
                $q = '
                    update refill_receipt
                    set outstanding_amount = outstanding_amount + '.$db->escape($allocated_amount).'
                        ,modid = '.$db->escape($modid).'
                        ,moddate = '.$db->escape($moddate).'
                    where id = '.$db->escape($refill_receipt_id).'
                ';
                if(!$db->query($q)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                }
            }
            
            
            
            if($success === 1){
                $q = '';
                switch($refill_receipt_allocation_type){
                    case 'refill_invoice':
                        $q = '
                            update refill_invoice 
                            set outstanding_amount = outstanding_amount + '.$db->escape($allocated_amount).'
                                ,modid='.$db->escape($modid).'
                                ,moddate='.$db->escape($moddate).'
                            where id = '.$db->escape($refill_receipt_allocation['refill_invoice_id']).'
                        ';
                        break;
                    
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