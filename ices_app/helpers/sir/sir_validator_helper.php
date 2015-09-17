<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SIR_Validator {
    
    public static function sales_invoice_pos_cancel_add_validate($data){
        //<editor-fold defaultstate="collapsed">
        
        $result = array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = array();
        $sir = isset($data['sir'])?$data['sir']:null;
        $sir_id = isset($sir['id'])?Tools::_str($sir['id']):'';
        $reference_id = isset($sir['reference_id'])?$sir['reference_id']:'';
        $db = new DB();
        $q = '
            select t1.*
            from sales_invoice t1
            where t1.id = '.$db->escape($reference_id).'
                and t1.sales_invoice_status !="X"
                
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $sales_invoice = $rs[0];
            $sales_invoice_info = $db->fast_get('sales_invoice_info',array('sales_invoice_id'=>$reference_id))[0];
            $is_delivery = Tools::_bool($sales_invoice_info['is_delivery']);
            
            $q = '
                select 1
                from sales_receipt_allocation t1
                    inner  join sales_receipt t2 on t1.sales_receipt_id = t2.id
                where t1.sales_invoice_id = '.$db->escape($sales_invoice['id']).'
                    and t1.sales_receipt_allocation_status != "X"
                    and t1.allocated_amount != (t2.amount - t2.change_amount)
            ';
            
            if(count($db->query_array($q))>0){
                $success = 0;
                $msg[] = 'Sales Receipt Allocation '.Lang::get('exists',true, false);
            }
            
            /*
            $q = '
                select 1
                from customer_deposit_allocation t1
                    inner join customer_deposit t2 on t1.customer_deposit_id = t2.id
                where t1.sales_invoice_id = '.$db->escape($sales_invoice['id']).'
                    and t1.customer_deposit_allocation_status != "X"
                    and t1.allocated_amount != t2.amount
            ';
            
            if(count($db->query_array($q))>0){
                $success = 0;
                $msg[] = 'Customer Deposit Allocation '.Lang::get('exists',true, false);
            }
            */
            if($is_delivery){
                $q = '
                    select 1
                    from sales_invoice_delivery_order_final sidof
                        inner join delivery_order_final_delivery_order dofdo
                            on sidof.delivery_order_final_id = dofdo.delivery_order_final_id
                        inner join delivery_order do
                            on dofdo.delivery_order_id = do.id
                                and do.delivery_order_status != "X"
                                and do.delivery_order_status != "process"
                    where sidof.sales_invoice_id = '.$db->escape($sales_invoice['id']).'
                        
                    limit 1
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $success = 0;
                    $msg[] = Lang::get('Delivery Order').' Final '.Lang::get('exists',true,false);
                }
            }
            else{
                $q = '
                    select 1
                    from sales_invoice_intake_final siif
                        inner join intake_final_intake ifi
                            on siif.intake_final_id = ifi.intake_final_id
                        inner join intake i
                            on ifi.intake_id = i.id
                                and i.intake_status != "X"
                                and i.intake_status != "process"
                    where siif.sales_invoice_id = '.$db->escape($sales_invoice['id']).'
                        
                    limit 1
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $success = 0;
                    $msg[] = Lang::get('Product Intake').' Final '.Lang::get('exists',true,false);
                }
            }
        }
        else{
            $success = 0;
            $msg[] = Lang::get('Reference').' '.Lang::get('empty',true,false);
        }
        
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    public static function refill_invoice_cancel_add_validate($data){
        //<editor-fold defaultstate="collapsed">
        
        $result = array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = array();
        $sir = isset($data['sir'])?$data['sir']:null;
        $sir_id = isset($sir['id'])?Tools::_str($sir['id']):'';
        $reference_id = isset($sir['reference_id'])?$sir['reference_id']:'';
        $db = new DB();
        $q = '
            select t1.*
            from refill_invoice t1
            where t1.id = '.$db->escape($reference_id).'
                and t1.refill_invoice_status = "invoiced"
                
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $refill_invoice = $rs[0];
           
            $q = '
                select 1
                from refill_receipt_allocation t1
                    inner  join refill_receipt t2 on t1.refill_receipt_id = t2.id
                where t1.refill_invoice_id = '.$db->escape($refill_invoice['id']).'
                    and t1.refill_receipt_allocation_status != "X"
            ';
            
            if(count($db->query_array($q))>0){
                $success = 0;
                $msg[] = 'Refill Receipt Allocation '.Lang::get('exists',true, false);
            }
            
            
            $q = '
                select 1
                from customer_deposit_allocation t1
                    inner join customer_deposit t2 on t1.customer_deposit_id = t2.id
                where t1.refill_invoice_id = '.$db->escape($refill_invoice['id']).'
                    and t1.customer_deposit_allocation_status != "X"

            ';
            
            if(count($db->query_array($q))>0){
                $success = 0;
                $msg[] = 'Customer Deposit Allocation '.Lang::get('exists',true, false);
            }
            
        }
        else{
            $success = 0;
            $msg[] = Lang::get('Reference').' '.Lang::get('empty',true,false);
        }
        
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
}

?>