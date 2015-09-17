<?php
    class Product_SubCategory_Engine {
        
        public static function get($id=""){
            $db = new DB();
            $q = "select * from product_subcategory where status>0 and id = ".$db->escape($id);
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $rs = $rs[0];
            else $rs = null;
            return $rs;
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
            
            if(strlen($data['product_category_id'])==0){
                $result['success'] = 0;
                $result['msg'][] = "Category cannot be empty";
            }
            
            $db = new DB();
            $id = isset($data['id'])?$db->escape($data['id']):'""';
            $q = '
                select 1 
                from product_subcategory
                where status>0 and id != '.$id.' and code = '.$db->escape($data['code']).'
            ';
            $rs = $db->query_array_obj($q);
            
            if(count($rs)>0){
                $result['success'] = 0;
                $result['msg'][] = 'Code already exists';
            }
            return $result;
        }
        
        public static function adjust($data=array()){
            $result = $data;
            
            return $result;
        }
        
        public static function save($product_subcategory_data){
            $db = new DB();
            $success = 1;
            $msg = array();
            $action = "";
            
            if(strlen($product_subcategory_data['id'])==0){
                unset($product_subcategory_data['id']);
                $action = "insert";
            }
            else{
                $action = "update";
                if(isset($product_subcategory_data['status'])){
                    if($product_subcategory_data['status'] == 0) $action = "delete";
                }
                
            }
            
            if(in_array($action,array("insert","update"))){
                $validation_res = self::validate($product_subcategory_data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            
            if($success == 1){
                $product_subcategory_data = self::adjust($product_subcategory_data);
                $modid = User_Info::get()['user_id'];
                $moddate = date("Y-m-d H:i:s");
                switch($action){                    
                    case 'insert':
                        try{
                            $db->trans_begin();
                            $product_subcategory_data = array_merge($product_subcategory_data,array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->insert('product_subcategory',$product_subcategory_data)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }                            
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Product Sub Category Success';
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
                            $product_subcategory_data = array_merge($product_subcategory_data,array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('product_subcategory',$product_subcategory_data,array("id"=>$product_subcategory_data['id']))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }                            
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Update Product Sub Category Success';
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
                        $db->update('product_subcategory',$data_delete,array("id"=>$product_subcategory_data['id']));
                        $msg[] = "Delete Product Sub Category Success";
                        break;
                }
            }
            if($success == 1){
                Message::set('success',$msg);
            }
            else{
                Message::set('error',$msg);
            }
            
            if($success == 1) return 1;
            else return 0;
        }
        
        
        
        
        
        public static function detail_render($pane,$data){
            $product_subcategory = self::get($data['id']);
            $pane->div_add()->div_set("class","form-group");
            $first_row = $pane->div_add()->div_set("class","form-group");
            $first_row->label_add()->label_set("value",'Code: ');
            $first_row->span_add()->span_set("value",$product_subcategory->code);
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'product_subcategory','edit')){
                $first_row->button_add()->button_set('value','Edit')
                        ->button_set('style','margin-left:20px')
                        ->button_set('icon','fa fa-pencil-square-o')
                        ->button_set('href',get_instance()->config->base_url().'product_subcategory/edit/'.$data['id']);
            }
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'product_subcategory','delete')){
                $first_row->button_add()->button_set('value','Delete')
                        ->button_set('icon','fa fa-cut')
                        ->button_set('class','btn btn-danger')
                        ->button_set('confirmation',true)
                        ->button_set('confirmation msg','Are you sure want to delete '.$product_subcategory->name.'?')
                        ->button_set('href',get_instance()->config->base_url().'product_subcategory/delete/'.$data['id']);
            }
            
            $pane->label_span_add()->label_span_set("value",array('label'=>"Name: ","span"=>$product_subcategory->name));
            $pane->textarea_add()->textarea_set('label','Notes')->textarea_set('name','notes')
                ->textarea_set('value',$product_subcategory->notes)
                ->textarea_set('attrib',array('disabled'=>''))
                ;
            $pane->hr_add();
            $pane->button_add()->button_set('value','BACK')
                ->button_set('icon','fa fa-arrow-left')
                ->button_set('href',get_instance()->config->base_url().'product_subcategory/index');
        }
        
    }
?>
