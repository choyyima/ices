<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Expedition_Engine {
        
        public static function expedition_exists($id=""){
            $result = false;
            $db = new DB();
            $q = '
                    select 1 
                    from expedition 
                    where status > 0 && id = '.$db->escape($id).'
                ';
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                $result = true;
            }
            return $result;
        }
        
        public static function path_get(){
            $path = array(
                'index'=>get_instance()->config->base_url().'expedition/'
                ,'expedition_engine'=>'expedition/expedition_engine'
                ,'expedition_renderer' => 'expedition/expedition_renderer'
                ,'ajax_search'=>get_instance()->config->base_url().'expedition/ajax_search/'
                ,'data_support'=>get_instance()->config->base_url().'expedition/data_support/'
                
            );
            
            return json_decode(json_encode($path));
        }
        
        private static $expedition_status_list = array(
            array(//label name is used for method name
                'val'=>'A'
                ,'label'=>'ACTIVE'
                ,'method'=>'active'
                ,'default'=>true
                ,'next_allowed_status'=>array('I')
            )
            ,array(
                'val'=>'I'
                ,'label'=>'INACTIVE'
                ,'method'=>'inactive'
                ,'next_allowed_status'=>array('A')
                
            )            
            
        );
        
        public static function expedition_status_list_get(){
            $result = array();
            $result = self::$expedition_status_list;
            return $result;
        }
        
        public static function expedition_status_get($product_status_val){
            $status_list = self::$expedition_status_list;
            $result = null;
            for($i = 0;$i<count($status_list);$i++){
                if($status_list[$i]['val'] === $product_status_val){
                    $result = $status_list[$i];
                }
            }
            return $result;
        }
        
        public static function expedition_status_next_allowed_status_get($curr_status_val){
            $result = array();
            $curr_status = null;
            for($i = 0;$i<count(self::$expedition_status_list);$i++){
                if(self::$expedition_status_list[$i]['val'] === $curr_status_val){
                    $curr_status = self::$expedition_status_list[$i];
                    break;
                }
            }
            
            for ($i = 0;$i<count($curr_status['next_allowed_status']);$i++){
                foreach(self::$expedition_status_list as $status){
                    if($status['val'] === $curr_status['next_allowed_status'][$i]){
                        $result[] = array('val'=>$status['val']
                                ,'label'=>$status['label']
                                ,'method'=>$status['method']);
                    }
                }
            }
            return $result;
        }
        
        public static function expedition_status_default_status_get(){
            $result = array();
            foreach(self::$expedition_status_list as $status){
                if(isset($status['default'])){
                    if($status['default']){
                        $result['val'] = $status['val'];
                        $result['label'] = $status['label'];
                        $result['method'] = $status['method'];
                    }
                }
            }
            return $result;
        }
        
        
        
        public static function phone_exists_in_expedition($id,$phone){
            $result = false;
            $db = new DB();
            $q = '
                select 1 
                from expedition 
                where (
                        phone ='.$db->escape(preg_replace('/[^0-9]/','',$phone)).' 
                        or phone2 ='.$db->escape(preg_replace('/[^0-9]/','',$phone)).' 
                        or phone3 ='.$db->escape(preg_replace('/[^0-9]/','',$phone)).' 
                    )
                    and phone!="" 
                    and id!='.$db->escape($id).'
            ';
            if(count($db->query_array_obj($q))>0){
                $result = true;
            }
            return $result;
        }
        
        public static function expedition_submit($id,$method,$post){
            $post = json_decode($post,TRUE);
            $data = $post;
            $ajax_post = false;                  
            $result = null;
            $cont = true;

            if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
            if($method == 'add') $data['expedition']['id'] = '';
            else $data['expedition']['id'] = $id;
            
            if($cont){
                $result = self::expedition_save($method,$data);
            }
            
            if(!$ajax_post){
                echo json_encode($result);
                die();
            }            
            else{
                echo json_encode($result);
                die();
            }
        }
        
        public static function expedition_get($id){
            $db = new DB();
            $result = null;
            $q = '
                select *
                , case expedition_status when "A" then "ACTIVE"
                    when "I" then "INACTIVE" end expedition_status_name
                from expedition
                where id = '.$db->escape($id).'
            ';
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                $result = $rs[0];
            }
            return $result;
        }
        
        private static function expedition_validate($action,$data=array()){
            $result = array(
                "success"=>1
                ,"msg"=>array()
            );
            
            switch($action){
                case 'add':
                case 'active':
                case 'inactive':
                    $expedition = isset($data['expedition'])?$data['expedition']:null;
                    $db = new DB();
                    $expedition_id = $data['expedition']['id'];

                    $expedition_name = isset($data['expedition']['name'])?$data['expedition']['name']:'';
                    $expedition_name = str_replace(' ','',$expedition_name);

                    if(strlen($expedition_name)==0){
                        $result['success'] = 0;
                        $result['msg'][] = "Name cannot be empty";
                    }

                    $phone = isset($expedition['phone'])?
                            preg_replace('/[^0-9]/','',$expedition['phone']):'';
                    $phone2 = isset($expedition['phone2'])?
                            preg_replace('/[^0-9]/','',$expedition['phone2']):'';
                    $phone3 = isset($expedition['phone3'])?
                            preg_replace('/[^0-9]/','',$expedition['phone3']):'';

                    if($phone !== ''){
                        if(self::phone_exists_in_expedition($expedition_id,$phone)){
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

                        if(self::phone_exists_in_expedition($expedition_id,$phone2)){
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
                        if(self::phone_exists_in_expedition($expedition_id,$phone3)){
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
                    
                    if(in_array($action,array('active','inactive'))){
                        $expedition_id = isset($expedition['id'])?$expedition['id']:'';
                        
                        $q = '
                            select * 
                            from expedition 
                            where id = '.$db->escape($expedition['id']).'
                        ';
                        $rs_expedition = $db->query_array_obj($q);

                        if(count($rs_expedition) === 0){
                            $result['success'] = 0;
                            $result['msg'][] = "Expedition data is not available";
                            break;
                        }
                        else{
                            $rs_expedition = $db->query_array_obj($q)[0];
                        }
                        
                        //check receive product status is in list
                        $status_exists_in_list = false;
                        foreach (self::$expedition_status_list as $status){
                            if($status['val'] === $expedition['expedition_status'])
                                $status_exists_in_list = true;
                        }
                        if(!$status_exists_in_list){
                            $result['success'] = 0;
                            $result['msg'][] = "Invalid Expedition Status";
                            break;
                        }

                        //check receive product status business logic
                        $status_business_logic_valid = true;
                        if($expedition['expedition_status'] !== $rs_expedition->expedition_status){
                            foreach(self::$expedition_status_list as $status){
                                if($status['val'] === $rs_expedition->expedition_status){
                                    if(isset($status['next_allowed_status'])){
                                        if(!in_array($expedition['expedition_status'],$status['next_allowed_status'])){
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
                            $result['success'] = 0;
                            $result['msg'][] = "Invalid Expedition Status business logic";
                            break;
                        }
                    }
                    
                    break;
            }
            
            
            return $result;
        }
        
        private static function expedition_adjust($method, $data=array()){
            $db = new DB();
            $result = array();
            
            $expedition = isset($data['expedition'])?$data['expedition']:null;
            
            switch($method){
                case 'add':
                    $result['expedition'] = array(
                        'name' => isset($expedition['name'])?$expedition['name']:'',
                        'address' => isset($expedition['address'])?$expedition['address']:'',
                        'notes' => isset($expedition['notes'])?$expedition['notes']:'',
                        'city' => isset($expedition['city'])?$expedition['city']:'',
                        'country' => isset($expedition['country'])?$expedition['country']:'',
                        'phone' => isset($expedition['phone'])?
                            preg_replace('/[^0-9]/','',$expedition['phone']):'',
                        'phone2' => isset($expedition['phone2'])?
                            preg_replace('/[^0-9]/','',$expedition['phone2']):'',
                        'phone3' => isset($expedition['phone3'])?
                            preg_replace('/[^0-9]/','',$expedition['phone3']):'',
                        'bb_pin'=>isset($expedition['bb_pin'])?$expedition['bb_pin']:'',
                        'email'=>isset($expedition['email'])?$expedition['email']:'',
                        'expedition_status'=>self::expedition_status_default_status_get()['val'],
                        'measurement_unit_id'=>isset($expedition['measurement_unit_id'])?$expedition['measurement_unit_id']:''
                    );
                    break;
                case 'active':
                case 'inactive':
                    $result['expedition'] = array(
                        'name' => isset($expedition['name'])?$expedition['name']:'',
                        'address' => isset($expedition['address'])?$expedition['address']:'',
                        'notes' => isset($expedition['notes'])?$expedition['notes']:'',
                        'city' => isset($expedition['city'])?$expedition['city']:'',
                        'country' => isset($expedition['country'])?$expedition['country']:'',
                        'phone' => isset($expedition['phone'])?
                            preg_replace('/[^0-9]/','',$expedition['phone']):'',
                        'phone2' => isset($expedition['phone2'])?
                            preg_replace('/[^0-9]/','',$expedition['phone2']):'',
                        'phone3' => isset($expedition['phone3'])?
                            preg_replace('/[^0-9]/','',$expedition['phone3']):'',
                        'bb_pin'=>isset($expedition['bb_pin'])?$expedition['bb_pin']:'',
                        'email'=>isset($expedition['email'])?$expedition['email']:'',
                        'expedition_status'=>isset($expedition['expedition_status'])?
                            $expedition['expedition_status']:'',
                        'measurement_unit_id'=>isset($expedition['measurement_unit_id'])?
                            $expedition['measurement_unit_id']:''
                    );  
                    break;
            }        
            
            return $result;
        }
        
        public static function expedition_save($method, $data){
            $db = new DB();
            $success = 1;
            $msg = array();
            $action = $method;
            $id = $data['expedition']['id'];
            
            $method_list = array('add');
            foreach(self::$expedition_status_list as $status){
                $method_list[] = strtolower($status['method']);
            }
            
            if(in_array($action,$method_list)){
                $validation_res = self::expedition_validate($action,$data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            else{
                $success = 0;
                $msg[] = 'Unknown method';
            }
            
            if($success == 1){
                $final_data = self::expedition_adjust($action,$data);
                $modid = User_Info::get()['user_id'];
                $moddate = date("Y-m-d H:i:s");
                switch($action){                    
                    case 'add':
                        try{
                            $fexpedition = $final_data['expedition'];
                            $expedition_id = '';
                            $db->trans_begin();
                            $rs = $db->query_array_obj('select func_code_counter("expedition") "code"');
                            $fexpedition['code'] = $rs[0]->code;
                            
                            $fexpedition = array_merge($fexpedition,array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->insert('expedition',$fexpedition)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success){
                                $result['trans_id'] = SI::get_trans_id($db,'expedition','code',$fexpedition['code']);
                                if($result['trans_id'] === null){
                                    $msg[] = 'Unable to get trans id';
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                                $expedition_id = $result['trans_id'];
                            }
                            
                            if($success == 1){
                                $expedition_status_log = array(
                                    'expedition_id'=>$expedition_id
                                    ,'expedition_status'=>$fexpedition['expedition_status']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('expedition_status_log',$expedition_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Expedition Success';
                            }
                            
                            
                        }
                        catch(Exception $e){
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }
                        
                        break;
                    case 'active':
                    case 'inactive':
                        try{
                            $db->trans_begin();
                            $fexpedition = $final_data['expedition'];
                            $expedition_id = $id;
                            if(!$db->update('expedition',$fexpedition,array("id"=>$expedition_id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }                            
                            $result['trans_id']=$id;
                            
                            if($success == 1){
                                $expedition_status_log = array(
                                    'expedition_id'=>$expedition_id
                                    ,'expedition_status'=>$fexpedition['expedition_status']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('expedition_status_log',$expedition_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Update Expedition Success';
                            }
                            
                            
                        }
                        catch(Exception $e){
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }
                        
                        
                        break;
                    case 'delete':

                        break;
                }
            }
            if($success == 1){
                Message::set('success',$msg);
            }
            else{
                Message::set('error',$msg);
            }
            
            $result['success'] = $success;
            $result['msg'] = $msg;
            
            return $result;
            
        }
        
        
        
        
        

        
    }
?>
