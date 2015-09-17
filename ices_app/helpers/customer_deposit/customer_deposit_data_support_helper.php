<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Deposit_Data_Support{
    
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
                        'val'=>Lang::get(SI::type_get('customer_deposit_engine', $reference_type)['label'])
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
            case 'refill_work_order':
                $q = '
                    select rwo.*
                    from refill_work_order rwo
                    where rwo.id = '.$db->escape($reference_id).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){

                    $result = array(
                        array('id'=>'type','label'=>'Type: ','val'=>SI::type_get('customer_deposit_engine', $reference_type)['label']),
                        array('id'=>'refill_work_order_date','label'=>Lang::get(array(array('val'=>'Work Order'),array('val'=>'Date'))).': ','val'=>Tools::_date($rs[0]['refill_work_order_date'],'F d, Y H:i:s')),
                        array('id'=>'total_estimated_amount','label'=>'Total Estimated Amount: ','val'=>Tools::thousand_separator($rs[0]['total_estimated_amount'])),
                        array('id'=>'total_deposit_amount','label'=>'Total Deposit Amount: ','val'=>Tools::thousand_separator($rs[0]['total_deposit_amount'])),
                    );
                }
                break;
                
        }        
        return $result;
        //</editor-fold>
    }
    
    public function customer_deposit_get($id){
        $result = array();
        $db = new DB();
        $q = '
            select t1.*,
                t2.code customer_code,
                t2.name customer_name,
                t1.outstanding_amount
            from customer_deposit t1
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
    
    public static function customer_deposit_outstanding_amount_search($param,$opt = array()){
        $result = array();
        $customer_id = isset($param['customer_id'])?Tools::_str($param['customer_id']):'';
        $lookup_val = isset($param['lookup_val'])?'%'.Tools::_str($param['lookup_val']).'%':'';
        $db = new DB();
        $limit = 10;
        $customer_id = isset($param['customer_id'])?
            Tools::_str($param['customer_id']):'';
        $cd_type = isset($opt['customer_deposit_type'])?
            Tools::_str($opt['customer_deposit_type']):'';
        
        $q_additional = ($customer_id !==''?' and t1.customer_id = '.$db->escape($customer_id):'')
            .($cd_type !==''?' and t1.customer_deposit_type = '.$db->escape($cd_type):'');
        
        $q = '
            select *
            from customer_deposit t1
            where t1.customer_deposit_status = "invoiced" 
                and t1.outstanding_amount > 0
                and t1.code like '.$db->escape($lookup_val).'
                '.$q_additional.'
            order by t1.id desc
            limit '.$limit.'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){                    
            $result = $rs;
        }
        return $result;
    }
    
    public function customer_deposit_is_allocated($customer_deposit_id){
        $result = false;
        $db = new DB();
        $q = '
            select 1
            from customer_deposit_allocation t1
            where t1.customer_deposit_allocation_status != "X"
                and t1.customer_deposit_id = '.$db->escape($customer_deposit_id).'
            limit 1
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = true;
        
        return $result;
    }
        
    public static function customer_payment_type_get($customer_id){
        //<editor-fold defaultstate="collapsed">
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
        //</editor-fold>
    }
    
    
}
?>