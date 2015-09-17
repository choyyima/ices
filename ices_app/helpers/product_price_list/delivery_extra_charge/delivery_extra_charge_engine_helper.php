<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Delivery_Extra_Charge_Engine {
        
        
        public static function path_get(){
            $path = array(
                'index'=>get_instance()->config->base_url().'product_price_list/'
                ,'delivery_extra_charge_renderer' => 'product_price_list/delivery_extra_charge/delivery_extra_charge_renderer'
                ,'ajax_search'=>get_instance()->config->base_url().'product_price_list/ajax_search/delivery_extra_charge/'
                ,'data_support'=>get_instance()->config->base_url().'product_price_list/data_support/delivery_extra_charge/'
                
            );
            
            return json_decode(json_encode($path));
        }
        
        public static function submit($id,$method,$post){
            $post = json_decode($post,TRUE);
            $data = $post;
            $ajax_post = false;                  
            $result = null;
            $cont = true;
            
            if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
            if($method == 'add') $data['delivery_extra_charge']['id'] = '';
            else $data['delivery_extra_charge']['id'] = $id;
            
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
            $delivery_extra_charge = isset($data['delivery_extra_charge'])?$data['delivery_extra_charge']:null;

            $db = new DB();
            
            $id = isset($delivery_extra_charge['id'])?$delivery_extra_charge['id']:'';
            
            $price_list_id = isset($delivery_extra_charge['product_price_list_id'])?$delivery_extra_charge['product_price_list_id']:'';
            if(strlen($price_list_id) === 0){
                $result['success'] = 0;
                $result['msg'][] = "Product Price List cannot be empty";
            }
            
            if($method !='add'){
                if(!SI::record_exists('product_price_list_delivery_extra_charge'
                        ,array('status'=>'1','id'=>$id))){
                    $result['success'] = 0;
                    $result['msg'][] = "Data does not exists";
                }
                
            }
            
            $description = isset($delivery_extra_charge['description'])?
                Tools::empty_to_null($delivery_extra_charge['description']):null;
            if(is_null($description)){
                $result['success'] = 0;
                $result['msg'][] = 'Description'.' '.Lang::get('empty',true,false);
            }
            
            $unit_id = isset($delivery_extra_charge['unit_id'])?$delivery_extra_charge['unit_id']:'';
            if(!SI::record_exists('unit',array('status'=>'1','id'=>$unit_id))){
                $result['success'] = 0;
                $result['msg'][] = 'Unit'.' '.Lang::get('empty',true,false);
            }
            
            $q = '
                select 1 
                from product_price_list_delivery_extra_charge t1
                where t1.status>0 and t1.unit_id != '.$unit_id.'
            ';
            if(count($db->query_array($q))>0){
                $result['success'] = 0;
                $result['msg'][] = "Unable to set different Unit at the same time";   
            
            }
            
            
            $min_qty = isset($delivery_extra_charge['min_qty'])?$delivery_extra_charge['min_qty']:'0';
            if(Tools::_float($min_qty)<Tools::_float(0)){
                $result['success'] = 0;
                $result['msg'][] = "Min Qty lower than 0";   
            }
            
            $amount = isset($delivery_extra_charge['amount'])?$delivery_extra_charge['amount']:'0';
            if(floatval($amount)<=0){
                $result['success'] = 0;
                $result['msg'][] = "Amount must be higher than 0";   
            }
            
            
            
            return $result;
        }
        
        public static function adjust($action,$data=array()){
            $db = new DB();
            $result = array();
            $delivery_extra_charge = $data['delivery_extra_charge'];
           
            
            $result['delivery_extra_charge'] = array(
                'description'=>Tools::_str($delivery_extra_charge['description']),
                'product_price_list_id'=>$delivery_extra_charge['product_price_list_id']
                ,'amount'=>$delivery_extra_charge['amount']
                ,'unit_id'=>$delivery_extra_charge['unit_id']
                ,'min_qty'=>$delivery_extra_charge['min_qty']
                ,'status'=>'1'
            );
            
            return $result;
        }
        
        public static function save($method,$data){
            $db = new DB();
            $success = 1;
            $msg = array();
            $action = $method;
            $result = array("success"=>0,"msg"=>array(),'trans_id'=>'');
            $delivery_extra_charge = $data['delivery_extra_charge'];
            $id = $delivery_extra_charge['id'];
            
            $method_list = array('add','update');
            
            
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
                    $fdelivery_extra_charge = $final_data['delivery_extra_charge'];
                    $delivery_extra_charge_id = '';
                    
                    switch($action){
                        case 'add':
                            if(!$db->insert('product_price_list_delivery_extra_charge',$fdelivery_extra_charge)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            break;
                        case 'update':    
                            if(!$db->update('product_price_list_delivery_extra_charge',$fdelivery_extra_charge,array('id'=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $delivery_extra_charge_id = '';
                            break;
                    }

                    if($success == 1){
                        $result['trans_id']=$delivery_extra_charge_id; // useful for view forwarder
                    }
                    
                    if($success == 1){
                        $price_list_id = $fdelivery_extra_charge['product_price_list_id'];
                        $rs = $db->query_array('select product_price_list_status from product_price_list where id ='.$db->escape($price_list_id));
                        $price_list_status = $rs[0]['product_price_list_status'];
                        $product_price_list_status_log = array(
                            'product_price_list_id'=>$price_list_id
                            ,'product_price_list_status'=>$price_list_status
                            ,'modid'=>$modid
                            ,'moddate'=>$moddate    
                        );

                        if(!$db->insert('product_price_list_status_log',$product_price_list_status_log)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                        }
                    }
                    
                    if($success == 1){
                        $db->trans_commit();
                        $msg[] = 'Save Delivery Extra Charge Success';
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
        
        public static function delete($id){
            $db = new DB();
            $fupdate_data = array('status'=>'0');            
            $db->update('product_price_list_delivery_extra_charge ',$fupdate_data,array('id'=>$id,'status'=>'1'));
        }
        
        
        
        
    }
?>