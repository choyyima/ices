<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Product_Medium_Engine {

    public static $status_list = array(
        //<editor-fold defaultstate="collapsed">
        array(//label name is used for method name
            'val'=>''
            ,'label'=>''
            ,'method'=>'refill_product_medium_add'
            ,'next_allowed_status'=>array()
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Add Refill - '),array('val'=>'Product Medium'),array('val'=>'success')
                )
            )
        ),
        array(//label name is used for method name
            'val'=>'active'
            ,'label'=>'ACTIVE'
            ,'method'=>'refill_product_medium_active'
            ,'default'=>true
            ,'next_allowed_status'=>array('inactive')
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Update Refill - '),array('val'=>'Product Medium'),array('val'=>'success')
                )
            )
        ),
        array(
            'val'=>'inactive'
            ,'label'=>'INACTIVE'
            ,'method'=>'refill_product_medium_inactive'
            ,'next_allowed_status'=>array('active')
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Update Refill - '),array('val'=>'Product Medium'),array('val'=>'success')
                )
            )
        )
        //</editor-fold>
    );

    
    public static function refill_product_medium_exists($id){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from refill_product_medium 
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
            'index'=>get_instance()->config->base_url().'refill_product_medium/',
            'refill_product_medium_engine'=>'refill_product_medium/refill_product_medium_engine',
            'refill_product_medium_data_support' => 'refill_product_medium/refill_product_medium_data_support',
            'refill_product_medium_renderer' => 'refill_product_medium/refill_product_medium_renderer',
            'ajax_search'=>get_instance()->config->base_url().'refill_product_medium/ajax_search/',
            'data_support'=>get_instance()->config->base_url().'refill_product_medium/data_support/',
        );

        return json_decode(json_encode($path));
    }

    public static function validate($method,$data=array()){            
        get_instance()->load->helper('refill_product_medium/refill_product_medium_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $refill_product_medium = isset($data['refill_product_medium'])?
                Tools::_arr($data['refill_product_medium']):array();
        
        switch($method){
            case 'refill_product_medium_add':
                $db = new DB();
                $code = isset($refill_product_medium['code'])?
                        Tools::_str($refill_product_medium['code']):'';
                $name = isset($refill_product_medium['name'])?
                        Tools::_str($refill_product_medium['name']):'';
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
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
                    from refill_product_medium
                    where code = '.$db->escape($code).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $success = 0;
                    $msg[] = 'Code exists';
                }
                
                if($success !== 1) break;
                //</editor-fold>

                break;
            case 'refill_product_medium_active':
                $db = new DB();
                $code = isset($refill_product_medium['code'])?
                        Tools::_str($refill_product_medium['code']):'';
                $name = isset($refill_product_medium['name'])?
                        Tools::_str($refill_product_medium['name']):'';
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
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
                    from refill_product_medium
                    where code = '.$db->escape($code).'
                        and id != '.$db->escape($refill_product_medium['id']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $success = 0;
                    $msg[] = 'Code exists';
                }
                
                if($success !== 1) break;
                //</editor-fold>

                break;
            case 'refill_product_medium_inactive':
                $db = new DB();
                $code = isset($refill_product_medium['code'])?
                        Tools::_str($refill_product_medium['code']):'';
                $name = isset($refill_product_medium['name'])?
                        Tools::_str($refill_product_medium['name']):'';
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
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
                    from refill_product_medium
                    where code = '.$db->escape($code).'
                        and id != '.$db->escape($refill_product_medium['id']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $success = 0;
                    $msg[] = 'Code exists';
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
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();
        $refill_product_medium_data = isset($data['refill_product_medium'])?
            Tools::_arr($data['refill_product_medium']):array();

        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        switch($action){
            case 'refill_product_medium_add':                

                $refill_product_medium = array();

                $refill_product_medium = array(
                    'code'=>Tools::_str($refill_product_medium_data['code']),
                    'name'=>Tools::_str($refill_product_medium_data['name']),
                    'refill_product_medium_status'=>SI::status_default_status_get('Refill_Product_Medium_Engine')['val'],
                    'notes'=>isset($refill_product_medium_data['notes'])?
                        Tools::empty_to_null(Tools::_str($refill_product_medium_data['notes'])):'',
                    'modid'=>$modid,
                    'status'=>'1',
                    'moddate'=>$datetime_curr,
                );

                $result['refill_product_medium'] = $refill_product_medium;                   
                
                break;
            case 'refill_product_medium_active':                
                $refill_product_medium = array();

                $refill_product_medium = array(
                    'code'=>Tools::_str($refill_product_medium_data['code']),
                    'name'=>Tools::_str($refill_product_medium_data['name']),
                    'refill_product_medium_status'=>'active',
                    'notes'=>isset($refill_product_medium_data['notes'])?
                        Tools::empty_to_null(Tools::_str($refill_product_medium_data['notes'])):'',
                    'modid'=>$modid,
                    'status'=>'1',
                    'moddate'=>$datetime_curr,
                );
                $result['refill_product_medium'] = $refill_product_medium;    
                break;
            case 'refill_product_medium_inactive':
                $refill_product_medium = array();

                $refill_product_medium = array(
                    'code'=>Tools::_str($refill_product_medium_data['code']),
                    'name'=>Tools::_str($refill_product_medium_data['name']),
                    'refill_product_medium_status'=>'inactive',
                    'notes'=>isset($refill_product_medium_data['notes'])?
                        Tools::empty_to_null(Tools::_str($refill_product_medium_data['notes'])):'',
                    'modid'=>$modid,
                    'status'=>'1',
                    'moddate'=>$datetime_curr,
                );
                $result['refill_product_medium'] = $refill_product_medium;
                break;
        }

        return $result;
        //</editor-fold>
    }

    public function refill_product_medium_add($db,$final_data){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $frefill_product_medium = $final_data['refill_product_medium'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $refill_product_medium_id = '';       

        if(!$db->insert('refill_product_medium',$frefill_product_medium)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $refill_product_medium_code = $frefill_product_medium['code'];

        if($success == 1){                                
            $refill_product_medium_id = $db->fast_get('refill_product_medium'
                    ,array('code'=>$refill_product_medium_code,'status'=>'1'))[0]['id'];
            $result['trans_id']=$refill_product_medium_id; 
        }

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    function refill_product_medium_active($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $frefill_product_medium = $final_data['refill_product_medium'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('refill_product_medium',$frefill_product_medium,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function refill_product_medium_inactive($db, $final_data,$id){
        return self::refill_product_medium_active($db, $final_data,$id);
    }
    
}
?>