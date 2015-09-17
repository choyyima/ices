<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Payment_Data_Support{
    
    function notification_deposit_date_null_get(){
        get_instance()->load->helper('rpt_simple/rpt_simple_data_support');
        $result = array('response'=>null);
        $response = null;        
        $temp_result = Rpt_Simple_Data_Support::report_table_customer_payment_deposit_date_null();        
        if($temp_result['info']['data_count']>0){
            $response = array(
                'icon'=>App_Icon::html_get(APP_Icon::money())
                ,'href'=>get_instance()->config->base_url().'rpt_simple/index/customer_payment/deposit_date_null'
                ,'msg'=>' '.($temp_result['info']['data_count']).' customer payment - '.Lang::get('has not been deposited',true,false,false,true)
            );
        }
        $result['response'] = $response;
        return $result;
    }
}
?>