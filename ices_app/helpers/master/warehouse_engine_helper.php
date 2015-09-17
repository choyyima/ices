<?php
    class Warehouse_Engine {
        
        public static function get($id=""){
            $result = null;
            $db = new DB();
            $q = "
                select t1.*
                    , concat(t2.first_name,\" \",t2.last_name) warehouse_manager_name
                    , concat(t3.code,\" - \",t3.name) warehouse_type
                    ,t3.code warehouse_type_code
                    ,t3.name warehouse_type_name
                from warehouse t1
                    left outer join user_login t2 on t1.warehouse_manager_id = t2.id
                    inner join warehouse_type t3 on t1.warehouse_type_id = t3.id
                where t1.status>0 and t1.id = ".$db->escape($id);
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $result = $rs[0];
            
            return $result;
        }
        
        public static function warehouse_get($id=""){
            $result = array();
            $db = new DB();
            $q = "
                select t1.*
                    , concat(t2.first_name,\" \",t2.last_name) warehouse_manager_name
                    , concat(t3.code,\" - \",t3.name) warehouse_type
                    ,t3.code warehouse_type_code
                    ,t3.name warehouse_type_name
                from warehouse t1
                    left outer join user_login t2 on t1.warehouse_manager_id = t2.id
                    inner join warehouse_type t3 on t1.warehouse_type_id = t3.id
                where t1.status>0 and t1.id = ".$db->escape($id);
            $rs = $db->query_array($q);
            if(count($rs)>0) $result = $rs[0];
            
            return $result;
        }
        
        public static function customer_get(){
            $db = new DB();
            $result = array();
            $q = '
                select *
                from warehouse
                where code = "WC"
                    and status = 1
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0){
                $result = $rs;
            }
            
            return $result;
        }
        
        public static function expedition_get(){
            $db = new DB();
            $result = array();
            $q = '
                select *
                from warehouse
                where code = "WE"
                    and status = 1
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0){
                $result = $rs;
            }
            
            return $result;
        }
        
        public static function supplier_get(){
            //<editor-fold defaultstate="collapsed">
            $db = new DB();
            $result = array();
            $q = '
                select t1.*,
                    t2.code warehouse_type_code,
                    t2.name warehouse_type_name
                from warehouse t1
                    inner join warehouse_type t2 on t1.warehouse_type_id = t2.id
                where t1.code = "WS"
                    and t1.status = 1
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0){
                $result = $rs;
            }
            
            return $result;
            //</editor-fold>
        }
        
        public static function refill_subcon_get(){
            //<editor-fold defaultstate="collapsed">
            $db = new DB();
            $result = array();
            $q = '
                select w.*,
                    wt.code warehouse_type_code,
                    wt.name warehouse_type_name
                from warehouse w
                    inner join warehouse_type wt on w.warehouse_type_id = wt.id
                where w.code = "WRS"
                    and w.status = 1
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0){
                $result = $rs;
            }
            
            return $result;
            //</editor-fold>
        }
        
        public static function BOS_get($field=''){
            //<editor-fold defaultstate="collapsed">
            $db = new DB();
            $result = array();
            $q = '
                select t1.*
                from warehouse t1
                    inner join warehouse_type t2 on t1.warehouse_type_id = t2.id
                where t1.status>0
                and t2.code = "BOS"
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0){
                if(is_string($field)){
                    switch($field){
                        case '':
                            $result = $rs;
                            break;
                        case 'id':
                            $temp_result = Tools::array_extract($rs,array('id'));
                            foreach($temp_result as $temp_idx=>$temp){
                                $result[] = $temp['id'];
                            }
                            break;

                    }
                }
                elseif(is_array($field)){
                    for($i = 0;$i<count($rs);$i++){
                        $trow = array();
                        foreach($field as $val){
                            $trow[$val] = null;
                            if(array_key_exists($val, $rs[$i])){
                                $trow[$val] = $rs[$i][$val];
                            }
                        }
                        $result[] = $trow;
                    }
                }
            }
            return $result;
            //</editor-fold>
        }
       
        public static function BOS_search($lookup_data){
            //<editor-fold defaultstate="collapsed">
            $db = new DB();
            $result = array();
            $q = '
                select t1.*
                from warehouse t1
                    inner join warehouse_type t2 on t1.warehouse_type_id = t2.id
                where t1.status>0
                    and t1.code like '.$db->escape('%'.$lookup_data.'%').'
                and t2.code = "BOS"
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0){
                $result = $rs;
            }
            return $result;
            //</editor-fold>
        }
        
        public static function is_type($type_code,$warehouse_id){
            $result = false;
            $db = new DB();
            $q = '
                select 1
                from warehouse t1
                    inner join warehouse_type t2 on t1.warehouse_type_id = t2.id
                where t1.id = '.$db->escape($warehouse_id).'
                    and t2.code = '.$db->escape($type_code).'
                    and t1.status>0
            ';
            if(count($db->query_array($q))>0) $result = true;
            return $result;
        }
        
        public static function validate($data=array()){
            //<editor-fold defaultstate="collapsed">
            $result = array(
                "success"=>1
                ,"msg"=>array()
            );
            $db = new DB();
            
            if(strlen($data['code'])==0){
                $result['success'] = 0;
                $result['msg'][] = "Code cannot be empty";
            }
            
            
            
            $warehouse_type_id = isset($data['warehouse_type_id'])?$data['warehouse_type_id']:'';
            
            $q = '
                select * from warehouse_type where id = '.$db->escape($warehouse_type_id).'
            ';            
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                if($rs[0]->code === 'BOS'){
                    $warehouse_manager_id = isset($data['warehouse_manager_id'])?$data['warehouse_manager_id']:'';
                    if(strlen($data['warehouse_manager_id']) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Manager cannot be empty";   
                    }
                }
            }
            else{
                $result['success'] = 0;
                $result['msg'][] = 'Invalid Warehouse Type';
            }
                        
            return $result;
            //</editor-fold>
        }
        
        public static function adjust($data=array()){
            $result = $data;
            unset($result['warehouse_manager']);
            unset($result['warehouse_type']);
            return $result;
        }
        
        public static function save($warehouse_data){
            //<editor-fold defaultstate="collapsed">
            $db = new DB();
            $success = 1;
            $msg = array();
            $action = "";
            
            if(strlen($warehouse_data['id'])==0){
                unset($warehouse_data['id']);
                $action = "insert";
            }
            else{
                $action = "update";
                if(isset($warehouse_data['status'])){
                    if($warehouse_data['status'] == 0) $action = "delete";
                }
                
            }
            
            if(in_array($action,array("insert","update"))){
                $validation_res = self::validate($warehouse_data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            
            if($success == 1){
                $warehouse_data = self::adjust($warehouse_data);
                $modid = User_Info::get()['user_id'];
                $moddate = date("Y-m-d H:i:s");
                switch($action){                    
                    case 'insert':
                        try{
                            $db->trans_begin();
                            
                            if(!$db->insert('warehouse',$warehouse_data)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }                            
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Warehouse Success';
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
                            $warehouse_data = array_merge($warehouse_data,array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('warehouse',$warehouse_data,array("id"=>$warehouse_data['id']))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }                            
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Update Warehouse Success';
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
                        $db->update('warehouse',$data_delete,array("id"=>$warehouse_data['id']));
                        $msg[] = "Delete Warehouse Success";
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
            //</editor-fold>
        }
        
        public static function detail_render($pane,$data){
            //<editor-fold defaultstate="collapsed">
            $warehouse = self::get($data['id']);
            $pane->div_add()->div_set("class","form-group");
            $first_row = $pane->div_add()->div_set("class","form-group");
            $first_row->label_add()->label_set("value",'Code: ');
            $first_row->span_add()->span_set("value",$warehouse->code);
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'warehouse','edit')){
                $first_row->button_add()->button_set('value','Edit')
                        ->button_set('style','margin-left:20px')
                        ->button_set('icon','fa fa-pencil-square-o')
                        ->button_set('href',get_instance()->config->base_url().'warehouse/edit/'.$data['id']);
            }
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'warehouse','delete')){
                $first_row->button_add()->button_set('value','Delete')
                        ->button_set('icon','fa fa-cut')
                        ->button_set('class','btn btn-danger')
                        ->button_set('confirmation',true)
                        ->button_set('confirmation msg','Are you sure want to delete '.$warehouse->name.'?')
                        ->button_set('href',get_instance()->config->base_url().'warehouse/delete/'.$data['id']);
            }
            
            //$pane->label_span_add()->label_span_set("value",array('label'=>"Code: ","span"=>$warehouse->code));
            $pane->label_span_add()->label_span_set("value",array('label'=>"Name: ","span"=>$warehouse->name));
            $pane->label_span_add()->label_span_set("value",array('label'=>"Warehouse Type: ","span"=>$warehouse->warehouse_type));
            $pane->label_span_add()->label_span_set("value",array('label'=>"Warehouse Manager: ","span"=>$warehouse->warehouse_manager_name));
            $pane->label_span_add()->label_span_set("value",array('label'=>"Address: ","span"=>$warehouse->address));
            $pane->label_span_add()->label_span_set("value",array('label'=>"City: ","span"=>$warehouse->city));
            $pane->label_span_add()->label_span_set("value",array('label'=>"Country: ","span"=>$warehouse->country));
            $pane->label_span_add()->label_span_set("value",array('label'=>"Phone: ","span"=>$warehouse->phone));
            $pane->label_span_add()->label_span_set("value",array('label'=>"Email: ","span"=>$warehouse->email));
            //$pane->label_span_add()->label_span_set("value",array('label'=>"Notes: ","span"=>$warehouse->notes));
            $pane->textarea_add()->textarea_set('label','Notes')->textarea_set('name','notes')
                ->textarea_set('value',$warehouse->notes)
                ->textarea_set('attrib',array('disabled'=>''))
                ;
            $pane->hr_add();
            $pane->button_add()->button_set('value','BACK')
                ->button_set('icon','fa fa-arrow-left')
                ->button_set('class','btn btn-default')
                ->button_set('href',get_instance()->config->base_url().'warehouse/index');
            //</editor-fold>
        }
        
    }
?>
