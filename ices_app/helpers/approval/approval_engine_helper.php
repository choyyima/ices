<?php
    class Approval_Engine {
        
        public static function get($id=""){
            $db = new DB();
            $q = "select t1.*, t2.name approval_type 
                    from approval  t1
                        inner join approval_type t2 on t1.approval_type_id = t2.id                    
                    where t1.status>0 and t1.id = ".$db->escape($id);
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $rs = $rs[0];
            else $rs = null;
            return $rs;
        }
        
        public static function detail_render($pane,$data){
            $approval = self::get($data['id']);
            $pane->div_add()->div_set("class","form-group");
            $first_row = $pane->div_add()->div_set("class","form-group");
            $first_row->label_add()->label_set("value",'Code: ');
            $first_row->span_add()->span_set("value",$approval->code);

            if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'approval','delete')){
                $first_row->button_add()->button_set('value','Delete')
                        ->button_set('icon','fa fa-cut')
                        ->button_set('class','btn btn-danger')
                        ->button_set('confirmation',true)
                        ->button_set('confirmation msg','Are you sure want to delete '.$approval->name.'?')
                        ->button_set('href',get_instance()->config->base_url().'approval/delete/'.$data['id']);
            }

            $pane->label_span_add()->label_span_set("value",array('label'=>"Type: ","span"=>$approval->approval_type));
            $pane->label_span_add()->label_span_set("value",array('label'=>"Name: ","span"=>$approval->name));
            $pane->label_span_add()->label_span_set("value",array('label'=>"Due Date: ","span"=>$approval->due_date));
            $pane->textarea_add()->textarea_set('label','Notes')->textarea_set('name','notes')
                ->textarea_set('value',$approval->notes)
                ->textarea_set('attrib',array('disabled'=>''))
                ;
            $pane->hr_add();
            $pane->button_add()->button_set('value','BACK')
                ->button_set('icon','fa fa-arrow-left')
                ->button_set('href',get_instance()->config->base_url().'approval/index');
        }
        
        public static function validate($action,$data=array()){
            $result = array(
                "success"=>1
                ,"msg"=>array()
            );
            if($action == 'insert'){
                if(strlen($data['name'])==0){
                    $result['success'] = 0;
                    $result['msg'][] = "Name cannot be empty";
                }

                if(strlen($data['due_date'])==0){
                    $result['success'] = 0;
                    $result['msg'][] = "Date cannot be empty";
                }

                if(strlen($data['approval_type_id'])==0){
                    $result['success'] = 0;
                    $result['msg'][] = "Approval Type cannot be empty";
                }
                $limit = isset($data['limit'])?$data['limit']:'';
                if((int)$limit<1){
                    $result['success'] = 0;
                    $result['msg'][] = "Limit at least 1";
                }
            }
            else if ($action == 'delete'){
                $db = new DB();
                $q = '
                    select 1 from approval where used != 0 and id = '.$data['id'].'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $result['success'] = 0;
                    $result['msg'][] = "Cannot delete used approval";
                }
            }
            return $result;
        }
        
        public static function adjust($action,$data=array()){
            $result = $data;
            switch($action){
                case 'insert':
                    $db = new DB();
                    $q = 'select code from approval_type where id = '.$db->escape($data['approval_type_id']).'';
                    $rs = $db->query_array_obj($q);
                    $approval_type_code = $rs[0]->code;
                    if(strtolower($approval_type_code) == 'sip'){
                        $rs = $db->query_array_obj('select func_code_counter("approval_sales_invoice_pos") "code"');
                        $approval_code = $rs[0]->code;
                        $result['code'] = $approval_code;
                    }

                    $result['due_date'] = $result['due_date'];
                    break;
            }
            return $result;
        }
        
        public static function save($approval_data){
            $db = new DB();
            $success = 1;
            $msg = array();
            $action = "";
            $result = array('success'=>1,'msg'=>[],'id'=>'');
            if(strlen($approval_data['id'])==0){
                unset($approval_data['id']);
                $action = "insert";
            }
            else{
                $action = "update";
                if(isset($approval_data['status'])){
                    if($approval_data['status'] == 0) $action = "delete";
                }
                
            }
            
            if(in_array($action,array("insert","update",'delete'))){
                $validation_res = self::validate($action,$approval_data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            
            if($success == 1){
                $approval_data = self::adjust($action,$approval_data);
                $modid = User_Info::get()['user_id'];
                $moddate = date("Y-m-d H:i:s");
                switch($action){                    
                    case 'insert':
                        try{
                            $db->trans_begin();
                            
                            $approval_data = array_merge($approval_data,array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->insert('approval',$approval_data)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $q = '
                                    select id from approval where code = '.$db->escape($approval_data['code']).'
                                ';
                                $rs = $db->query_array_obj($q);
                                $approval_id = $rs[0]->id;
                                $result['id'] = $approval_id;
                                        
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Approval Success';
                            }
                            
                            
                        }
                        catch(Exception $e){
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }
                        
                        break;
                    case 'update':
                        $success = 0;
                        $msg[] = 'Update Approval is not allowed';
                        break;
                    case 'delete':
                        $data_delete = array("status"=>0,"modid"=>$modid,"moddate"=>$moddate);
                        $db->update('approval',$data_delete,array("id"=>$approval_data['id']));
                        $msg[] = "Delete Approval Success";
                        break;
                }
            }
            if($success == 1){
                Message::set('success',$msg);
            }
            else{
                Message::set('error',$msg);
            }
            
            $result['success'] = $success;
            $result['msg'] = $msg;
            
            return $result;
        }
        
        public function approval_use($db, $id){
            $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
            $success = 1;
            $msg = array();
            
            $modid = User_Info::get()['user_id'];
            $moddate = Date('Y-m-d H:i:s');
            $approval_id = $id;
            $q = '
                update approval
                set `use` = `use`+1,
                    `modid` = '.$db->escape($modid).',
                    `moddate`='.$db->escape($moddate).'
                where id = '.$db->escape($approval_id).'
            ';
            if(!$db->query($q)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
            
            $result['success'] = $success;
            $result['msg'] = $msg;
            
            return $result;
        }
        
    }
?>
