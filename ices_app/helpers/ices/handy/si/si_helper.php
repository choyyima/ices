<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SI{
    private static $app_base_dir = '';
    
    function helper_init(){
        $t_app = array();
        foreach(ICES_Engine::$app_list as $idx=>$row){
            if($row['val'] === 'ices'){
                $t_app = $row;
            }
        }
        self::$app_base_dir = $t_app['app_base_dir'];
    }
    
    function duplicate_value($tbl_name,$id="",$field,$value){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        
        $q = '
            select 1 
            from '.$tbl_name.'
            where status>0 and id != '.$db->escape($id).' 
                and (
                    '.$field.' = '.$db->escape($value).'
                )
        ';
        $rs = $db->query_array_obj($q);

        if(count($rs)>0){
            $result = true;
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function get_status_attr($data){
        //<editor-fold defaultstate="collapsed">
        $result = '';
        
        switch($data){
            case 'OPENED':
                $result = '<span style="color:blue"><strong>'.$data.'</strong></span>';
                break;
            case 'DELIVERED':
                $result = '<span style="color:lightgreen"><strong>'.$data.'</strong></span>';
                break;
            case 'POSTPONED':
                $result = '<span style="color:magenta"><strong>'.$data.'</strong></span>';
                break;
            case 'REGISTERED':
                $result = '<span style="color:blue"><strong>'.$data.'</strong></span>';
                break;
            case 'INITIALIZED':
                $result = '<span style="color:blue"><strong>'.$data.'</strong></span>';
                break;
            case 'PROCESS':
                $result = '<span style="color:blue"><strong>'.$data.'</strong></span>';
                break;
            
            case 'RECEIVED':
                $result = '<span style="color:green"><strong>'.$data.'</strong></span>';
                break;
            case 'CLOSED':
                $result = '<span style="color:green"><strong>'.$data.'</strong></span>';
                break;
            case 'ACTIVE':
                $result = '<span style="color:green"><strong>'.$data.'</strong></span>';
                break;
            case 'INVOICED':
                $result = '<span style="color:green"><strong>'.$data.'</strong></span>';
                break;
            case 'DONE':
                $result = '<span style="color:green"><strong>'.$data.'</strong></span>';
                break;
            case 'CONFIRMED':
                $result = '<span style="color:green"><strong>'.$data.'</strong></span>';
                break;
            case 'APPROVED':
                $result = '<span style="color:green"><strong>'.$data.'</strong></span>';
                break;
            case 'YES':
                $result = '<span style="color:green"><strong>'.$data.'</strong></span>';
                break;
            case 'FINALIZED':
                $result = '<span style="color:green"><strong>'.$data.'</strong></span>';
                break;
            
            
            case 'INACTIVE':
                $result = '<span style="color:red"><strong>'.$data.'</strong></span>';
                break;
            case 'REJECTED':
                $result = '<span style="color:rgb(161, 25, 0);"><strong>'.$data.'</strong></span>';
                break;
            case 'CANCELED':
                $result = '<span style="color:red"><strong>'.$data.'</strong></span>';
                break;
            case 'RETURNED':
                $result = '<span style="color:red"><strong>'.$data.'</strong></span>';
                break;
            case 'NO':
                $result = '<span style="color:red"><strong>'.$data.'</strong></span>';
                break;
            
            default:
                $result = '<span>'.$data.'</span>';
                break;
        }
        return $result;
        //</editor-fold>
    }
    
    function get_trans_id($db,$table, $identifier, $value,$has_status = true){
        //<editor-fold defaultstate="collapsed">
        if($db === null){
            $db = new DB();
        }
        $result = null;
        $q = '
            select id 
            from '.$table.'
            where '.($has_status?'status>0 ':'1=1').'
                and '.$identifier.' = '.$db->escape($value).'
        ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0) $result = $rs[0]->id;
        return $result;
        //</editor-fold>
    }
    
    function record_exists($tbl='',$filter=array()){
        // <editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = false;
        $q_where = '';
        foreach ($filter as $key => $val) {
            $q_where .= ' and ' . $key . ' = ' . $db->escape($val);
        }
        $q = '
            select 1
            from ' . $tbl . '
            where 1 = 1 ' . $q_where . '
        ';
        $rs = $db->query_array_obj($q);


        if (count($rs) > 0) {
            $result = true;
        }


        return $result; 
        // </editor-fold>
    }
    
    public static function status_list_get($obj){
        $result = array();
        $string = ' return class_exists("'.$obj.'")?(isset('.$obj.'::$status_list)?'.$obj.'::$status_list:NULL):NULL;';
        $result = eval($string);
        return $result;
    }

    public static function status_get($obj,$product_status_val){
        $status_list = self::status_list_get($obj);
        $result = null;
        for($i = 0;$i<count($status_list);$i++){
            if($status_list[$i]['val'] === $product_status_val){
                $result = $status_list[$i];
            }
        }
        return $result;
    }

    public static function status_next_allowed_status_get($obj,$curr_status_val){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $curr_status = null;
        $status_list = self::status_list_get($obj);
        for($i = 0;$i<count($status_list);$i++){
            if($obj::$status_list[$i]['val'] === $curr_status_val){
                $curr_status = $obj::$status_list[$i];
                break;
            }
        }

        for ($i = 0;$i<count($curr_status['next_allowed_status']);$i++){
            foreach($status_list as $status){
                $user_select = isset($status['user_select_next_allowed_status'])?
                    Tools::_bool($status['user_select_next_allowed_status']):true;
                if($status['val'] === $curr_status['next_allowed_status'][$i] && $user_select){
                    $result[] = array('val'=>$status['val']
                            ,'text'=>$status['text']
                            ,'method'=>$status['method']);
                }
            }
        }
        return $result;
        //</editor-fold>
    }

    public static function status_default_status_get($obj){
        //<editor-fold defaultstate="collapsed">
        $result = array('val'=>'','label'=>'','method'=>'');
        $status_list = self::status_list_get($obj);
        if(!is_null($status_list)){
            foreach($status_list as $status){
                if(isset($status['default'])){
                    if($status['default']){
                        $result['val'] = $status['val'];
                        $result['label'] = $status['label'];
                        $result['method'] = $status['method'];
                    }
                }
            }
        }
        return $result;
        //</editor-fold>
    }

    public static function type_default_type_get($obj,$type_list_name='$module_type_list'){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $type_list = self::type_list_get($obj,$type_list_name);
        if(!is_null($type_list)){
            foreach($type_list as $type){
                if(isset($type['default'])){
                    if($type['default']){
                        $result = $type;
                    }
                }
            }
        }
        return $result;
        //</editor-fold>
    }
    
    public static function type_list_get($obj,$type_list_name = '$module_type_list'){
        $result = array();
        $string = ' return class_exists("'.$obj.'")?(isset('.$obj.'::'.$type_list_name.')?'.$obj.'::'.$type_list_name.':NULL):NULL;';
        $result = eval($string);
        return $result;
    }

    public static function type_get($obj,$product_module_type_val,$type_list_name = '$module_type_list'){
        $module_type_list = self::type_list_get($obj,$type_list_name);
        $result = null;
        for($i = 0;$i<count($module_type_list);$i++){
            if($module_type_list[$i]['val'] === $product_module_type_val){
                $result = $module_type_list[$i];
            }
        }
        return $result;
    }
    
    public static function type_match($obj, $product_module_type_val,$type_list_name = '$module_type_list'){
        
        $module_type_list = self::type_list_get($obj,$type_list_name);
        $result = false;
        for($i = 0;$i<count($module_type_list);$i++){
            if($module_type_list[$i]['val'] === $product_module_type_val){
                $result = true;
            }
        }
        return $result;
    
    }
    
    public static function code_counter_get($db,$code){
        $result = null;
        $rs = $db->query_array(
            'select func_code_counter('.$db->escape($code).') code'
            );
        if(count($rs)>0)$result = $rs[0]['code'];
        return $result;
    }
    
    public static function code_counter_store_get($db,$store_id, $code){
        $result = null;
        $rs = $db->query_array(
                'select func_code_counter_store('.$db->escape($code).' ,'
                .$db->escape($store_id).' )code'
                );
        if(count($rs)>0)$result = $rs[0]['code'];
        return $result;
    }
    
    public static function status_log_add($db, $tbl,$id,$status){
        //<editor-fold defaultstate="collapsed">
        $result = array('success'=>1,'msg'=>array());
        $success = 1;
        $msg = array();
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $status_log = array(
            $tbl.'_id'=>$id,
            $tbl.'_status'=>$status,
            'modid'=>$modid,
            'moddate'=>$moddate,  
        );

        if(!$db->insert($tbl.'_status_log',$status_log)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    public static function form_data(){
        get_instance()->load->helper(self::$app_base_dir.'handy/si/si_form_data');
        return new SI_Form_Data();
    }
    
    public static function form_renderer(){
        get_instance()->load->helper(self::$app_base_dir.'handy/si/si_form_renderer');
        return new SI_Form_Renderer();
    }
        
    public static function data_submit(){
        get_instance()->load->helper(self::$app_base_dir.'handy/si/si_data_submit');
        return new SI_Data_Submit();
    }
    
    public static function result_format_get(){
        return array('success'=>1,'msg'=>array(),'response'=>array());
    }
    
}




?>