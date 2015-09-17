<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Refund_Data_Support{
    
    
    public function customer_refund_get($id){
        $result = array();
        $db = new DB();
        $q = '
            select t1.*,
                t2.code customer_code,
                t2.name customer_name,
            from customer_refund t1
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
    
    public function reference_detail_get($reference_type, $reference_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        switch($reference_type){
            case 'customer_deposit':
                get_instance()->load->helper('customer_deposit/customer_deposit_data_support');
                $temp_data = Customer_Deposit_Data_Support::customer_deposit_get($reference_id);
                if(count($temp_data)>0){
                    $result = array(
                        array('id'=>'type','label'=>'Type: ','val'=>'Customer Deposit'),
                        array('id'=>'customer','label'=>'Customer: ','val'=>$temp_data['customer_code'].' - '.$temp_data['customer_name']),
                        array('id'=>'customer_deposit_date','label'=>'Customer Deposit Date: ','val'=>Tools::_date($temp_data['customer_deposit_date'],'F d, Y H:i:s')),
                        array('id'=>'amount','label'=>'Amount: ','val'=>Tools::thousand_separator($temp_data['amount'])),
                        array('id'=>'outstanding_amount','label'=>'Outstanding Amount: ','val'=>Tools::thousand_separator($temp_data['outstanding_amount'])),

                    );
                }
                break;
        }
        
        return $result;
        //</editor-fold>
    }
}
?>