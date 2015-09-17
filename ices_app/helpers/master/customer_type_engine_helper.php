<?php
    class Customer_Type_Engine {
        
        public static function get($id=""){
            $db = new DB();
            $q = "select * from customer_type where status>0 and id = ".$db->escape($id);
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $rs = $rs[0];
            else $rs = null;
            return $rs;
        }
        
        public static function validate($data=array()){
            //<editor-fold defaultstate="collapsed">
            $result = array(
                "success"=>1
                ,"msg"=>array()
            );
            if(strlen($data['code'])==0){
                $result['success'] = 0;
                $result['msg'][] = "Code cannot be empty";
            }
            
            $db = new DB();
            $id = isset($data['id'])?$db->escape($data['id']):'""';
            $q = '
                select 1 
                from customer_type
                where status>0 and id != '.$id.' and code = '.$db->escape($data['code']).'
            ';
            $rs = $db->query_array_obj($q);
            
            if(count($rs)>0){
                $result['success'] = 0;
                $result['msg'][] = 'Code already exists';
            }
            return $result;
            //</editor-fold>
        }
        
        public static function adjust($data=array()){
            $result['customer_type'] = $data;
            $result['product_price_list'] = array();
            $result['refill_product_price_list'] = array();
            unset($result['customer_type']['product_price_list']);
            if(isset($data['product_price_list'])){
                for($i = 0;$i<count($data['product_price_list']);$i++){
                    $result['product_price_list'][] = array(
                        'product_price_list_id'=>$data['product_price_list'][$i]
                        );
                }
            }
            unset($result['customer_type']['refill_product_price_list']);
            if(isset($data['refill_product_price_list'])){
                for($i = 0;$i<count($data['refill_product_price_list']);$i++){
                    $result['refill_product_price_list'][] = array(
                        'refill_product_price_list_id'=>$data['refill_product_price_list'][$i]
                        );
                }
            }
            return $result;
        }
        
        public static function save($customer_type_data){
            $db = new DB();
            $result = array("success"=>0,"msg"=>array(),'trans_id'=>'');
            $success = 1;
            $msg = array();
            $action = "";
            
            if(strlen($customer_type_data['id'])==0){
                unset($customer_type_data['id']);
                $action = "insert";
            }
            else{
                $action = "update";
                if(isset($customer_type_data['status'])){
                    if($customer_type_data['status'] == 0) $action = "delete";
                }
                
            }
            
            if(in_array($action,array("insert","update"))){
                $validation_res = self::validate($customer_type_data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            
            if($success == 1){
                $final_data = self::adjust($customer_type_data);
                $modid = User_Info::get()['user_id'];
                $moddate = date("Y-m-d H:i:s");
                $customer_type_id = '';
                switch($action){                    
                    case 'insert':
                        try{
                            $db->trans_begin();
                            $customer_type_data = $final_data['customer_type'];
                            $product_price_list_data = $final_data['product_price_list'];
                            $refill_product_price_list_data = $final_data['refill_product_price_list'];
                            $customer_type_data = array_merge($customer_type_data,array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->insert('customer_type',$customer_type_data)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }         
                            
                            if($success == 1){
                                $q = '
                                    select id 
                                    from customer_type
                                    where status>0 
                                        and code = '.$db->escape($customer_type_data['code']).'
                                ';
                                $rs = $db->query_array_obj($q);
                                $customer_type_id = $rs[0]->id;

                            }
                            
                            if($success == 1){
                                for($i = 0;$i<count($product_price_list_data);$i++){
                                    $product_price_list_data[$i]['customer_type_id'] = $customer_type_id;

                                    if(!$db->insert('customer_type_product_price_list',$product_price_list_data[$i])){
                                        $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                        break;
                                    }
                                }
                            }
                            
                            if($success == 1){
                                for($i = 0;$i<count($refill_product_price_list_data);$i++){
                                    $refill_product_price_list_data[$i]['customer_type_id'] = $customer_type_id;

                                    if(!$db->insert('customer_type_refill_product_price_list',$refill_product_price_list_data[$i])){
                                        $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                        break;
                                    }
                                }
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Customer Type Success';
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
                            $customer_type_data = $final_data['customer_type'];
                            $product_price_list_data = $final_data['product_price_list'];
                            $refill_product_price_list_data = $final_data['refill_product_price_list'];
                            $customer_type_id = $customer_type_data['id'];
                            $db->trans_begin();
                            $customer_type_data = array_merge($customer_type_data,array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('customer_type',$customer_type_data,array("id"=>$customer_type_data['id']))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }                            
                            
                            if($success == 1){
                                $db->query('delete from customer_type_product_price_list where customer_type_id = '.$db->escape($customer_type_id));
                                for($i = 0;$i<count($product_price_list_data);$i++){
                                    $product_price_list_data[$i]['customer_type_id'] = $customer_type_id;
                                    if(!$db->insert('customer_type_product_price_list',$product_price_list_data[$i])){
                                        $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                    }
                                }
                            }
                            
                            if($success == 1){
                                $db->query('delete from customer_type_refill_product_price_list where customer_type_id = '.$db->escape($customer_type_id));
                                for($i = 0;$i<count($refill_product_price_list_data);$i++){
                                    $refill_product_price_list_data[$i]['customer_type_id'] = $customer_type_id;

                                    if(!$db->insert('customer_type_refill_product_price_list',$refill_product_price_list_data[$i])){
                                        $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                        break;
                                    }
                                }
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Update Customer Type Success';
                            }
                            
                            
                        }
                        catch(Exception $e){
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }
                        
                        
                        break;
                    case 'delete':
                        $data_delete = array("status"=>0,"modid"=>$modid,"moddate"=>$moddate);
                        $db->update('customer_type',$data_delete,array("id"=>$customer_type_data['id']));
                        $db->trans_commit();
                        $msg[] = "Delete Customer Type Success";
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
        
        
        
        
        
        public static function detail_render($pane,$data){
            
            $db = new DB();
            $q = '
                select group_concat(t3.name) name
                from customer_type t1
                    inner join customer_type_product_price_list t2 on t1.id = t2.customer_type_id
                    inner join product_price_list t3 on t3.id = t2.product_price_list_id
                where t1.id = '.$db->escape($data['id']).'
            ';
            
            $product_price_list_name = $db->query_array_obj($q)[0]->name;
            
            $customer_type = self::get($data['id']);
            $pane->div_add()->div_set("class","form-group");
            $first_row = $pane->div_add()->div_set("class","form-group");
            $first_row->label_add()->label_set("value",'Code: ');
            $first_row->span_add()->span_set("value",$customer_type->code);
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'customer_type','edit')){
                $first_row->button_add()->button_set('value','Edit')
                        ->button_set('style','margin-left:20px')
                        ->button_set('icon','fa fa-pencil-square-o')
                        ->button_set('href',get_instance()->config->base_url().'customer_type/edit/'.$data['id']);
            }
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'customer_type','delete')){
                $first_row->button_add()->button_set('value','Delete')
                        ->button_set('icon','fa fa-cut')
                        ->button_set('class','btn btn-danger')
                        ->button_set('confirmation',true)
                        ->button_set('confirmation msg','Are you sure want to delete '.$customer_type->name.'?')
                        ->button_set('href',get_instance()->config->base_url().'customer_type/delete/'.$data['id']);
            }
            
            $pane->label_span_add()->label_span_set("value",array('label'=>"Name: ","span"=>$customer_type->name));
            $pane->label_span_add()->label_span_set("value",array('label'=>"Product Price List: ","span"=>$product_price_list_name));
            $pane->textarea_add()->textarea_set('label','Notes')->textarea_set('name','notes')
                ->textarea_set('value',$customer_type->notes)
                ->textarea_set('attrib',array('disabled'=>''))
                ;
            $pane->hr_add();
            $pane->button_add()->button_set('value','BACK')
                ->button_set('class','btn btn-default')
                ->button_set('icon','fa fa-arrow-left')
                ->button_set('href',get_instance()->config->base_url().'customer_type/index');
        }
        
    }
?>
