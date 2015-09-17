<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Purchase_Receipt_Engine_old {
        
        public static function get($id=""){
            $db = new DB();
            $q = "select t1.*, t2.code supplier_code
                from purchase_receipt t1
                    inner join supplier t2 on t1.supplier_id = t2.id
                where t1.status>0 and t1.id = ".$db->escape($id);
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
            $purchase_receipt = $data['purchase_receipt'];
            switch($method){
                case 'insert':                    
                    
                    $store_id = isset($purchase_receipt['store_id'])?$purchase_receipt['store_id']:'';
                    if(strlen($store_id) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Store cannot be empty";
                    }
                    
                    $payment_type_id = isset($purchase_receipt['payment_type_id'])?$purchase_receipt['payment_type_id']:'';
                    if(strlen($payment_type_id) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Payment Type"." "."empty";
                    }
                    
                    if(strlen($purchase_receipt['purchase_receipt_date']) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Date cannot be empty";
                    }
                    
                    if(strlen($purchase_receipt['supplier_id']) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Supplier cannot be empty";
                    }
                    
                    if(floatval(str_replace(',','',$purchase_receipt['amount'])) <1){
                        $result['success'] = 0;
                        $result['msg'][] = "Amount cannot be less than 1";
                    }
                    
                    
                    break;
                    
                case 'update':
                    $purchase_receipt_data = self::get($purchase_receipt['id']);
                    if($purchase_receipt_data != null){
                        if($purchase_receipt_data->purchase_receipt_status == 'X'){
                            $result['success'] = 0;
                            $result['msg'][] = 'Cannot update Cancelled Purchase Receipt data';
                        }
                    }
                    else{
                        $result['success'] = 0;
                        $result['msg'][] = 'Invalid Purchase Receipt';
                    }
                    break;
                    
                case 'cancel':
                    $purchase_receipt_data = self::get($purchase_receipt['id']);
                    if($purchase_receipt_data->allocated_amount != 0){
                            $result['success'] = 0;
                            $result['msg'][] = 'Cannot cancel Purchase Receipt with allocated amount';
                    }
                    
                    if($purchase_receipt_data != null){
                        if($purchase_receipt_data->purchase_receipt_status == 'X'){
                            $result['success'] = 0;
                            $result['msg'][] = 'Cannot cancel Cancelled Purchase Receipt data';
                        }
                    }
                    else{
                        $result['success'] = 0;
                        $result['msg'][] = 'Invalid Purchase Order';
                    }
                    
                    if(strlen(str_replace(' ','',$purchase_receipt['cancellation_reason'])) == 0){
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
                'purchase_receipt'=>array(
                    'store_id'=>''
                    ,'code'=>''
                    ,'purchase_receipt_status'=>''
                    ,'status'=>''
                    ,'notes'=>''
                    ,'id'=>''
                    ,'purchase_receipt_date'=>''
                    ,'amount'=>'0'
                    ,'payment_type_id'=>''
                    ,'bank_acc'=>''
                    ,'bank_acc_supplier'=>''
                )
            );
            $po = null;
            if (isset($data['po'])) $po = $data['po'];
            
            $purchase_receipt = $data['purchase_receipt'];
            
            switch($action){
                case 'insert':
                    unset($result['purchase_receipt']['id']);
                    
                    $result['purchase_receipt']['store_id'] = $purchase_receipt['store_id'];
                    $result['purchase_receipt']['code'] = '';
                    $result['purchase_receipt']['purchase_receipt_status']='I';
                    $result['purchase_receipt']['status']='1';
                    $result['purchase_receipt']['notes']=$purchase_receipt['notes'];
                    $result['purchase_receipt']['purchase_receipt_date']=$purchase_receipt['purchase_receipt_date'];
                    $result['purchase_receipt']['amount']=str_replace(',','',$purchase_receipt['amount']);
                    $result['purchase_receipt']['supplier_id']=str_replace(',','',$purchase_receipt['supplier_id']);
                    $result['purchase_receipt']['payment_type_id']=$purchase_receipt['payment_type_id'];
                    $result['purchase_receipt']['bank_acc']=$purchase_receipt['bank_acc'];
                    $result['purchase_receipt']['bank_acc_supplier']=$purchase_receipt['bank_acc_supplier'];
                    break;
                    
                case 'update':
                    $result = array(
                        'purchase_receipt'=>array(
                            'notes'=>$purchase_receipt['notes']
                        )
                    );
                    break;
                case 'cancel':
                    $result = array(
                        'purchase_receipt'=>array(
                            'notes'=>$purchase_receipt['notes']
                            ,'purchase_receipt_status'=>'X'
                            ,'cancellation_reason'=>$purchase_receipt['cancellation_reason']
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
            $result = array("status"=>0,"msg"=>array(),'trans_id'=>'');
            $purchase_receipt_data = $data['purchase_receipt'];
            $id = $purchase_receipt_data['id'];
            
           if(strlen($id)==0){
                $action = "insert";
            }
            else{
                $action = "update";
                if(isset($purchase_receipt_data['purchase_receipt_status'])){
                    if($purchase_receipt_data['purchase_receipt_status'] == 'X') $action = "cancel";
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
                            $fpurchase_receipt = array_merge($final_data['purchase_receipt'],array("modid"=>$modid,"moddate"=>$moddate));
                            $purchase_receipt_id = '';
                            $rs = $db->query_array_obj('select func_code_counter_store("purchase_receipt",'.$db->escape($fpurchase_receipt['store_id']).') "code"');
                            $fpurchase_receipt['code'] = $rs[0]->code;
                            if(!$db->insert('purchase_receipt',$fpurchase_receipt)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $q = '
                                    select id 
                                    from purchase_receipt 
                                    where status>0 
                                        and purchase_receipt_status = "I" 
                                        and code = '.$db->escape($fpurchase_receipt['code']).'
                                ';
                                $rs_purchase_receipt = $db->query_array_obj($q);
                                $purchase_receipt_id = $rs_purchase_receipt[0]->id;
                                $result['trans_id']=$purchase_receipt_id; // useful for view forwarder
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Purchase Receipt Success';
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
                            $fpurchase_receipt = array_merge($final_data['purchase_receipt'],array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('purchase_receipt',$fpurchase_receipt,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $result['trans_id']=$id;
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Update Purchase Order Success';
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
                            $po = array_merge($final_data['purchase_receipt'],array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('purchase_receipt',$po,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $result['trans_id']=$id;
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Cancel Purchase Order Success';
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
        
        public static function modal_purchase_receipt_render($app,$modal,$data){
            $modal->header_set(array('title'=>'Purchase Receipt','icon'=>App_Icon::info()));
            $components = self::components_purchase_receipt_render($app, $modal, $data);
            $components['purchase_receipt_product']->div_set('class','hide');
            $components['supplier']->detail_set('ajax_url',get_instance()->config->base_url().'purchase_receipt/ajax_search/detail_supplier_get');
            $modal->modal_button_footer_add(
                    "purchase_receipt_button_save"
                    ,'button'
                    ,''
                    ,  App_Icon::detail_btn_save()
                    ,'Submit'
                );
        }
        
        public static function purchase_receipt_render($app,$form,$data,$path,$method){
            $id = $data['id'];
            $components = self::components_purchase_receipt_render($app, $form, $data);
            $data_detail = array(
                'id'=>'' // harus di-assign dari sini karena di isikan ke hidden input
                ,'store'=>''
                ,'code'=>''
                ,'purchase_receipt_date'=>''
                ,'amount'=>'0'
                ,'allocated_amount'=>'0'
                ,'available_amount'=>'0'
                ,'purchase_receipt_status'=>array()
                ,'cancellation_reason'=>''
                ,'notes'=>''
                ,'supplier'=>array()
                ,'payment_type'=>array()
                ,'bank_acc'=>''
                ,'bank_acc_supplier'=>''
            );
            $data_detail = json_decode(json_encode($data_detail));
            
            $list_detail = array(
                'purchase_receipt_status'=>array()
            );
            $list_detail = json_decode(json_encode($list_detail));
            
            $form->hr_add();
            $db = new DB();
            switch($method){
                case 'Add':
                    self::purchase_receipt_data_set($method,$id, $data_detail);
                    $list_detail->purchase_receipt_status = array(array('id'=>'I','data'=>'INVOICED'));
                    
                    $components['cancellation_reason']->div_set('class','hide');
                    $components['purchase_receipt_bank_acc']->input_set('hide',true);
                    $components['purchase_receipt_bank_acc_supplier']->input_set('hide',true);
                    $form->button_add()->button_set('value','Submit')
                            ->button_set('id','purchase_receipt_submit')
                            ->button_set('icon',App_Icon::detail_btn_save())
                        ;
                    
                    break;
                case 'View':
                    //set data                    
                    self::purchase_receipt_data_set($method,$id,$data_detail);
                    if($data_detail->purchase_receipt_status['id'] === 'I'){
                        $list_detail->purchase_receipt_status = array(
                            array('id'=>'I','data'=> SI::get_status_attr('INVOICED'))
                            ,array('id'=>'X','data'=>SI::get_status_attr('CANCELED'))
                        );
                    }
                    else{
                        $list_detail->purchase_receipt_status = array(
                            array('id'=>'X','data'=>SI::get_status_attr('CANCELED'))
                        );
                    }
                    //end of set data
                    
                    //additional setting
                    $disable = array('disabled'=>'');
                    $purchase_receipt = self::purchase_receipt_get($id);
                    $components['store']->input_select_set('attrib',$disable);
                    $components['supplier']->input_select_set('attrib',$disable);
                    $components['purchase_receipt_date']->input_set('attrib',$disable);
                    if($purchase_receipt->purchase_receipt_status !== 'X'){
                        $components['cancellation_reason']->div_set('class','hidden');
                    }

                    $components['amount']->input_set('attrib',$disable);
                    $components['allocated_amount']->input_set('attrib',$disable);
                    $components['available_amount']->input_set('attrib',$disable);
                    if($data_detail->payment_type['id'] === '1'){
                        $components['purchase_receipt_bank_acc']->input_set('hide',true);
                    }
                    else{
                        $components['purchase_receipt_bank_acc']->input_set('hide',false);
                    }
                    $components['purchase_receipt_bank_acc']->input_set('attrib',$disable);
                    $components['purchase_receipt_bank_acc_supplier']->input_set('attrib',$disable);
                    $components['purchase_receipt_payment_type']->input_select_set('attrib',$disable);
                    
                    
                    $generate_submit = false;
                    
                    if(Security_Engine::get_controller_permission(
                            User_Info::get()['user_id']
                            ,'purchase_receipt' 
                            ,'edit')
                    ){
                        if($purchase_receipt->purchase_receipt_status !=='X'){
                            $generate_submit = true;                        
                        }
                    }                    

                    if($generate_submit){
                        $form->button_add()->button_set('value','Submit')
                            ->button_set('id','purchase_receipt_submit')
                            ->button_set('icon',App_Icon::detail_btn_save())
                            ;
                    }
                    else{
                        $components['purchase_receipt_status']->input_select_set('attrib',$disable);
                        $components['cancellation_reason']->textarea_set('attrib',$disable);
                        $components['notes']->textarea_set('attrib',$disable);
                    }
                    
                    

                    break;
                
            }
            
            // <editor-fold defaultstate="collapsed" desc="bind value and draw button">
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$path->index)
                ->button_set('class','btn btn-default')
                ;
            
            $components['id']
                    ->input_set('value',$data_detail->id)
                ;
            $components['code']
                    ->input_set('value',$data_detail->code)
                ;
            
            
            $components['store']
                    ->input_select_set('value',$data_detail->store);
            
            $components['supplier']
                    ->input_select_set('value',$data_detail->supplier)
                    ->input_select_set('ajax_url',$path->index.'ajax_search/detail_supplier_search')
                    ->detail_set('ajax_url',$path->index.'ajax_search/detail_supplier_get')
                ;
            $components['purchase_receipt_date']
                    ->input_set('value',$data_detail->purchase_receipt_date)
                ;
            $components['purchase_receipt_status']
                    ->input_select_set('value',$data_detail->purchase_receipt_status)
                    ->input_select_set('data_add',$list_detail->purchase_receipt_status)
                ;

            $components['cancellation_reason']
                    ->textarea_set('value',$data_detail->cancellation_reason)
                ;
            $components['amount']
                    ->input_set('value', Tools::thousand_separator($data_detail->amount,2,true))
                ;
            $components['allocated_amount']
                    ->input_set('value', Tools::thousand_separator($data_detail->allocated_amount,2,true))
                ;
            $components['available_amount']
                    ->input_set('value', Tools::thousand_separator($data_detail->available_amount,2,true))
                ;
           $components['notes']
                    ->textarea_set('value', $data_detail->notes)
                ; 
           $components['purchase_receipt_payment_type']->input_select_set('value',$data_detail->payment_type);
           
           $components['purchase_receipt_bank_acc']->input_set('value',$data_detail->bank_acc);
           $components['purchase_receipt_bank_acc_supplier']->input_set('value',$data_detail->bank_acc_supplier);
            //</editor-fold>
            
            
            
            $js = '<script>$("#purchase_receipt_method").val("'.$method.'");</script>';             
            $app->js_set($js);
            
            $param = array(
                'ajax_url'=>$path->ajax_search
                ,'index_url'=>$path->index
                ,'detail_tab'=>'#detail_tab'
                ,'view_url'=>$path->index.'view/'
                ,'window_scroll'=>'body'
            );
            $js = get_instance()->load->view('transaction/purchase/receipt/purchase_receipt_js',$param,TRUE);
            $app->js_set($js);
            
            
        }
        
        public static function receipt_allocation_view_render($app,$pane,$data,$path){
            $id = $data['id'];
            $purchase_receipt = self::get($id);
            $pane->form_group_add();
            if($purchase_receipt->purchase_receipt_status != 'X'){
                if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'purchase_receipt_allocation','add')){
                $pane->button_add()->button_set('class','primary')
                        ->button_set('value','New Receipt Allocation')
                        ->button_set('icon','fa fa-plus')
                        ->button_set('attrib',array(
                            'data-toggle'=>"modal" 
                            ,'data-target'=>"#modal_purchase_receipt_allocation"
                        ))
                        ->button_set('disable_after_click',false)
                        ->button_set('id','purchase_receipt_allocation_new')
                    ;
                }
            }
            $pane->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
            $tbl = $pane->table_add();
            $tbl->table_set('class','table');
            $tbl->table_set('id','purchase_receipt_allocation_table');
            $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center'),"is_key"=>true));            
            $tbl->table_set('columns',array("name"=>"purchase_invoice_code","label"=>"Purchase Invoice <br/> Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"purchase_receipt_code","label"=>"Purchase Receipt <br/> Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"allocated_amount","label"=>"Allocated <br/> Amount (Rp.)",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"purchase_receipt_allocation_status_name","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('data key','id');
            
            $db = new DB();
            $q = '
                select distinct NULL row_num
                    ,case t1.purchase_receipt_allocation_status 
                        when "O" then "OPENED" 
                        when "X" then "CANCELED" 
                        when "I" then "INVOICED" 
                    end purchase_receipt_allocation_status_name
                    ,t1.allocated_amount
                    ,t2.code purchase_receipt_code
                    ,t3.code purchase_invoice_code
                    ,t1.id
                    ,t1.moddate
                from purchase_receipt_allocation t1
                    inner join purchase_receipt t2 on t1.purchase_receipt_id = t2.id
                    inner join purchase_invoice t3 on t1.purchase_invoice_id = t3.id
                where t2.id = '.$id.' order by t1.moddate desc

            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['allocated_amount'] = Tools::thousand_separator($rs[$i]['allocated_amount'],2,true);
                $rs[$i]['purchase_receipt_allocation_status_name'] = SI::get_status_attr($rs[$i]['purchase_receipt_allocation_status_name']);
            }
            $tbl->table_set('data',$rs);
            
            
            $modal_purchase_receipt_allocation = $app->engine->modal_add()->id_set('modal_purchase_receipt_allocation')->width_set('75%');
            
            $po = self::get($id);
            
            $purchase_receipt_allocation_data = array(
                'purchase_receipt'=>array(
                    'id'=>$po->id
                )                
            );
            $purchase_receipt_allocation_data = json_decode(json_encode($purchase_receipt_allocation_data));

            Purchase_Receipt_Allocation_Engine::modal_purchase_receipt_allocation_render(
                    $app
                    ,$modal_purchase_receipt_allocation
                    ,$purchase_receipt_allocation_data
                );
            
            
            $param = array(
                'index_url'=>$path->index
                ,'ajax_search'=>$path->ajax_search
                ,'purchase_receipt_id'=>$purchase_receipt->id
                ,'purchase_receipt_code'=>$purchase_receipt->code
                ,'purchase_receipt_supplier_id'=>$purchase_receipt->supplier_id
                ,'purchase_receipt_supplier_code'=>$purchase_receipt->supplier_code
            );

            $js = get_instance()->load->view('transaction/purchase/receipt/purchase_receipt_allocation_js',$param,TRUE);
            $app->js_set($js);
            
        }
        
        
        public static function purchase_receipt_data_set($method,$id,$data_detail){
            $result = null;
            $db = new DB();
            
            if($method === 'Add'){
                $data_detail->code = '[AUTO GENERATE]';
                $data_detail->purchase_receipt_date = date('Y-m-d');
                $data_detail->purchase_receipt_status = array('id'=>'I','data'=>'INVOICED');     
                
                $default_store = array('id'=>'','data'=>'');
                $store_id = isset(User_Info::get()['default_store_id'])?User_Info::get()['default_store_id']:'';
                $q = 'select id id, name data from store where status>0 and id = '.$db->escape($store_id);            
                $rs_default_store = $db->query_array_obj($q);
                if(count($rs_default_store)>0){
                    $default_store['id'] = $rs_default_store[0]->id;
                    $default_store['data'] = $rs_default_store[0]->data;
                }
                $data_detail->store = $default_store;
                
                
                
                
            }
            else if (in_array($method,array('View')) ){

                $purchase_receipt = self::purchase_receipt_get($id);
                $data_detail->id = $id;
                $data_detail->code = $purchase_receipt->code;
                $data_detail->supplier = array('id'=>$purchase_receipt->supplier_id,'data'=>$purchase_receipt->supplier_name);
                $data_detail->purchase_receipt_date = $purchase_receipt->purchase_receipt_date;
                $purchase_receipt_status_name = SI::get_status_attr($purchase_receipt->purchase_receipt_status_name);
                $data_detail->purchase_receipt_status = array("id"=>$purchase_receipt->purchase_receipt_status,"data"=>$purchase_receipt_status_name);
                $data_detail->cancellation_reason = $purchase_receipt->cancellation_reason;
                $data_detail->notes = $purchase_receipt->notes;
                $data_detail->amount = $purchase_receipt->amount;
                $data_detail->allocated_amount = $purchase_receipt->allocated_amount;
                $data_detail->available_amount = $purchase_receipt->available_amount;
                $data_detail->payment_type = array('id'=>$purchase_receipt->payment_type_id, 'data'=>$purchase_receipt->payment_type_name);
                $data_detail->bank_acc = $purchase_receipt->bank_acc;
                $data_detail->bank_acc_supplier = $purchase_receipt->bank_acc_supplier;
                $data_detail->store = array('id'=>$purchase_receipt->store_id,'data'=>$purchase_receipt->store_name);
            }
            
        }
        
        public static function purchase_receipt_get($id){
            $db = new DB();
            $q = '
                select t1.code, date(t1.purchase_receipt_date) purchase_receipt_date
                    ,t1.purchase_receipt_status
                    ,case t1.purchase_receipt_status 
                        when "O" then "OPENED"
                        when "I" then "INVOICED"
                        when "X" then "CANCELED"
                        end purchase_receipt_status_name
                     ,t1.cancellation_reason
                     ,t1.notes
                     ,t1.amount
                     ,t1.allocated_amount
                     ,t1.amount - t1.allocated_amount available_amount
                     ,t1.supplier_id
                     ,t2.name supplier_name
                     ,t1.bank_acc
                     ,t1.bank_acc_supplier
                     ,t1.payment_type_id
                     ,t3.code payment_type_name
                     ,t1.store_id
                     ,t4.name store_name
                from purchase_receipt t1
                    inner join supplier t2 on t1.supplier_id = t2.id
                    inner join payment_type t3 on t3.id = t1.payment_type_id
                    inner join store t4 on t4.id = t1.store_id
                where t1.id = '.$db->escape($id)
                ;
            $rs = $db->query_array_obj($q);
            return $rs[0];
        }
        
        public static function components_purchase_receipt_render($app,$form,$data){
            $po_id = '';
            if(isset($data->po->id)) $po_id = $data->po->id;                        
            $components = array();
            
            $components['id'] = $form->input_add()->input_set('id','purchase_receipt_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $form->input_add()->input_set('id','purchase_receipt_method')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;

            $db = new DB();
            $store_list = array();
            $q = 'select id id, name data from store where status>0';            
            $store_list = $db->query_array($q);
            
            $components['store'] = $form->input_select_add()
                    ->input_select_set('label','Store')
                    ->input_select_set('icon',App_Icon::store())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','purchase_receipt_store')
                    ->input_select_set('data_add',$store_list)
                    ->input_select_set('value',array())
                    ->div_set('id','purchase_receipt_div_store')
                                        
                ;
            
            $components['code'] = $form->input_add()->input_set('id','purchase_receipt_code')
                    ->input_set('label','Purchase Receipt Code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->input_set('value','')                    
                    ;
            
            $supplier_detail = array(
                array('name'=>'code','label'=>'Code')
                ,array('name'=>'name','label'=>'Name')
                ,array('name'=>'address','label'=>'Address')
            );
            
            $components['supplier'] = $form->input_select_detail_add()
                    ->input_select_set('label','Supplier')
                    ->input_select_set('icon','fa fa-user')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','purchase_receipt_supplier')
                    ->input_select_set('min_length','1')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->detail_set('rows',$supplier_detail)
                    ->detail_set('id',"purchase_receipt_supplier_detail")
                    ->detail_set('ajax_url','')
                ;
            
            $components['purchase_receipt_date']=$form->input_add()->input_set('label','Purchase Receipt Date')
                    ->input_set('id','purchase_receipt_purchase_receipt_date')
                    ->input_set('is_date_picker',true)
                    ->input_set('icon','fa fa-calendar')
                    ->input_set('value','')                    
                    ;
            
            $db = new DB();
            $q = 'select id id, code data from payment_type ';
            $rs = $db->query_array($q);
            
            
            $components['purchase_receipt_payment_type'] = $form->input_select_add()
                    ->input_select_set('label','Payment Type')
                    ->input_select_set('icon','fa fa-user')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','purchase_receipt_payment_type')
                    ->input_select_set('data_add',$rs)
                    ->input_select_set('value',array())
                    ;
            
            $components['purchase_receipt_bank_acc']=$form->input_add()->input_set('label','My Bank Account')
                    ->input_set('id','purchase_receipt_bank_acc')
                    ->input_set('icon','fa fa-dollar')
                    ->input_set('value','') 
                    ->div_set('id','purchase_receipt_div_bank_acc')
                    //->input_set('hide',true)
                    ;
            
            $components['purchase_receipt_bank_acc_supplier']=$form->input_add()->input_set('label','Supplier\'s Bank Account')
                    ->input_set('id','purchase_receipt_bank_acc_supplier')
                    ->input_set('icon','fa fa-dollar')
                    ->input_set('value','') 
                    ->div_set('id','purchase_receipt_div_bank_acc_supplier')
                    //->input_set('hide',true)
                    ;
            
            $components['purchase_receipt_status'] = $form->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-user')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','purchase_receipt_purchase_receipt_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ;
            
            $components['cancellation_reason']=$form->textarea_add()->textarea_set('label','Cencellation Reason')
                    ->textarea_set('id','purchase_receipt_cancellation_reason')
                    ->textarea_set('value','')
                    ->div_set('id','purchase_receipt_div_cancellation_reason')                    
                    ;
            
            $components['amount'] = $form->input_add()->input_set('id','purchase_receipt_amount')
                    ->input_set('label','Amount (Rp.)')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('style'=>'font-weight:bold'))
                    ->input_set('value','')
                    
                    ;
            
            $components['allocated_amount'] = $form->input_add()->input_set('id','purchase_receipt_allocated_amount')
                    ->input_set('label','Allocated Amount (Rp.)')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->input_set('value','')                    
                    ;
            
            $components['available_amount'] = $form->input_add()->input_set('id','purchase_receipt_available_amount')
                    ->input_set('label','Available Amount (Rp.)')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->input_set('value','')                    
                    ;
            
            $components['notes'] = $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','purchase_receipt_notes')
                    ->textarea_set('value','')
                    ->textarea_set('attrib',array())                    
                    ;
            
            $tbl = $form->table_add();
            $tbl->table_set('class','table');
            $tbl->table_set('id','purchase_receipt_table_product');
            
            return $components;
            
        }
        
        
        
        
    }
?>
