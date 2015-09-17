<?php
class Supplier_Engine {

    public static $prefix_id = 'supplier';
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
                , 'method'=>'supplier_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('Supplier'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'A'
                ,'label'=>'ACTIVE'
                ,'method'=>'supplier_active'
                ,'default'=>true
                ,'next_allowed_status'=>array('I')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('Supplier'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            )
            ,array(
                'val'=>'I'
                ,'label'=>'INACTIVE'
                ,'method'=>'supplier_inactive'
                ,'next_allowed_status'=>array('A')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('Supplier'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            )

        );

        

        //</editor-fold>
    }

    public static function path_get(){
        return (object)array(
            'index' => get_instance()->config->base_url().'supplier/'
        );
    }
    

    public static function phone_exists_in_supplier($id,$phone){
        $result = false;
        $db = new DB();
        $q = '
            select 1 
            from supplier 
            where phone ='.$db->escape(preg_replace('/[^0-9]/','',$phone)).' 
                and phone!="" 
                and id!='.$db->escape($id).'
        ';
        if(count($db->query_array_obj($q))>0){
            $result = true;
        }
        return $result;
    }
    
    public static function validate($action, $data=array()){
        $result = array(
            "success"=>1
            ,"msg"=>array()
        );
        $db = new DB();
        $supplier_id = $data['supplier']['id'];
        if(strlen($data['supplier']['code'])==0){
            $result['success'] = 0;
            $result['msg'][] = Lang::get('Code').' '.Lang::get('empty');
        }

        if(SI::duplicate_value('supplier',$supplier_id,'code',$data['supplier']['code'])){
            $result['success'] = 0;
            $result['msg'][] = "Code already exists";
        }

        if(strlen($data['supplier']['name'])==0){
            $result['success'] = 0;
            $result['msg'][] = Lang::get('Name').' '.Lang::get('empty');
        }

        if(SI::duplicate_value('supplier',$supplier_id,'name',$data['supplier']['name'])){
            $result['success'] = 0;
            $result['msg'][] = "Name already exists";
        }

        $phone = isset($data['supplier']['phone'])?str_replace('_','',$data['supplier']['phone']):'';
        $phone2 = isset($data['supplier']['phone2'])?str_replace('_','',$data['supplier']['phone2']):'';
        $phone3 = isset($data['supplier']['phone3'])?str_replace('_','',$data['supplier']['phone3']):'';



        if($phone !== ''){
            if(self::phone_exists_in_supplier($supplier_id,$phone)){
                $result['success'] = 0;
                $result['msg'][] = "Phone number already exists";
            }
            else{
                if($phone == $phone2 || $phone == $phone3){
                    $result['success'] = 0;
                    $result['msg'][] = "Phone number already exists";
                }
            }
        }

        if($phone2 !== ''){

            if(self::phone_exists_in_supplier($supplier_id,$phone2)){
                $result['success'] = 0;
                $result['msg'][] = "Phone 2 number already exists";
            }
            else{
                if($phone2 == $phone || $phone2 == $phone3){
                    $result['success'] = 0;
                    $result['msg'][] = "Phone 2 number already exists";
                }
            }
        }

        if($phone3 !== ''){
            if(self::phone_exists_in_supplier($supplier_id,$phone3)){
                $result['success'] = 0;
                $result['msg'][] = "Phone 3 number already exists";
            }
            else{
                if($phone3 == $phone || $phone3 == $phone2){
                    $result['success'] = 0;
                    $result['msg'][] = "Phone 3 number already exists";
                }
            }
        }

        return $result;
    }

    public static function adjust($method, $data=array()){
        $result = $data;
        if($method == 'insert'){
            unset($result['supplier']['id']);
        }
        $result['supplier']['phone'] = isset($result['supplier']['phone'])?
                 preg_replace('/[^0-9]/','',$result['supplier']['phone']):'';
        $result['supplier']['phone2'] = isset($result['supplier']['phone2'])?
                preg_replace('/[^0-9]/','',$result['supplier']['phone2']):'';
        $result['supplier']['phone3'] = isset($result['supplier']['phone3'])?
                preg_replace('/[^0-9]/','',$result['supplier']['phone3']):'';
        return $result;
    }


    public function supplier_add($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        $fsupplier = $final_data['supplier'];
        $supplier_id = '';
        $db->trans_begin();
        

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $fsupplier = array_merge($fsupplier,array("modid"=>$modid,"moddate"=>$moddate));
        if(!$db->insert('supplier',$fsupplier)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success){
            $result['trans_id'] = SI::get_trans_id($db,'supplier','code',$fsupplier['code']);
            if($result['trans_id'] === null){
                $msg[] = 'Unable to get trans id';
                $db->trans_rollback();                                
                $success = 0;
            }
            $supplier_id = $result['trans_id'];
        }

                
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        
        //</editor-fold>
    }
    
    public function supplier_active($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        $fsupplier = $final_data['supplier'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $supplier_id = $id;
        
        if(!$db->update('supplier',$fsupplier,array("id"=>$supplier_id))){
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
    
    public function supplier_inactive($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        return Supplier_Engine::supplier_active($db,$final_data,$id);
        //</editor-fold>
    }


}
?>
