<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Product_Price_List_Engine {

    public static $status_list = array(
        //<editor-fold defaultstate="collapsed">
        array(
            'val'=>''
            ,'label'=>''
            , 'method'=>'refill_product_price_list_add'
            ,'next_allowed_status'=>array()
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Add Refill - '),array('val'=>'Product Price List'),array('val'=>'success')
                )
            )
        ),
        array(//label name is used for method name
            'val'=>'active'
            ,'label'=>'ACTIVE'
            ,'method'=>'refill_product_price_list_active'
            ,'default'=>true
            ,'next_allowed_status'=>array('inactive')
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Update Refill - '),array('val'=>'Product Price List'),array('val'=>'success')
                )
            )

        ),
        array(
            'val'=>'inactive'
            ,'label'=>'INACTIVE'
            ,'method'=>'refill_product_price_list_inactive'
            ,'next_allowed_status'=>array('active')
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Update Refill - '),array('val'=>'Product Price List'),array('val'=>'success')
                )
            )

        )
        //</editor-fold>
    );

    
    public static function refill_product_price_list_exists($id){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from refill_product_price_list 
                where status > 0 && id = '.$db->escape($id).'
            ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
        //</editor-fold>
    }

    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'refill_product_price_list/',
            'refill_product_price_list_engine'=>'refill_product_price_list/refill_product_price_list_engine',
            'refill_product_price_list_data_support' => 'refill_product_price_list/refill_product_price_list_data_support',
            'refill_product_price_list_renderer' => 'refill_product_price_list/refill_product_price_list_renderer',
            'ajax_search'=>get_instance()->config->base_url().'refill_product_price_list/ajax_search/',
            'data_support'=>get_instance()->config->base_url().'refill_product_price_list/data_support/',
        );

        return json_decode(json_encode($path));
    }

    public static function validate($method,$data=array()){            
        get_instance()->load->helper('refill_product_price_list/refill_product_price_list_data_support');
        get_instance()->load->helper('refill_product_medium/refill_product_medium_data_support');
        get_instance()->load->helper('unit/unit_engine');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $rppl = isset($data['rppl'])?
                Tools::_arr($data['rppl']):array();
        $rppl_product_arr = isset($data['rppl_product'])?
                Tools::_arr($data['rppl_product']):array();
        $rppl_id = isset($rppl['id'])?Tools::_str($rppl['id']):'';
        switch($method){
            case 'refill_product_price_list_add':
            case 'refill_product_price_list_active':
            case 'refill_product_price_list_inactive':
                $db = new DB();
                $code = isset($rppl['code'])?
                        Tools::_str($rppl['code']):'';
                $name = isset($rppl['name'])?
                        Tools::_str($rppl['name']):'';
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
                
                if(in_array($method,array('refill_product_price_list_add','refill_product_price_list_active'))){
                    $q = '
                        select 1 
                        from refill_product_price_list t1
                        where t1.refill_product_price_list_status = "active"
                            and t1.status>0
                            and t1.id !='.$db->escape($rppl['id']).'
                    ';
                    if(count($db->query_array($q))>0){
                        $success = 0;
                        $msg[] = 'Active Product Price List '.Lang::get('exists',true,false);
                    }
                }
                if(preg_replace('/ /','',$code) === ''){
                    $success = 0;
                    $msg[] = 'Code empty';
                }
                
                if(preg_replace('/ /','',$name) === ''){
                    $success = 0;
                    $msg[] = 'Name empty';
                }
                
                $q = '
                    select 1
                    from refill_product_price_list t1
                    where t1.code = '.$db->escape($code).'
                        and t1.id != '.$db->escape($rppl['id']).'
                        and t1.status>0
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $success = 0;
                    $msg[] = 'Code exists';
                }
                
                get_instance()->load->helper('refill_product_category/refill_product_category_data_support');
                if(count($rppl_product_arr)!== Tools::_int(Refill_Product_Category_Data_Support::rpc_rpm_cu_active_count($rppl_product_arr))){
                    $success = 0;
                    $msg[] = 'Product Category / Product Medium / Unit invalid';
                }
                
                if($success === 1){
                    foreach($rppl_product_arr as $idx=>$rppl_product){
                        $rppl_product_price = isset($rppl_product['rppl_product_price'])?
                            Tools::_arr($rppl_product['rppl_product_price']):array();
                        $temp_result = Refill_Product_Price_List_Data_Support::price_list_function_is_valid($rppl_product_price);

                        if(!$temp_result['valid']){
                            $success = 0;
                            $msg = array_merge($temp_result['msg'],$msg);
                            break;
                        }
                    }
                }
                
                if($success === 1){
                    for($i = 0;$i<count($rppl_product_arr);$i++){
                        $refill_product_category_id_i = isset($rppl_product_arr[$i]['refill_product_category_id'])?
                            Tools::_str($rppl_product_arr[$i]['refill_product_category_id']):'';
                        $refill_product_medium_id_i = isset($rppl_product_arr[$i]['refill_product_medium_id'])?
                            Tools::_str($rppl_product_arr[$i]['refill_product_medium_id']):'';
                        $capacity_unit_id_i = isset($rppl_product_arr[$i]['capacity_unit_id'])?
                            Tools::_str($rppl_product_arr[$i]['capacity_unit_id']):'';
                        
                        for($j = 0;$j<count($rppl_product_arr);$j++){
                            $refill_product_category_id_j = isset($rppl_product_arr[$j]['refill_product_category_id'])?
                            Tools::_str($rppl_product_arr[$j]['refill_product_category_id']):'';
                            $refill_product_medium_id_j = isset($rppl_product_arr[$j]['refill_product_medium_id'])?
                                Tools::_str($rppl_product_arr[$j]['refill_product_medium_id']):'';
                            $capacity_unit_id_j = isset($rppl_product_arr[$j]['capacity_unit_id'])?
                                Tools::_str($rppl_product_arr[$j]['capacity_unit_id']):'';
                            
                            if($i !== $j){
                                if($refill_product_category_id_i === $refill_product_category_id_j &&
                                    $refill_product_medium_id_i === $refill_product_medium_id_j &&
                                    $capacity_unit_id_i === $capacity_unit_id_j
                                ){
                                    $success = 0;
                                    $msg[] = 'Duplicate Product';
                                }
                            }
                        }                        
                        if($success !== 1 ) break;
                    }
                }
                
                
                if($success !== 1) break;
                //</editor-fold>
                
                
                
            
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

    public static function adjust($action,$data=array()){
        $db = new DB();
        $result = array();
        $refill_product_price_list_data = isset($data['rppl'])?
            Tools::_arr($data['rppl']):array();
        $rppl_product_data = isset($data['rppl_product'])?
            Tools::_arr($data['rppl_product']):array();
        
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        switch($action){
            case 'refill_product_price_list_add':                
            case 'refill_product_price_list_active':
            case 'refill_product_price_list_inactive':
                $refill_product_price_list = array();

                $refill_product_price_list = array(
                    'code'=>Tools::_str($refill_product_price_list_data['code']),
                    'name'=>Tools::_str($refill_product_price_list_data['name']),
                    'refill_product_price_list_status'=>'',//SI::status_default_status_get('Refill_Product_Price_List_Engine')['val'],
                    'notes'=>isset($refill_product_price_list_data['notes'])?
                        Tools::empty_to_null(Tools::_str($refill_product_price_list_data['notes'])):'',
                    'modid'=>$modid,
                    'status'=>'1',
                    'moddate'=>$datetime_curr,
                );
                if($action ==='refill_product_price_list_add'){
                    $refill_product_price_list['refill_product_price_list_status'] = SI::status_default_status_get('Refill_Product_Price_List_Engine')['val'];
                }
                else if ($action ==='refill_product_price_list_active'){
                    $refill_product_price_list['refill_product_price_list_status'] = 'active';
                }
                else if($action === 'refill_product_price_list_inactive'){
                    $refill_product_price_list['refill_product_price_list_status'] = 'inactive';
                }
                
                $rppl_product = array();
                for($i = 0;$i<count($rppl_product_data);$i++){
                    $rppl_product[] = array(
                        'refill_product_category_id'=>$rppl_product_data[$i]['refill_product_category_id'],
                        'refill_product_medium_id'=>$rppl_product_data[$i]['refill_product_medium_id'],
                        'capacity_unit_id'=>$rppl_product_data[$i]['capacity_unit_id'],
                        'rppl_product_price'=>array()
                    );
                    
                    foreach($rppl_product_data[$i]['rppl_product_price'] as $idx=>$rppl_product_price_data){
                        $rppl_product[count($rppl_product)-1]['rppl_product_price'][] = array(
                            'min_cap'=>$rppl_product_price_data['min_cap'],
                            'max_cap'=>$rppl_product_price_data['max_cap'],
                            'price'=>  Refill_Product_Price_List_Data_Support::price_function_get($rppl_product_price_data['price']),
                        );
                    }
                }
                
                $result['refill_product_price_list'] = $refill_product_price_list;                   
                $result['rppl_product'] = $rppl_product;
                
                break;
            
        }
        
        return $result;
    }

    public function refill_product_price_list_add($db,$final_data,$id){
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $frefill_product_price_list = $final_data['refill_product_price_list'];
        $frppl_product = $final_data['rppl_product'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $refill_product_price_list_id = '';       

        if(!$db->insert('refill_product_price_list',$frefill_product_price_list)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $refill_product_price_list_code = $frefill_product_price_list['code'];

        if($success == 1){                                
            $refill_product_price_list_id = $db->fast_get('refill_product_price_list'
                    ,array('code'=>$refill_product_price_list_code,'status'=>'1'))[0]['id'];
            $result['trans_id']=$refill_product_price_list_id; 
        }

        //<editor-fold defaultstate="collapsed" desc="RPPL Product insert">
        if($success === 1){
            if(!$db->query('delete from rppl_product where refill_product_price_list_id = '.
                $db->escape($refill_product_price_list_id))){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        if($success === 1){
            for($i = 0;$i<count($frppl_product);$i++){
                $frppl_product[$i]['refill_product_price_list_id'] = $refill_product_price_list_id;
                $rppl_product_price_arr = $frppl_product[$i]['rppl_product_price'];
                unset($frppl_product[$i]['rppl_product_price']);
                
                if(!$db->insert('rppl_product',$frppl_product[$i])){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
                
                if($success === 1){
                    $rppl_product_id = $db->fast_get('rppl_product'
                        ,array(
                            'refill_product_price_list_id'=>$refill_product_price_list_id,
                            'refill_product_category_id'=>$frppl_product[$i]['refill_product_category_id'],
                            'refill_product_medium_id'=>$frppl_product[$i]['refill_product_medium_id'],
                            'capacity_unit_id'=>$frppl_product[$i]['capacity_unit_id'],
                        )
                    )[0]['id'];
            
                    foreach($rppl_product_price_arr as $idx=>$rppl_product_price){
                        $rppl_product_price['rppl_product_id'] = $rppl_product_id;
                        if(!$db->insert('rppl_product_price',$rppl_product_price)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                            break;
                        }
                    }
                }
                
            }
        }
        //</editor-fold>
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
    }
    
    function refill_product_price_list_active($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $frefill_product_price_list = $final_data['refill_product_price_list'];
        $frppl_product = $final_data['rppl_product'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $refill_product_price_list_id = $id;
        
        if(!$db->update('refill_product_price_list',$frefill_product_price_list,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        //<editor-fold defaultstate="collapsed" desc="RPPL Product insert">
        if($success === 1){
            if(!$db->query('delete from rppl_product where refill_product_price_list_id = '.
                $db->escape($refill_product_price_list_id))){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        if($success === 1){
            for($i = 0;$i<count($frppl_product);$i++){
                $frppl_product[$i]['refill_product_price_list_id'] = $refill_product_price_list_id;
                $rppl_product_price_arr = $frppl_product[$i]['rppl_product_price'];
                unset($frppl_product[$i]['rppl_product_price']);
                
                if(!$db->insert('rppl_product',$frppl_product[$i])){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
                
                if($success === 1){
                    $rppl_product_id = $db->fast_get('rppl_product'
                        ,array(
                            'refill_product_price_list_id'=>$refill_product_price_list_id,
                            'refill_product_category_id'=>$frppl_product[$i]['refill_product_category_id'],
                            'refill_product_medium_id'=>$frppl_product[$i]['refill_product_medium_id'],
                            'capacity_unit_id'=>$frppl_product[$i]['capacity_unit_id'],
                        )
                    )[0]['id'];
            
                    foreach($rppl_product_price_arr as $idx=>$rppl_product_price){
                        $rppl_product_price['rppl_product_id'] = $rppl_product_id;
                        if(!$db->insert('rppl_product_price',$rppl_product_price)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                            break;
                        }
                    }
                }
                
            }
        }
        //</editor-fold>
        
        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }
    
    function refill_product_price_list_inactive($db, $final_data,$id){
        return Refill_Product_Price_List_Engine::refill_product_price_list_active($db, $final_data, $id);
    }
    
    
}
?>