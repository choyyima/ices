<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_Receipt_Data_Support {

    public function purchase_receipt_get($id){
        $result = array();
        $db = new DB();
        $q = '
            select t1.*,
                t2.code supplier_code,
                t2.name supplier_name,
                t1.outstanding_amount
            from purchase_receipt t1
                inner join supplier t2 on t1.supplier_id = t2.id
            where t1.id = '.$db->escape($id).'
                and t1.status > 0
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
    }

    public static function purchase_receipt_outstanding_amount_search($param){
        $result = array();
        $supplier_id = isset($param['supplier_id'])?Tools::_str($param['supplier_id']):'';
        $lookup_val = isset($param['lookup_val'])?'%'.Tools::_str($param['lookup_val']).'%':'';
        $db = new DB();
        $limit = 10;
        $q = '
            select *
            from purchase_receipt t1
            where t1.purchase_receipt_status = "invoiced" 
                and t1.outstanding_amount > 0
                and t1.code like '.$db->escape($lookup_val).'
                and t1.supplier_id = '.$db->escape($supplier_id).'
            order by t1.purchase_receipt_date desc
            limit '.$limit.'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){                    
            $result = $rs;
        }
        return $result;
    }
    
    public static function supplier_payment_type_get(){
        $result = array();
        $db = new DB();

        $q = '
            select *
            from payment_type t1         
            where 1 = 1
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs;
        return $result;
    }

    public function purchase_receipt_is_allocated($purchase_receipt_id){
        $result = false;
        $db = new DB();
        $q = '
            select 1
            from purchase_receipt_allocation t1
            where t1.purchase_receipt_allocation_status != "X"
                and t1.purchase_receipt_id = '.$db->escape($purchase_receipt_id).'
            limit 1
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = true;

        return $result;
    }

    function notification_outstanding_amount_get(){
        get_instance()->load->helper('rpt_simple/rpt_simple_data_support');
        $result = array('response'=>null);
        $response = null;        
        $temp_result = Rpt_Simple_Data_Support::report_table_purchase_receipt_outstanding_amount();        
        if($temp_result['info']['data_count']>0){
            $response = array(
                'icon'=>App_Icon::html_get(APP_Icon::purchase_receipt())
                ,'href'=>get_instance()->config->base_url().'rpt_simple/index/purchase_receipt/outstanding_amount'
                ,'msg'=>' '.($temp_result['info']['data_count']).' purchase receipt - '.'outstanding amount'
            );
        }
        $result['response'] = $response;
        return $result;
    }
}
?>