<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Bill_Data_Support{
    
    public static function reference_detail_get($reference_type,$reference_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        switch($reference_type){
            case 'delivery_order_final_confirmation':
                get_instance()->load->helper('dofc/dofc_data_support');
                get_instance()->load->helper('dofc/dofc_engine');
                get_instance()->load->helper('sales_pos/sales_pos_engine');
                $sales_pos_path = Sales_Pos_Engine::path_get();
                $dofc = DOFC_Data_Support::dofc_get($reference_id);
                $dofc_path = DOFC_Engine::path_get();
                $si = DOFC_Data_Support::sales_invoice_get($reference_id);
                $result = array(
                    array(
                        'id'=>'type','label'=>'Type: ',
                        'val'=>Lang::get(SI::type_get('customer_bill_engine', $reference_type)['label'])
                    ),
                    array(
                        'id'=>'dofc','label'=>Lang::get('Delivery Order Final Confirmation').': ',
                        'val'=>'<a href="'.$dofc_path->index.'view/'.$dofc['id'].'" target="_blank">'.$dofc['code'].'</a>'
                    )
                );
                if(count($si)>0){
                    $result[]  = array(
                        'id'=>'sales_invoice','label'=>'Sales Invoice: ',
                        'val'=>'<a href="'.$sales_pos_path->index.'view/'.$si['id'].'" target="_blank">'.$si['code'].'</a>'
                    );
                }
                break;
                
        }        
        return $result;
        //</editor-fold>
    }
    public static function customer_bill_get($id){
        $result = array();
        $db = new DB();
        $q = '
            select t1.*
            from customer_bill t1
            where t1.id = '.$db->escape($id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
    }
    
    
    public static function customer_bill_outstanding_amount_search($param,$opt=array()){
        $result = array();
        $customer_id = isset($param['customer_id'])?Tools::_str($param['customer_id']):'';
        $lookup_val = isset($param['lookup_val'])?'%'.Tools::_str($param['lookup_val']).'%':'';
        $customer_bill_type = isset($opt['customer_bill_type'])?Tools::_str($opt['customer_bill_type']):'';
        
        $q_additional = ($customer_bill_status === ''?' and t1.customer_bill_type= '.$db->escape($customer_bill_type):'')
            ;
        $db = new DB();
        $limit = 10;
        $q = '
            select *
            from customer_bill t1
            where t1.customer_bill_status = "invoiced" 
                and t1.outstanding_amount > 0
                and t1.code like '.$db->escape($lookup_val).'
                and t1.customer_id = '.$db->escape($customer_id).'
                '.$q_additional.'
            order by t1.customer_bill_date desc
            limit '.$limit.'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){                    
            $result = $rs;
        }
        return $result;
    }
        
    function notification_outstanding_amount_get(){
        get_instance()->load->helper('rpt_simple/rpt_simple_data_support');
        $result = array('response'=>null);
        $response = null;        
        $temp_result = Rpt_Simple_Data_Support::report_table_customer_bill_outstanding_amount();        
        if($temp_result['info']['data_count']>0){
            $response = array(
                'icon'=>App_Icon::html_get(APP_Icon::customer_bill())
                ,'href'=>get_instance()->config->base_url().'rpt_simple/index/customer_bill/outstanding_amount'
                ,'msg'=>' '.($temp_result['info']['data_count']).' customer bill - '.'outstanding amount'
            );
        }
        $result['response'] = $response;
        return $result;
    }
    
}
?>