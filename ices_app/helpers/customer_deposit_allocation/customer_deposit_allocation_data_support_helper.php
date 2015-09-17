<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Deposit_Allocation_Data_Support{
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
                get_instance()->load->helper('customer_bill/customer_bill_engine');
                get_instance()->load->helper('customer_bill/customer_bill_data_support');
                $t_path = Customer_Bill_Engine::path_get();
                $temp_data = Customer_Bill_Data_Support::customer_bill_get($reference_id);
                if(count($temp_data)>0){
                    $result = array(
                        array('id'=>'code','label'=>'Code: ','val'=>'<a href="'.$t_path->index.'view/'.$temp_data['id'].'" target="_blank">'.$temp_data['code'].'</a>'),
                        array('id'=>'type','label'=>'Type: ','val'=>'Customer Bill'),
                        array('id'=>'transactional_date','label'=>'Transactional Date: ','val'=>Tools::_date($temp_data['customer_bill_date'],'F d, Y H:i:s')),
                        array('id'=>'amount','label'=>'Amount ('.Tools::currency_get().'): ','val'=>Tools::thousand_separator($temp_data['amount'])),
                        array('id'=>'outstanding_amount','label'=>'Outstanding Amount ('.Tools::currency_get().'): ','val'=>Tools::thousand_separator($temp_data['outstanding_amount'])),
                    );
                }
                break;
            case 'refill_invoice':
                get_instance()->load->helper('refill_invoice/refill_invoice_engine');
                get_instance()->load->helper('refill_invoice/refill_invoice_data_support');
                $t_path = Refill_Invoice_Engine::path_get();
                $temp_data = Refill_Invoice_Data_Support::refill_invoice_get($reference_id);
                if(count($temp_data)>0){
                    $result = array(
                        array('id'=>'code','label'=>'Code: ','val'=>'<a href="'.$t_path->index.'view/'.$temp_data['id'].'" target="_blank">'.$temp_data['code'].'</a>'),
                        array('id'=>'type','label'=>'Type: ','val'=>'Refill Invoice'),
                        array('id'=>'transactional_date','label'=>'Transactional Date: ','val'=>Tools::_date($temp_data['refill_invoice_date'],'F d, Y H:i:s')),
                        array('id'=>'amount','label'=>'Amount ('.Tools::currency_get().'): ','val'=>Tools::thousand_separator($temp_data['grand_total_amount'])),
                        array('id'=>'outstanding_amount','label'=>'Outstanding Amount ('.Tools::currency_get().'): ','val'=>Tools::thousand_separator($temp_data['outstanding_amount'])),
                    );
                }
                break;
        }
        
        return $result;
        //</editor-fold>
    }
}
?>