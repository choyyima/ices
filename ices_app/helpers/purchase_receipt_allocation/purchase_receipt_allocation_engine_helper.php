<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Purchase_Receipt_Allocation_Engine {
        
        public static $module_type_list = array(
            array('val'=>'purchase_invoice','label'=>'Purchase Invoice'),
        );
        
        public static $status_list = array(
            //<editor-fold defaultstate="collapsed">
            array(//label name is used for method name
                'val'=>'invoiced'
                ,'label'=>'INVOICED'
                ,'method'=>'purchase_receipt_allocation_invoiced'
                ,'default'=>true
                ,'next_allowed_status'=>array('X')
            )
            ,array(
                'val'=>'X'
                ,'label'=>'CANCELED'
                ,'method'=>'purchase_receipt_allocation_canceled'
                ,'next_allowed_status'=>array()
            )
            //</editor-fold>
        );

        public static function purchase_receipt_allocation_exists($id){
            $result = false;
            $db = new DB();
            $q = '
                    select 1 
                    from purchase_receipt_allocation 
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
                'index'=>get_instance()->config->base_url().'purchase_receipt_allocation/',
                'purchase_receipt_allocation_engine'=>'purchase_receipt_allocation/purchase_receipt_allocation_engine',
                'purchase_receipt_allocation_data_support' => 'purchase_receipt_allocation/purchase_receipt_allocation_data_support',
                'purchase_receipt_allocation_renderer' => 'purchase_receipt_allocation/purchase_receipt_allocation_renderer',
                'ajax_search'=>get_instance()->config->base_url().'purchase_receipt_allocation/ajax_search/',
                'data_support'=>get_instance()->config->base_url().'purchase_receipt_allocation/data_support/',
            );
            
            return json_decode(json_encode($path));
        }
        
        public static function submit($id,$method,$post){
            get_instance()->load->helper(self::path_get()->purchase_receipt_allocation_data_support);
            
            $post = json_decode($post,TRUE);
            $data = $post;
            $ajax_post = false;                  
            $result = null;
            $cont = true;
            
            if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
            if($method == 'purchase_receipt_allocation_add') $data['purchase_receipt_allocation']['id'] = '';
            else $data['purchase_receipt_allocation']['id'] = $id;
            
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
            $purchase_receipt_allocation = isset($data['purchase_receipt_allocation'])?$data['purchase_receipt_allocation']:null;
            $purchase_receipt = isset($data['purchase_receipt'])?$data['purchase_receipt']:null;
            $reference = isset($data['reference'])?$data['reference']:null;
            switch($method){
                case 'purchase_receipt_allocation_add':
                    $db = new DB();
                    
                    $purchase_receipt_allocation_type = isset($purchase_receipt_allocation['purchase_receipt_allocation_type'])?
                            $purchase_receipt_allocation['purchase_receipt_allocation_type']:'';
                    $allocated_amount = isset($purchase_receipt_allocation['allocated_amount'])?
                        Tools::_str($purchase_receipt_allocation['allocated_amount']):'0';
                    
                    if(!SI::type_match('Purchase_Receipt_Allocation_Engine',$purchase_receipt_allocation_type)){
                        $success = 0;
                        $msg[]='Mismatch Module Type';
                        break;
                    }
                    $store_id = isset($purchase_receipt_allocation['store_id'])?
                        Tools::_str($purchase_receipt_allocation['store_id']):'';
                    
                    
                    if(!SI::record_exists('store', array('id'=>$store_id,'status'=>'1'))){
                        $success = 0;
                        $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                    }
                    
                    $purchase_receipt_id = isset($purchase_receipt['id'])?
                            Tools::_str($purchase_receipt['id']):'';
                    $purchase_receipt_outstanding_amount = 0;
                    $q = '
                        select t1.*, t1.outstanding_amount
                        from purchase_receipt t1
                        where t1.id ='.$purchase_receipt_id.' 
                            and (t1.outstanding_amount) >= '.$db->escape($allocated_amount).'
                    ';
                    
                    $purchase_receipt_db = array();
                    
                    $rs = $db->query_array($q);
                    if(!count($rs)>0){
                        $success = 0;
                        $msg[] = 'Purchase Receipt '.Lang::get('empty',true,false);
                        
                    }
                    else{
                        $purchase_receipt_db = $rs[0];
                    }
                    
                    $reference_id = isset($reference['id'])?
                            Tools::_str($reference['id']):'';
                    $reference_outstanding_amount = 0;
                    $reference_supplier_id = '';
                    $reference_exists = false;
                    $q = '';
                    switch($purchase_receipt_allocation_type){
                        case 'purchase_invoice':
                            $q = '
                                select outstanding_amount, supplier_id
                                from purchase_invoice t1
                                where t1.id = '.$reference_id.' and t1.outstanding_amount >0
                                    and t1.outstanding_amount >= '.$db->escape($allocated_amount).'
                            ';
                            break;
                        case 'customer_bill':
                            $q = '
                                select outstanding_amount, supplier_id
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
                        $reference_supplier_id = $rs[0]['supplier_id'];
                    }
                    
                    if(!$reference_exists){
                        $success = 0;
                        $msg[] = 'Reference '.Lang::get('empty',true,false);
                        
                    }                   
                    
                    if(!(isset($purchase_receipt_db['supplier_id'])?
                        $purchase_receipt_db['supplier_id']:'') === $reference_supplier_id){
                        $success = 0;
                        $msg[] = 'Supplier '.Lang::get('invalid',true,false);
                    }
                    
                    if(floatval($allocated_amount)<=floatval('0')){
                        $success = 0;
                        $msg[] = 'Allocated Amount 0';
                        
                    }
                                        
                    if($success !== 1) break;
                    
                    
                    break;
                case 'purchase_receipt_allocation_canceled':
                    $db = new DB();
                    $temp_result = Validator::validate_on_cancel(
                        array(
                            'module'=>'purchase_receipt_allocation',
                            'module_name'=>'Purchase Receipt Allocation',
                            'module_engine'=>'Purchase_Receipt_Allocation_Engine',
                            'table'=>'purchase_receipt_allocation',
                        ),
                        $purchase_receipt_allocation
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
            $cda = isset($data['purchase_receipt_allocation'])?
                Tools::_arr($data['purchase_receipt_allocation']):array();
            $cd = isset($data['purchase_receipt'])?
                Tools::_arr($data['purchase_receipt']):array();
            $reference = isset($data['reference'])?
                Tools::_arr($data['reference']):array();
            switch($action){
                case 'purchase_receipt_allocation_add':                    
                    $modid = User_Info::get()['user_id'];
                    $datetime_curr = Date('Y-m-d H:i:s');
                    
                    $cda_type = $cda['purchase_receipt_allocation_type'];
                    
                    $purchase_receipt_allocation = array();
                    
                    $purchase_receipt_allocation = array(
                        'store_id'=>$cda['store_id'],
                        'purchase_receipt_allocation_type'=>$cda_type,
                        'purchase_receipt_id'=>$cd['id'],
                        'allocated_amount'=>$cda['allocated_amount'],
                        'purchase_receipt_allocation_status'=>
                            SI::status_default_status_get('Purchase_Receipt_Allocation_Engine')['val'],
                        'modid'=>$modid,
                        'moddate'=>$datetime_curr,
                        
                    );
                    
                    switch($cda_type){
                        case 'purchase_invoice':
                            $purchase_receipt_allocation['purchase_invoice_id'] = $reference['id'];
                            break;
                        case 'customer_bill':
                            $purchase_receipt_allocation['customer_bill_id'] = $reference['id'];
                            break;
                        
                    }
                    
                    $result['purchase_receipt_allocation'] = $purchase_receipt_allocation;                   
                    
                    break;
                case 'purchase_receipt_allocation_canceled':
                    $purchase_receipt_allocation = array(
                        'purchase_receipt_allocation_status'=>'X',
                        'cancellation_reason'=>$cda['cancellation_reason'],
                        
                    );
                    $result['purchase_receipt_allocation'] = $purchase_receipt_allocation;
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
            $purchase_receipt_allocation_data = $data['purchase_receipt_allocation'];
            $id = $purchase_receipt_allocation_data['id'];
            
            $method_list = array('purchase_receipt_allocation_add');
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
                    case 'purchase_receipt_allocation_add':
                        try{ 
                            $db->trans_begin();
                            $temp_result = self::purchase_receipt_allocation_add($db,$final_data);
                            $success = $temp_result['success'];
                            $msg = array_merge($msg, $temp_result['msg']);
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Purchase Receipt Allocation Success';
                                $result['trans_id'] = $temp_result['trans_id'];
                            }
                        }
                        catch(Exception $e){
                            
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }
                        break;
                    case 'purchase_receipt_allocation_canceled':
                        try{
                            $db->trans_begin();
                            $temp_result = self::purchase_receipt_allocation_cancel($db,$final_data,$id);
                            $success = $temp_result['success'];
                            $msg = array_merge($msg,$temp_result['msg']);
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Cancel Purchase Receipt Allocation Success';
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
               
        public function purchase_receipt_allocation_add($db,$final_data){
            $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
            $success = 1;
            $msg = array();
            
            $fpurchase_receipt_allocation = $final_data['purchase_receipt_allocation'];

            $store_id = $fpurchase_receipt_allocation['store_id'];
            $modid = User_Info::get()['user_id'];
            $moddate = Date('Y-m-d H:i:s');

            $purchase_receipt_allocation_id = '';   
            $q = '
                select t1.*
                from purchase_receipt t1
                where t1.id = '.$fpurchase_receipt_allocation['purchase_receipt_id'].'
            ';
            $purchase_receipt = $db->query_array($q)[0];
            
            $allocated_amount = Tools::_float($fpurchase_receipt_allocation['allocated_amount']);
            
            $fpurchase_receipt_allocation['code'] = SI::code_counter_store_get($db,$store_id, 'purchase_receipt_allocation');
            if(!$db->insert('purchase_receipt_allocation',$fpurchase_receipt_allocation)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }

            if($success === 1){                
                
                $q = '
                    update purchase_invoice 
                    set outstanding_amount = outstanding_amount - '.$db->escape($allocated_amount).'
                        ,modid='.$db->escape($modid).'
                        ,moddate='.$db->escape($moddate).'
                    where id = '.$db->escape($fpurchase_receipt_allocation['purchase_invoice_id']).'
                ';
                
                if(!$db->query($q)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;                
                }
            }
            
            $purchase_receipt_allocation_code = $fpurchase_receipt_allocation['code'];
            
            if($success == 1){                                
                $purchase_receipt_allocation_id = $db->fast_get('purchase_receipt_allocation'
                        ,array('code'=>$purchase_receipt_allocation_code))[0]['id'];
                $result['trans_id']=$purchase_receipt_allocation_id; 
            }
            
            if($success === 1){
                $purchase_receipt_id = $fpurchase_receipt_allocation['purchase_receipt_id'];
                $q = '
                    update purchase_receipt
                    set outstanding_amount = outstanding_amount -  '.$db->escape($fpurchase_receipt_allocation['allocated_amount']).'
                    where id = '.$db->escape($purchase_receipt_id).'
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
        }
        
        public function purchase_receipt_allocation_cancel($db,$final_data,$id){
            $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
            $success = 1;
            $msg = array();
            
            $modid = User_Info::get()['user_id'];
            $moddate = Date('Y-m-d H:i:s');
            
            $fpurchase_receipt_allocation = array_merge($final_data['purchase_receipt_allocation'],array("modid"=>$modid,"moddate"=>$moddate));
            $purchase_receipt_allocation_id = $id;
            
            $purchase_receipt_allocation = array();
            $q = '
                select t1.*
                from purchase_receipt_allocation t1
                where t1.id = '.$db->escape($id).'
            ';
            $purchase_receipt_allocation = $db->query_array($q)[0];

            $allocated_amount = $purchase_receipt_allocation['allocated_amount'];
            $purchase_receipt_allocation_status_old = $purchase_receipt_allocation['purchase_receipt_allocation_status'];
            $purchase_receipt_allocation_type = $purchase_receipt_allocation['purchase_receipt_allocation_type'];
            $purchase_receipt_id = $purchase_receipt_allocation['purchase_receipt_id'];
            $purchase_receipt = $db->fast_get('purchase_receipt',array('id'=>$purchase_receipt_id))[0];
            
            if(!$db->update('purchase_receipt_allocation',$fpurchase_receipt_allocation,
                    array("id"=>$purchase_receipt_allocation_id))){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
            
            if($success == 1){
                $q = '
                    update purchase_receipt
                    set outstanding_amount = outstanding_amount + '.$db->escape($allocated_amount).'
                        ,modid = '.$db->escape($modid).'
                        ,moddate = '.$db->escape($moddate).'
                    where id = '.$db->escape($purchase_receipt_id).'
                ';
                if(!$db->query($q)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                }
            }
            
            
            
            if($success === 1){
                $q = '';
                switch($purchase_receipt_allocation_type){
                    case 'purchase_invoice':
                        $q = '
                            update purchase_invoice 
                            set outstanding_amount = outstanding_amount + '.$db->escape($allocated_amount).'
                                ,modid='.$db->escape($modid).'
                                ,moddate='.$db->escape($moddate).'
                            where id = '.$db->escape($purchase_receipt_allocation['purchase_invoice_id']).'
                        ';
                        break;
                    case 'customer_bill':
                        $q = '
                            update customer_bill 
                            set outstanding_amount = outstanding_amount + '.$db->escape($allocated_amount).'
                                ,modid='.$db->escape($modid).'
                                ,moddate='.$db->escape($moddate).'
                            where id = '.$db->escape($purchase_receipt_allocation['customer_bill_id']).'
                        ';
                        break;                    
                }
                if(!$db->query($q)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                }
                
            }
            
            $result['success'] = $success;
            $result['msg'] = $msg;
            return $result;
        }
        
    }
?>