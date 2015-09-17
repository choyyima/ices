<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BOM_Engine {

    public static $status_list;
    public static $module_type_list;

    public static function helper_init(){
        self::$status_list = array(
            //<editor-fold defaultstate="collapsed">
            array(
                'val'=>''
                ,'label'=>''
                , 'method'=>'bom_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add'),array('val'=>'Bill of Material'),array('val'=>'success')
                    )
                )
            ),
            array(//label name is used for method name
                'val'=>'active'
                ,'label'=>'ACTIVE'
                ,'method'=>'bom_active'
                ,'default'=>true
                ,'next_allowed_status'=>array('inactive')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update'),array('val'=>'Bill of Material'),array('val'=>'success')
                    )
                )
            )
            ,array(
                'val'=>'inactive'
                ,'label'=>'INACTIVE'
                ,'method'=>'bom_inactive'
                ,'next_allowed_status'=>array('active')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update'),array('val'=>'Bill of Material'),array('val'=>'success')
                    )
                )
            )            
            //</editor-fold>
        );
       
        self::$module_type_list = array(
            array('val'=>'normal','label'=>'Normal'),
        );
    }
    
    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'bom/'
            ,'bom_engine'=>'bom/bom_engine'
            ,'bom_data_support'=>'bom/bom_data_support'
            ,'bom_renderer' => 'bom/bom_renderer'
            ,'ajax_search'=>get_instance()->config->base_url().'bom/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'bom/data_support/'

        );

        return json_decode(json_encode($path));
    }

    public static function delete_all_related_table_records($db, $bom_id){
        //<editor-fold defaultstate="collapsed">
        $success = 1;
        $msg = array();
        $result = array('success'=>$success,'msg' => $msg);
        
        $q = 'delete from bom_component_product where bom_id = '.$db->escape($bom_id);
        if(!$db->query($q)){
            $success = 0;
            $msg[] = $db->_error_message();
            $db->trans_rollback();
        }
        
        if($success === 1){
            $q = 'delete from bom_result_product where bom_id = '.$db->escape($bom_id);
            if(!$db->query($q)){
                $success = 0;
                $msg[] = $db->_error_message();
                $db->trans_rollback();
            }
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    

    public static function validate($action,$data=array()){
        //<editor-fold defaultstate="collapsed">
        $result = array(
            "success"=>1
            ,"msg"=>array()
        );
        $success = 1;
        $msg = array();
        
        $bom = isset($data['bom'])?Tools::_arr($data['bom']):null;
        $bom_result_product = isset($data['bom_result_product'])?Tools::_arr($data['bom_result_product']):null;
        $bom_component_product = isset($data['bom_component_product'])?Tools::_arr($data['bom_component_product']):null;
        $bom_type = isset($bom['bom_type'])?Tools::_str($bom['bom_type']):'';
        
        switch($action){
            case 'bom_add':
            case 'bom_active':
            case 'bom_inactive':
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
                $db = new DB();
                $bom_id = $data['bom']['id'];
                
                if(!SI::type_match('BOM_Engine',$bom_type)){
                    $success = 0;
                    $msg[]=Lang::get(array('Module Type','invalid'),true,true,false,false,true);
                    break;
                }
                
                $bom_name = isset($bom['name'])?Tools::empty_to_null(Tools::_str($bom['name'])):'';
                if($bom_name === null){
                    $success = 0;
                    $msg[] = Lang::get(array("Name","empty"),true,true,false,false,true);
                }
                
                //</editor-fold>
                
                switch($bom_type){
                    case 'normal':
                        //<editor-fold defaultstate="collapsed">
                        get_instance()->load->helper('product/product_data_support');
                        $t_comp_reg_prod = array();
                        $t_res_reg_prod = array();
                        //<editor-fold defaultstate="collapsed" desc="Prepare Temp Comp & Res Prod">
                        
                        
                        $t_res_reg_prod = array(
                            'product_type'=>isset($bom_result_product['product_type'])?Tools::_str($bom_result_product['product_type']):'',
                            'product_id'=>isset($bom_result_product['product_id'])?Tools::_str($bom_result_product['product_id']):'',
                            'unit_id'=>isset($bom_result_product['unit_id'])?Tools::_str($bom_result_product['unit_id']):'',
                            'qty'=>isset($bom_result_product['qty'])?Tools::_str($bom_result_product['qty']):'',
                        );

                        foreach($bom_component_product as $i=>$row){
                            $product_type = isset($row['product_type'])?Tools::_str($row['product_type']):'';
                            if($product_type === 'registered_product'){
                                $t_comp_reg_prod[] = array(
                                    'product_type'=>$product_type,
                                    'product_id'=>isset($row['product_id'])?Tools::_str($row['product_id']):'',
                                    'unit_id'=>isset($row['unit_id'])?Tools::_str($row['unit_id']):'',
                                    'qty'=>isset($row['qty'])?Tools::_str($row['qty']):'',
                                );
                            }
                        }
                        //</editor-fold>
                        
                        if(count($t_res_reg_prod)=== 0 ){
                            $success = 0;
                            $msg[] = Lang::get(array('Result Product','empty'),true,true,false,false,true);
                        }
                        
                        if(!Product_Data_Support::product_unit_all_exists(array($t_res_reg_prod),array('product_status'=>'active'))){
                            $success = 0;
                            $msg[] = Lang::get(array('Result Product','invalid'),true,true,false,false,true);
                        }
                        
                        if(count($t_comp_reg_prod)=== 0 ){
                            $success = 0;
                            $msg[] = Lang::get(array('Component Product','empty'),true,true,false,false,true);
                        }

                        if(!Product_Data_Support::product_unit_all_exists($t_comp_reg_prod,array('product_status'=>'active'))){
                            $success = 0;
                            $msg[] = Lang::get(array('Component Product','invalid'),true,true,false,false,true);
                        }
                        /*
                        $q  = '
                            select 1
                            from bom_result_product brp
                            where brp.product_type = "registered_product"
                                and brp.bom_id != '.$db->escape($bom_id).'
                        ';
                        if(count($db->query_array($q))>0){
                            $success = 0;
                            $msg[] = Lang::get(array('Result Product',array('val'=>'exists','uc_first'=>false)),true,true,false,false,true);
                        }
                        */
                        //</editor-fold>
                        break;
                }
                
               
                break;
            default:
                $success = 0;
                $msg[] = 'Invalid Method';
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        
        return $result;
        //</editor-fold>
    }

    public static function adjust($method, $data=array()){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();

        $bom = isset($data['bom'])?$data['bom']:null;
        $bom_result_product_data = isset($data['bom_result_product'])?Tools::_arr($data['bom_result_product']):array();
        $bom_component_product_data = isset($data['bom_component_product'])?Tools::_arr($data['bom_component_product']):array();
        
        switch($method){
            case 'bom_add':
            case 'bom_active':
            case 'bom_inactive':
                $result['bom'] = array(
                    'bom_type' => isset($bom['bom_type'])?$bom['bom_type']:'',
                    'name' => isset($bom['name'])?$bom['name']:'',
                    'notes' => isset($bom['notes'])?$bom['notes']:'',
                    'bom_status'=>SI::status_default_status_get('bom_engine')['val'],
                    
                );
                
                $bom_result_product = array(
                    'product_type'=>$bom_result_product_data['product_type'],
                    'product_id'=>$bom_result_product_data['product_id'],
                    'unit_id'=>$bom_result_product_data['unit_id'],
                    'qty'=>$bom_result_product_data['qty'],
                );
                
                $bom_component_product = array();
                foreach($bom_component_product_data as $i=>$row){
                    $bom_component_product[] = array(
                        'product_type'=>$row['product_type'],
                        'product_id'=>$row['product_id'],
                        'unit_id'=>$row['unit_id'],
                        'qty'=>$row['qty']                            
                    );
                }
                
                $result['bom_result_product'] = $bom_result_product;                
                $result['bom_component_product'] = $bom_component_product;
                
                if($method === 'bom_active'){
                    $result['bom']['bom_status'] = 'active';
                }
                else if($method === 'bom_inactive'){
                    $result['bom']['bom_status'] = 'inactive';
                }
                break;
                
        }        

        return $result;
        //</editor-fold>
    }

    public function bom_add($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fbom = $final_data['bom'];
        $fbom_result_product = $final_data['bom_result_product'];
        $fbom_component_product = $final_data['bom_component_product'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $bom_type = $fbom['bom_type'];
        
        $bom_id = '';       
        $fbom['code'] = SI::code_counter_get($db,'bill_of_material');
        
        if(!$db->insert('bom',$fbom)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        $bom_code = $fbom['code'];

        if($success == 1){                                
            $bom_id = $db->fast_get('bom'
                    ,array('code'=>$bom_code))[0]['id'];
            $result['trans_id']=$bom_id; 
        }

        if($success == 1){
            $bom_status_log = array(
                'bom_id'=>$bom_id
                ,'bom_status'=>$fbom['bom_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('bom_status_log',$bom_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
                
            }
        }
        
        if($success === 1){
            $temp_result = BOM_Engine::delete_all_related_table_records($db,$bom_id);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        if($success === 1){
            switch($bom_type){
                case 'normal':
                    //<editor-fold defaultstate="collapsed">
                    $fbom_result_product['bom_id'] = $bom_id;
                    if(!$db->insert('bom_result_product',$fbom_result_product)){
                        $success = 0;
                        $msg[] = $db->_error_message();
                        $db->trans_rollback();
                    }
                    
                    if($success === 1){
                        foreach($fbom_component_product as $i=>$row){
                            $row['bom_id'] = $bom_id;
                            if(!$db->insert('bom_component_product',$row)){
                                $success = 0;
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();
                                break;
                            }
                        }
                    }
                    
                    //</editor-fold>
                    break;
            }
        }
        

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }

    function bom_active($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fbom = $final_data['bom'];
        $fbom_result_product = $final_data['bom_result_product'];
        $fbom_component_product = $final_data['bom_component_product'];

        $bom_id = $id;
        
        $bom_type = $fbom['bom_type'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('bom',$fbom,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'bom',
                $id,$fbom['bom_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        if($success === 1){
            $temp_result = BOM_Engine::delete_all_related_table_records($db,$bom_id);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        if($success === 1){
            switch($bom_type){
                case 'normal':
                    //<editor-fold defaultstate="collapsed">
                    $fbom_result_product['bom_id'] = $bom_id;
                    if(!$db->insert('bom_result_product',$fbom_result_product)){
                        $success = 0;
                        $msg[] = $db->_error_message();
                        $db->trans_rollback();
                    }
                    
                    if($success === 1){
                        foreach($fbom_component_product as $i=>$row){
                            $row['bom_id'] = $bom_id;
                            if(!$db->insert('bom_component_product',$row)){
                                $success = 0;
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();
                                break;
                            }
                        }
                    }
                    
                    //</editor-fold>
                    break;
            }
        }
        
        
        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function bom_inactive($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        return BOM_Engine::bom_active($db,$final_data,$id);
        //</editor-fold>
    }



}
?>
