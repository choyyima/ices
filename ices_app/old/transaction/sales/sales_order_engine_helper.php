<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Sales_Order_Engine {
        
        public static function get($id=""){
            $db = new DB();
            $q = "select * 
                from sales_order 
                where status>0 and id = ".$db->escape($id);
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $rs = $rs[0];
            else $rs = null;
            return $rs;
        }
        
        public static function customer_get($id){
            $result = null;
            $db = new DB();
            $q = '
                select id, name from customer where status>0 and id = '.$db->escape($id).'
            ';                    
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $result = $rs[0];
            return $result;
            
        }
        
        public static function warehouse_get(){
            $result = null;
            $db = new DB();
            $q = '
                select * from warehouse where status>0
            ';                    
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $result = $rs;
            return $result;
        }
        
        public static function movement_type_get($name){
            $result = null;
            $db = new DB();
            $q = '
                select id, code, name from movement_type where status>0 and code = '.$db->escape($name).'
            ';                    
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $result = $rs[0];
            return $result;
            
        }
        
        public static function movement_active_get($so_id){
            $result = null;
            $db = new DB();
            $q = '
                select t1.*
                from movement t1
                inner join sales_order_movement t2 on t1.id = t2.movement_id
                where t1.status>0 
                    and t2.sales_order_id = '.$db->escape($so_id).'
                    and t1.movement_status !="X"
            ';
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $result = $rs;
            return $result;
            
        }
        
        public static function validate($method,$data=array()){
            $result = array(
                "success"=>1
                ,"msg"=>array()
            );
            $so = $data['so'];
            $so_detail = $data['so_detail'];
            switch($method){
                case 'insert':
                    if(strlen($so['customer_id']) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Customer cannot be empty";
                    }
                    
                    if(strlen($so['date']) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Date cannot be empty";
                    }
                    
                    if(count($so_detail)<1){
                        $result['success'] = 0;
                        $result['msg'][] = "Item cannot be empty";
                    }
                    
                    foreach($so_detail as $item){
                        if($item['qty'] == 0){
                            $result['success'] = 0;
                            $result['msg'][] = "Qty cannot be zero";
                            break;
                        }
                        if($item['discount']>$item['price']){
                            $result['success'] = 0;
                            $result['msg'][] = "Discount must be lower than price";
                            break;
                        }
                        

                        
                    }
                    
                    break;
                    
                case 'update':
                    $so_data = self::get($so['id']);
                    if($so_data != null){
                        if($so_data->sales_order_status == 'X'){
                            $result['success'] = 0;
                            $result['msg'][] = 'Cannot update Cancelled Sales Order data';
                        }
                    }
                    else{
                        $result['success'] = 0;
                        $result['msg'][] = 'Invalid Sales Order';
                    }
                    break;
                    
                case 'cancel':
                    $so_data = self::get($so['id']);
                    if($so_data != null){
                        if($so_data->sales_order_status == 'X'){
                            $result['success'] = 0;
                            $result['msg'][] = 'Cannot update Cancelled Sales Order data';
                        }
                    }
                    else{
                        $result['success'] = 0;
                        $result['msg'][] = 'Invalid Sales Order';
                    }
                    
                    $mov = self::movement_active_get($so['id']);
                    if(count($mov)>0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Sales Order has active movement';
                    }
                    
                    if(strlen(str_replace(' ','',$so['cancellation_reason'])) == 0){
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
                'so'=>array(
                    'code'=>''
                    ,'customer_id'=>''
                    ,'sales_order_status'=>''
                    ,'status'=>''
                    ,'notes'=>''
                    ,'cancellation_reason'=>''
                    ,'discount'=>'0'
                    ,'grand_total'=>'0'
                    ,'total'=>'0'
                )
                ,'so_detail'=>array()
            );
            $so = $data['so'];
            $so_detail = $data['so_detail'];
            
            switch($action){
                case 'insert':
                    unset($result['so']['id']);
                    
                    $rs = $db->query_array_obj('select func_code_counter("sales_order") "code"');
                    $result['so']['code'] = $rs[0]->code;
                    $result['so']['customer_id'] = $so['customer_id'];
                    $result['so']['sales_order_status']='O';
                    $result['so']['status']='1';
                    $result['so']['notes']=$so['notes'];
                    $result['so']['date']=$so['date'];
                    $result['so']['discount']=$so['discount'];
                    $result['so']['grand_total']=$so['grand_total'];
                    $result['so']['total']=$so['total'];
                    if(isset($so['approval_id'])) $result['so']['approval_id']=$so['approval_id'];
                    foreach($so_detail as $item){
                        $result['so_detail'][] = array(
                            'item_id' => $item['item_id']
                            ,'unit_id' => $item['unit_id']
                            ,'qty' => $item['qty']
                            ,'price' => $item['price']
                            ,'discount' => $item['discount']
                            ,'sub_total' => $item['qty'] * ($item['price'] - $item['discount'])
                        );
                    }
                    
                    break;
                    
                case 'update':
                    $result = array(
                        'so'=>array(
                            'notes'=>$so['notes']
                        )
                    );
                    break;
                case 'cancel':
                    $result = array(
                        'so'=>array(
                            'notes'=>$so['notes']
                            ,'sales_order_status'=>'X'
                            ,'cancellation_reason'=>$so['cancellation_reason']
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
            $result = array("status"=>0,"msg"=>array());
            $so_data = $data['so'];
            $id = $so_data['id'];
            
           if(strlen($id)==0){
                $action = "insert";
            }
            else{
                $action = "update";
                if(isset($so_data['sales_order_status'])){
                    if($so_data['sales_order_status'] == 'X') $action = "cancel";
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
                            $so = array_merge($final_data['so'],array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->insert('sales_order',$so)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $q = '
                                    select id 
                                    from sales_order 
                                    where status>0 
                                        and sales_order_status = "O" 
                                        and code = '.$db->escape($so['code']).'
                                ';
                                $so = $db->query_array_obj($q);
                                $so_id = $so[0]->id;
                                $result['trans_id']=$so_id;
                                $so_detail = $final_data['so_detail'];
                                foreach($so_detail as $detail){
                                    $detail['sales_order_id'] = $so_id;
                                    if(!$db->insert('sales_order_detail',$detail)){
                                        $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                        break;
                                    }                                        
                                }
                            }
                            if($success == 1){
                                if(isset($final_data['so']['approval_id'])){
                                    $q = '
                                        update approval
                                        set used = 1 
                                        where id = '.$db->escape($final_data['so']['approval_id']).'
                                    ';
                                    if(!$db->query($q)){
                                        $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                    }
                                }
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Sales Order Success';
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
                            $so = array_merge($final_data['so'],array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('sales_order',$so,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $result['trans_id']=$id;
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Update Sales Order Success';
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
                            $so = array_merge($final_data['so'],array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('sales_order',$so,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $result['trans_id']=$id;
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Cancel Sales Order Success';
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
        
        public static function get_so_render($id){
            $so = array(
                'code'=>''
                ,'date'=>''
                ,'notes'=>''
                ,'sales_order_status'=>array('id'=>'','data'=>'')
                ,'cancellation_reason'=>''
                ,'customer_id'=>array('id'=>'','data'=>'')
                ,'customer'=>array(
                    'phone'=>''
                    ,'address'=>''
                    ,'city'=>''
                    ,'customer_type'=>''
                )
                ,'approval'=>array(
                    'id'=>'', 'data'=>''
                )
            );
            
            $so = json_decode(json_encode($so));
            
            
            if(strlen($id)==0){
                $so->code = '[AUTO GENERATE]';
                $so->date = date('Y-m-d');
                $so->sales_order_status = (object)array('id'=>'O','data'=>'OPENED');
                $so_status_list[]=(array)$so->sales_order_status;
            }
            else{
                $db = new DB();                
                $q = '
                    select * 
                    from sales_order
                    where id = '.$db->escape($id).'
                ';
                $rs = $db->query_array_obj($q);
                foreach($rs as $row){
                    $so->code = $row->code;
                    $so->date = $row->date;
                    $so->notes = $row->notes;
                    $so->sales_order_status->id = $row->sales_order_status;
                    switch($row->sales_order_status){
                        case 'O': $so->sales_order_status->data='OPENED'; break;
                        case 'X': $so->sales_order_status->data='CANCELLED'; break;                        
                        case 'I': $so->sales_order_status->data='INVOICED'; break;                        
                        case 'D': $so->sales_order_status->data='DELIVERED'; break;                        
                    }
                    $so->cancellation_reason = $row->cancellation_reason;
                    $so_status_list[]=(array)$so->sales_order_status;
                    
                    $customer = self::customer_get($row->customer_id);
                    $so->customer_id = array(
                        'id'=>$customer->id
                        ,'data'=>$customer->name
                    );
                    
                    if($row->sales_order_status!='X'){
                        $so_status_list[]=array(
                            "id"=>'X'
                            ,'data'=>'CANCELLED'
                        );
                    }
                }
                
                $q = '
                    select t1.address, t1.phone, t1.city, group_concat(t2.name SEPARATOR ", ") customer_type
                    from customer t1
                        left outer join customer_customer_type t3 on t1.id = t3.customer_id
                        left outer join customer_type t2 on t3.customer_type_id = t2.id
                    where t1.id = '.$so->customer_id['id'].'
                    group by t1.address, t1.phone, t1.city
                ';
                $customer = $db->query_array_obj($q);
                foreach($customer as $row){
                    $so->customer = $row;
                }
                
                $q = '
                    select t1.id id, concat(t1.code,", ",t1.name,", ",t1.notes) data 
                    from approval t1 
                    where t1.id = '.$rs[0]->approval_id.'
                ';
                $approval = $db->query_array_obj($q)[0];
                $so->approval = $approval;
            }
            
            return $so;            
        }
        
        public static function get_so_status_list($id,$so){
            $so_status_list = array();
            if(strlen($id)==0){
                $so_status_list[]=(array)$so->sales_order_status;
            }
            else{
                $so_status_list[]=(array)$so->sales_order_status;
                if($so->sales_order_status->id!='X'){
                    $so_status_list[]=array(
                        "id"=>'X'
                        ,'data'=>'CANCELLED'
                    );
                }
            }
            return $so_status_list;
        }
        
        public static function get_so_detail($id){
            $so_detail = array();
            if(strlen($id)>0){
                $db = new DB();
                $q = '
                    select 
                        t2.id item_id
                        , t2.name item_name, t3.id unit_id, t3.name unit_name
                        , format(t1.qty,2) qty
                        , format(t1.price,2) price
                        ,format(t1.qty * t1.price,2) sub_total
                        ,COALESCE(format(t4.sent_qty,2),0) sent_qty
                        ,format(t1.qty - COALESCE(t4.sent_qty,0),2) available_qty
                    from sales_order_detail t1
                        inner join item t2 on t1.item_id = t2.id
                        inner join unit t3 on t3.id = t1.unit_id
                        left outer join (
                                select tt4.id so_id, tt2.item_id, tt2.unit_id, sum(tt2.qty) sent_qty
                                from movement tt1
                                        inner join movement_detail tt2 on tt1.id = tt2.movement_id
                                        inner join sales_order_movement tt3 on tt3.movement_id = tt1.id
                                        inner join sales_order tt4 on tt4.id = tt3.sales_order_id
                                where  tt1.movement_status != "X"
                                group by tt2.item_id, tt2.unit_id, tt4.id
                        ) t4 on t4.item_id = t1.item_id and t4.unit_id = t1.unit_id and t4.so_id = t1.sales_order_id
                    where t1.sales_order_id = '.$db->escape($id).'
                ';
                $rs = $db->query_array_obj($q);
                foreach($rs as $row){
                    $so_detail[]=$row;
                }
            }
            return $so_detail;
        }
        
        public static function order_render($app,$pane,$data,$path,$controller_method){
            $id = $data['id'];
            if(! in_array($controller_method,array('add','edit','view'))){
                die();                
            }
            
            $so = self::get_so_render($id);            
            $so_status_list = self::get_so_status_list($id, $so);
            
            $db = new DB();    

            $pane->input_add()->input_set('id','so_code')
                    ->input_set('label','Code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->input_set('value',$so->code)
                    ;            

            $input_select_customer = $pane->input_select_add()
                    ->input_select_set('label','Customer')
                    ->input_select_set('icon','fa fa-user')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','so_customer_id')
                    ->input_select_set('min_length','1')
                    ->input_select_set('ajax_url',$path->ajax_search.'so_customer')
                    ->input_select_set('value',(array)$so->customer_id)
                    ;
            
            $div_customer_detail = $pane->div_add()
                    ->div_set('id','so_customer_detail')
                    ->div_set('attrib',array('style'=>'border-top:none'));
            $li_customer_detail = $div_customer_detail->ul_add()->ul_class('todo-list ui-sortable')->li_add();
            $span_customer_detail_header = $li_customer_detail->span_add();
            $span_customer_detail_header->i_add()->i_class('fa fa-ellipsis-v');
            $span_customer_detail_header->i_add()->i_class('fa fa-ellipsis-v');
            $li_customer_detail->div_add()->span_add()->span_set('value','<strong>Address: </strong>')->span_add()->span_set('id','so_customer_detail_address')->span_set('value',$so->customer->address);
            $li_customer_detail->div_add()->span_add()->span_set('value','<strong>Phone: </strong>')->span_add()->span_set('id','so_customer_detail_phone')->span_set('value',$so->customer->phone);
            $li_customer_detail->div_add()->span_add()->span_set('value','<strong>City: </strong>')->span_add()->span_set('id','so_customer_detail_city')->span_set('value',$so->customer->city);
            $li_customer_detail->div_add()->span_add()->span_set('value','<strong>Customer Type: </strong>')->span_add()->span_set('id','so_customer_detail_customer_type')->span_set('value',$so->customer->customer_type);
            
            $input_date = $pane->input_add()->input_set('label','Date')
                    ->input_set('id','so_date')
                    ->input_set('is_date_picker',true)
                    ->input_set('icon','fa fa-calendar')
                    ->input_set('value',$so->date)                    
                    ;            
            
                    
            $input_select_so_status = $pane->input_select_add()
                    ->input_select_set('name','item_id')
                    ->input_select_set('label','Status')
                    ->input_select_set('icon',App_Icon::item())
                    ->input_select_set('min_length','0')
                    ->input_select_set('data_add',$so_status_list)
                    ->input_select_set('id','so_status')
                    ->input_select_set('value',(array)$so->sales_order_status)
                    ;
            
            $textarea_cancellation_reason = $pane->textarea_add()->textarea_set('label','Cencellation Reason')
                    ->textarea_set('id','so_cancellation_reason')
                    ->textarea_set('value',$so->cancellation_reason)
                    ->div_set('id','div_so_cancellation_reason')                    
                    ;            
            
            $textarea_notes=$pane->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','so_notes')
                    ->textarea_set('value',$so->notes)
                    ->textarea_set('attrib',array())                    
                    ;
            
            $so_approval = $pane->input_select_add()
                    ->input_select_set('label','Price Approval')
                    ->input_select_set('icon','fa fa-info')
                    ->input_select_set('min_length','1')
                    ->input_select_set('ajax_url',$path->ajax_search.'so_approval')
                    ->input_select_set('value',(array)$so->approval)
                    ->input_select_set('data_add',array())
                    ->input_select_set('id','so_approval_id')
                    ;
            
            $input_select_item = $pane->input_select_add()
                    ->input_select_set('label','Item')
                    ->input_select_set('icon',App_Icon::item())
                    ->input_select_set('min_length','1')
                    ->input_select_set('ajax_url',$path->ajax_search.'so_item')
                    ->input_select_set('value',array())
                    ->input_select_set('id','so_item_input_select')
                    ;
            
            $so_detail = self::get_so_detail($id);
            
            $param = array(
                'ajax_search'=>$path->ajax_search
                ,'index_url'=>$path->index
            );
            $so_js = get_instance()->load->view('transaction/sales/so_js',$param,TRUE);
            $app->js_set($so_js);
            
            $new_customer_modal = $app->engine->modal_add();
            
            if($controller_method =='add'){
                $pane->custom_component_add()
                        ->src_set('transaction/sales/so')
                        ;
                $param = array(
                    'ajax_search'=>$path->ajax_search
                    ,'index_url'=>$path->index
                    ,'so_detail'=>$so_detail
                    ,'id'=>$id
                );
                $item_js = get_instance()->load->view('transaction/sales/so_add_edit_js',$param,TRUE);
                $app->js_set($item_js);
                
                self::new_customer_modal_render($app,$new_customer_modal);
                $select2_js = '
                    $("[for=\'s2id_autogen1_search\']").parent().parent().append(\'<input id="so_new_customer" class="btn btn-primary" style="margin:4px 4px 4px 5px" type="button" value="New Customer">\');
                    $("#so_new_customer").on("click",function(){

                        $("[for=\'s2id_autogen1_search\']").parent().parent().hide();
                        $("#new_customer_modal").modal("show");

                    });
                ';
                $app->js_set($select2_js);
                
            }
            else{
                
                $tbl = $pane->table_add();
                $tbl->table_set('class','table');
                $tbl->table_set('id','so_item_table');
                $tbl->table_set('columns',array("name"=>"row_num","label"=>"#"));            
                $tbl->table_set('columns',array("name"=>"item_name","label"=>"Item"));
                $tbl->table_set('columns',array("name"=>"ordered_qty","label"=>"Ordered Qty"));
                //$tbl->table_set('columns',array("name"=>"pending_invoice","label"=>"Pending Invoiced"));
                //$tbl->table_set('columns',array("name"=>"pending_qty","label"=>"Pending Qty"));
                $tbl->table_set('columns',array("name"=>"unit_name","label"=>"Unit"));
                $tbl->table_set('columns',array("name"=>"price","label"=>"Price"));
                $tbl->table_set('columns',array("name"=>"discount","label"=>"Discount"));
                $tbl->table_set('columns',array("name"=>"sub_total","label"=>"Sub Total"));
                
                $db = new DB();
                $q = '
                    select 
                        t2.id item_id
                        , t2.name item_name, t3.id unit_id, t3.name unit_name
                        , format(t1.qty,2) ordered_qty
                        , format(t1.price,2) price
                        ,format(t1.sub_total,2) sub_total
                        ,format(t1.qty - COALESCE(t4.sent_qty,0),2) pending_qty
                        ,format(t1.qty - COALESCE(t4.sent_qty,0),2) pending_invoice
                        ,format(t1.discount,2) discount
                    from sales_order_detail t1
                        inner join item t2 on t1.item_id = t2.id
                        inner join unit t3 on t3.id = t1.unit_id
                        left outer join (
                                select tt4.id so_id, tt2.item_id, tt2.unit_id, sum(tt2.qty) sent_qty
                                from movement tt1
                                        inner join movement_detail tt2 on tt1.id = tt2.movement_id
                                        inner join sales_order_movement tt3 on tt3.movement_id = tt1.id
                                        inner join sales_order tt4 on tt4.id = tt3.sales_order_id
                                where  tt1.movement_status != "X"
                                group by tt2.item_id, tt2.unit_id, tt4.id
                        ) t4 on t4.item_id = t1.item_id and t4.unit_id = t1.unit_id and t4.so_id = t1.sales_order_id
                    where t1.sales_order_id = '.$db->escape($id).'
                ';
                $rs = $db->query_array($q);
                $total = 0;
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['row_num'] = $i+1; 
                    //$total+= str_replace(',','',$rs[$i]['sub_total']);
                }
                
                $q = '
                    select 
                    format(total,2) total
                    ,format(grand_total,2) grand_total 
                    ,format(discount,2) discount
                    from sales_order where id = '.$db->escape($id);
                $so_header = $db->query_array_obj($q);
                
                $total = $so_header[0]->total;
                $discount = $so_header[0]->discount;
                $grand_total = $so_header[0]->grand_total;
                
                $tbl->table_set('data',$rs);
                $tbl->footer_set('<tfoot>
                    <tr><td colspan="5" /><td/><strong>TOTAL</strong></td><td ><strong>'.$total.'</strong></td></tr>
                    <tr>    <td style="border-top:none" colspan="5" /><td style="border-top:none"/><strong>DISCOUNT</strong></td><td style="border-top:none"><strong>'.$discount.'</strong></td></tr>
                    <tr>    <td  colspan="5" /><td/><strong>GRANDTOTAL</strong></td><td ><strong>'.$grand_total.'</strong></td></tr>
                    </tfoot>');
                
            }
            
            
            $disabled = array('disabled'=>'');
            
            if($so->sales_order_status->id !== 'X'){
                $textarea_cancellation_reason->div_set('class','hidden');
            }
            
            $pane->hr_add();
            
            if($controller_method  == 'add'){
                $pane->button_add()->button_set('value','Submit')
                    ->button_set('id','so_submit')
                    ->button_set('icon',App_Icon::detail_btn_save())
                    ;
                $div_customer_detail->div_set('class','box hidden');
                $so_approval->input_select_set('checkbox',true);
            }            
            else if ($controller_method == 'edit'){
                if(strlen($id)>0){
                    $so_approval->input_select_set('attrib',$disabled);
                    $div_customer_detail->div_set('class','box');
                    $input_select_customer->input_select_set('attrib',$disabled);
                    $input_date->input_set('attrib',$disabled);
                    $input_select_item->div_set('class','hidden');
                    
                    if($so->sales_order_status->id !=='X'){
                        $pane->button_add()->button_set('value','Submit')
                        ->button_set('id','so_submit')
                        ->button_set('icon',App_Icon::detail_btn_save())
                        ;
                    }
                    
                    if($so->sales_order_status->id == 'X'){
                        $input_select_so_status->input_select_set('attrib',$disabled);
                        $textarea_cancellation_reason->textarea_set('attrib',$disabled);
                        $textarea_notes->textarea_set('attrib',$disabled);
                    }
                }                
            }
            else if ($controller_method == 'view'){
                $so_approval->input_select_set('attrib',$disabled);
                $div_customer_detail->div_set('class','box');
                $input_select_item->div_set('class','hidden');
                $input_select_customer->input_select_set('attrib',$disabled);
                $input_date->input_set('attrib',$disabled);
                $input_select_so_status->input_select_set('attrib',$disabled);
                $textarea_notes->textarea_set('attrib',$disabled);
                $textarea_cancellation_reason->textarea_set('attrib',$disabled);
                if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'sales','edit')){
                    if($so->sales_order_status->id !=='X'){
                        $pane->button_add()->button_set('value','Edit')
                            ->button_set('id','so_edit')
                            ->button_set('icon',App_Icon::detail_btn_edit())
                            ->button_set('href',$path->index.'edit/'.$id);
                            ;
                    }
                }
            }
            
            $pane->button_add()->button_set('value','BACK')
                    ->button_set('icon',App_Icon::btn_back())
                    ->button_set('href',$path->index)
                    ->button_set('class','btn btn-danger')
                    ;
        }
        
        public function new_customer_modal_render($app,$modal){
            $data = array(
                'id'=>''
                ,'code'=>''
                ,'name'=>''
                ,'address'=>''
                ,'phone'=>''
                ,'phone2'=>''
                ,'phone3'=>''
                ,'phone4'=>''
                ,'phone5'=>''
                ,'address'=>''
                ,'city'=>''
                ,'country'=>''            
                ,'email'=>''
                ,'notes'=>''
                ,'customer_type_id'=>''

            );
            
            $selected_u_group=array("id"=>"","data"=>"");
            
            $q ='
                select id id,name data
                from customer_type
                where status>0
            ';
            $db = new DB();
            $customer_type = $db->query_array($q);
            $modal->id_set('new_customer_modal')->width_set('75%')
                        ->header_set(array('title'=>'Customer','icon'=>'fa fa-user'));
            $modal->input_add()->input_set('label','Phone')->input_set('name','phone')
                    ->input_set('id','new_customer_modal_phone')
                    ->input_set('icon','fa fa-phone')
                    ->input_set('input_mask_type','phone-mobile')
                    ->input_set('value',$data['phone']);
            $modal->input_add()->input_set('label','Phone 2')->input_set('name','phone2')
                    ->input_set('icon','fa fa-phone')
                    ->input_set('id','new_customer_modal_phone2')
                    ->input_set('input_mask_type','phone-mobile')
                    ->input_set('value',$data['phone2']);
            $modal->input_add()->input_set('label','Phone 3')->input_set('name','phone3')
                    ->input_set('icon','fa fa-phone')
                    ->input_set('id','new_customer_modal_phone3')
                    ->input_set('input_mask_type','phone-mobile')
                    ->input_set('value',$data['phone3']);
            $modal->input_add()->input_set('label','Phone 4')->input_set('name','phone4')
                    ->input_set('icon','fa fa-phone')
                    ->input_set('id','new_customer_modal_phone4')
                    ->input_set('input_mask_type','phone-mobile')
                    ->input_set('value',$data['phone4']);
            $modal->input_add()->input_set('label','Phone 5')->input_set('name','phone5')
                    ->input_set('icon','fa fa-phone')
                    ->input_set('id','new_customer_modal_phone5')
                    ->input_set('input_mask_type','phone-mobile')
                    ->input_set('value',$data['phone5']);
            $modal->input_add()->input_set('label','Code')
                    ->input_set('id','new_customer_modal_code')
                    ->input_set('name','code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('value',$data['code']);
            
            $customer_type_columns = array(
                array(
                    "name"=>"code"
                    ,"label"=>"Code"
                )
                ,array(
                    "name"=>"name"
                    ,"label"=>"Name"
                )
            );

            $customer_type_ist = $modal->input_select_table_add();
            $customer_type_ist->input_select_set('name','customer_id')
                    ->input_select_set('id','input_select')
                    ->input_select_set('label','Customer Type')
                    ->input_select_set('icon','fa fa-tag')
                    ->input_select_set('min_length','1')
                    ->input_select_set('data_add',$customer_type)
                    ->input_select_set('value',array("id"=>"","data"=>""))
                    ->table_set('columns',$customer_type_columns)
                    ->table_set('id',"new_customer_modal_customer_type_table")
                    ->table_set('ajax_url',get_instance()->config->base_url().'customer/ajax_search/customer_type')
                    ->table_set('column_key','id')
                    ->table_set('allow_duplicate_id',false)
                    ->table_set('selected_value',array());
                    ;
            
            
            $modal->input_add()->input_set('label','Name')->input_set('name','name')
                    ->input_set('id','new_customer_modal_name')
                    ->input_set('icon','fa fa-user')
                    ->input_set('value',$data['name']);
            $modal->input_add()->input_set('label','Address')->input_set('name','address')
                    ->input_set('id','new_customer_modal_address')
                    ->input_set('icon','fa fa-location-arrow')
                    ->input_set('value',$data['address']);
            $modal->input_add()->input_set('label','City')->input_set('name','city')
                    ->input_set('id','new_customer_modal_city')
                    ->input_set('icon','fa fa-location-arrow')
                    ->input_set('value',$data['city']);
            $modal->input_add()->input_set('label','Country')->input_set('name','country')
                    ->input_set('id','new_customer_modal_country')
                    ->input_set('icon','fa fa-location-arrow')
                    ->input_set('value',$data['country']);
            
            $modal->input_add()->input_set('label','Email')->input_set('name','email')
                    ->input_set('icon','fa fa-envelope')
                    ->input_set('id','new_customer_modal_email')
                    ->input_set('value',$data['email']);

            $modal->textarea_add()->textarea_set('label','Notes')->textarea_set('name','notes')
                    ->textarea_set('value',$data['notes'])
                    ->textarea_set('id','new_customer_modal_notes')
                    ;
            
            $modal->modal_button_footer_add("new_customer_modal_button_save",'button','',  App_Icon::detail_btn_save(),'Submit');
            
            $param = array(
                'customer_ajax_url'=>get_instance()->config->base_url().'customer/add/'
            );
            $js = get_instance()->load->view('transaction/sales/new_customer_modal_js',$param,TRUE);
            $app->js_set($js);
        }
        
        
    }
?>
