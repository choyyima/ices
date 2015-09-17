<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Item_Price_List_Engine {
        
        public static function get($id=""){
            $db = new DB();
            $q = "
                select t1.*, t2.name customer_type_name 
                from t1.item_price_list 
                    left outer join customer_type t2 on t1.customer_type_id = t2.id
                where t1.status>0 and t1.id = ".$db->escape($id);
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $rs = $rs[0];
            else $rs = null;
            return $rs;
        }
        
        public static function subcategories_get($id=""){
            $db = new DB();
            $q = "select t2.* 
                from item t1
                inner join item_subcategory t2 on t1.item_subcategory_id = t2.id
                where t1.status>0 and t2.id and t1.id = ".$db->escape($id);
            $rs = $db->query_array_obj($q);
            return $rs;
        }
        
        public static function units_get($id=""){
            $db = new DB();
            $q = "select t3.* 
                from item t1
                inner join item_unit t2 on t1.id = t2.item_id
                inner join unit t3 on t3.id = t2.unit_id
                where t1.status>0 and t2.status>0 and t3.status>0 and t1.id = ".$db->escape($id);
            $rs = $db->query_array_obj($q);            
            return $rs;
        }
        
        
        public static function validate($data=array()){
            $result = array(
                "success"=>1
                ,"msg"=>array()
            );
            
            $item_price_list = $data['item_price_list'];
            $item_price_list_detail = $data['item_price_list_detail'];
            
            if(strlen($item_price_list['code'])==0){
                $result['success'] = 0;
                $result['msg'][] = "Code cannot be empty";
            }
            if(strlen($item_price_list['name'])==0){
                $result['success'] = 0;
                $result['msg'][] = "Name cannot be empty";
            }           
            
            $db = new DB();
            $id = isset($item_price_list['id'])?$db->escape($item_price_list['id']):'""';
            $q = '
                select 1 
                from item_price_list
                where status>0 and id != '.$id.' 
                    and (
                        code = '.$db->escape($item_price_list['code']).'                        
                    )
            ';
            $rs = $db->query_array_obj($q);
            

            if(count($rs)>0){
                $result['success'] = 0;
                $result['msg'][] = 'Code already exists';
            }
            
            foreach($item_price_list_detail as $price_list){
                $item = $price_list['item'];
                foreach($price_list['unit'] as $unit){
                    $price_from = 0; if(strlen($unit['price_from']>0)) $price_from = $unit['price_from'];
                    $price_to = 0; if(strlen($unit['price_to']>0)) $price_to = $unit['price_to'];
                    if($price_to<$price_from){
                        $result['success'] = 0;
                        $result['msg'][] = $item['name'].' '.$unit['name'].' Price From must be higher or equal than Price To';
                    }
                }
            }
            
            
            return $result;
        }
        
        public static function adjust($data=array()){
            $result = $data;
            
            return $result;
        }
        
        public static function save($data){
            $db = new DB();
            $success = 1;
            $msg = array();
            $action = "";
            $result = array("status"=>0,"msg"=>array());
            $item_price_list_data = $data['item_price_list'];
            $item_price_list_detail_data = $data['item_price_list_detail'];
            if(strlen($item_price_list_data['id'])==0){
                unset($item_price_list_data['id']);
                $action = "insert";
            }
            else{
                $action = "update";
                if(isset($item_price_list_data['status'])){
                    if($item_price_list_data['status'] == 0) $action = "delete";
                }
                
            }
            
            if(in_array($action,array("insert","update"))){
                $validation_res = self::validate($data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            
            if($success == 1){
                $item_price_list_data = self::adjust($item_price_list_data);
                $modid = User_Info::get()['user_id'];
                $moddate = date("Y-m-d H:i:s");
                switch($action){                    
                    case 'insert':
                        try{
                            $db->trans_begin();
                            $item_price_list_data = array_merge($item_price_list_data,array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->insert('item_price_list',$item_price_list_data)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $price_list = $db->query_array_obj('select id from item_price_list where status>0 and code = '.$db->escape($item_price_list_data['code']));
                                $price_list_id = $price_list[0]->id;
                                foreach($item_price_list_detail_data as $price_list_detail){
                                    $item = $price_list_detail['item'];
                                    
                                    foreach($price_list_detail['unit'] as $unit){
                                        $data_inserted = array(
                                            'item_price_list_id'=>$price_list_id
                                            ,"unit_id"=>$unit['id']
                                            ,'item_id'=>$item['id']
                                            ,'price_from'=>$unit['price_from']
                                            ,'price_to'=>$unit['price_to']
                                        );
                                        if(!$db->insert('item_price_list_detail',$data_inserted)){
                                            $msg[] = $db->_error_message();
                                            $db->trans_rollback();                                
                                            $success = 0;
                                            break;
                                        }                                        
                                    }                                    
                                }
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Item Success';
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
                            $item_price_list_data = array_merge($item_price_list_data,array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('item_price_list',$item_price_list_data,array("id"=>$item_price_list_data['id']))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }                            
                            if($success == 1){
                                $q = 'delete from item_price_list_detail where item_price_list_id = '.$db->escape($item_price_list_data['id']);
                                if(!$db->query($q)){
                                    $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                        break;
                                }
                            }
                            if($success == 1){
                                $price_list_id = $item_price_list_data['id'];
                                foreach($item_price_list_detail_data as $price_list_detail){
                                    $item = $price_list_detail['item'];
                                    
                                    foreach($price_list_detail['unit'] as $unit){
                                        $data_inserted = array(
                                            'item_price_list_id'=>$price_list_id
                                            ,"unit_id"=>$unit['id']
                                            ,'item_id'=>$item['id']
                                            ,'price_from'=>$unit['price_from']
                                            ,'price_to'=>$unit['price_to']
                                        );
                                        if(!$db->insert('item_price_list_detail',$data_inserted)){
                                            $msg[] = $db->_error_message();
                                            $db->trans_rollback();                                
                                            $success = 0;
                                            break;
                                        }                                        
                                    }                                    
                                }
                            }                            
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Update Item Success';
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
                        $db->update('item_price_list',$data_delete,array("id"=>$item_price_list_data['id']));
                        $msg[] = "Delete Item Success";
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
        
        
        
        public static function price_list_edit_render($pane,$data,$path,$app){
            
            $item_selector = $pane->input_select_add()
                ->input_select_set('name','item_id')
                ->input_select_set('label','Item')
                ->input_select_set('icon',App_Icon::item())
                ->input_select_set('min_length','1')
                ->input_select_set('ajax_url',$path->ajax_search.'item')
                ->input_select_set('value',array())
                ->input_select_set('id','item_selector')
                ;
            
            $pane->custom_component_add()
                ->src_set('master/item/item_price_list/item_price_list')
                ;
            
            $detail = array();
            $item = array('id'=>'','name'=>'');
            foreach($data['item_price_list_detail'] as $row){
                if($item['id']!=$row['item_id']){
                    $item['id'] = $row['item_id'];
                    $item['name'] = $row['item_name'];
                    $detail[] = array("item"=>$item,'unit'=>array());
                    
                }
                $detail[count($detail)-1]['unit'][] = array(
                    'id'=>$row['unit_id']
                    ,'name'=>$row['unit_name']
                    ,'price_from'=>$row['price_from']
                    ,'price_to'=>$row['price_to']
                );
            };
            
            $param = array(
                "ajax_url"=>$path->ajax_search
                ,"index_url"=>$path->index
                ,'item_price_list_detail'=>$detail
            );
            $js = get_instance()->load->view($path->item_price_list_js,$param,TRUE);
            
            $app->js_set($js);
        }
        
        public static function detail_edit_render($pane,$data){
            $pane->input_add()->input_set('label','Code')->input_set('name','code')
                ->input_set('icon','fa fa-info')
                ->input_set('value',$data['item_price_list']['code'])
                ->input_set('id','item_price_list_code');
            $pane->input_add()->input_set('label','Name')->input_set('name','name')
                ->input_set('icon','fa fa-gift')
                ->input_set('id','item_price_list_name')
                ->input_set('value',$data['item_price_list']['name']);
            $pane->textarea_add()->textarea_set('label','Notes')->textarea_set('name','notes')
                ->textarea_set('value',$data['item_price_list']['notes'])
                ->textarea_set('id','item_price_list_notes')
                ;
        }
        
        public static function detail_render($pane,$data,$path){
            $item_price_list = self::get($data['id']);
            
            $pane->div_add()->div_set("class","form-group");
            $first_row = $pane->div_add()->div_set("class","form-group");
            $first_row->label_add()->label_set("value",'Code: ');
            $first_row->span_add()->span_set("value",$item_price_list->code);
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'item_price_list','edit')){
                $first_row->button_add()->button_set('value','Edit')
                        ->button_set('style','margin-left:20px')
                        ->button_set('icon',App_Icon::detail_btn_edit())
                        ->button_set('href',$path->index.'edit/'.$data['id']);
            }
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'item_price_list','delete')){
                $first_row->button_add()->button_set('value','Delete')
                        ->button_set('icon',App_Icon::detail_btn_delete())
                        ->button_set('class','btn btn-danger')
                        ->button_set('confirmation',true)
                        ->button_set('confirmation msg','Are you sure want to delete '.$item_price_list->name.'?')
                        ->button_set('href',$path->index.'delete/'.$data['id']);
            }
            
            $pane->label_span_add()->label_span_set("value",array('label'=>"Name: ","span"=>$item_price_list->name));
            
            $pane->textarea_add()->textarea_set('label','Notes')->textarea_set('name','notes')
                ->textarea_set('value',$item_price_list->notes)
                ->textarea_set('attrib',array('disabled'=>''))
                ;
            $pane->hr_add();
            $pane->button_add()->button_set('value','BACK')
                ->button_set('icon','fa fa-arrow-left')
                ->button_set('href',get_instance()->config->base_url().'item/index');
        
        }
        
    }
?>
