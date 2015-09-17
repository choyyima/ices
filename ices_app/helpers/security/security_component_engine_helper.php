<?php
class Security_Component_Engine {
    public static function save($component_data){
        $db = get_instance()->db;
        $success = 1;
        $msg = array(); 
        $action = "";

        if(strlen($component_data['id'])==0){
            unset($component_data['id']);
            $action = "insert";
        }
        else{
            $action = "update";
            if(isset($component_data['status'])){
                if($component_data['status'] == 0) $action = "delete";
            }

        }

        if(in_array($action,array("insert","update"))){
            $validation_res = self::validate($component_data);
            $success = $validation_res['success']; 
            $msg = $validation_res['msg'];
        }

        if($success == 1){
            $component_data = self::adjust($component_data);
            switch($action){
                case 'insert':
                    $db->insert('security_component',$component_data);
                    $msg[] = 'Add Component Success';
                    break;
                case 'update':
                    $db->update('security_component',$component_data,array("id"=>$component_data['id']));
                    $msg[] = "Update Component Success";
                    break;
                case 'delete':
                    $db->query('delete from security_component where id = '.$db->escape($component_data['id']));
                    $msg[] = "Delete Component Success";
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
        if(strlen($data['module'])==0){
            $result['success'] = 0;
            $result['msg'][] = "Module cannot be empty";
        }
        if(strlen($data['comp_id'])==0){
            $result['success'] = 0;
            $result['msg'][] = "Component ID cannot be empty";
        }



        $db = new DB();
        $id = isset($data['id'])?$db->escape($data['id']):'""';
        $q = 'select 1 from security_component where id !='.$id.' and module = '.$db->escape($data['module']).' and comp_id='.$db->escape($data['comp_id']);
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result['success'] = 0;
            $result['msg'][] = 'Duplicate Module and Comp. ID';
        }

        return $result;
    }

    public static function adjust($data=array()){
        $result = $data;
        return $result;
    }

    public static function get($id=""){
        $db = new DB();
        $q = "select * from security_component where id = ".$db->escape($id);
        return $db->query_array($q);
    }

    public static function get_all(){
        $db = new DB();
        $q = "select * from security_component";
        return $db->query_array_obj($q);
    }

    public static function detail_render($pane,$data){
        $component = self::get($data['id']);
        $component = json_decode(json_encode($component[0]));
        $pane->div_add()->div_set("class","form-group");
        $first_row = $pane->div_add()->div_set("class","form-group");
        $first_row->label_add()->label_set("value",'Name: ');
        $first_row->span_add()->span_set("value",$component->module);
        if(Security_Engine::get_component_permission(User_Info::get()['user_id'],'component','edit')){
            $first_row->button_add()->button_set('value','Edit')
                    ->button_set('style','margin-left:20px')
                    ->button_set('icon','fa fa-pencil-square-o')
                    ->button_set('href',get_instance()->config->base_url().'component/edit/'.$data['id']);
        }
        if(Security_Engine::get_component_permission(User_Info::get()['user_id'],'component','delete')){
            $first_row->button_add()->button_set('value','Delete')
                    ->button_set('icon','fa fa-cut')
                    ->button_set('class','btn btn-danger')
                    ->button_set('confirmation',true)
                    ->button_set('confirmation msg','Are you sure want to delete '.$component->module.'/'.$component->comp_id.'?')
                    ->button_set('href',get_instance()->config->base_url().'component/delete/'.$data['id']);
        }
        $pane->label_span_add()->label_span_set("value",array('label'=>"Method: ","span"=>$component->comp_id));

        $pane->hr_add();
        $pane->button_add()->button_set('value','BACK')
            ->button_set('icon','fa fa-arrow-left')
            ->button_set('class','btn btn-default')
            ->button_set('href',get_instance()->config->base_url().'component/index');

    }



}
?>
