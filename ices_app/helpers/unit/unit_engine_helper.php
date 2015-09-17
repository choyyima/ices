<?php
class Unit_Engine {

    public static $prefix_id = 'unit';
    public static $prefix_method;
    public static $status_list;
    public static $module_type_list;
    public static $stock_location_list;

    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$prefix_method = self::$prefix_id;

        self::$status_list = array(
            array(
                'val'=>''
                ,'label'=>''
                , 'method'=>'unit_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('Unit'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>''
                ,'label'=>''
                ,'method'=>'unit_update'
                ,'default'=>true
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('Unit'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            )
            ,array(
                'val'=>''
                ,'label'=>''
                ,'method'=>'unit_delete'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Delete')
                        ,array('val'=>Lang::get(array('Unit'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            )

        );



        //</editor-fold>
    }
    
    public static function path_get(){
        return (object)array(
            'index' => get_instance()->config->base_url().'unit/'
        );
    }

    public static function validate($action,$data=array()){
        $result = array(
            "success"=>1
            ,"msg"=>array()
        );
        
        $success = 1;
        $msg = array();
        
        $code = isset($data['unit']['code'])?Tools::empty_to_null(Tools::_str($data['unit']['code'])):'';
        $name = isset($data['unit']['name'])?Tools::empty_to_null(Tools::_str($data['unit']['name'])):'';
        
        switch($action){
            case 'unit_add':
            case 'unit_update':
                if($code == null){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get('Code').' '.Lang::get('empty');
                }

                if($name == null){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get('Name').' '.Lang::get('empty');
                }
                break;
            case 'unit_delete':
                
                break;
            default:
                $success = 0;
                $msg[] = 'Invalid Method';
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        
        return $result;
    }

    public static function adjust($method='',$data=array()){
        $result = array();
        $unit = isset($data['unit'])?Tools::_arr($data['unit']):array();
        switch($method){
            case 'unit_add':
            case 'unit_update':
                $result['unit'] = array(
                    'code'=>$unit['code'],
                    'name'=>$unit['name']
                );
                break;
            case 'unit_delete':
                $result['unit'] = array(
                    'status'=>0
                );
                break;
        }
        
        

        return $result;
    }

    public static function save($unit_data){
        $db = new DB();
        $success = 1;
        $msg = array();
        $action = "";

        if(strlen($unit_data['id'])==0){
            unset($unit_data['id']);
            $action = "insert";
        }
        else{
            $action = "update";
            if(isset($unit_data['status'])){
                if($unit_data['status'] == 0) $action = "delete";
            }

        }

        if(in_array($action,array("insert","update"))){
            $validation_res = self::validate($unit_data);
            $success = $validation_res['success']; 
            $msg = $validation_res['msg'];
        }

        if($success == 1){
            $unit_data = self::adjust($unit_data);
            $modid = User_Info::get()['user_id'];
            $moddate = date("Y-m-d H:i:s");
            switch($action){                    
                case 'insert':
                    try{
                        $db->trans_begin();

                        if(!$db->insert('unit',$unit_data)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                        }                            

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = 'Add Unit Success';
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
                        $unit_data = array_merge($unit_data,array("modid"=>$modid,"moddate"=>$moddate));
                        if(!$db->update('unit',$unit_data,array("id"=>$unit_data['id']))){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                        }                            

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = 'Update Unit Success';
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
                    $db->update('unit',$data_delete,array("id"=>$unit_data['id']));
                    $msg[] = "Delete Unit Success";
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
    
    public function unit_add($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        $funit = $final_data['unit'];
        $unit_id = '';
        $db->trans_begin();
        

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $funit = array_merge($funit,array("modid"=>$modid,"moddate"=>$moddate));
        if(!$db->insert('unit',$funit)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success){
            $result['trans_id'] = SI::get_trans_id($db,'unit','code',$funit['code']);
            if($result['trans_id'] === null){
                $msg[] = 'Unable to get trans id';
                $db->trans_rollback();                                
                $success = 0;
            }
            $unit_id = $result['trans_id'];
        }

                
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        
        //</editor-fold>
    }
    
    public function unit_update($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        $funit = $final_data['unit'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $unit_id = $id;
        
        if(!$db->update('unit',$funit,array("id"=>$unit_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }                            
        $result['trans_id']=$id;
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    public function unit_delete($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        $funit = $final_data['unit'];
        $unit_id = $id;
        $db->trans_begin();
        

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $funit = array_merge($funit,array("modid"=>$modid,"moddate"=>$moddate));

        if(!$db->update('unit',$funit,array("id"=>$unit_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        
        //</editor-fold>
    }
    
    public static function unit_active_count($unit_id_arr){
        //<editor-fold defaultstate="collapsed">
        $result = 0;
        $db = new DB();
        $unit_q = '';
        foreach($unit_id_arr as $idx=>$unit_id){
            $unit_q .= ($unit_q ==='')?
                ('select '.$db->escape($unit_id).' id'):' union all select '.$db->escape($unit_id);
        }
        $q = '
            select count(1) count_result
            from unit t1
                inner join (
                   '.$unit_q.'
               ) t2 on t1.id = t2.id 
            where t1.status>0 
        ';
        $result = $db->query_array($q)[0]['count_result'];
        return $result;
        //</editor-fold>
    }

}
?>
