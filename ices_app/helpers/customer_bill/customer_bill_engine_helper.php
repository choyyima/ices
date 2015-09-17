<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Bill_Engine {

    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'customer_bill/'
            ,'customer_bill_engine'=>'customer_bill/customer_bill_engine'
            ,'customer_bill_renderer' => 'customer_bill/customer_bill_renderer'
            ,'ajax_search'=>get_instance()->config->base_url().'customer_bill/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'customer_bill/data_support/'

        );

        return json_decode(json_encode($path));
    }

    public static $status_list = array(
        array(//label name is used for method name
            'val'=>'invoiced'
            ,'label'=>'INVOICED'
            ,'method'=>'customer_bill_invoiced'
            ,'default'=>true
            ,'next_allowed_status'=>array('X')
        )
        ,array(
            'val'=>'X'
            ,'label'=>'CANCELED'
            ,'method'=>'customer_bill_canceled'
            ,'next_allowed_status'=>array()

        )            
    );
    
    public static $module_type_list = array(
        array('val'=>'delivery_order_final_confirmation','label'=>'Delivery Order Final Confirmation'),
    );

    public static function customer_bill_exists($id){
        $result = false;
        $db = new DB();
        $q = '
            select 1 
            from customer_bill
            where status > 0 && id = '.$db->escape($id).'
        ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
    }
    
    public static function submit($id,$method,$post){
            $post = json_decode($post,TRUE);
            $data = $post;
            $ajax_post = false;                  
            $result = null;
            $cont = true;
            
            if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
            if($method == 'add') $data['customer_bill']['id'] = '';
            else $data['customer_bill']['id'] = $id;
            
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
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('customer_bill/customer_bill_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $customer_bill = isset($data['customer_bill'])?$data['customer_bill']:null;
        $customer_bill_id = isset($customer_bill['id'])?
            Tools::_str($customer_bill['id']):'';

        $db = new DB();
        switch($method){
            case 'customer_bill_add':
                //<editor-fold defaultstate="collapsed">
                $success = 0;
                $msg[] = 'Add Customer Bill '.Lang::get('failed',true,false);

                //</editor-fold>
                break;
            case 'customer_bill_invoiced':
                $success = 0;
                $msg[] = 'Update Customer Bill '.Lang::get('failed',true,false);


                break;
            case 'customer_bill_canceled':
                $success = 0;
                $msg[] = 'Cancel Customer Bill '.Lang::get('failed',true,false);

                break;
            default:
                $success = 0;
                $msg[] = 'Unknown Validation Method';
                break;

        }
        $result['msg'] = $msg;
        $result['success'] = $success;
        return $result;
        //</editor-fold>
    }

    public static function adjust($action,$data=array()){
        //<editor-fold defaultstate="collapsed">

        $db = new DB();
        $result = array();

        switch($action){
            case 'customer_bill_add':

                break;

            case 'customer_bill_invoiced':
                //<editor-fold defaultstate="collapsed">

                //</editor-fold>
                break;
            case 'customer_bill_canceled':
                //<editor-fold defaultstate="collapsed">

                //</editor-fold>
                break;
        }

        return $result;
        //</editor-fold>
    }

    public static function save($method,$data){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $success = 1;
        $msg = array();
        $action = $method;
        $result = array("success"=>0,"msg"=>array(),'trans_id'=>'');
        $customer_bill_data = $data['customer_bill'];
        $id = $customer_bill_data['id'];

        $method_list = array('customer_bill_add');
        foreach(SI::status_list_get('Customer_Bill_Engine') as $status){
            $method_list[] = strtolower($status['method']);
        }

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

            switch($action){                    
                case 'customer_bill_add':
                    try{ 
                        $db->trans_begin();

                    }
                    catch(Exception $e){

                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }
                    break;
                case 'customer_bill_invoiced':                        
                    try{
                        $db->trans_begin();

                    }
                    catch(Exception $e){
                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }                        

                    break;
                case 'customer_bill_canceled':
                    try{
                        $db->trans_begin();

                    }
                    catch(Exception $e){
                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    } 
                    break;
            }
        }

        if($success == 1){
            Message::set('success',$msg);
        }            

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }
    
    public function customer_bill_add($db,$final_data){
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fcustomer_bill = $final_data['customer_bill'];

        $store_id = $fcustomer_bill['store_id'];
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $cb_type = $fcustomer_bill['customer_bill_type'];
        $customer_bill_id = '';                            
        $fcustomer_bill['code'] = SI::code_counter_store_get($db,$store_id, 'customer_bill');
        if(!$db->insert('customer_bill',$fcustomer_bill)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $customer_bill_code = $fcustomer_bill['code'];

        if($success == 1){                                
            $customer_bill_id = $db->fast_get('customer_bill'
                    ,array('code'=>$customer_bill_code))[0]['id'];
            $result['trans_id']=$customer_bill_id; 
        }

        if($success == 1){
            $customer_bill_status_log = array(
                'customer_bill_id'=>$customer_bill_id
                ,'customer_bill_status'=>$fcustomer_bill['customer_bill_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('customer_bill_status_log',$customer_bill_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        if($success === 1){
            switch($cb_type){
                case 'delivery_order_final_confirmation':
                    $fdofc_cb = $final_data['dofc_cb'];
                    $fdofc_cb['customer_bill_id'] = $customer_bill_id;
                    
                    if(!$db->insert('dofc_cb',$fdofc_cb)){
                        $success = 0;
                        $msg[] = $db->_error_message();   
                        $db->trans_rollback(); 
                    }
                    
                    break;
            }
        }
        
        if($success === 1){
            get_instance()->load->helper('customer/customer_engine');
            $temp_result = Customer_Engine::customer_credit_add($db,$fcustomer_bill['amount'],$fcustomer_bill['customer_id']);
            $success = $temp_result['success'];
            $msg = array_merge($temp_result['msg'],$msg);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
    }

    
    public function customer_bill_canceled($db,$final_data,$id){
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        $customer_bill_id = $id;
        
        $fcustomer_bill = $final_data['customer_bill'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $customer_bill = $db->fast_get('customer_bill',array('id'=>$customer_bill_id))[0];
        
        if(!$db->update('customer_bill',$fcustomer_bill,array("id"=>$customer_bill_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'customer_bill',
                $customer_bill_id,$customer_bill['customer_bill_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        if($success === 1){
            get_instance()->load->helper('customer/customer_engine');
            $temp_result = Customer_Engine::customer_credit_add($db,
                    -1*$customer_bill['amount'],
                    $customer_bill['customer_id']);
            $success = $temp_result['success'];
            $msg = array_merge($temp_result['msg'],$msg);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
    }


}
?>
