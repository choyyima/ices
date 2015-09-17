<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SIR_Saver {
    public static function sales_invoice_pos_cancel_add($db, $final_data,$sir_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('sales_pos/sales_pos_engine');
        get_instance()->load->helper('sales_pos/sales_pos_data_support');
        get_instance()->load->helper('sales_receipt/sales_receipt_engine');
        get_instance()->load->helper('sales_receipt_allocation/sales_receipt_allocation_engine');
        get_instance()->load->helper('customer_deposit/customer_deposit_engine');
        get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_engine');
        get_instance()->load->helper('delivery_order_final/delivery_order_final_engine');
        get_instance()->load->helper('intake_final/intake_final_engine');
        get_instance()->load->helper('sales_prospect/sales_prospect_engine');
        
        $result = array('success'=>'1','msg'=>array(),'trans_id'=>$sir_id);
        $success = 1;
        $msg = array();
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $fsir = $final_data['sir'];
        
        $si_id = $fsir['reference_id'];
        $si_info = Sales_Pos_Data_Support::sales_invoice_info_get($si_id);
        
        if($success === 1){
            $sales_pos_param = array(
                'sales_pos'=>array(
                    'id'=>$si_id,
                    'sales_invoice_status'=>'X',
                    'moddate'=>$moddate,
                    'modid'=>$modid,
                ),
            );
            $temp_result = Sales_Pos_Engine::sales_pos_canceled($db, $sales_pos_param,$fsir['reference_id']);
            $success = $temp_result['success'];
            $msg = array_merge($temp_result['msg'],$msg);
        }
        
        if($success === 1){
            if($si_info['reference_type'] === 'sales_prospect'){
                $sales_prospect_id = $si_info['reference_id'];
                $sales_prospect_param = array(
                    'sales_prospect_status'=>'registered',
                    'moddate'=>$moddate,
                    'modid'=>$modid
                );
                
                $temp_result = Sales_Prospect_Engine::sales_prospect_registered($db,array('sales_prospect'=>$sales_prospect_param),$sales_prospect_id);
                $success = $temp_result['success'];
                $msg = array_merge($msg, $temp_result['msg']);
            }
        }
        
        if($success === 1){
            //<editor-fold defaultstate="collapsed" desc="SR & SRA cancel">
            $q = '
                select sra.*
                from sales_receipt_allocation sra
                where sra.sales_invoice_id = '.$db->escape($si_id).'
                    and sra.sales_receipt_allocation_status = "invoiced"
            ';
            $t_sra = $db->query_array($q);
            
            $cancel_sales_receipt = true;
            foreach($t_sra as $idx=>$row){
                $sra_param = array(
                    'sales_receipt_allocation'=>array(
                        'sales_receipt_allocation_status'=>'X',
                        'cancellation_reason'=>$fsir['description']
                    )
                );
                
                $temp_result = Sales_Receipt_Allocation_Engine::sales_receipt_allocation_canceled($db, $sra_param,$row['id']);
                $success = $temp_result['success'];
                $msg = array_merge($temp_result['msg'],$msg);
                if($success !== 1) break;
                
                if($cancel_sales_receipt){
                    $sr_param = array(
                        'sales_receipt'=>array(
                            'sales_receipt_status'=>'X',
                            'cancellation_reason'=>$fsir['description']
                        )
                    );

                    $temp_result = Sales_Receipt_Engine::sales_receipt_canceled($db, $sr_param,$row['sales_receipt_id']);
                    $success = $temp_result['success'];
                    $msg = array_merge($temp_result['msg'],$msg);
                    $cancel_sales_receipt = false;
                    if($success !== 1) break;
                }
            }
            //</editor-fold>
        }
        
        if($success === 1){
            //<editor-fold defaultstate="collapsed" desc="CDA cancel">
            $q = '
                select cda.*
                from customer_deposit_allocation cda
                where cda.sales_invoice_id = '.$db->escape($si_id).'
                    and cda.customer_deposit_allocation_status = "invoiced"
            ';
            $t_cda = $db->query_array($q);
            
            foreach($t_cda as $idx=>$row){
                $cda_param = array(
                    'customer_deposit_allocation'=>array(
                        'customer_deposit_allocation_status'=>'X',
                        'cancellation_reason'=>$fsir['description']
                    )
                );
                
                $temp_result = Customer_Deposit_Allocation_Engine::customer_deposit_allocation_canceled($db, $cda_param,$row['id']);
                $success = $temp_result['success'];
                $msg = array_merge($temp_result['msg'],$msg);
                if($success !== 1) break;
            }
            //</editor-fold>
        }
        
        if($success === 1){
            //<editor-fold defaultstate="collapsed" desc="DOF">
            $q = '
                select dof.id
                from delivery_order_final dof
                    inner join sales_invoice_delivery_order_final sidof 
                        on dof.id = sidof.delivery_order_final_id
                where sidof.sales_invoice_id = '.$db->escape($si_id).'
                    and dof.delivery_order_final_status = "process"
            ';
            $t_dof = $db->query_array($q);
            
            foreach($t_dof as $idx=>$row){
                $dof_param = array(
                    'delivery_order_final'=>array(
                        'delivery_order_final_status'=>'X',
                        'cancellation_reason'=>$fsir['description']
                    )
                );
                
                $temp_result = Delivery_Order_Final_Engine::delivery_order_final_canceled($db, $dof_param,$row['id']);
                $success = $temp_result['success'];
                $msg = array_merge($temp_result['msg'],$msg);
                if($success !== 1) break;
                
            }
            //</editor-fold>
        }
        
        if($success === 1){
            //<editor-fold defaultstate="collapsed" desc="INTF">
            $q = '
                select intake_final.id
                from intake_final
                    inner join sales_invoice_intake_final siif 
                        on intake_final.id = siif.intake_final_id
                where siif.sales_invoice_id = '.$db->escape($si_id).'
                    and intake_final.intake_final_status = "process"
            ';
            $t_intake_final = $db->query_array($q);
            
            foreach($t_intake_final as $idx=>$row){
                $if_param = array(
                    'intake_final'=>array(
                        'intake_final_status'=>'X',
                        'cancellation_reason'=>$fsir['description']
                    )
                );
                
                $temp_result = Intake_Final_Engine::intake_final_canceled($db, $if_param,$row['id']);
                $success = $temp_result['success'];
                $msg = array_merge($temp_result['msg'],$msg);
                if($success !== 1) break;
                
            }
            //</editor-fold>
        }
        
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    public static function refill_invoice_cancel_add($db, $final_data,$sir_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_invoice/refill_invoice_engine');
        
        $result = array('success'=>'1','msg'=>array(),'trans_id'=>$sir_id);
        $success = 1;
        $msg = array();
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $fsir = $final_data['sir'];
        
        $si_id = $fsir['reference_id'];
        
        if($success === 1){
            $refill_invoice_param = array(
                'refill_invoice'=>array(
                    'id'=>$si_id,
                    'refill_invoice_status'=>'X',
                    'moddate'=>$moddate,
                    'modid'=>$modid,
                ),
            );
            $temp_result = Refill_Invoice_Engine::refill_invoice_canceled($db, $refill_invoice_param,$fsir['reference_id']);
            $success = $temp_result['success'];
            $msg = array_merge($temp_result['msg'],$msg);
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    public static function mf_work_process_free_rules_add($db, $final_data, $sir_id){
        //<editor-fold defaultstate="collapsed">
        // This Function must exists to prevent error from sir engine auto caller
        $result = array('success'=>'1','msg'=>array(),'trans_id'=>$sir_id);
        $success = 1;
        $msg = array();
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
}

?>