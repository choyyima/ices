<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_Receipt_Allocation_Data_Support{
    
    
    public static function sales_receipt_allocation_exists($id){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from sales_receipt_allocation 
                where status > 0 && id = '.$db->escape($id).'
            ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
        //</editor-fold>
    }
    
    public function reference_detail_get($reference_type, $reference_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        switch($reference_type){
            case 'sales_invoice':
                get_instance()->load->helper('sales_pos/sales_pos_engine');
                get_instance()->load->helper('sales_pos/sales_pos_data_support');
                $t_path = Sales_Pos_Engine::path_get();
                $temp_data = Sales_Pos_Data_Support::sales_invoice_get($reference_id);
                if(count($temp_data)>0){
                    $result = array(
                        array('id'=>'code','label'=>'Code: ','val'=>'<a href="'.$t_path->index.'view/'.$temp_data['id'].'" target="_blank">'.$temp_data['code'].'</a>'),
                        array('id'=>'type','label'=>'Type: ','val'=>'Sales Invoice'),
                        array('id'=>'transactional_date','label'=>'Transactional Date: ','val'=>Tools::_date($temp_data['sales_invoice_date'],'F d, Y H:i:s')),
                        array('id'=>'amount','label'=>'Amount ('.Tools::currency_get().'): ','val'=>Tools::thousand_separator($temp_data['grand_total'])),
                        array('id'=>'outstanding_amount','label'=>'Outstanding Amount ('.Tools::currency_get().'): ','val'=>Tools::thousand_separator($temp_data['outstanding_amount'])),
                    );
                }
                
                break;
            case 'customer_bill':
                get_instance()->load->helper('customer_bill/customer_bill_data_support');
                get_instance()->load->helper('customer_bill/customer_bill_engine');
                $t_path = Sales_Pos_Engine::path_get();
                $temp_data = Customer_Bill_Data_Support::customer_bill_get($reference_id);
                if(count($temp_data)>0){
                    $result = array(
                        array('id'=>'code','label'=>'Code: ','val'=>'<a href="'.$t_path->index.'view/'.$temp_data['id'].'" target="_blank">'.$temp_data['code'].'</a>'),
                        array('id'=>'type','label'=>'Type: ','val'=>'Sales Invoice'),
                        array('id'=>'transactional_date','label'=>'Transactional Date: ','val'=>Tools::_date($temp_data['customer_bill_date'],'F d, Y H:i:s')),
                        array('id'=>'amount','label'=>'Amount ('.Tools::currency_get().'): ','val'=>Tools::thousand_separator($temp_data['amount'])),
                        array('id'=>'outstanding_amount','label'=>'Outstanding Amount ('.Tools::currency_get().'): ','val'=>Tools::thousand_separator($temp_data['outstanding_amount'])),
                    );
                }
                break;
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function purchase_receipt_outstanding_amount_search($param){
        $result = array();
        $customer_id = isset($param['customer_id'])?Tools::_str($param['customer_id']):'';
        $lookup_val = isset($param['lookup_val'])?'%'.Tools::_str($param['lookup_val']).'%':'';
        $db = new DB();
        $limit = 10;
        $q = '
            select *
            from purchase_receipt t1
            where t1.purchase_receipt_status = "invoiced" 
                and t1.outstanding_amount > 0
                and t1.code like '.$db->escape($lookup_val).'
                and t1.customer_id = '.$db->escape($customer_id).'
            order by t1.purchase_receipt_date desc
            limit '.$limit.'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){                    
            $result = $rs;
        }
        return $result;
    }
}
?>