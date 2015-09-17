<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SI_Data_Submit{
    function __construct(){
        
    }
    
    public function submit($engine_class,$param){
        //<editor-fold defaultstate="collapsed">
        $engine_class = Tools::class_name_get($engine_class);
        $id = $param['id'];
        $method = $param['method'];
        $data_post = json_decode($param['data_post'],TRUE);
        $primary_data_key = $param['primary_data_key']; //ex: refill_work_order
        $last_func = isset($param['last_func'])?$param['last_func']:true;
        
        $ajax_post = false;                  
        $result = null;
        $cont = true;

        if(isset($data_post['ajax_post'])) $ajax_post = $data_post['ajax_post'];
        if(!isset($data_post[$primary_data_key])) $data_post[$primary_data_key] = array();
        
        $data_post[$primary_data_key]['id'] = $id;

        if($cont){
            $result = self::save($engine_class,$method,$data_post,$primary_data_key);
        }
        
        if($ajax_post){
            echo json_encode($result);
        }
        
        if($last_func) die();
        
        return $result;
        //</editor-fold>
        
    }
    
    private function save($engine_class,$method,$data,$primary_data_key){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $success = 1;
        $msg = array();
        $action = $method;
        $result = array("success"=>0,"msg"=>array(),'trans_id'=>'');
        $id = $data[$primary_data_key]['id'];

        foreach(eval('return '.$engine_class.'::$status_list;') as $status){
            $method_list[] = strtolower($status['method']);
        }
        
        $db_lock_name = ICES_Engine::$app['app_db_lock_name'];
        $q = 'select get_lock("'.$db_lock_name
            .'",'. ICES_Engine::$app['app_db_lock_limit'].') lock_val';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            if(Tools::_float($rs[0]['lock_val']) !== Tools::_float('1')){
                $success = 0;
                $msg[] = 'Error Get DB Lock Value';
            }
        }
        else{
            $success = 0;
            $msg[] = $db->_error_message();
        }
        
        if($success === 1){
            if(in_array($action,$method_list)){
                $validation_res = eval('return '.$engine_class.'::validate($action,$data);');
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            else{
                $success = 0;
                $msg[] = 'Unknown method';
            }
        }

        if($success == 1){
            $final_data = eval('return '.$engine_class.'::adjust($action,$data);');
            $modid = User_Info::get()['user_id'];
            $moddate = date("Y-m-d H:i:s");
            try{ 
                $db->trans_begin();
                $temp_result = eval('return '.$engine_class.'::'.$action.'($db,$final_data,$id);');
                $success = $temp_result['success'];
                $msg = array_merge($msg, $temp_result['msg']);

                if($success === 1){
                    $db->trans_commit();
                    $msg[] = $this->msg_success_get($engine_class,$method);
                    $result['trans_id'] = $temp_result['trans_id'];
                }

            }
            catch(Exception $e){

                $db->trans_rollback();
                $msg[] = $e->getMessage();
                $success = 0;
            }
        }
        
        $q = 'select RELEASE_LOCK("'.$db_lock_name.'")';
        $rs = $db->query($q);
        
        if($success === 1){
            Message::set('success',$msg);
        }       
        else if ($success === 0){
            Message::set('error',$msg);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }
    
    private function msg_success_get($engine_class,$method){
        //<editor-fold defaultstate="collapsed">
        $result = '';
        $status_list = eval('return '.$engine_class.'::$status_list;');
        foreach($status_list as $idx=>$status){
            if($status['method'] == $method){
                $result = Lang::get($status['msg']['success'],true,true,false,false,true);
            }
        }
        return $result;
        //</editor-fold>
    }
    
    
}

?>