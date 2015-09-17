<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Purchase_Receipt_Allocation_Engine_old {
        
        public static function path_get(){
            return (object) array(
                'index'=>get_instance()->config->base_url().'purchase_receipt_allocation/'
                ,'ajax_search'=>get_instance()->config->base_url().'purchase_receipt_allocation/ajax_search/'
                
            );
        }
        
        public static function get($id=""){
            $db = new DB();
            $q = "select * 
                from purchase_receipt_allocation
                where status>0 and id = ".$db->escape($id);
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $rs = $rs[0];
            else $rs = null;
            return $rs;
        }
        
        
        public static function validate($method,$data=array()){
            $result = array(
                "success"=>1
                ,"msg"=>array()
            );
            $purchase_receipt_allocation = $data['purchase_receipt_allocation'];
            switch($method){
                case 'insert':                    
                    
                    if(strlen($purchase_receipt_allocation['purchase_receipt_id']) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Purchase Receipt cannot be empty";
                    }
                    
                    if(strlen($purchase_receipt_allocation['purchase_invoice_id']) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Purchase Invoice cannot be empty";
                    }
                    
                    if(floatval(str_replace(',','',$purchase_receipt_allocation['allocated_amount'])) <1){
                        $result['success'] = 0;
                        $result['msg'][] = "Allocated Amount cannot be less than 1";
                    }
                    
                    $temp_data = array(
                        'purchase_receipt_id'=>$purchase_receipt_allocation['purchase_receipt_id']
                        ,'purchase_invoice_id'=>$purchase_receipt_allocation['purchase_invoice_id']
                    );
                    
                    if(floatval(str_replace(',','',$purchase_receipt_allocation['allocated_amount']))
                            > self::purchase_receipt_allocation_outstanding_amount_get($temp_data)['outstanding_amount'] ){
                        $result['success'] = 0;
                        $result['msg'][] = "Allocated Amount cannot be higher than available amount";
                    }
                    
                    
                    break;
                    
                case 'update':
                    $purchase_receipt_allocation_data = self::get($purchase_receipt_allocation['id']);
                    if($purchase_receipt_allocation_data != null){
                        if($purchase_receipt_allocation_data->purchase_receipt_allocation_status == 'X'){
                            $result['success'] = 0;
                            $result['msg'][] = 'Cannot update Cancelled Purchase Receipt Allocation data';
                        }
                    }
                    else{
                        $result['success'] = 0;
                        $result['msg'][] = 'Invalid Purchase Receipt Allocation';
                    }
                    break;
                    
                case 'cancel':
                    $purchase_receipt_allocation_data = self::get($purchase_receipt_allocation['id']);
                    if($purchase_receipt_allocation_data != null){
                        if($purchase_receipt_allocation_data->purchase_receipt_allocation_status == 'X'){
                            $result['success'] = 0;
                            $result['msg'][] = 'Cannot cancel Cancelled Purchase Receipt Allocation data';
                        }
                    }
                    else{
                        $result['success'] = 0;
                        $result['msg'][] = 'Invalid Purchase Receipt Allocation';
                    }
                    
                    if(strlen(str_replace(' ','',$purchase_receipt_allocation['cancellation_reason'])) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Cancellation Reason is required';
                    }
                    break;
                    
               
            }
            
            return $result;
        }
        
        public static function adjust($action,$data=array()){
            $db = new DB();
            $result = array(
                'purchase_receipt_allocation'=>array(
                    'purchase_receipt_id'=>''
                    ,'purchase_invoice_id'=>''
                    ,'allocated_amount'=>''
                )
            );
            
            $pra = $data['purchase_receipt_allocation'];
            
            switch($action){
                case 'insert':
                    $rs = $db->query_array_obj('select func_code_counter("purchase_receipt_allocation") "code"');
                    $result['purchase_receipt_allocation']['code'] = $rs[0]->code;
                    $result['purchase_receipt_allocation']['purchase_receipt_allocation_status']='I';
                    $result['purchase_receipt_allocation']['purchase_receipt_id']=$pra['purchase_receipt_id'];
                    $result['purchase_receipt_allocation']['purchase_invoice_id']=$pra['purchase_invoice_id'];
                    $result['purchase_receipt_allocation']['allocated_amount']=$pra['allocated_amount'];
                    break;
                    
                case 'update':
                    $result = array(
                        'purchase_receipt_allocation'=>array(
                            'notes'=>$pra['notes']
                        )
                    );
                    break;
                case 'cancel':
                    $result = array(
                        'purchase_receipt_allocation'=>array(
                            'notes'=>$pra['notes']
                            ,'purchase_receipt_allocation_status'=>'X'
                            ,'cancellation_reason'=>$pra['cancellation_reason']
                        )
                    );
                    break;
            }
            
            return $result;
        }
        
        public static function save($data){
            $db = new DB();
            $success = 1;
            $msg = array();
            $action = "";
            $result = array("success"=>0,"msg"=>array(),'trans_id'=>'');
            $purchase_receipt_allocation_data = $data['purchase_receipt_allocation'];
            $id = $purchase_receipt_allocation_data['id'];
            
           if(strlen($id)==0){
                $action = "insert";
            }
            else{
                $action = "update";
                if(isset($purchase_receipt_allocation_data['purchase_receipt_allocation_status'])){
                    if($purchase_receipt_allocation_data['purchase_receipt_allocation_status'] == 'X') $action = "cancel";
                }                
            }
            
            if(in_array($action,array("insert","update","cancel"))){
                $validation_res = self::validate($action,$data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            else{
                $success = 0;
                $msg[] = 'Unknown data format';
            }

            if($success == 1){
                $final_data = self::adjust($action,$data);
                $modid = User_Info::get()['user_id'];
                $moddate = date("Y-m-d H:i:s");
                
                switch($action){                    
                    case 'insert':
                        try{
                            $db->trans_begin();
                            $fpurchase_receipt_allocation = array_merge($final_data['purchase_receipt_allocation'],array("modid"=>$modid,"moddate"=>$moddate));
                            $purchase_receipt_allocation_id = '';
                            if(!$db->insert('purchase_receipt_allocation',$fpurchase_receipt_allocation)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $q = '
                                    update purchase_receipt
                                    set allocated_amount = allocated_amount + '.$fpurchase_receipt_allocation['allocated_amount'].'
                                    where id = '.$db->escape($fpurchase_receipt_allocation['purchase_receipt_id']).'
                                ';
                                if(!$db->query($q)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }                                
                            }
                            
                            if($success == 1){
                                $q = '
                                    select id 
                                    from purchase_receipt_allocation
                                    where status>0 
                                        and purchase_receipt_allocation_status = "I" 
                                        and code = '.$db->escape($fpurchase_receipt_allocation['code']).'
                                ';
                                $rs_purchase_receipt_allocation = $db->query_array_obj($q);
                                $purchase_receipt_allocation_id = $rs_purchase_receipt_allocation[0]->id;
                                $result['trans_id']=$purchase_receipt_allocation_id; // useful for view forwarder
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Purchase Receipt Allocation Success';
                            }
                        }
                        catch(Exception $e){
                            
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }
                        break;
                    case 'update':
                        try{
                            $db->trans_begin();
                            $fpurchase_receipt_allocation = array_merge($final_data['purchase_receipt_allocation'],array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('purchase_receipt_allocation',$fpurchase_receipt_allocation,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $result['trans_id']=$id;
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Update Purchase Receipt Allocation Success';
                            }
                        }
                        catch(Exception $e){
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }                        
                        
                        break;
                    case 'cancel':
                        try{
                            $db->trans_begin();
                            $po = array_merge($final_data['purchase_receipt_allocation'],array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('purchase_receipt_allocation',$po,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $result['trans_id']=$id;
                            if($success == 1){
                                $q = '
                                    update purchase_receipt t1
                                    inner join purchase_receipt_allocation  t2 on t1.id = t2.purchase_receipt_id
                                    set t1.allocated_amount = t1.allocated_amount - t2.allocated_amount
                                    where t2.id = '.$db->escape($id).'
                                ';
                                if(!$db->query($q)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                                
                            }
                            
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
        
        public static function modal_purchase_receipt_allocation_render($app,$modal,$data){
            $modal->header_set(array('title'=>'Purchase Receipt Allocation','icon'=>App_Icon::info()));
            $components = self::components_purchase_receipt_allocation_render($app, $modal, $data,true);
            $path = self::path_get();
            $modal->modal_button_footer_add(
                    "purchase_receipt_allocation_submit"
                    ,'button'
                    ,''
                    ,App_Icon::detail_btn_save()
                    ,'Submit'
                );
            $param = array(
                    'ajax_url'=>$path->index.'ajax_search/'
                );
            $js = get_instance()->load->view('purchase_receipt_allocation/modal_purchase_receipt_allocation_js',$param,TRUE);
            $app->js_set($js);
        }
        
        
        public static function components_purchase_receipt_allocation_render($app,$form,$data,$is_modal){
            $components = array();
            $path = self::path_get();
            $form->input_add()->input_set('id','purchase_receipt_allocation_method')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;

            
            
            $components['id'] = $form->input_add()->input_set('id','purchase_receipt_allocation_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;

            $supplier_detail = array(
                array('name'=>'code','label'=>'Code')
                ,array('name'=>'name','label'=>'Name')
                ,array('name'=>'phone','label'=>'Phone')
            );
            
            $form->input_select_detail_add()
                    ->input_select_set('label','Supplier')
                    ->input_select_set('icon','fa fa-user')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','purchase_receipt_allocation_supplier')
                    ->input_select_set('min_length','1')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url',$path->ajax_search.'detail_supplier_search')
                    ->detail_set('rows',$supplier_detail)
                    ->detail_set('id',"purchase_receipt_allocation_supplier_detail")
                    ->detail_set('ajax_url',$path->ajax_search.'detail_supplier_get')
                ;
            
            $purchase_receipt_detail = array(
                array('name'=>'code','label'=>'Code')
                ,array('name'=>'amount','label'=>'Amount('.Tools::currency_get().')')
                ,array('name'=>'outstanding_amount','label'=>'Outstanding Amount('.Tools::currency_get().')')
                ,array('name'=>'payment_type_name','label'=>'Payment Type')
            );
            
            $components['purchase_receipt'] = $form->input_select_detail_add()
                    ->input_select_set('label','Purchase Receipt')
                    ->input_select_set('icon','fa fa-user')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','purchase_receipt_allocation_purchase_receipt')
                    ->input_select_set('min_length','1')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url',$path->ajax_search.'detail_purchase_receipt_search')
                    ->detail_set('rows',$purchase_receipt_detail)
                    ->detail_set('id',"purchase_receipt_allocation_purchase_receipt_detail")
                    ->detail_set('ajax_url',$path->ajax_search.'detail_purchase_receipt_get')
                ;
            
            $purchase_invoice_detail = array(
                array('name'=>'code','label'=>'Code')
                ,array('name'=>'grand_total','label'=>'Grand Total('.Tools::currency_get().')')
                ,array('name'=>'outstanding_amount','label'=>'Outstanding Amount('.Tools::currency_get().')')
            );
            
            $components['purchase_invoice'] = $form->input_select_detail_add()
                    ->input_select_set('label','Purchase Invoice')
                    ->input_select_set('icon','fa fa-user')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','purchase_receipt_allocation_purchase_invoice')
                    ->input_select_set('min_length','1')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url',$path->ajax_search.'detail_purchase_invoice_search')
                    ->detail_set('rows',$purchase_invoice_detail)
                    ->detail_set('id',"purchase_receipt_allocation_purchase_invoice_detail")
                    ->detail_set('ajax_url',$path->ajax_search.'detail_purchase_invoice_get')
                ;
            
            $components['purchase_receipt_status'] = $form->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-user')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','purchase_receipt_allocation_purchase_receipt_allocation_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ;
            
            $components['cancellation_reason']=$form->textarea_add()->textarea_set('label','Cencellation Reason')
                    ->textarea_set('id','purchase_receipt_allocation_cancellation_reason')
                    ->textarea_set('value','')
                    ->div_set('id','purchase_receipt_allocation_div_cancellation_reason')                    
                    ;
            
            $components['outstanding_amount'] = $form->input_add()->input_set('id','purchase_receipt_allocation_outstanding_amount')
                    ->input_set('label','Outstanding Amount (Rp.)')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->input_set('value','')                    
                    ;
            
            $components['allocated_amount'] = $form->input_add()->input_set('id','purchase_receipt_allocation_allocated_amount')
                    ->input_set('label','Allocated Amount (Rp.)')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->input_set('value','')                    
                    ;

            $components['notes'] = $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','purchase_receipt_allocation_notes')
                    ->textarea_set('value','')
                    ->textarea_set('attrib',array())                    
                    ;
            $param = array(
                'ajax_url'=>$path->index.'ajax_search/'
                ,'index_url'=>$path->index
                ,'detail_tab'=>'#detail_tab'
                ,'view_url'=>$path->index.'view/'
                ,'window_scroll'=>'body'
                ,'data_support_url'=>$path->index.'data_support/'
            );
            
            if($is_modal){
                $param['detail_tab'] = '#modal_purchase_receipt_allocation';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_purchase_receipt_allocation';
            }
            
            $js = get_instance()->load->view('purchase_receipt_allocation/purchase_receipt_allocation_basic_function_js',$param,TRUE);
            $app->js_set($js);
            return $components;
            
        }
        
        public static function purchase_receipt_allocation_outstanding_amount_get($data){
            // the most important procedure in purchase receipt allocation
            $purchase_receipt_id = '';
            $purchase_invoice_id = '';
            $cont = true;
            if(isset($data['purchase_receipt_id'])) $purchase_receipt_id = $data['purchase_receipt_id'];
            else $cont = false;
            
            if(isset($data['purchase_invoice_id'])) $purchase_invoice_id = $data['purchase_invoice_id'];
            else $cont = false;
            
            
            $result['outstanding_amount'] = 0;
            
            if($cont){
                $db = new DB();
                $purchase_receipt_outstanding_amount = 0;
                $purchase_invoice_outstanding_amount = 0;
                $q = '
                    select amount - allocated_amount outstanding_amount
                    from purchase_receipt
                    where id = '.$db->escape($purchase_receipt_id).'
                ';
                $rs = $db->query_array_obj($q);
                if(count($rs)>0){
                    $purchase_receipt_outstanding_amount = $rs[0]->outstanding_amount;
                }
                $q = '
                    select purchase_invoice_outstanding_amount_get('.$db->escape($purchase_invoice_id).') outstanding_amount
                ';
                $rs = $db->query_array_obj($q);
                if(count($rs)>0){
                    $purchase_invoice_outstanding_amount = $rs[0]->outstanding_amount;
                }
                
                if($purchase_receipt_outstanding_amount<$purchase_invoice_outstanding_amount){
                    $result['outstanding_amount'] = $purchase_receipt_outstanding_amount;
                }
                else if ($purchase_invoice_outstanding_amount<$purchase_receipt_outstanding_amount){
                    $result['outstanding_amount'] = $purchase_invoice_outstanding_amount;
                }
                $result['outstanding_amount'] = Tools::round($result['outstanding_amount'],2);
            }
            
            return $result;
        }
        
        
    }
?>
