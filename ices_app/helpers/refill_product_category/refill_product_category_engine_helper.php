<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Product_Category_Engine {

    public static $status_list = array(
        //<editor-fold defaultstate="collapsed">
        array(//label name is used for method name
            'val'=>''
            ,'label'=>''
            ,'method'=>'refill_product_category_add'
            ,'next_allowed_status'=>array()
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Add Refill - '),array('val'=>'Product Category'),array('val'=>'success')
                )
            )
        ),
        array(//label name is used for method name
            'val'=>'active'
            ,'label'=>'ACTIVE'
            ,'method'=>'refill_product_category_active'
            ,'default'=>true
            ,'next_allowed_status'=>array('inactive')
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Update Refill - '),array('val'=>'Product Category'),array('val'=>'success')
                )
            )
        ),
        array(
            'val'=>'inactive'
            ,'label'=>'INACTIVE'
            ,'method'=>'refill_product_category_inactive'
            ,'next_allowed_status'=>array('active')
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Update Refill - '),array('val'=>'Product Category'),array('val'=>'success')
                )
            )
        )
        //</editor-fold>
    );

    
    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'refill_product_category/',
            'refill_product_category_engine'=>'refill_product_category/refill_product_category_engine',
            'refill_product_category_data_support' => 'refill_product_category/refill_product_category_data_support',
            'refill_product_category_renderer' => 'refill_product_category/refill_product_category_renderer',
            'ajax_search'=>get_instance()->config->base_url().'refill_product_category/ajax_search/',
            'data_support'=>get_instance()->config->base_url().'refill_product_category/data_support/',
        );

        return json_decode(json_encode($path));
    }

    public static function validate($method,$data=array()){   
        // <editor-fold defaultstate="collapsed">

        get_instance()->load->helper('refill_product_category/refill_product_category_data_support');
        get_instance()->load->helper('refill_product_medium/refill_product_medium_data_support');
        get_instance()->load->helper('unit/unit_engine');
        $result = array(
            "success" => 1
            , "msg" => array()
                );
        $success = 1;
        $msg = array();
        $refill_product_category = isset($data['refill_product_category']) ?
                Tools::_arr($data['refill_product_category']) : array();
        $rpc_rpm_cu = isset($data['rpc_rpm_cu']) ? $data['rpc_rpm_cu'] : array();
        switch ($method) {
            case 'refill_product_category_add':
            case 'refill_product_category_active':
            case 'refill_product_category_inactive':
                $db = new DB();
                $code = isset($refill_product_category['code']) ?
                        Tools::_str($refill_product_category['code']) : '';
                $name = isset($refill_product_category['name']) ?
                        Tools::_str($refill_product_category['name']) : '';
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
                if (preg_replace('/ /', '', $code) === '') {
                    $success = 0;
                    $msg[] = 'Code empty';
                }


                if (preg_replace('/ /', '', $name) === '') {
                    $success = 0;
                    $msg[] = 'Name empty';
                }


                $q = '
                    select 1
                    from refill_product_category t1
                    where t1.code = ' . $db->escape($code) . '
                        and t1.id != ' . $db->escape($refill_product_category['id']) . '
                        and t1.status>0
                ';
                $rs = $db->query_array($q);
                if (count($rs) > 0) {
                    $success = 0;
                    $msg[] = 'Code exists';
                }


                $rpm_id_arr = array();
                $capacity_unit_id_arr = array();
                $rpc_rpm_cu_check_id_count = true;
                foreach ($rpc_rpm_cu as $idx => $item) {
                    $rpm_id = isset($item['refill_product_medium_id']) ?
                            Tools::_str($item['refill_product_medium_id']) : '';
                    $capacity_unit_id = isset($item['capacity_unit_id']) ?
                            Tools::_str($item['capacity_unit_id']) : '';


                    if (count(Tools::array_extract($rpc_rpm_cu, array(), array(
                                        'data' => array(array('refill_product_medium_id' => $rpm_id, 'unit_id' => $capacity_unit_id))
                            ))) > 1) {
                        $success = 0;
                        $msg[] = 'Product Medium Unit duplicate';
                        $rpc_rpm_cu_check_id_count = false;
                        break;
                    }


                    $rpm_id_arr[] = $rpm_id;
                    $capacity_unit_id_arr[] = $capacity_unit_id;
                }


                if ($rpc_rpm_cu_check_id_count) {
                    if (Tools::_int(count($rpm_id_arr))
                        !== Tools::_int(Refill_Product_Medium_Data_Support::rpm_active_count($rpm_id_arr))
                    ) {
                        $success = 0;
                        $msg[] = 'Refill Product Medium invalid';
                    }


                    if (Tools::_int(count($capacity_unit_id_arr))
                        !== Tools::_int(Unit_Engine::unit_active_count($capacity_unit_id_arr))
                    ) {
                        $success = 0;
                        $msg[] = 'Capacity Unit invalid';
                    }
                
                }


                if ($success !== 1)
                    break;
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
        // </editor-fold>
    }

    public static function adjust($action,$data=array()){
        // <editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();
        $refill_product_category_data = isset($data['refill_product_category']) ?
                Tools::_arr($data['refill_product_category']) : array();
        $rpc_rpm_cu_data = isset($data['rpc_rpm_cu']) ?
                Tools::_arr($data['rpc_rpm_cu']) : array();



        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        switch ($action) {
            case 'refill_product_category_add':

            case 'refill_product_category_active':
            case 'refill_product_category_inactive':
                $refill_product_category = array();

                $refill_product_category = array(
                    'code' => Tools::_str($refill_product_category_data['code']),
                    'name' => Tools::_str($refill_product_category_data['name']),
                    'refill_product_category_status' => '', //SI::status_default_status_get('Refill_Product_Category_Engine')['val'],
                    'notes' => isset($refill_product_category_data['notes']) ?
                            Tools::empty_to_null(Tools::_str($refill_product_category_data['notes'])) : '',
                    'modid' => $modid,
                    'status' => '1',
                    'moddate' => $datetime_curr,
                );
                if ($action === 'refill_product_category_add') {
                    $refill_product_category['refill_product_category_status'] = SI::status_default_status_get('Refill_Product_Category_Engine')['val'];
                }                 else if ($action === 'refill_product_category_active') {
                    $refill_product_category['refill_product_category_status'] = 'active';
                }                 else if ($action === 'refill_product_category_inactive') {
                    $refill_product_category['refill_product_category_status'] = 'inactive';
                }


                $rpc_rpm_cu = array();
                for ($i = 0; $i < count($rpc_rpm_cu_data); $i++) {
                    $rpc_rpm_cu[] = array(
                        'refill_product_medium_id' => $rpc_rpm_cu_data[$i]['refill_product_medium_id'],
                        'capacity_unit_id' => $rpc_rpm_cu_data[$i]['capacity_unit_id'],
                    );
                }


                $result['refill_product_category'] = $refill_product_category;

                $result['rpc_rpm_cu'] = $rpc_rpm_cu;


                break;
        
        }


        return $result;
        // </editor-fold>
    }

    public function refill_product_category_add($db,$final_data){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $frefill_product_category = $final_data['refill_product_category'];
        $frpc_rpm_cu = $final_data['rpc_rpm_cu'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $refill_product_category_id = '';       

        if(!$db->insert('refill_product_category',$frefill_product_category)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $refill_product_category_code = $frefill_product_category['code'];

        if($success == 1){                                
            $refill_product_category_id = $db->fast_get('refill_product_category'
                    ,array('code'=>$refill_product_category_code,'status'=>'1'))[0]['id'];
            $result['trans_id']=$refill_product_category_id; 
        }

        //<editor-fold defaultstate="collapsed" desc="RPC_RPM_U insert">
        if($success === 1){
            if(!$db->query('delete from rpc_rpm_cu where refill_product_category_id = '.
                $db->escape($refill_product_category_id))){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        if($success === 1){
            for($i = 0;$i<count($frpc_rpm_cu);$i++){
                $frpc_rpm_cu[$i]['refill_product_category_id'] = $refill_product_category_id;
                 if(!$db->insert('rpc_rpm_cu',$frpc_rpm_cu[$i])){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
            }
        }
        //</editor-fold>
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    function refill_product_category_active($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $frefill_product_category = $final_data['refill_product_category'];
        $frpc_rpm_cu = $final_data['rpc_rpm_cu'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $refill_product_category_id = $id;
        
        if(!$db->update('refill_product_category',$frefill_product_category,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        //<editor-fold defaultstate="collapsed" desc="RPC_RPM_U insert">
        if($success === 1){
            if(!$db->query('delete from rpc_rpm_cu where refill_product_category_id = '.
                $db->escape($refill_product_category_id))){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        if($success === 1){
            for($i = 0;$i<count($frpc_rpm_cu);$i++){
                $frpc_rpm_cu[$i]['refill_product_category_id'] = $refill_product_category_id;
                 if(!$db->insert('rpc_rpm_cu',$frpc_rpm_cu[$i])){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
            }
        }
        //</editor-fold>
        
        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function refill_product_category_inactive($db, $final_data,$id){
        return self::refill_product_category_active($db, $final_data, $id);
    }
    
}
?>