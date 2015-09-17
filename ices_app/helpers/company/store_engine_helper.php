<?php
    class Store_Engine {
        
        public static function get($id=""){
            $db = new DB();
            $q = "select * from store where status>0 and id = ".$db->escape($id);
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $rs = $rs[0];
            else $rs = null;
            return $rs;
        }
        
        public static function store_exists($id=""){
            $result = false;
            $db = new DB();
            $q = "select * from store where status>0 and id = ".$db->escape($id);
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $result = true;
            return $result;
        }
        
        public static function validate($data=array()){
            $result = array(
                "success"=>1
                ,"msg"=>array()
            );
            if(strlen($data['code'])==0){
                $result['success'] = 0;
                $result['msg'][] = "Code cannot be empty";
            }
            
            if(strlen($data['name'])==0){
                $result['success'] = 0;
                $result['msg'][] = "Name cannot be empty";
            }
            return $result;
        }
        
        public static function adjust($data=array()){
            $result = array(
                'store'=>array()
                ,'store_warehouse'=>array()
            );
            
            $result['store'] = $data;
            if(isset($result['store']))
            unset($result['store']['warehouse_id']);
            
            if(isset($data['warehouse_id']))
            foreach($data['warehouse_id'] as $warehouse_id){
                $result['store_warehouse'][] = array(
                    'store_id'=>''
                    ,'warehouse_id'=>$warehouse_id
                );
            }
            
            
            return $result;
        }
        
        public static function save($data){
            $db = new DB();
            $success = 1;
            $msg = array();
            $action = "";
            $result = array('success'=>'','msg'=>'');
            $store_id = $data['id'];
            
            if(strlen($data['id'])==0){
                unset($data['id']);
                $action = "insert";
            }
            else{
                $action = "update";
                if(isset($data['status'])){
                    if($data['status'] == 0) $action = "delete";
                }
                
            }
            
            if(in_array($action,array("insert","update"))){
                $validation_res = self::validate($data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            
            if($success == 1){
                $final_data = self::adjust($data);
                $store_warehouse = $final_data['store_warehouse']; 
                $store = $final_data['store'];
                
                $modid = User_Info::get()['user_id'];
                $moddate = date("Y-m-d H:i:s");
                
                switch($action){                    
                    case 'insert':
                        try{
                            $db->trans_begin();
                            
                            if(!$db->insert('store',$store)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $q = '
                                    select id 
                                    from store 
                                    where status>0 and code = '.$db->escape($store['code']).'
                                ';
                                $rs = $db->query_array_obj($q);
                                $store_id = $rs[0]->id;
                            }
                            
                            if($success == 1){  
                                for($i = 0;$i<count($store_warehouse);$i++){
                                    $store_warehouse[$i]['store_id'] = $store_id;
                                    if(!$db->insert('store_warehouse',$store_warehouse[$i])){
                                        $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                        break;
                                    }
                                }
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Store Success';
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
                            $store_data = array_merge($store,array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('store',$store,array("id"=>$store_id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $q = 'delete from store_warehouse where store_id = '.$db->escape($store_id);
                                if(!$db->query($q)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                for($i = 0;$i<count($store_warehouse);$i++){
                                    $store_warehouse[$i]['store_id'] = $store_id;
                                    if(!$db->insert('store_warehouse',$store_warehouse[$i])){
                                        $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                        break;
                                    }
                                }
                            }                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Update Store Success';
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
                        $db->update('store',$data_delete,array("id"=>$store_id));
                        $msg[] = "Delete Store Success";
                        break;
                }
            }
            
            $result['success'] = $success;
            $result['msg'] = $msg;
            
            if($success == 1){
                Message::set('success',$msg);
            }
            else{
                Message::set('error',$msg);
            }
            
            return $result;
        }
        
        
        
        
        
        public static function detail_render($pane,$data){
            $store = self::get($data['id']);
            $pane->div_add()->div_set("class","form-group");
            $first_row = $pane->div_add()->div_set("class","form-group");
            $first_row->label_add()->label_set("value",'Code: ');
            $first_row->span_add()->span_set("value",$store->code);
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'store','edit')){
                $first_row->button_add()->button_set('value','Edit')
                        ->button_set('style','margin-left:20px')
                        ->button_set('icon','fa fa-pencil-square-o')
                        ->button_set('href',get_instance()->config->base_url().'store/edit/'.$data['id']);
            }
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'store','delete')){
                $first_row->button_add()->button_set('value','Delete')
                        ->button_set('icon','fa fa-cut')
                        ->button_set('class','btn btn-danger')
                        ->button_set('confirmation',true)
                        ->button_set('confirmation msg','Are you sure want to delete '.$store->name.'?')
                        ->button_set('href',get_instance()->config->base_url().'store/delete/'.$data['id']);
            }
            
            //$pane->label_span_add()->label_span_set("value",array('label'=>"Code: ","span"=>$store->code));
            
            $db = new DB();
            $q = '
                select group_concat(t3.name separator ", ") name
                from store t1
                    inner join store_warehouse t2 on t1.id = t2.store_id
                    inner join warehouse t3 on t3.id = t2.warehouse_id
                where t1.id = '.$db->escape($data['id']).'
            ';
            
            $warehouse_name = $db->query_array_obj($q)[0]->name;
            
            $pane->label_span_add()->label_span_set("value",array('label'=>"Name: ","span"=>$store->name));
            $pane->label_span_add()->label_span_set("value",array('label'=>"Address: ","span"=>$store->address));
            $pane->label_span_add()->label_span_set("value",array('label'=>"City: ","span"=>$store->city));
            $pane->label_span_add()->label_span_set("value",array('label'=>"Country: ","span"=>$store->country));
            $pane->label_span_add()->label_span_set("value",array('label'=>"Phone: ","span"=>$store->phone));
            $pane->label_span_add()->label_span_set("value",array('label'=>"Email: ","span"=>$store->email));
            $pane->label_span_add()->label_span_set("value",array('label'=>"Warehouse: ","span"=>$warehouse_name));
            
            $pane->textarea_add()->textarea_set('label','Notes')->textarea_set('name','notes')
                ->textarea_set('value',$store->notes)
                ->textarea_set('attrib',array('disabled'=>''))
                ;
            $pane->hr_add();
            $pane->button_add()->button_set('value','BACK')
                ->button_set('icon','fa fa-arrow-left')
                ->button_set('href',get_instance()->config->base_url().'store/index');
        }
        
    }
?>
