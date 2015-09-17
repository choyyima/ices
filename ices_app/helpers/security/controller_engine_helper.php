<?php
    class Controller_Engine {
        public static function save($controller_data){
            $db = get_instance()->db;
            $success = 1;
            $msg = array(); 
            $action = "";
            
            if(strlen($controller_data['id'])==0){
                unset($controller_data['id']);
                $action = "insert";
            }
            else{
                $action = "update";
                if(isset($controller_data['status'])){
                    if($controller_data['status'] == 0) $action = "delete";
                }
                
            }
            
            if(in_array($action,array("insert","update"))){
                $validation_res = self::validate($controller_data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            
            if($success == 1){
                $controller_data = self::adjust($controller_data);
                switch($action){
                    case 'insert':
                        $db->insert('security_controller',$controller_data);
                        $msg[] = 'Add Controller Success';
                        break;
                    case 'update':
                        $db->update('security_controller',$controller_data,array("id"=>$controller_data['id']));
                        $msg[] = "Update Controller Success";
                        break;
                    case 'delete':
                        $db->query('delete from security_controller where id = '.$db->escape($controller_data['id']));
                        $msg[] = "Delete Controller Success";
                        break;
                }                    
                Message::set('success',$msg);
            }
            else{
                Message::set('error',$msg);
            }
            
            if($success == 1) return 1;
            else return 0;
        }
        
        public static function validate($data=array()){
            $result = array(
                "success"=>1
                ,"msg"=>""
            );
            if(strlen($data['name'])==0){
                $result['success'] = 0;
                $result['msg'][] = "Name cannot be empty";
            }
            if(strlen($data['method'])==0){
                $result['success'] = 0;
                $result['msg'][] = "Method cannot be empty";
            }
            
            
            
            $db = new DB();
            $id = isset($data['id'])?$db->escape($data['id']):'""';
            $q = 'select 1 from security_controller where id !='.$id.' and name = '.$db->escape($data['name']).' and method='.$db->escape($data['method']);
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                $result['success'] = 0;
                $result['msg'][] = 'Duplicate Name and Method';
            }
            
            return $result;
        }
        
        public static function adjust($data=array()){
            $result = $data;
            return $result;
        }
        
        public static function get($id=""){
            $db = new DB();
            $q = "select * from security_controller where id = ".$db->escape($id);
            return $db->query_array($q);
        }
        
        public static function get_all(){
            $db = new DB();
            $q = "select * from security_controller";
            return $db->query_array_obj($q);
        }
        
        public static function detail_render($pane,$data){
            $controller = self::get($data['id']);
            $controller = json_decode(json_encode($controller[0]));
            $pane->div_add()->div_set("class","form-group");
            $first_row = $pane->div_add()->div_set("class","form-group");
            $first_row->label_add()->label_set("value",'Name: ');
            $first_row->span_add()->span_set("value",$controller->name);
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'controller','edit')){
                $first_row->button_add()->button_set('value','Edit')
                        ->button_set('style','margin-left:20px')
                        ->button_set('icon','fa fa-pencil-square-o')
                        ->button_set('href',get_instance()->config->base_url().'controller/edit/'.$data['id']);
            }
            
            $pane->label_span_add()->label_span_set("value",array('label'=>"Method: ","span"=>$controller->method));
            
            $pane->hr_add();
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'controller','delete')){
                $pane->button_add()->button_set('value','Delete')
                        ->button_set('icon','fa fa-times')
                        ->button_set('class','btn btn-danger')
                        ->button_set('confirmation',true)
                        ->button_set('confirmation msg','Are you sure want to delete '.$controller->name.'/'.$controller->method.'?')
                        ->button_set('href',get_instance()->config->base_url().'controller/delete/'.$data['id']);
            }
            $pane->button_add()->button_set('value','BACK')
                ->button_set('icon','fa fa-arrow-left')
                ->button_set('class','btn btn-default')
                ->button_set('href',get_instance()->config->base_url().'controller/index');

        }
        
    }
?>
