<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Validator{
    
    function validate_on_update($config,$data){
        $result = array('success'=>1,'msg'=>array());
        $success = 1;
        $msg = array();
        
        $module = isset($config['module'])?Tools::_str($config['module']):'';
        $module_name = isset($config['module_name'])?Tools::_str($config['module_name']):'';
        $module_engine = isset($config['module_engine'])?Tools::_str($config['module_engine']):'';
        $tbl = isset($config['table'])?Tools::_str($config['table']):$module;
        $status_field = isset($config['status_field'])?Tools::_str($config['status_field']):$tbl.'_status';
        $data = Tools::_arr($data);
        
        $db = new DB();
        //check data exists
        $id = isset($data['id'])?$data['id']:'';

        $q = '
            select t1.*
            from '.$tbl.' t1
            where t1.id = '.$db->escape($id).'
                and t1.status>0
        ';

        $rs = $db->query_array($q);
        
        if(count($rs)===0){
            $success = 0;
            $msg[] = $module_name." data is not available";
            
        }
        else $rs = $rs[0];
        
        if($success == 1){
            //check is cancelled
            if($rs[$status_field] === 'X'){
                $success = 0;
                $msg[] = "Cannot update Canceled ".$module_name;
            }
        }
        
        if($success === 1){
        //check status business logic            
            $status_business_logic_valid = true;
            $status_list = SI::status_list_get($module_engine);
            $istatus = isset($data[$status_field])?$data[$status_field]:'';
            if($istatus !== $rs[$status_field]){
                foreach($status_list as $status_idx=>$status){
                    if($status['val'] === $rs[$status_field]){
                        if(isset($status['next_allowed_status'])){
                            if(!in_array($istatus,$status['next_allowed_status'])){
                                $status_business_logic_valid = false;
                            }
                        }
                        else{
                            $status_business_logic_valid = false;
                        }
                        break;
                    }
                }
            }
            if(!$status_business_logic_valid){
                $success = 0;
                $msg[] = $module_name." Status Invalid";
                
            }
        }
                
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
    }
    
    function validate_on_cancel($config,$data){
        //<editor-fold defaultstate="collapsed">
        $result = array('success'=>1,'msg'=>array());
        $success = 1;
        $msg = array();
        
        $module = isset($config['module'])?Tools::_str($config['module']):'';
        $module_name = isset($config['module_name'])?Tools::_str($config['module_name']):'';
        $module_engine = isset($config['module_engine'])?Tools::_str($config['module_engine']):'';
        $tbl = isset($config['table'])?Tools::_str($config['table']):$module;
        $status_field = isset($config['status_field'])?Tools::_str($config['status_field']):$tbl.'_status';
        $data = Tools::_arr($data);
        
        $db = new DB();
        //check delivery order exists
        $id = isset($data['id'])?$data['id']:'';

        $q = '
            select t1.*
            from '.$tbl.' t1
            where t1.id = '.$db->escape($id).'
        ';

        $rs = $db->query_array($q);
        
        if(count($rs)===0){
            $success = 0;
            $msg[] = $module_name." data is not available";
            
        }
        else $rs = $rs[0];
        
        if($success == 1){
            //check is cancelled
            if($rs[$status_field] === 'X'){
                $success = 0;
                $msg[] = "Cannot update Canceled ".$module_name;
            }
        }
        
        if($success === 1){
        //check status business logic            
            $status_business_logic_valid = true;
            $status_list = SI::status_list_get($module_engine);
            $istatus = isset($data[$status_field])?$data[$status_field]:'';
            if($istatus !== $rs[$status_field]){
                foreach($status_list as $status_idx=>$status){
                    if($status['val'] === $rs[$status_field]){
                        if(isset($status['next_allowed_status'])){
                            if(!in_array($istatus,$status['next_allowed_status'])){
                                $status_business_logic_valid = false;
                            }
                        }
                        else{
                            $status_business_logic_valid = false;
                        }
                        break;
                    }
                }
            }
            if(!$status_business_logic_valid){
                $success = 0;
                $msg[] = $module_name." Status Invalid";
                
            }
        }
        
        $cancellation_reason = isset($data['cancellation_reason'])?Tools::_str($data['cancellation_reason']):'';
        if(strlen(str_replace(' ','',$cancellation_reason)) === 0){
            $success = 0;
            $msg[] = 'Cancellation Reason required';
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
}

?>