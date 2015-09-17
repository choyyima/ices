<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_Receipt_Allocation_Data_Support{
    public function reference_detail_get($reference_type, $reference_id){
        $result = array();
        switch($reference_type){
            case 'purchase_invoice':
                get_instance()->load->helper('purchase_invoice/purchase_invoice_data_support');
                $temp_data = Purchase_Invoice_Data_Support::purchase_invoice_get($reference_id);
                $result = array(
                    'transactional_date'=>Tools::_date(
                        $temp_data['purchase_invoice_date'],
                        'F d, Y H:i:s'),
                    'amount'=>Tools::thousand_separator($temp_data['grand_total']),
                    'outstanding_amount'=>Tools::thousand_separator($temp_data['outstanding_amount'])
                );
                break;
            case 'customer_bill':
                get_instance()->load->helper('customer_bill/customer_bill_data_support');
                $temp_data = Customer_Bill_Data_Support::customer_bill_get($reference_id);
                $result = array(
                    'transactional_date'=>Tools::_date(
                        $temp_data['customer_bill_date'],
                        'F d, Y H:i:s'),
                    'amount'=>Tools::thousand_separator($temp_data['amount']),
                    'outstanding_amount'=>Tools::thousand_separator($temp_data['outstanding_amount'])
                );
                break;
        }
        
        return $result;
    }
}
?>