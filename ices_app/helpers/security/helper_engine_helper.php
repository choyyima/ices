<?php
    class Helper_Engine {
        public static function save($helper_data){
            $db = get_instance()->db;
            $success = 1;
            $msg = array(); 
            $action = "";
            
            if(strlen($helper_data['id'])==0){
                unset($helper_data['id']);
                $action = "insert";
            }
            else{
                $action = "update";
                if(isset($helper_data['status'])){
                    if($helper_data['status'] == 0) $action = "delete";
                }
                
            }
            
            if(in_array($action,array("insert","update"))){
                $validation_res = self::validate($helper_data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            
            if($success == 1){
                $helper_data = self::adjust($helper_data);
                switch($action){
                    case 'insert':
                        $db->insert('security_helper',$helper_data);
                        $msg[] = 'Add Helper Success';
                        break;
                    case 'update':
                        $db->update('security_helper',$helper_data,array("id"=>$helper_data['id']));
                        $msg[] = "Update Helper Success";
                        break;
                    case 'delete':
                        $db->query('delete from security_helper where id = '.$db->escape($helper_data['id']));
                        $msg[] = "Delete Helper Success";
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
            $q = 'select 1 from security_helper where name = '.$db->escape($data['name']).' and method='.$db->escape($data['method']);
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
            $q = "select * from security_helper where id = ".$db->escape($id);
            return $db->query_array($q);
        }
        
        public static function get_all(){
            $db = new DB();
            $q = "select * from security_helper";
            return $db->query_array_obj($q);
        }
    }
?>
