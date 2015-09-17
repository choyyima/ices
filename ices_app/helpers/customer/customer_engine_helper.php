<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Engine {

    public static function customer_exists($id=""){
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from customer 
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
            'index'=>get_instance()->config->base_url().'customer/'
            ,'customer_engine'=>'customer/customer_engine'
            ,'customer_renderer' => 'customer/customer_renderer'
            ,'ajax_search'=>get_instance()->config->base_url().'customer/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'customer/data_support/'

        );

        return json_decode(json_encode($path));
    }

    static $status_list;
    
    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$status_list = array(
            array(
                'val'=>''
                ,'label'=>''
                , 'method'=>'customer_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('Customer'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'A'
                ,'label'=>'ACTIVE'
                ,'method'=>'customer_active'
                ,'default'=>true
                ,'next_allowed_status'=>array('I')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('Customer'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            )
            ,array(
                'val'=>'I'
                ,'label'=>'INACTIVE'
                ,'method'=>'customer_inactive'
                ,'next_allowed_status'=>array('A')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('Customer'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            )

        );
                
        //</editor-fold>
    }
    

    public static function customer_status_list_get(){
        $result = array();
        $result = self::$status_list;
        return $result;
    }

    public static function customer_status_get($product_status_val){
        $status_list = self::$status_list;
        $result = null;
        for($i = 0;$i<count($status_list);$i++){
            if($status_list[$i]['val'] === $product_status_val){
                $result = $status_list[$i];
            }
        }
        return $result;
    }

    public static function customer_status_next_allowed_status_get($curr_status_val){
        $result = array();
        $curr_status = null;
        for($i = 0;$i<count(self::$tatus_list);$i++){
            if(self::$status_list[$i]['val'] === $curr_status_val){
                $curr_status = self::$status_list[$i];
                break;
            }
        }

        for ($i = 0;$i<count($curr_status['next_allowed_status']);$i++){
            foreach(self::$status_list as $status){
                if($status['val'] === $curr_status['next_allowed_status'][$i]){
                    $result[] = array('val'=>$status['val']
                            ,'label'=>$status['label']
                            ,'method'=>$status['method']);
                }
            }
        }
        return $result;
    }

    public static function customer_status_default_status_get(){
        $result = array();
        foreach(self::$status_list as $status){
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



    public static function phone_exists_in_customer($id,$phone){
        $result = false;
        $db = new DB();
        $q = '
            select 1 
            from customer 
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

    public static function validate($action,$data=array()){
        get_instance()->load->helper('customer/customer_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()
        );
        
        switch($action){
            case 'customer_add':
            case 'customer_active':
            case 'customer_inactive':
                $customer = isset($data['customer'])?$data['customer']:null;
                $db = new DB();
                $customer_id = $data['customer']['id'];

                $customer_name = isset($data['customer']['name'])?$data['customer']['name']:'';
                $customer_name = str_replace(' ','',$customer_name);

                if(strlen($customer_name)==0){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get("Name").' '.Lang::get('empty',true,false,false,true);
                }

                $phone = isset($customer['phone'])?
                        preg_replace('/[^0-9]/','',$customer['phone']):'';
                $phone2 = isset($customer['phone2'])?
                        preg_replace('/[^0-9]/','',$customer['phone2']):'';
                $phone3 = isset($customer['phone3'])?
                        preg_replace('/[^0-9]/','',$customer['phone3']):'';

                $customer_type = isset($data['customer_type'])?$data['customer_type']:array();
                if(count($customer_type) === 0){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get("Customer Type").' '.Lang::get('empty',true,false,false,true);;
                }

                if($phone !== ''){
                    if(self::phone_exists_in_customer($customer_id,$phone)){
                        $result['success'] = 0;
                        $result['msg'][] = Lang::get('Phone Number').' '.Lang::get('exists',true,false,false,true).' ('.Customer_Data_Support::customer_get_by_field('phone', $phone)['code'].')';
                    }
                    else{
                        if($phone == $phone2 || $phone == $phone3){
                            $result['success'] = 0;
                            $result['msg'][] = Lang::get('Phone Number').' '.Lang::get('exists',true,false,false,true);
                        }
                    }
                }
                else{
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get('Phone Number').' '.Lang::get('empty',true,false,false,true);
                }

                if($phone2 !== ''){

                    if(self::phone_exists_in_customer($customer_id,$phone2)){
                        $result['success'] = 0;
                        $result['msg'][] = Lang::get('Phone Number').' 2 '.Lang::get('exists',true,false,false,true).' ('.Customer_Data_Support::customer_get_by_field('phone', $phone2)['code'].')';;
                    }
                    else{
                        if($phone2 == $phone || $phone2 == $phone3){
                            $result['success'] = 0;
                            $result['msg'][] = Lang::get('Phone Number').' 2 '.Lang::get('exists',true,false,false,true);
                        }
                    }
                }

                if($phone3 !== ''){
                    if(self::phone_exists_in_customer($customer_id,$phone3)){
                        $result['success'] = 0;
                        $result['msg'][] = Lang::get('Phone Number').' 3 '.Lang::get('exists',true,false,false,true).' ('.Customer_Data_Support::customer_get_by_field('phone', $phone3)['code'].')';;
                    }
                    else{
                        if($phone3 == $phone || $phone3 == $phone2){
                            $result['success'] = 0;
                            $result['msg'][] = Lang::get('Phone Number').' 3 '.Lang::get('exists',true,false,false,true);
                        }
                    }
                }

                if(in_array($action,array('active','inactive'))){
                    $customer_id = isset($customer['id'])?$customer['id']:'';

                    $q = '
                        select * 
                        from customer 
                        where id = '.$db->escape($customer['id']).'
                    ';
                    $rs_customer = $db->query_array_obj($q);

                    if(count($rs_customer) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Customer data is not available";
                        break;
                    }
                    else{
                        $rs_customer = $db->query_array_obj($q)[0];
                    }

                    //check receive product status is in list
                    $status_exists_in_list = false;
                    foreach (self::$customer_status_list as $status){
                        if($status['val'] === $customer['customer_status'])
                            $status_exists_in_list = true;
                    }
                    if(!$status_exists_in_list){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Customer Status";
                        break;
                    }

                    //check receive product status business logic
                    $status_business_logic_valid = true;
                    if($customer['customer_status'] !== $rs_customer->customer_status){
                        foreach(self::$customer_status_list as $status){
                            if($status['val'] === $rs_customer->customer_status){
                                if(isset($status['next_allowed_status'])){
                                    if(!in_array($customer['customer_status'],$status['next_allowed_status'])){
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
                        $result['msg'][] = "Invalid Customer Status business logic";
                        break;
                    }
                }

                break;
            
            default:
                $result['success'] = 0;
                $result['msg'][] = 'Invalid Method';
                break;
        }


        return $result;
    }

    public static function adjust($method, $data=array()){
        $db = new DB();
        $result = array();

        $customer = isset($data['customer'])?$data['customer']:null;
        $customer_type = isset($data['customer_type'])?$data['customer_type']:null;
        $is_credit = isset($customer['is_credit'])?($customer['is_credit'] === '1'?'1':'0'):'0';
        $is_sales_receipt_outstanding = isset($customer['is_sales_receipt_outstanding'])?($customer['is_sales_receipt_outstanding'] === '1'?'1':'0'):'0';
        switch($method){
            case 'customer_add':

                $result['customer'] = array(
                    'name' => isset($customer['name'])?$customer['name']:'',
                    'address' => isset($customer['address'])?$customer['address']:'',
                    'notes' => isset($customer['notes'])?$customer['notes']:'',
                    'city' => isset($customer['city'])?$customer['city']:'',
                    'country' => isset($customer['country'])?$customer['country']:'',
                    'phone' => isset($customer['phone'])?
                        preg_replace('/[^0-9]/','',$customer['phone']):'',
                    'phone2' => isset($customer['phone2'])?
                        preg_replace('/[^0-9]/','',$customer['phone2']):'',
                    'phone3' => isset($customer['phone3'])?
                        preg_replace('/[^0-9]/','',$customer['phone3']):'',
                    'bb_pin'=>isset($customer['bb_pin'])?$customer['bb_pin']:'',
                    'email'=>isset($customer['email'])?$customer['email']:'',
                    'customer_status'=>self::customer_status_default_status_get()['val'],
                    'is_credit'=>'0',
                    'is_sales_receipt_outstanding'=>'0',
                    'customer_credit'=>'0'
                );
                break;
            case 'customer_active':
            case 'customer_inactive':
                $result['customer'] = array(
                    'name' => isset($customer['name'])?$customer['name']:'',
                    'address' => isset($customer['address'])?$customer['address']:'',
                    'notes' => isset($customer['notes'])?$customer['notes']:'',
                    'city' => isset($customer['city'])?$customer['city']:'',
                    'country' => isset($customer['country'])?$customer['country']:'',
                    'phone' => isset($customer['phone'])?
                        preg_replace('/[^0-9]/','',$customer['phone']):'',
                    'phone2' => isset($customer['phone2'])?
                        preg_replace('/[^0-9]/','',$customer['phone2']):'',
                    'phone3' => isset($customer['phone3'])?
                        preg_replace('/[^0-9]/','',$customer['phone3']):'',
                    'bb_pin'=>isset($customer['bb_pin'])?$customer['bb_pin']:'',
                    'email'=>isset($customer['email'])?$customer['email']:'',
                    'customer_status'=>isset($customer['customer_status'])?
                        $customer['customer_status']:'',
                    'is_credit'=>$is_credit,
                );  
                break;
        }        
        if(Security_Engine::get_component_permission(User_Info::get()['user_id'],'customer','sales_receipt_outstanding_set')){
            $result['customer']['is_sales_receipt_outstanding'] = $is_sales_receipt_outstanding;
        }
        $result['customer_customer_type'] = array();
        for($i = 0;$i<count($customer_type);$i++){
            $result['customer_customer_type'][] = array(
                'customer_type_id'=>$customer_type[$i]
            );
        }

        return $result;
    }

    public function customer_add($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        $fcustomer_customer_type = $final_data['customer_customer_type'];
        $fcustomer = $final_data['customer'];
        $customer_id = '';
        $db->trans_begin();
        $rs = $db->query_array_obj('select func_code_counter("customer") "code"');
        $fcustomer['code'] = $rs[0]->code;

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $fcustomer = array_merge($fcustomer,array("modid"=>$modid,"moddate"=>$moddate));
        if(!$db->insert('customer',$fcustomer)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success){
            $result['trans_id'] = SI::get_trans_id($db,'customer','code',$fcustomer['code']);
            if($result['trans_id'] === null){
                $msg[] = 'Unable to get trans id';
                $db->trans_rollback();                                
                $success = 0;
            }
            $customer_id = $result['trans_id'];
        }

        if($success == 1){
            $customer_status_log = array(
                'customer_id'=>$customer_id
                ,'customer_status'=>$fcustomer['customer_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('customer_status_log',$customer_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }

        if($success == 1){
            $q = '
                delete from customer_customer_type 
                where customer_id = '.$db->escape($customer_id).'                                       
            ';
            $db->query($q);

            for($i = 0;$i<count($fcustomer_customer_type);$i++){
                $fcustomer_customer_type[$i]['customer_id'] = $customer_id;

                if(!$db->insert('customer_customer_type',$fcustomer_customer_type[$i])){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }

                $customer_customer_type_log = array(
                    'customer_id'=>$customer_id
                    ,'customer_type_id'=>$fcustomer_customer_type[$i]['customer_type_id']
                    ,'modid'=>$modid
                    ,'moddate'=>$moddate    
                );

                if(!$db->insert('customer_customer_type_log',$customer_customer_type_log)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                }

            }
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        
        //</editor-fold>
    }
    
    public function customer_active($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        $fcustomer_customer_type = $final_data['customer_customer_type'];
        $fcustomer = $final_data['customer'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $customer_id = $id;
        
        if(!$db->update('customer',$fcustomer,array("id"=>$customer_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }                            
        $result['trans_id']=$id;


        if($success == 1){
            $q = '
                delete from customer_customer_type 
                where customer_id = '.$db->escape($customer_id).'                                       
            ';
            $db->query($q);

            for($i = 0;$i<count($fcustomer_customer_type);$i++){
                $fcustomer_customer_type[$i]['customer_id'] = $customer_id;

                if(!$db->insert('customer_customer_type',$fcustomer_customer_type[$i])){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }

                $customer_customer_type_log = array(
                    'customer_id'=>$customer_id
                    ,'customer_type_id'=>$fcustomer_customer_type[$i]['customer_type_id']
                    ,'modid'=>$modid
                    ,'moddate'=>$moddate    
                );

                if(!$db->insert('customer_customer_type_log',$customer_customer_type_log)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                }
            }
        }

        if($success == 1){
            $customer_status_log = array(
                'customer_id'=>$customer_id
                ,'customer_status'=>$fcustomer['customer_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('customer_status_log',$customer_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    public function customer_inactive($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        return Customer_Engine::customer_active($db,$final_data,$id);
        //</editor-fold>
    }
    
    public function customer_credit_add($db, $amount,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();
        $customer_id = $id;
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $q = '
            update customer
            set customer_credit = customer_credit + '.$db->escape($amount).',
                modid = '.$db->escape($modid).',
                moddate = '.$db->escape($moddate).'
            where id = '.$db->escape($customer_id).'
        ';
        
        if(!$db->query($q)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
        
    public function customer_debit_add($db, $amount,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();
        $customer_id = $id;
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $q = '
            update customer
            set customer_debit = customer_debit + '.$db->escape($amount).',
                modid = '.$db->escape($modid).',
                moddate = '.$db->escape($moddate).'
            where id = '.$db->escape($customer_id).'
        ';
        
        if(!$db->query($q)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }    
        

        
    }
?>
