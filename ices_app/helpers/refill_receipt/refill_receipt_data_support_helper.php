<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Receipt_Data_Support {

    public static function refill_receipt_get($id){
        $result = array();
        $db = new DB();
        $q = '
            select t1.*,
                t2.code customer_code,
                t2.name customer_name,
                t1.outstanding_amount
            from refill_receipt t1
                inner join customer t2 on t1.customer_id = t2.id
            where t1.id = '.$db->escape($id).'
                and t1.status > 0
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
    }

    public static function refill_receipt_exists($id){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from refill_receipt 
                where status > 0 && id = '.$db->escape($id).'
            ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function customer_payment_type_get($customer_id){
        $result = array();
        $db = new DB();

        $q = 'select 1 from customer where is_credit = 1 and id = '.$db->escape($customer_id);
        $rs = $db->query_array($q);
        $is_credit = false;
        if(count($rs)>0){
            $is_credit=true;
        }

        $q = '
            select *
            from payment_type t1         
            where 1 = 1 '.($is_credit=== false?' and is_credit != "1" ':'').'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs;
        return $result;
    }

    public function refill_receipt_is_allocated($refill_receipt_id){
        $result = false;
        $db = new DB();
        $q = '
            select 1
            from refill_receipt_allocation t1
            where t1.refill_receipt_allocation_status != "X"
                and t1.refill_receipt_id = '.$db->escape($refill_receipt_id).'
            limit 1
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = true;
        
        return $result;
    }
    
    public static function refill_receipt_outstanding_amount_search($param){
        $result = array();
        $customer_id = isset($param['customer_id'])?Tools::_str($param['customer_id']):'';
        $lookup_val = isset($param['lookup_val'])?'%'.Tools::_str($param['lookup_val']).'%':'';
        $db = new DB();
        $limit = 10;
        $q = '
            select *
            from refill_receipt t1
            where t1.refill_receipt_status = "invoiced" 
                and t1.outstanding_amount > 0
                and t1.code like '.$db->escape($lookup_val).'
                and t1.customer_id = '.$db->escape($customer_id).'
            order by t1.refill_receipt_date desc
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
        $temp_result = Rpt_Simple_Data_Support::report_table_refill_receipt_outstanding_amount();        
        if($temp_result['info']['data_count']>0){
            $response = array(
                'icon'=>App_Icon::html_get('fa fa-money')
                ,'href'=>get_instance()->config->base_url().'rpt_simple/index/refill_receipt/outstanding_amount'
                ,'msg'=>' '.($temp_result['info']['data_count']).' refill receipt - '.Lang::get('unallocated receipt',true,false,false,true)
            );
        }
        $result['response'] = $response;
        return $result;
    }
}
?>