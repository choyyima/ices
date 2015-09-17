<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Customer_Deposit_Allocation_Engine {
        
        public static $module_type_list = array(
            array('val'=>'sales_invoice','label'=>'Sales Invoice'),
            array('val'=>'customer_bill','label'=>'Customer Bill'),
            array('val'=>'refill_invoice','label'=>'Refill Invoice'),
        );
        
        public static function path_get(){
            $path = array(
                'index'=>get_instance()->config->base_url().'customer_deposit_allocation/'
                ,'customer_deposit_allocation_engine'=>'customer_deposit_allocation/customer_deposit_allocation_engine'
                ,'customer_deposit_allocation_data_support'=>'customer_deposit_allocation/customer_deposit_allocation_data_support'
                ,'ajax_search'=>get_instance()->config->base_url().'customer_deposit_allocation/ajax_search/'
                ,'data_support'=>get_instance()->config->base_url().'customer_deposit_allocation/data_support/'
                
            );
            
            return json_decode(json_encode($path));
        }
        
        public static $status_list = array(
            array(//label name is used for method name
                'val'=>'invoiced'
                ,'label'=>'INVOICED'
                ,'method'=>'customer_deposit_allocation_invoiced'
                ,'default'=>true
                ,'next_allowed_status'=>array('X')
            )
            ,array(
                'val'=>'X'
                ,'label'=>'CANCELED'
                ,'method'=>'customer_deposit_allocation_canceled'
                ,'next_allowed_status'=>array()
                
            )            
            
        );
        
        public static function cda_allocate_amount_get($customer_id, $amount_paid){
            //<editor-fold defaultstate="collapsed">
            $result = array();
            $db = new DB();
            $q = '
                select t1.*
                from customer_deposit t1
                where t1.customer_id = '.$db->escape($customer_id).'
                    and (t1.outstanding_amount) >0
                    and t1.customer_deposit_status = "invoiced"
                    and t1.status > 0
                    and t1.customer_deposit_type = "delivery_order_final_confirmation"
                order by t1.customer_deposit_date asc
            '; 
            $rs = $db->query_array($q);
            if(count($rs)>0){
                $amount_leftover = Tools::_float($amount_paid);
                for($i = 0;$i<count($rs);$i++){
                    $temp = array(
                        'customer_deposit_id'=>$rs[$i]['id']
                        ,'amount'=>$rs[$i]['amount']
                        ,'allocated_amount'=>$rs[$i]['outstanding_amount']
                    );
                    if($amount_leftover>0){
                        
                        if(Tools::_float($temp['allocated_amount']) > $amount_leftover){
                            $temp['allocated_amount'] = Tools::_str($amount_leftover);
                        }
                        $amount_leftover -= Tools::_float($temp['allocated_amount']);
                        $result[] = $temp;
                    }
                    else{
                        break;
                    }
                }
            }
            return $result;
            //</editor-fold>
        }
        
        public static function submit($id,$method,$post){
            
            get_instance()->load->helper(self::path_get()->customer_deposit_allocation_data_support);
            
            $post = json_decode($post,TRUE);
            $data = $post;
            $ajax_post = false;                  
            $result = null;
            $cont = true;
            
            if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
            if($method == 'customer_deposit_allocation_add') $data['customer_deposit_allocation']['id'] = '';
            else $data['customer_deposit_allocation']['id'] = $id;
            
            if($cont){
                $result = self::save($method,$data);
            }
            
            if(!$ajax_post){
                
            }            
            else{
                echo json_encode($result);
                die();
            }
        }
        
        public static function validate($method,$data=array()){        
            
            $result = array(
                "success"=>1
                ,"msg"=>array()
                
            );
            $success = 1;
            $msg = array();
            $customer_deposit_allocation = isset($data['customer_deposit_allocation'])?$data['customer_deposit_allocation']:null;
            $customer_deposit = isset($data['customer_deposit'])?$data['customer_deposit']:null;
            $reference = isset($data['reference'])?$data['reference']:null;
            switch($method){
                case 'customer_deposit_allocation_add':
                    $db = new DB();
                    
                    $customer_deposit_allocation_type = isset($customer_deposit_allocation['customer_deposit_allocation_type'])?
                            $customer_deposit_allocation['customer_deposit_allocation_type']:'';
                    $allocated_amount = isset($customer_deposit_allocation['allocated_amount'])?
                        Tools::_str($customer_deposit_allocation['allocated_amount']):'0';
                    
                    if(!SI::type_match('Customer_Deposit_Allocation_Engine',$customer_deposit_allocation_type)){
                        $success = 0;
                        $msg[]='Mismatch Module Type';
                        break;
                    }
                    $store_id = isset($customer_deposit_allocation['store_id'])?
                        Tools::_str($customer_deposit_allocation['store_id']):'';
                    
                    
                    if(!SI::record_exists('store', array('id'=>$store_id,'status'=>'1'))){
                        $success = 0;
                        $msg[] = 'Invalid Store';
                    }
                    
                    $customer_deposit_id = isset($customer_deposit['id'])?
                            Tools::_str($customer_deposit['id']):'';
                    $customer_deposit_outstanding_amount = 0;
                    $q = '
                        select t1.*, t1.outstanding_amount
                        from customer_deposit t1
                        where t1.id ='.$customer_deposit_id.' 
                            and t1.outstanding_amount >= '.$db->escape($allocated_amount).'
                    ';
                    
                    $customer_deposit_db = array();
                    $rs = $db->query_array($q);
                    if(!count($rs)>0){
                        $success = 0;
                        $msg[] = 'Customer Deposit '.Lang::get('empty',true,false);
                    }
                    else{
                        $customer_deposit_db = $rs[0];
                    }
                    
                    $reference_id = isset($reference['id'])?
                            Tools::_str($reference['id']):'';
                    $reference_db = array();
                    $reference_outstanding_amount = 0;
                    $reference_exists = false;
                    $reference_customer_id = '';
                    $q = '';
                    switch($customer_deposit_allocation_type){
                        case 'sales_invoice':
                            $q = '
                                select outstanding_amount, customer_id
                                    ,t1.sales_invoice_type
                                from sales_invoice t1
                                where t1.id = '.$reference_id.' and t1.outstanding_amount >0
                                    and t1.outstanding_amount >= '.$db->escape($allocated_amount).'
                            ';
                            break;
                        case 'customer_bill':
                            $q = '
                                select outstanding_amount, customer_id
                                    ,t1.customer_bill_type
                                from customer_bill t1
                                where t1.id = '.$reference_id.' and t1.outstanding_amount >0
                                    and t1.outstanding_amount >= '.$db->escape($allocated_amount).'
                            ';
                            break;
                        case 'refill_invoice':
                            $q = '
                                select outstanding_amount, customer_id
                                    ,t1.refill_invoice_type
                                from refill_invoice t1
                                where t1.id = '.$reference_id.' and t1.outstanding_amount >0
                                    and t1.outstanding_amount >= '.$db->escape($allocated_amount).'
                            ';
                            break;
                    }
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        $reference_db = $rs;
                        $reference_exists = true;
                        $reference_outstanding_amount = $rs[0]['outstanding_amount'];
                        $reference_customer_id = $rs[0]['customer_id'];
                    }
                    
                    if(!$reference_exists){
                        $success = 0;
                        $msg[] = 'Reference '.Lang::get('empty',true,false);
                        
                    }
                    
                    if(!(isset($sales_receipt_db['customer_id'])?
                        $customer_deposit_db['customer_id']:'') === $reference_customer_id){
                        $success = 0;
                        $msg[] = 'Customer '.Lang::get('invalid',true,false);
                    }
                    
                    if(floatval($allocated_amount)<=floatval('0')){
                        $success = 0;
                        $msg[] = 'Allocated Amount 0';
                        
                    }
                    
                    if(count($reference_db)>0 && count($customer_deposit_db)>0){
                        $cd_reference_relation = false;
                        if($customer_deposit_db['customer_deposit_type'] === 'delivery_order_final_confirmation'
                            && $customer_deposit_allocation_type === 'sales_invoice'
                        ){ 
                            $cd_reference_relation = true;
                        }
                        else if($customer_deposit_db['customer_deposit_type'] === 'refill_work_order'
                            && $customer_deposit_allocation_type === 'refill_invoice'
                        ){
                            $cd_reference_relation = true;
                        }
                        else if ($customer_deposit_db['customer_deposit_type'] === 'delivery_order_final_confirmation'
                            && $customer_deposit_allocation_type === 'customer_bill'
                        ){
                            if($reference_db['customer_bill_type'] === 'delivery_order_final_confirmation'){
                                $cd_reference_relation = true;
                            }
                        }
                        
                        if(!$cd_reference_relation){
                            $success = 0;
                            $msg[] = 'Customer Deposit - Reference relationship invalid';
                        }
                    }
                    
                    if($success !== 1) break;
                    
                    
                    break;
                case 'customer_deposit_allocation_canceled':
                    $db = new DB();
                    $temp_result = Validator::validate_on_cancel(
                        array(
                            'module'=>'customer_deposit_allocation',
                            'module_name'=>'Customer Deposit Allocation',
                            'module_engine'=>'Customer_Deposit_Allocation_Engine',
                            'table'=>'customer_deposit_allocation',
                        ),
                        $customer_deposit_allocation
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
        }
        
        public static function adjust($action,$data=array()){
            $db = new DB();
            $result = array();
            $cda = isset($data['customer_deposit_allocation'])?
                Tools::_arr($data['customer_deposit_allocation']):array();
            $cd = isset($data['customer_deposit'])?
                Tools::_arr($data['customer_deposit']):array();
            $reference = isset($data['reference'])?
                Tools::_arr($data['reference']):array();
            switch($action){
                case 'customer_deposit_allocation_add':                    
                    $modid = User_Info::get()['user_id'];
                    $datetime_curr = Date('Y-m-d H:i:s');
                    
                    $cda_type = $cda['customer_deposit_allocation_type'];
                    
                    $customer_deposit_allocation = array();
                    
                    $customer_deposit_allocation = array(
                        'store_id'=>$cda['store_id'],
                        'customer_deposit_allocation_type'=>$cda_type,
                        'customer_deposit_id'=>$cd['id'],
                        'allocated_amount'=>$cda['allocated_amount'],
                        'customer_deposit_allocation_status'=>
                            SI::status_default_status_get('Customer_Deposit_Allocation_Engine')['val'],
                        'modid'=>$modid,
                        'moddate'=>$datetime_curr,
                        
                    );
                    
                    switch($cda_type){
                        case 'sales_invoice':
                            $customer_deposit_allocation['sales_invoice_id'] = $reference['id'];
                            break;
                        case 'customer_bill':
                            $customer_deposit_allocation['customer_bill_id'] = $reference['id'];
                            break;
                        case 'refill_invoice':
                            $customer_deposit_allocation['refill_invoice_id'] = $reference['id'];
                            break;
                    }
                    
                    $result['customer_deposit_allocation'] = $customer_deposit_allocation;                   
                    
                    break;
                case 'customer_deposit_allocation_canceled':
                    $customer_deposit_allocation = array(
                        'customer_deposit_allocation_status'=>'X',
                        'cancellation_reason'=>$cda['cancellation_reason'],
                        
                    );
                    $result['customer_deposit_allocation'] = $customer_deposit_allocation;
                    break;
            }
            
            return $result;
        }
        
        public static function save($method,$data){
            $db = new DB();
            $success = 1;
            $msg = array();
            $action = $method;
            $result = array("success"=>0,"msg"=>array(),'trans_id'=>'');
            $customer_deposit_allocation_data = $data['customer_deposit_allocation'];
            $id = $customer_deposit_allocation_data['id'];
            
            $method_list = array('customer_deposit_allocation_add');
            foreach(self::$status_list as $status){
                $method_list[] = strtolower($status['method']);
            }
            
            if(in_array($action,$method_list)){
                $validation_res = self::validate($action,$data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            else{
                $success = 0;
                $msg[] = 'Unknown method';
            }

            if($success == 1){
                $final_data = self::adjust($action,$data);
                $modid = User_Info::get()['user_id'];
                $moddate = date("Y-m-d H:i:s");
                
                switch($action){                    
                    case 'customer_deposit_allocation_add':
                        try{ 
                            $db->trans_begin();
                            $temp_result = self::customer_deposit_allocation_add($db,$final_data);
                            $success = $temp_result['success'];
                            $msg = array_merge($msg, $temp_result['msg']);
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Customer Deposit Allocation Success';
                                $result['trans_id'] = $temp_result['trans_id'];
                            }
                        }
                        catch(Exception $e){
                            
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }
                        break;
                    case 'customer_deposit_allocation_canceled':
                        try{
                            $db->trans_begin();
                            $temp_result = self::customer_deposit_allocation_canceled($db,$final_data,$id);
                            $success = $temp_result['success'];
                            $msg = array_merge($msg,$temp_result['msg']);
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Cancel Customer Deposit Allocation Success';
                            }
                        }
                        catch(Exception $e){
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        } 
                        break;
                }
            }
            
            if($success == 1){
                Message::set('success',$msg);
            }            
            
            $result['success'] = $success;
            $result['msg'] = $msg;
            
            return $result;
        }
               
        public function customer_deposit_allocation_add($db,$final_data,$id=''){
            $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
            $success = 1;
            $msg = array();
            
            $fcustomer_deposit_allocation = $final_data['customer_deposit_allocation'];

            $store_id = $fcustomer_deposit_allocation['store_id'];
            $modid = User_Info::get()['user_id'];
            $moddate = Date('Y-m-d H:i:s');
            

            $customer_deposit_allocation_id = '';      
            $q = '
                select t1.*
                from customer_deposit t1
                where t1.id = '.$fcustomer_deposit_allocation['customer_deposit_id'].'
            ';
            $customer_deposit = $db->query_array($q)[0];
            $allocated_amount = $fcustomer_deposit_allocation['allocated_amount'];
            $cda_type = $fcustomer_deposit_allocation['customer_deposit_allocation_type'];
            
            $fcustomer_deposit_allocation['code'] = SI::code_counter_store_get($db,$store_id, 'customer_deposit_allocation');
            if(!$db->insert('customer_deposit_allocation',$fcustomer_deposit_allocation)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }

            $customer_deposit_allocation_code = $fcustomer_deposit_allocation['code'];
            
            if($success == 1){                                
                $customer_deposit_allocation_id = $db->fast_get('customer_deposit_allocation'
                        ,array('code'=>$customer_deposit_allocation_code))[0]['id'];
                $result['trans_id']=$customer_deposit_allocation_id; 
            }
            
            if($success === 1){
                get_instance()->load->helper('customer/customer_engine');
                $temp_result = Customer_Engine::customer_debit_add($db, -1*$allocated_amount,
                    $customer_deposit['customer_id']);
                $success = $temp_result['success'];
                $msg = array_merge($msg, $temp_result['msg']);

            }
            
            if($success === 1){
                get_instance()->load->helper('customer/customer_engine');
                $temp_result = Customer_Engine::customer_credit_add($db, -1*$allocated_amount,
                    $customer_deposit['customer_id']);
                $success = $temp_result['success'];
                $msg = array_merge($msg, $temp_result['msg']);

            }
            
            if($success === 1){
                $customer_deposit_id = $fcustomer_deposit_allocation['customer_deposit_id'];
                $q = '
                    update customer_deposit
                    set outstanding_amount = outstanding_amount - '.$db->escape($fcustomer_deposit_allocation['allocated_amount']).'
                    where id = '.$db->escape($customer_deposit_id).'
                ';
                if(!$db->query($q)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                }
            }
            
            if($success === 1){                
                $allocated_amount = Tools::_float($fcustomer_deposit_allocation['allocated_amount']);
                $q = '';
                switch($cda_type){
                    case 'sales_invoice':
                        $q = '
                            update sales_invoice 
                            set outstanding_amount = outstanding_amount - '.$db->escape($allocated_amount).'
                                ,modid='.$db->escape($modid).'
                                ,moddate='.$db->escape($moddate).'
                            where id = '.$db->escape($fcustomer_deposit_allocation['sales_invoice_id']).'
                        ';
                        break;
                    
                    case 'customer_bill':
                        $q = '
                            update customer_bill 
                            set outstanding_amount = outstanding_amount - '.$db->escape($allocated_amount).'
                                ,modid='.$db->escape($modid).'
                                ,moddate='.$db->escape($moddate).'
                            where id = '.$db->escape($fcustomer_deposit_allocation['customer_bill_id']).'
                        ';
                        
                        break;                    
                    case 'refill_invoice':
                        $q = '
                            update refill_invoice 
                            set outstanding_amount = outstanding_amount - '.$db->escape($allocated_amount).'
                                ,modid='.$db->escape($modid).'
                                ,moddate='.$db->escape($moddate).'
                            where id = '.$db->escape($fcustomer_deposit_allocation['refill_invoice_id']).'
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
        }
        
        public function customer_deposit_allocation_canceled($db,$final_data,$id){
            $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
            $success = 1;
            $msg = array();
            
            $modid = User_Info::get()['user_id'];
            $moddate = Date('Y-m-d H:i:s');
            
            $fcustomer_deposit_allocation = array_merge($final_data['customer_deposit_allocation'],array("modid"=>$modid,"moddate"=>$moddate));
            $customer_deposit_allocation_id = $id;
            
            $customer_deposit_allocation = array();
            $q = '
                select t1.*
                from customer_deposit_allocation t1
                where t1.id = '.$db->escape($id).'
            ';
            $customer_deposit_allocation = $db->query_array($q)[0];

            $allocated_amount = $customer_deposit_allocation['allocated_amount'];
            $customer_deposit_allocation_status_old = $customer_deposit_allocation['customer_deposit_allocation_status'];
            $customer_deposit_allocation_type = $customer_deposit_allocation['customer_deposit_allocation_type'];
            $customer_deposit_id = $customer_deposit_allocation['customer_deposit_id'];
            $customer_deposit = $db->fast_get('customer_deposit',array('id'=>$customer_deposit_id))[0];
            
            if(!$db->update('customer_deposit_allocation',$fcustomer_deposit_allocation,
                    array("id"=>$customer_deposit_allocation_id))){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
            
            if($success === 1){
                get_instance()->load->helper('customer/customer_engine');
                $temp_result = Customer_Engine::customer_debit_add($db, $allocated_amount,
                    $customer_deposit['customer_id']);
                $success = $temp_result['success'];
                $msg = array_merge($msg, $temp_result['msg']);

            }
            
            if($success === 1){
                get_instance()->load->helper('customer/customer_engine');
                $temp_result = Customer_Engine::customer_credit_add($db, $allocated_amount,
                    $customer_deposit['customer_id']);
                $success = $temp_result['success'];
                $msg = array_merge($msg, $temp_result['msg']);

            }
            
            if($success == 1){
                $q = '
                    update customer_deposit
                    set outstanding_amount = outstanding_amount + '.$db->escape($allocated_amount).'
                        ,modid = '.$db->escape($modid).'
                        ,moddate = '.$db->escape($moddate).'
                    where id = '.$db->escape($customer_deposit_id).'
                ';
                if(!$db->query($q)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                }
            }
            
            
            
            if($success === 1){
                $q = '';
                switch($customer_deposit_allocation_type){
                    case 'sales_invoice':
                        $q = '
                            update sales_invoice 
                            set outstanding_amount = outstanding_amount + '.$db->escape($allocated_amount).'
                                ,modid='.$db->escape($modid).'
                                ,moddate='.$db->escape($moddate).'
                            where id = '.$db->escape($customer_deposit_allocation['sales_invoice_id']).'
                        ';
                        break;
                    case 'customer_bill':
                        $q = '
                            update customer_bill 
                            set outstanding_amount = outstanding_amount + '.$db->escape($allocated_amount).'
                                ,modid='.$db->escape($modid).'
                                ,moddate='.$db->escape($moddate).'
                            where id = '.$db->escape($customer_deposit_allocation['customer_bill_id']).'
                        ';
                        break;
                    case 'refill_invoice':
                        $q = '
                            update refill_invoice 
                            set outstanding_amount = outstanding_amount + '.$db->escape($allocated_amount).'
                                ,modid='.$db->escape($modid).'
                                ,moddate='.$db->escape($moddate).'
                            where id = '.$db->escape($customer_deposit_allocation['refill_invoice_id']).'
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
        }
        
    }
?>