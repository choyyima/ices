<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Product_Unit_Conversion_Engine {
        public static $type =  array(
            array('val'=>'sales_moq','label'=>'Sales MOQ')
            ,array('val'=>'sales_real_weight','label'=>'Sales Real Weight')
            ,array('val'=>'sales_expedition_weight','label'=>'Sales Expedition weight')
            
        );
        
        public static $status_list = array(
            //<editor-fold defaultstate="collapsed">
            array(//label name is used for method name
                'val'=>'active'
                ,'label'=>'ACTIVE'
                ,'method'=>''
                ,'default'=>true
                ,'next_allowed_status'=>array('inactive')
            )
            ,array(
                'val'=>'inactive'
                ,'label'=>'INACTIVE'
                ,'method'=>''
                ,'next_allowed_status'=>array('active')
            )
            //</editor-fold>
        );
        
        public static function type_label_get($val){
            $result = '';
            foreach(self::$type as $idx=>$type){
                if($val === $type['val']) $result = $type['label'];
            }
            return $result;
        }
        
        public static function path_get(){
            $path = array(
                'index'=>get_instance()->config->base_url().'product/'
                ,'product_unit_conversion_engine' => 'product_unit_conversion/product_unit_conversion_engine'
                ,'product_unit_conversion_renderer' => 'product_unit_conversion/product_unit_conversion_renderer'
                ,'ajax_search'=>get_instance()->config->base_url().'product/ajax_search/'
                ,'data_support'=>get_instance()->config->base_url().'product/data_support/product_unit_conversion/'
                
            );
            
            return json_decode(json_encode($path));
        }
        
        
        public static function delete($id){
            $db = new DB();
            $db->query('update product_unit_conversion set status = 0 , moddate=now(), modid="'.User_Info::get()['user_id'].'"  where id = '.$db->escape($id));
            
        }
        
        
        public static function submit($id,$method,$post){
            $post = json_decode($post,TRUE);
            $data = $post;
            $ajax_post = false;                  
            $result = null;
            $cont = true;
            
            if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
            if($method == 'add') $data['product_unit_conversion']['id'] = '';
            else $data['product_unit_conversion']['id'] = $id;
            
            if($cont){
                $result = self::save($method,$data);
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
        
        public static function validate($method,$data=array()){
            $result = array(
                "success"=>1
                ,"msg"=>array()
                
            );
            $unit_conversion = isset($data['product_unit_conversion'])?
                    $data['product_unit_conversion']:null;

            $db = new DB();
            
            $unit_conversion_type = isset($unit_conversion['type'])?$unit_conversion['type']:'';
            if(!Tools::data_array_exists(self::$type,array('val'=>$unit_conversion_type))){
                $result['success'] = 0;
                $result['msg'][] = "Invalid Unit Conversion Type";
            }
            
            $id = isset($unit_conversion['id'])?$unit_conversion['id']:'';

            $product_id = isset($unit_conversion['product_id'])?$unit_conversion['product_id']:'';
            if(strlen($product_id) === 0){
                $result['success'] = 0;
                $result['msg'][] = "Product cannot be empty";
            }
            
            $qty_1 = isset($unit_conversion['qty_1'])?$unit_conversion['qty_1']:'0';
            if(floatval($qty_1)<=0){
                $result['success'] = 0;
                $result['msg'][] = "Qty 1 must be higher than 0";
            }
            
            $unit_id_1 = isset($unit_conversion['unit_id_1'])?$unit_conversion['unit_id_1']:'';
            $q = '
                select 1
                from product_unit t1
                where t1.product_id = '.$db->escape($product_id).'
                    and t1.unit_id = '.$db->escape($unit_id_1).'
                
            ';
            if(count($db->query_array($q)) == 0){
                $result['success'] = 0;
                $result['msg'][] = "Unit 1 cannot be empty";
            }
            
            $qty_2 = isset($unit_conversion['qty_2'])?$unit_conversion['qty_2']:'0';
            if(floatval($qty_2)<=0){
                $result['success'] = 0;
                $result['msg'][] = "Qty 2 must be higher than 0";
            }
            
            $unit_id_2 = isset($unit_conversion['unit_id_2'])?$unit_conversion['unit_id_2']:'';
            $q = '
                select 1
                from unit 
                where id = '.$db->escape($unit_id_2).'
            ';
            if(count($db->query_array($q)) == 0){
                $result['success'] = 0;
                $result['msg'][] = "Unit 2 cannot be empty";
            }
            
            
            
            switch($unit_conversion_type){
                case 'sales_moq':
                    
                    if($unit_id_2 === $unit_id_1 && $unit_id_2 !== '' ){
                        $result['success'] = 0;
                        $result['msg'][] = "Unit 1 and Unit 2 cannot be similar";
                    }
                    
                    $q = '
                        select 1
                        from product_unit_conversion
                        where product_id = '.$db->escape($product_id).'
                            and unit_id_1 = '.$db->escape($unit_id_1).'
                            and unit_id_2 = '.$db->escape($unit_id_2).'
                            and type = "sales_moq"
                    ';
                    if(count($db->query_array($q))>0){
                        $result['success'] = 0;
                        $result['msg'][] = "Unit Conversion already exists";
                    }
                    
                    
                    
                    break;
                case 'sales_real_weight':
                    $q = '
                        select 1
                        from product_unit_conversion
                        where product_id = '.$db->escape($product_id).'
                            and unit_id_1 = '.$db->escape($unit_id_1).'
                            and unit_id_2 = '.$db->escape($unit_id_2).'
                            and type = "sales_real_weight"
                            and status > 0
                    ';
                    if(count($db->query_array($q))>0){
                        $result['success'] = 0;
                        $result['msg'][] = "Unit Conversion already exists";
                    }
                    break;
                case 'sales_expedition_weight':
                    $expedition_id = isset($unit_conversion['expedition_id'])? 
                        $unit_conversion['expedition_id']:'';
                    
                    if(count($db->query_array('select * from expedition where id = '.$db->escape($expedition_id))) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Expedition cannot be empty";
                    }
                    if($result['success'] === 1){
                        $q = '
                            select 1
                            from product_unit_conversion
                            where product_id = '.$db->escape($product_id).'
                                and unit_id_1 = '.$db->escape($unit_id_1).'
                                and unit_id_2 = '.$db->escape($unit_id_2).'
                                and type = "sales_expedition_weight"
                                and expedition_id = '.$db->escape($expedition_id).'
                                and status > 0
                        ';
                        if(count($db->query_array($q))>0){
                            $result['success'] = 0;
                            $result['msg'][] = "Unit Conversion already exists";
                        }
                    }
                    break;
            }
            return $result;
        }
        
        public static function adjust($action,$data=array()){
            $db = new DB();
            $result = array();
            $unit_conversion = $data['product_unit_conversion'];
            
            $product_id = isset($unit_conversion['product_id'])? $unit_conversion['product_id']:'';
            $type = isset($unit_conversion['type'])? $unit_conversion['type']:'';
            $qty_1 = isset($unit_conversion['qty_1'])? $unit_conversion['qty_1']:'1';
            $unit_id_1 = isset($unit_conversion['unit_id_1'])? $unit_conversion['unit_id_1']:'';
            $qty_2 = isset($unit_conversion['qty_2'])? $unit_conversion['qty_2']:'';
            $unit_id_2 = isset($unit_conversion['unit_id_2'])? $unit_conversion['unit_id_2']:'';
            $expedition_id = isset($unit_conversion['expedition_id'])? $unit_conversion['expedition_id']:'';
            switch($type){
                case 'sales_moq':
                    $result['product_unit_conversion'] = array(
                        'product_id'=>$product_id
                        ,'type'=>'sales_moq'
                        ,'qty_1'=>$qty_1
                        ,'unit_id_1'=>$unit_id_1
                        ,'qty_2'=>$qty_2
                        ,'unit_id_2'=>$unit_id_2
                        
                    );
                    break;
                case 'sales_real_weight':
                    $result['product_unit_conversion'] = array(
                        'product_id'=>$product_id
                        ,'type'=>'sales_real_weight'
                        ,'qty_1'=>$qty_1
                        ,'unit_id_1'=>$unit_id_1
                        ,'qty_2'=>$qty_2
                        ,'unit_id_2'=>$unit_id_2
                    );
                    break;
                case 'sales_expedition_weight':
                    
                    $result['product_unit_conversion'] = array(
                        'product_id'=>$product_id
                        ,'type'=>'sales_expedition_weight'
                        ,'qty_1'=>$qty_1
                        ,'unit_id_1'=>$unit_id_1
                        ,'qty_2'=>$qty_2
                        ,'unit_id_2'=>$unit_id_2
                        ,'expedition_id'=>$expedition_id
                    );
                    break;
            }
            if($action === 'add'){
                $result['product_unit_conversion']['product_unit_conversion_status']='active';
            }
            return $result;
        }
        
        public static function save($method,$data){
            $db = new DB();
            $success = 1;
            $msg = array();
            $action = $method;
            $result = array("success"=>0,"msg"=>array(),'trans_id'=>'');
            $product_unit_conversion = $data['product_unit_conversion'];
            $id = $product_unit_conversion['id'];
            
            $method_list = array('add');
            
            
            if(in_array($action,$method_list)){
                $validation_res = self::validate($action,$data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            else{
                $success = 0;
                $msg[] = 'Unknown method';
            }

            if($success == 1){
                $final_data = self::adjust($action,$data);
                $modid = User_Info::get()['user_id'];
                $moddate = date("Y-m-d H:i:s");
                
                try{ 
                    $db->trans_begin();
                    $funit_conversion = $final_data['product_unit_conversion'];
                    $unit_conversion_id = '';
                    
                    switch($action){
                        case 'add':
                            if(!$db->insert('product_unit_conversion',$funit_conversion)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $result['trans_id']=$unit_conversion_id;
                            }
                            
                            break;
                    }


                    if($success == 1){
                        $db->trans_commit();
                        $msg[] = 'Save Product Unit Conversion Success';
                    }
                }
                catch(Exception $e){

                    $db->trans_rollback();
                    $msg[] = $e->getMessage();
                    $success = 0;
                }
            }
            
            if($success == 1){
                Message::set('success',$msg);
            }            
            
            $result['success'] = $success;
            $result['msg'] = $msg;
            
            return $result;
        }
        
        function active($id){
            $result = array('success'=>1,'msg'=>array());
            $success = 1;
            $msg = array();
            $db = new DB();
            $q = '
                update product_unit_conversion 
                set product_unit_conversion_status = "active" 
                where id = '.$db->escape($id);
            if(!$db->query($q)){
                $success = 0;
                $msg[] = $db->_error_message();
            }
            
            $result['success'] = $success;
            $result['msg'] = $msg;
            
            echo json_encode($result);
            die();
            
        }
        
        function inactive($id){
            $result = array('success'=>1,'msg'=>array());
            $success = 1;
            $msg = array();
            $db = new DB();
            $q = '
                update product_unit_conversion 
                set product_unit_conversion_status = "inactive" 
                where id = '.$db->escape($id);
            if(!$db->query($q)){
                $success = 0;
                $msg[] = $db->_error_message();
            }
            
            $result['success'] = $success;
            $result['msg'] = $msg;
            
            echo json_encode($result);
            die();
            
        }
        
    }
?>