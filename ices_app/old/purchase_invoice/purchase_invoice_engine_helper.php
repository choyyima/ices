<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Purchase_Invoice_Engine_old {
        
        public static function get($id=""){
            $db = new DB();
            $q = "select t1.*, t2.code supplier_code
                from purchase_invoice t1
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
            $purchase_invoice = $data['purchase_invoice'];
            $product = $data['product'];
            switch($method){
                case 'insert':
                    if(strlen($purchase_invoice['supplier_id']) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Supplier cannot be empty";
                    }
                    
                    $store_id = isset($purchase_invoice['store_id'])?$purchase_invoice['store_id']:'';
                    if(strlen($store_id) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Store cannot be empty";
                    }
                    
                    if(strlen($purchase_invoice['purchase_invoice_date']) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Date cannot be empty";
                    }
                    
                    if(count($product)<1){
                        $result['success'] = 0;
                        $result['msg'][] = "Product cannot be empty";
                    }
                    
                    foreach($product as $prod){
                        if($prod['qty'] == 0){
                            $result['success'] = 0;
                            $result['msg'][] = "Qty cannot be zero";
                            break;
                        }
                    }
                    
                    break;
                    
                case 'update':
                    $purchase_invoice_data = self::get($purchase_invoice['id']);
                    if($purchase_invoice_data != null){
                        if($purchase_invoice_data->purchase_invoice_status == 'X'){
                            $result['success'] = 0;
                            $result['msg'][] = 'Cannot update Cancelled Purchase Invoice data';
                        }
                    }
                    else{
                        $result['success'] = 0;
                        $result['msg'][] = 'Invalid Purchase Invoice';
                    }
                    break;
                    
                case 'cancel':
                    $purchase_invoice_data = self::get($purchase_invoice['id']);
                    if($purchase_invoice_data != null){
                        if($purchase_invoice_data->purchase_invoice_status == 'X'){
                            $result['success'] = 0;
                            $result['msg'][] = 'Cannot update Cancelled Purchase Invoice data';
                        }
                    }
                    else{
                        $result['success'] = 0;
                        $result['msg'][] = 'Invalid Purchase Invoice';
                    }
                    
                    if(strlen(str_replace(' ','',$purchase_invoice['cancellation_reason'])) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Cancellation Reason is required';
                    }
                    
                    $db = new DB();
                    $q = '
                        select 1 
                        from purchase_receipt_allocation 
                        where purchase_receipt_allocation_status = "I"
                            and purchase_invoice_id = '.$db->escape($purchase_invoice['id']).'
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Cannot cancel Purchase Invoice with Invoiced Payment';
                    }
                    
                    $q = '
                        select 1
                        from purchase_invoice t1
                            inner join purchase_invoice_rma t2 on t1.id = t2.purchase_invoice_id
                            inner join rma t3 on t3.id = t2.rma_id and t3.rma_status != "X"
                        where t1.id = '.$db->escape($purchase_invoice['id']).'
                    ';
                    
                    if(count($db->query_array_obj($q))>0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Cannot cancel Purchase Invoice with Active RMA';
                    }
                    break;
                    
               
            }
            
            return $result;
        }
        
        public static function adjust($action,$data=array()){
            $db = new DB();
            $result = array(
                'purchase_invoice'=>array(
                    'store_id'=>''
                    ,'code'=>''
                    ,'supplier_id'=>''
                    ,'purchase_invoice_status'=>''
                    ,'status'=>''
                    ,'notes'=>''
                    ,'id'=>''
                    ,'purchase_invoice_date'=>''
                    ,'total_product'=>'0'
                    ,'total_expense'=>'0'
                    ,'grand_total'=>'0'
                )
                ,'purchase_invoice_product'=>array()
                ,'purchase_order_purchase_invoice'=>array()
                ,'purchase_invoice_expense'=>array()
            );
            $po = null;
            if (isset($data['po'])) $po = $data['po'];
            
            $purchase_invoice = $data['purchase_invoice'];
            $product = $data['product'];
            $expense = $data['expense'];
            switch($action){
                case 'insert':
                    unset($result['purchase_invoice']['id']);
                    
                    $result['purchase_invoice']['store_id'] = $purchase_invoice['store_id'];
                    $result['purchase_invoice']['code'] = '';
                    $result['purchase_invoice']['supplier_id'] = $purchase_invoice['supplier_id'];
                    $result['purchase_invoice']['purchase_invoice_status']='I';
                    $result['purchase_invoice']['status']='1';
                    $result['purchase_invoice']['notes']=$purchase_invoice['notes'];
                    $result['purchase_invoice']['purchase_invoice_date']=$purchase_invoice['purchase_invoice_date'];
                    $result['purchase_invoice']['total_product']=$purchase_invoice['total_product'];
                    $result['purchase_invoice']['total_expense']=$purchase_invoice['total_expense'];
                    $result['purchase_invoice']['grand_total']=$purchase_invoice['total_product']+$purchase_invoice['total_expense'];
                    foreach($product as $prod){
                        $result['purchase_invoice_product'][] = array(
                            'product_id' => $prod['product_id']
                            ,'unit_id' => $prod['unit_id']
                            ,'qty' => $prod['qty']
                            ,'amount' => $prod['amount']
                            ,'purchase_invoice_id'=>''
                            ,'sub_total'=>$prod['sub_total']
                        );
                    }
                    
                    $result['purchase_invoice_expense'] = $expense;
                    
                    if($po != null){
                        $result['purchase_order_purchase_invoice'][] = array(
                            'purchase_order_id'=>$po['id']
                            ,'purchase_invoice_id'=>''
                        );
                    }
                    
                    break;
                    
                case 'update':
                    $result = array(
                        'purchase_invoice'=>array(
                            'notes'=>$purchase_invoice['notes']
                        )
                    );
                    break;
                case 'cancel':
                    $result = array(
                        'purchase_invoice'=>array(
                            'notes'=>$purchase_invoice['notes']
                            ,'purchase_invoice_status'=>'X'
                            ,'cancellation_reason'=>$purchase_invoice['cancellation_reason']
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
            $purchase_invoice_data = $data['purchase_invoice'];
            $id = $purchase_invoice_data['id'];
            
           if(strlen($id)==0){
                $action = "insert";
            }
            else{
                $action = "update";
                if(isset($purchase_invoice_data['purchase_invoice_status'])){
                    if($purchase_invoice_data['purchase_invoice_status'] == 'X') $action = "cancel";
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
                            $fpurchase_invoice = array_merge($final_data['purchase_invoice'],array("modid"=>$modid,"moddate"=>$moddate));
                            $purchase_invoice_id = '';
                            $rs = $db->query_array_obj('select func_code_counter_store("purchase_invoice",'.$db->escape($fpurchase_invoice['store_id']).') "code"');
                            $fpurchase_invoice['code'] = $rs[0]->code;
                            if(!$db->insert('purchase_invoice',$fpurchase_invoice)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $q = '
                                    select id 
                                    from purchase_invoice 
                                    where status>0 
                                        and purchase_invoice_status = "I" 
                                        and code = '.$db->escape($fpurchase_invoice['code']).'
                                ';
                                $rs_purchase_invoice = $db->query_array_obj($q);
                                $purchase_invoice_id = $rs_purchase_invoice[0]->id;
                                $result['trans_id']=$purchase_invoice_id; // useful for non modal insert
                                $f_product = $final_data['purchase_invoice_product'];
                                foreach($f_product as $fprod){
                                    $fprod['purchase_invoice_id'] = $purchase_invoice_id;
                                    if(!$db->insert('purchase_invoice_product',$fprod)){
                                        $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                        break;
                                    }                                        
                                }
                            }
                            
                            if($success == 1){
                                $purchase_order_purchase_invoice = $final_data['purchase_order_purchase_invoice'];
                                foreach($purchase_order_purchase_invoice as $po_pi){
                                    $po_pi['purchase_invoice_id'] = $purchase_invoice_id;
                                    $po_pi['modid'] = $modid;
                                    $po_pi['moddate'] = $moddate;
                                    if(!$db->insert('purchase_order_purchase_invoice',$po_pi)){
                                        $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                        break;
                                    }
                                }
                                
                                if($success == 1){
                                    $purchase_invoice_expense = $final_data['purchase_invoice_expense'];
                                    foreach($purchase_invoice_expense as $pie){
                                        $pie['purchase_invoice_id'] = $purchase_invoice_id;
                                        //$pie['modid'] = $modid;
                                        //$pie['moddate'] = $moddate;
                                        if(!$db->insert('purchase_invoice_expense',$pie)){
                                            $msg[] = $db->_error_message();
                                            $db->trans_rollback();                                
                                            $success = 0;
                                            break;
                                        }
                                    }
                                }
                                
                                if($success == 1 && count($purchase_order_purchase_invoice)>0){
                                    $purchase_order_id = $purchase_order_purchase_invoice[0]['purchase_order_id'];
                                    if(!$db->query('update purchase_order set purchase_order_status = "I" where id = '.$db->escape($purchase_order_id))){
                                        $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                        break;
                                    }
                                }
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Purchase Invoice Success';
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
                            $fpurchase_invoice = array_merge($final_data['purchase_invoice'],array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('purchase_invoice',$fpurchase_invoice,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $result['trans_id']=$id;
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Update Purchase Invoice Success';
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
                            $po = array_merge($final_data['purchase_invoice'],array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('purchase_invoice',$po,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $result['trans_id']=$id;
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Cancel Purchase Invoice Success';
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
        
        public static function modal_purchase_invoice_render($app,$modal,$data){
            
            $modal->header_set(array('title'=>'Purchase Invoice','icon'=>App_Icon::info()));
            $components = self::components_purchase_invoice_render($app, $modal, $data);
            $components['purchase_invoice_product']->div_set('class','hide');
            $components['supplier']->detail_set('ajax_url',get_instance()->config->base_url().'purchase_invoice/ajax_search/detail_supplier_get');
            $modal->modal_button_footer_add(
                    "purchase_invoice_button_save"
                    ,'button'
                    ,''
                    ,  App_Icon::detail_btn_save()
                    ,'Submit'
                );
        }
        
        public static function purchase_invoice_render($app,$form,$data,$path,$method){
            
            
            $id = $data['id'];
            $components = self::components_purchase_invoice_render($app, $form, $data);
            
            $modal_supplier = $app->engine->modal_add()->id_set('modal_supplier')->width_set('75%');
            $modal_supplier->modal_button_footer_add("supplier_submit",'button','',  App_Icon::detail_btn_save(),'Submit');
            
            get_instance()->load->helper($path->supplier_engine);
            Supplier_Engine::modal_supplier_render($app, $modal_supplier, $data);
            
            
            $data_detail = array(
                'code'=>''
                ,'po_code'=>array()
                ,'purchase_invoice_date'=>''
                ,'supplier'=>array()
                ,'purchase_invoice_status'=>array()
                ,'cancellation_reason'=>''
                ,'notes'=>''
                ,'store'=>array()
            );
            $data_detail = json_decode(json_encode($data_detail));
            
            $list_detail = array(
                'purchase_invoice_status'=>array()
            );
            
            $list_detail = json_decode(json_encode($list_detail));
            
            
            $db = new DB();
            switch($method){
                case 'Add':
                    self::purchase_invoice_data_set($method,$id,$data_detail);
                                        
                    
                    $list_detail->purchase_invoice_status = array(array('id'=>'I','data'=>'INVOICED'));
                    
                    $components['po_code']->div_set('class','hide');
                    $components['cancellation_reason']->div_set('class','hide');
                    
                    if(Security_Engine::get_controller_permission(User_Info::get()['user_id']
                            ,'supplier','add')){
                        $components['supplier']
                            ->detail_set('button_new_id','purchase_invoice_button_supplier_new')
                            ->detail_set('button_new',true)
                            ->detail_set('button_new_target','modal_supplier')
                            ;
                    }
                    // draw table
                                        
                    
                    // end of draw table
                    $form->hr_add();
                    $form->button_add()->button_set('value','Submit')
                            ->button_set('id','purchase_invoice_submit')
                            ->button_set('icon',App_Icon::detail_btn_save())
                        ;
                    
                    break;
                case 'View':
                    //set data                    
                    self::purchase_invoice_data_set($method,$id,$data_detail);
                    //end of set data
                    
                    //draw table
                    self::purchase_invoice_table_view_draw($form, $id);
                    //end of draw table
                    if($data_detail->purchase_invoice_status['id'] === 'I'){
                        $list_detail->purchase_invoice_status = array(
                            array('id'=>'I','data'=> SI::get_status_attr('INVOICED'))
                            ,array('id'=>'X','data'=>SI::get_status_attr('CANCELED'))
                        );
                    }
                    else{
                        $list_detail->purchase_invoice_status = array(
                            array('id'=>'X','data'=>SI::get_status_attr('CANCELED'))
                        );
                    }
                    //additional setting
                    $disable = array('disabled'=>'');
                    $components['store']->input_select_set('attrib',$disable);
                    $purchase_order = self::purchase_order_get($id);
                    if($purchase_order === null) $components['po_code']->div_set('class','hide');
                    $purchase_invoice = self::purchase_invoice_get($id);
                    if($purchase_invoice->purchase_invoice_status !== 'X'){
                        $components['cancellation_reason']->div_set('class','hidden');
                    }
                    $components['purchase_invoice_date']->input_set('attrib',$disable);
                    $components['supplier']->input_select_set('attrib',$disable);
                    $components['purchase_invoice_product']->div_set('class','hide');
                    $form->hr_add();
                    
                    $generate_submit = false;
                    
                    if(Security_Engine::get_controller_permission(
                            User_Info::get()['user_id']
                            ,'purchase_invoice' 
                            ,'edit')
                    ){
                        if($purchase_invoice->purchase_invoice_status !=='X'){
                            $generate_submit = true;                        
                        }
                    }                    

                    if($generate_submit){
                        $form->button_add()->button_set('value','Submit')
                            ->button_set('id','purchase_invoice_submit')
                            ->button_set('icon',App_Icon::detail_btn_save())
                            ;
                    }
                    else{
                        $components['purchase_invoice_status']->input_select_set('attrib',$disable);
                        $components['cancellation_reason']->textarea_set('attrib',$disable);
                        $components['notes']->textarea_set('attrib',$disable);
                    }
                    
                    break;
                
            }
            
            // <editor-fold defaultstate="collapsed" desc="bind value and draw button">
            $back_href = $path->index;
            if($method ==='Edit'){
                $back_href.='view/'.$id;
            }
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;
            
            $components['code']
                    ->input_set('value',$data_detail->code)
                ;
            $components['po_code']
                    ->input_select_set('value',$data_detail->po_code)
                    ->input_select_set('allow_empty',true)
                    ->input_select_set('ajax_url',$path->index.'ajax_search/detail_po_code_search')
                ;
            $components['purchase_invoice_date']
                    ->input_set('value',$data_detail->purchase_invoice_date)
                ;
            $components['supplier']
                    ->input_select_set('value',$data_detail->supplier)
                    ->input_select_set('ajax_url',$path->index.'ajax_search/detail_supplier_search')
                    ->detail_set('ajax_url',$path->index.'ajax_search/detail_supplier_get')
                ;
            $components['purchase_invoice_status']
                    ->input_select_set('value',$data_detail->purchase_invoice_status)
                    ->input_select_set('data_add',$list_detail->purchase_invoice_status)
                ;
            $components['purchase_invoice_product']
                    ->input_select_set('ajax_url',$path->ajax_search.'purchase_invoice_product')
                ;
            $components['cancellation_reason']
                    ->textarea_set('value',$data_detail->cancellation_reason)
                ;
            $components['notes']
                    ->textarea_set('value',$data_detail->notes)
                ;
            $components['store']->input_select_set('value',$data_detail->store);
            
            //</editor-fold>
            $purchase_invoice_product = array();                    
            
            $param = array(
                'ajax_url'=>$path->ajax_search
                ,'index_url'=>$path->index
                ,'purchase_invoice_product'=>$purchase_invoice_product
                ,'id'=>$id
            );
            $js = get_instance()->load->view('purchase_invoice/purchase_invoice_js',$param,TRUE);
            $app->js_set($js);
            $param = array(
                'detail_tab'=>'#detail_tab'
            );
            $js = get_instance()->load->view('purchase_invoice/expense_js',$param,TRUE);
            $app->js_set($js);
            $js = '<script>
                        $("#purchase_invoice_method").val("'.$method.'");
                        if("'.$method.'" === "View" || "'.$method.'" === "Edit"){
                            $("#purchase_invoice_product_table").parent().parent().addClass("hide");
                            $("#purchase_invoice_expense_table").parent().addClass("hide");
                        }
                </script>';
            $app->js_set($js);
            
        }
        
        public static function purchase_invoice_table_view_draw($form,$id){
            $tbl = $form->table_add();
            $tbl->table_set('class','table');
            $tbl->div_set('label','Product');
            $tbl->table_set('id','purchase_invoice_product_view_table');
            $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));            
            $tbl->table_set('columns',array("name"=>"product_img","label"=>""));
            $tbl->table_set('columns',array("name"=>"product_name","label"=>"Product",'attribute'=>'style="text-align:center"'));
            $tbl->table_set('columns',array("name"=>"qty","label"=>"Qty",'attribute'=>'style="text-align:right"'));
            $tbl->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'attribute'=>'style="text-align:center"'));
            $tbl->table_set('columns',array("name"=>"amount","label"=>"Amount(".Tools::currency_get().')','attribute'=>'style="text-align:right"'));
            $tbl->table_set('columns',array(
                "name"=>"sub_total","label"=>"Sub Total(".Tools::currency_get().')'
                ,'col_attrib'=>array('style'=>'width:230px;'),'attribute'=>'style="text-align:right"'));

            
            
            $db = new DB();
            $q = '
                select 
                    null row_num
                    ,t2.id product_id
                    , t2.name product_name, t3.id unit_id, t3.name unit_name
                    , format(t1.qty,2) qty
                    , format(t1.amount,2) amount
                    ,format(t1.sub_total,2) sub_total
                from purchase_invoice_product t1
                    inner join product t2 on t1.product_id = t2.id
                    inner join unit t3 on t3.id = t1.unit_id

                where t1.purchase_invoice_id = '.$db->escape($id).'
            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $filename = 'img/product/'.$rs[$i]['product_id'].'.jpg';                    
                $rs[$i]['product_img']='<img src = "'.Tools::img_load($filename,false).'"></img>';
            }
            $tbl->table_set('data',$rs);

            $q = 'select format(total_product,2) total from purchase_invoice where id ='.$db->escape($id);
            $rs = $db->query_array_obj($q);
            $total = $rs[0]->total;
            $tbl->footer_set('<tfoot id="purchase_invoice_total"><td colspan="5" /><td style="text-align:right"><strong>TOTAL</strong></td><td style="text-align:right"><strong>'.$total.'</strong></td></tfoot>');

            $tbl = $form->table_add();
            $tbl->table_set('class','table');
            $tbl->div_set('label','Expense');
            $tbl->table_set('id','purchase_invoice_expense_view_table');
            $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));            
            $tbl->table_set('columns',array("name"=>"description","label"=>"Description"));
            $tbl->table_set('columns',array("name"=>"amount","label"=>"Amount(".Tools::currency_get().')','col_attrib'=>array('style'=>'width:230px'),'attribute'=>'style="text-align:right"'));
            
            $db = new DB();
            $q = '
                select *,null row_num                    
                from purchase_invoice_expense
                where purchase_invoice_id = '.$db->escape($id).'
            ';
            $rs = $db->query_array($q);
            $total =0;
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $total+=$rs[$i]['amount'];
                $rs[$i]['amount'] = Tools::thousand_separator($rs[$i]['amount'],2,true);
            }
            $total  = Tools::thousand_separator($total,2,true);
            $tbl->table_set('data',$rs);
            
            $tbl->footer_set('<tfoot id="purchase_invoice_expense_view_total"><td colspan="1" /><td style="text-align:right"><strong>TOTAL</strong></td><td colspan="2" style="text-align:right"><strong>'.$total.'</strong></td></tfoot>');
        }
        
        public static function purchase_invoice_data_set($method,$id,$data_detail){
            $result = null;
            $db = new DB();
            if($method === 'Add'){
                $data_detail->code = '[AUTO GENERATE]';
                $data_detail->po_code = array('id'=>'','data'=>'');
                $data_detail->purchase_invoice_date = date('Y-m-d');
                $data_detail->supplier = array('id'=>'','data'=>'');
                $data_detail->purchase_invoice_status = array('id'=>'I','data'=>'INVOICED');
                $db = new DB();
                
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
            else if (in_array($method,array('Edit','View'))){
                $purchase_order = self::purchase_order_get($id);
                if($purchase_order !== null){
                    //$components['po_code']->div_set('class','hide');
                    $data_detail->po_code = array('id'=>$purchase_order->code,'data'=>$purchase_order->code);
                }

                $purchase_invoice = self::purchase_invoice_get($id);
                $data_detail->code = $purchase_invoice->code;
                $data_detail->purchase_invoice_date = $purchase_invoice->purchase_invoice_date;
                $data_detail->supplier = array('id'=>$purchase_invoice->supplier_id,'data'=>$purchase_invoice->supplier_name);
                $purchase_invoice_status_name = SI::get_status_attr($purchase_invoice->purchase_invoice_status_name);
                $data_detail->purchase_invoice_status = array("id"=>$purchase_invoice->purchase_invoice_status,"data"=>$purchase_invoice_status_name);
                $data_detail->cancellation_reason = $purchase_invoice->cancellation_reason;
                $data_detail->notes = $purchase_invoice->notes;
                $data_detail->store = array('id'=>$purchase_invoice->store_id, 'data'=>$purchase_invoice->store_name);
            }
            
            
        }
        
        public static function purchase_order_get($purchase_invoice_id){
            $db = new DB();
            $q = '
                select distinct t1.*
                from purchase_order t1
                    inner join purchase_order_purchase_invoice t2 on t1.id = t2.purchase_order_id
                where t2.purchase_invoice_id = '.$db->escape($purchase_invoice_id).'
            ';
            $rs = $db->query_array_obj($q);
            $result = null;
            if(count($rs)>0) $result = $rs[0];
            return $result;
        }
        
        public static function purchase_invoice_get($id){
            $db = new DB();
            $q = '
                select t1.code, date(t1.purchase_invoice_date) purchase_invoice_date
                    ,t2.id supplier_id, t2.name supplier_name
                    ,t1.purchase_invoice_status
                    ,case t1.purchase_invoice_status 
                        when "O" then "OPENED"
                        when "I" then "INVOICED"
                        when "X" then "CANCELED"
                        end purchase_invoice_status_name
                     ,t1.cancellation_reason
                     ,t1.notes
                     ,t1.store_id
                     ,t3.name store_name
                from purchase_invoice t1
                    inner join store t3 on t1.store_id = t3.id
                    inner join supplier t2 on t1.supplier_id = t2.id
                where t1.id = '.$db->escape($id)
                ;
            $rs = $db->query_array_obj($q);
            return $rs[0];
        }
        
        public static function receipt_allocation_view_render($app,$pane,$data,$path){
            $id = $data['id'];
            $purchase_invoice = self::get($id);
            $pane->form_group_add();
            if($purchase_invoice->purchase_invoice_status != 'X'){
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
            $tbl->table_set('columns',array("name"=>"purchase_invoice_code","label"=>"Purchase Invoice Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"purchase_receipt_code","label"=>"Purchase Receipt Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"allocated_amount","label"=>"Allocated Amount (Rp.)",'attribute'=>'style="text-align:right"','col_attrib'=>array('style'=>'text-align:right')));
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
                where t3.id = '.$id.' order by t1.moddate desc

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
                ,'purchase_invoice_id'=>$purchase_invoice->id
                ,'purchase_invoice_code'=>$purchase_invoice->code
                ,'purchase_invoice_supplier_id'=>$purchase_invoice->supplier_id
                ,'purchase_invoice_supplier_code'=>$purchase_invoice->supplier_code
            );

            $js = get_instance()->load->view('purchase_invoice/purchase_receipt_allocation_js',$param,TRUE);
            $app->js_set($js);
            
        }
        
        public static function rma_view_render($app,$pane,$data,$path){
            get_instance()->load->helper('rma/rma_renderer');
            $id = $data['id'];
            $purchase_invoice = self::get($id);
            $pane->form_group_add();
            if($purchase_invoice->purchase_invoice_status != 'X'){
                if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'rma','add')){
                $pane->button_add()->button_set('class','primary')
                        ->button_set('value','New RMA')
                        ->button_set('icon','fa fa-plus')
                        ->button_set('attrib',array(
                            'data-toggle'=>"modal" 
                            ,'data-target'=>"#modal_rma"
                        ))
                        ->button_set('disable_after_click',false)
                        ->button_set('id','rma_new')
                    ;
                }
            }
            $pane->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
            $tbl = $pane->table_add();
            $tbl->table_set('class','table');
            $tbl->table_set('id','rma_table');
            $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
            $tbl->table_set('columns',array("name"=>"rma_code","label"=>"RMA Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
            $tbl->table_set('columns',array("name"=>"rma_status_name","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('data key','id');
            
            $db = new DB();
            $q = '
                select distinct NULL row_num
                    ,case t1.rma_status 
                        when "O" then "OPENED" 
                        when "X" then "CANCELED" 
                        when "C" then "CLOSED" 
                    end rma_status_name
                    ,t1.id
                    ,t1.moddate
                    ,t1.code rma_code
                from rma t1
                    inner join purchase_invoice_rma t2 on t1.id = t2.rma_id
                    inner join purchase_invoice t3 on t2.purchase_invoice_id = t3.id
                where t3.id = '.$id.' order by t1.moddate desc

            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['rma_status_name'] = SI::get_status_attr($rs[$i]['rma_status_name']);
            }
            $tbl->table_set('data',$rs);
            
            
            $modal_purchase_rma = $app->engine->modal_add()->id_set('modal_rma')->width_set('75%')
                    ->footer_attr_set(array('style'=>'display:none'))
                    ;
            
            $po = self::get($id);
            
            RMA_Renderer::modal_rma_render(
                    $app
                    ,$modal_purchase_rma
                );
            
            
            $param = array(
                'index_url'=>$path->index
                ,'ajax_search'=>$path->ajax_search
                ,'purchase_invoice_id'=>$purchase_invoice->id
                ,'purchase_invoice_code'=>$purchase_invoice->code
            );

            $js = get_instance()->load->view('purchase_invoice/rma_js',$param,TRUE);
            $app->js_set($js);
            
        }
        
        
        public static function receive_product_view_render($app,$pane,$data,$path){
            get_instance()->load->helper('receive_product/receive_product_engine');
            $path = Receive_Product_Engine::path_get();
            get_instance()->load->helper($path->receive_product_purchase_invoice_engine);
            $id = $data['id'];
            $purchase_invoice = self::get($id);
            $pane->form_group_add();
            if($purchase_invoice->purchase_invoice_status != 'X'){
                if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'receive_product','add')){
                $pane->button_add()->button_set('class','primary')
                        ->button_set('value','New Receive Product')
                        ->button_set('icon','fa fa-plus')
                        ->button_set('attrib',array(
                            'data-toggle'=>"modal" 
                            ,'data-target'=>"#modal_receive_product"
                        ))
                        ->button_set('disable_after_click',false)
                        ->button_set('id','receive_product_new')
                    ;
                }
            }
            
            $pane->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
            $tbl = $pane->table_add();
            $tbl->table_set('class','table');
            $tbl->table_set('id','receive_product_table');
            $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
            $tbl->table_set('columns',array("name"=>"code","label"=>"Receive Product Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
            $tbl->table_set('columns',array("name"=>"receive_product_date","label"=>"Receive Product Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"receive_product_status_name","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            
            $tbl->table_set('data key','id');
            
            $db = new DB();
            $q = '
                select t1.id, t1.code code
                    ,t1.receive_product_date
                    ,t1.receive_product_status
                from receive_product t1
                    inner join purchase_invoice_receive_product t2 on t2.receive_product_id = t1.id
                    inner join purchase_invoice t3 on t3.id = t2.purchase_invoice_id
                where t3.id = '.$db->escape($id).'
                    order by t1.receive_product_date desc
            ';
            $rs = $db->query_array($q);
            
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['receive_product_status_name'] = 
                        SI::get_status_attr(Receive_Product_Purchase_Invoice_Engine::receive_product_purchase_invoice_status_get($rs[$i]['receive_product_status'])['label']);
            }
            $tbl->table_set('data',$rs);
            
            
            $modal_receive_product = $app->engine->modal_add()->id_set('modal_receive_product')->width_set('75%')
                        ->footer_attr_set(array('style'=>'display:none'))
                    ;
            
            get_instance()->load->helper('receive_product/receive_product_renderer');
            
            Receive_Product_Renderer::modal_receive_product_render(
                $app
                ,$modal_receive_product
            );
            
            
            $param = array(
                'index_url'=>$path->index
                ,'ajax_search'=>$path->ajax_search
                ,'purchase_invoice_id'=>$purchase_invoice->id
                ,'purchase_invoice_code'=>$purchase_invoice->code
            );

            $js = get_instance()->load->view('purchase_invoice/receive_product_js',$param,TRUE);
            $app->js_set($js);
            
             
            
        }
        
        public static function components_purchase_invoice_render($app,$form,$data){
            //<editor-fold defaultstate="collapsed">
            $po_id = '';
            if(isset($data->po->id)) $po_id = $data->po->id;                        
            $components = array();
            $form->input_add()->input_set('id','purchase_invoice_method')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $components['purchase_invoice_id'] = $form->input_add()->input_set('id','purchase_invoice_id')
                    ->input_set('hide',true)
                    ->input_set('value',$data['id'])
                    ;

            
            $components['po_id'] = $form->input_add()->input_set('id','purchase_invoice_po_id')
                    ->input_set('hide',true)
                    ->input_set('value',$po_id)
                    ;
            
            $db = new DB();
            $store_list = array();
            $q = 'select id id, name data from store where status>0';            
            $store_list = $db->query_array($q);
            
            
            
            $components['store'] = $form->input_select_add()
                    ->input_select_set('label','Store')
                    ->input_select_set('icon',App_Icon::store())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','purchase_invoice_store')
                    ->input_select_set('data_add',$store_list)
                    ->input_select_set('value',array())
                    ->div_set('id','purchase_invoice_div_store')
                                        
                ;
            
            $components['code'] = $form->input_add()->input_set('id','purchase_invoice_purchase_invoice_code')
                    ->input_set('label','Purchase Invoice Code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->input_set('value','')                    
                    ;
            
            $components['po_code']=$form->input_select_add()
                ->input_select_set('label','Purchase Order Code')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','1')
                ->input_select_set('id','purchase_invoice_po_code')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('attrib',array('disabled'=>''))
                ;

            
            $components['purchase_invoice_date']=$form->input_add()->input_set('label','Purchase Invoice Date')
                    ->input_set('id','purchase_invoice_date')
                    ->input_set('is_date_picker',true)
                    ->input_set('icon','fa fa-calendar')
                    ->input_set('value','')                    
                    ;
            
            $supplier_detail = array(
                array('name'=>'code','label'=>'Code')
                ,array('name'=>'name','label'=>'Name')
                ,array('name'=>'address','label'=>'Address')
                ,array('name'=>'phone','label'=>'Phone')
            );
            
            $components['supplier'] = $form->input_select_detail_add()
                    ->input_select_set('label','Supplier')
                    ->input_select_set('icon','fa fa-user')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','purchase_invoice_supplier')
                    ->input_select_set('min_length','1')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->detail_set('rows',$supplier_detail)
                    ->detail_set('id',"purchase_invoice_supplier_detail")
                    ->detail_set('ajax_url','')
                ;
            
            $components['purchase_invoice_status'] = $form->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-user')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','purchase_invoice_purchase_invoice_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ;
            
            $components['cancellation_reason']=$form->textarea_add()->textarea_set('label','Cencellation Reason')
                    ->textarea_set('id','purchase_invoice_cancellation_reason')
                    ->textarea_set('value','')
                    ->div_set('id','purchase_invoice_div_cancellation_reason')                    
                    ;
            
            $components['notes'] = $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','purchase_invoice_notes')
                    ->textarea_set('value','')
                    ->textarea_set('attrib',array())                    
                    ;
            
            $components['purchase_invoice_product'] = $form->input_select_add()
                ->input_select_set('label','Product')
                ->input_select_set('icon',App_Icon::product())
                ->input_select_set('min_length','1')
                ->input_select_set('value',array())
                ->input_select_set('id','purchase_invoice_product')
                ->input_select_set('allow_empty',false)
                ;
            
            
            $form->custom_component_add()
                        ->src_set('purchase_invoice/purchase_invoice_view')
                        ;
            
            $form->custom_component_add()
                        ->src_set('purchase_invoice/expense')
                        ;
            
            return $components;
            //</editor-fold>
        }
        
        
        
        
        
    }
?>
