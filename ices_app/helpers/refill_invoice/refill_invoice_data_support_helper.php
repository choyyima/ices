<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Invoice_Data_Support{
    
    
    public static function refill_invoice_get($id){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = null;
        $q = '
            select *
            from refill_invoice
            where id = '.$db->escape($id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
    
    public static function ri_product_get($refill_invoice_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select rip.*
                ,rwop.product_marking_code
                ,rpc.id rpc_id
                ,rpc.code rpc_code
                ,rpc.name rpc_name
                ,rpm.id rpm_id
                ,rpm.code rpm_code
                ,rpm.name rpm_name
                ,u.id capacity_unit_id
                ,u.code capacity_unit_code
                ,u.name capacity_unit_name
                ,mu.code unit_code
                ,mu.name unit_name
            from ri_product rip
            left outer join refill_work_order_product rwop 
                on rip.product_id = rwop.id and rip.unit_id = rwop.unit_id
            left outer join refill_product_category rpc 
                on rwop.refill_product_category_id = rpc.id
            left outer join refill_product_medium rpm 
                on rwop.refill_product_medium_id = rpm.id
            left outer join unit u
                on rwop.capacity_unit_id = u.id
            inner join unit mu
                on rwop.unit_id = mu.id
            where rip.refill_invoice_id = '.$db->escape($refill_invoice_id).'
        ';
        $ri_product = $db->query_array($q);
        if(count($ri_product)>0){
            $ri_product = json_decode(json_encode($ri_product));
            $q_pcr = '';
            foreach($ri_product as $idx=>$row){
                $q_pcr.= ($q_pcr===''?'':',').$row->id;
                $row->product_recondition_cost = array();
                $row->product_sparepart_cost = array();
            }
            $q = ' 
                select distinct riprc.*
                from ri_product_recondition_cost riprc
                where riprc.ri_product_id in ('.$q_pcr.')
            ';
            $riprc = $db->query_array($q);
            if(count($riprc)>0){
                foreach($ri_product as $idx=>$row){
                    foreach($riprc as $idx2=>$row2){
                        if($row->id === $row2['ri_product_id']){
                            $row->product_recondition_cost[] = json_decode(json_encode($row2));
                        }
                    }
                }                
            }
            
            $q = ' 
                select distinct ripsc.*
                    ,p.code product_code
                    ,p.name product_name
                    ,u.code unit_code
                from ri_product_sparepart_cost ripsc
                left outer join product p
                    on p.id = ripsc.product_id and ripsc.product_type = "registered_product"
                inner join unit u
                    on u.id = ripsc.unit_id
                where ripsc.ri_product_id in ('.$q_pcr.')
            ';
            $ripsc = $db->query_array_obj($q);
            if(count($ripsc)>0){
                foreach($ri_product as $idx=>$row){
                    foreach($ripsc as $idx2=>$row2){
                        if($row->id === $row2->ri_product_id){
                            $row2->product_text = $row2->product_code;
                            $row2->unit_text = $row2->unit_code;
                            $row->product_sparepart_cost[] = $row2;
                        }
                    }
                }                
            }
            
            $ri_product = json_decode(json_encode($ri_product),true);
            $result = $ri_product;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function refill_invoice_exists($id=""){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from refill_invoice 
                where status > 0 && id = '.$db->escape($id).'
            ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function reference_search($lookup_str=''){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        
        $rs = Refill_Work_Order_Data_Support::rwo_search($lookup_str,array('refill_work_order_status'=>'done'));
        if(count($rs) > 0){
            foreach($rs as $i=>$row){
                $rs[$i]['id'] = $rs[$i]['id'];
                $rs[$i]['reference_type'] = 'refill_work_order';
                $rs[$i]['text'] = SI::html_tag('strong',$row['code']);
            }
            $result = $rs;
        }
        
        
        return $result;
        //</editor-fold>
    }
    
    public static function reference_dependency_get($reference_type, $reference_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();        
        $reference = Refill_Invoice_Data_Support::reference_get($reference_type, $reference_id);
        $reference_detail = array();
        $reference_product = array();
        if(!is_null($reference)){
            $reference_detail = Refill_Invoice_Data_Support::reference_detail_get($reference_type, $reference_id);
            $reference_product = Refill_Invoice_Data_Support::reference_product_get($reference_type, $reference_id);            
        }
        $result['reference'] = $reference;
        $result['reference_detail'] = $reference_detail;
        $result['reference_product'] = $reference_product;
        return $result;
        //</editor-fold>
    }
    
    public static function reference_get($reference_type, $reference_id){
        //<editor-fold defaultstate="collapsed">
        //This method will be used on data VALIDATION
        $result = null;
        switch($reference_type){
            case 'refill_work_order':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
                get_instance()->load->helper('refill_work_order/refill_work_order_engine');
                $temp_data = Refill_Work_Order_Data_Support::refill_work_order_get($reference_id);
                if(count($temp_data)>0){
                    $temp_data['id']=$temp_data['id'];
                    $temp_data['text']=$temp_data['code'];
                    $temp_data['customer_text']  = SI::html_tag('strong',$temp_data['customer_code']).' '.$temp_data['customer_name'];
                    $result = $temp_data;
                }
                //</editor-fold>
                break;
        }
        return $result;
        //</editor-fold>
        
    }
    
    public static function reference_detail_get($reference_type, $reference_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        switch($reference_type){
            case 'refill_work_order':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
                get_instance()->load->helper('refill_work_order/refill_work_order_engine');
                $temp_data = Refill_Work_Order_Data_Support::refill_work_order_get($reference_id);
                if(count($temp_data)>0){
                    $t_path = Refill_Work_Order_Engine::path_get();
                    $result = array(
                        array('id'=>'code','label'=>'Code: ','val'=>'<a href="'.$t_path->index.'view/'.$temp_data['id'].'" target="_blank">'.$temp_data['code'].'</a>'),
                        array('id'=>'type','label'=>'Type: ','val'=>SI::type_get('refill_invoice_engine', 'refill_work_order','$module_type_list')['label']),
                        array('id'=>'refill_work_order_date','label'=>'Date: ','val'=>Tools::_date($temp_data['refill_work_order_date'],'F d, Y H:i:s')),
                        array('id'=>'total_estimated_amount','label'=>'Total Estimated Amount ('.Tools::currency_get().'): ','val'=>Tools::thousand_separator($temp_data['total_estimated_amount'])),
                    );
                }
                //</editor-fold>
                break;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function reference_product_get($reference_type, $reference_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        $result = array();
        $db = new DB();
        switch($reference_type){
            case 'refill_work_order':
                $rwo_product = Refill_Work_Order_Data_Support::refill_work_order_product_get($reference_id);
                $rwo_product = json_decode(json_encode($rwo_product));
                $q_product_id = '';                
                foreach($rwo_product as $idx=>$row){
                    $q_product_id .= ($q_product_id === ''?'':',').$row->id;
                    $row->product_text = $row->product_marking_code;
                    $row->unit_text = $row->unit_code;
                    $row->amount = '0';
                    $row->product_recondition_cost = array();
                    $row->product_sparepart_cost = array();
                }
                
                //<editor-fold defaultstate="collapsed" desc="Product Recondition">
                $q = '
                    select distinct rcrf_product.product_id
                        ,rcrfprc.id
                        ,rcrfprc.product_recondition_name
                        ,rcrfprc.amount
                    from refill_checking_result_form rcrf
                    inner join rcrf_product 
                        on rcrf.id = rcrf_product.refill_checking_result_form_id
                    inner join rcrf_product_recondition_cost rcrfprc
                        on rcrf_product.id = rcrfprc.rcrf_product_id
                    where rcrf.status > 0
                        and rcrf.refill_checking_result_form_status = "done"
                        and rcrf_product.product_type = "refill_work_order_product"
                        and rcrf_product.product_id in ('.$q_product_id.')
                ';
                
                $t_rs = $db->query_array($q);
                foreach($rwo_product as $idx=>$row){
                    $pr_total_amount = Tools::_float('0');
                    foreach($t_rs as $idx=>$row2){
                        if($row->id === $row2['product_id']){
                            $pr_total_amount+= Tools::_float($row2['amount']);
                            $t_product_recondition_cost = array(
                                'reference_type'=>'rcrf_product_recondition_cost',
                                'reference_id'=>$row2['id'],
                                'product_recondition_name'=>$row2['product_recondition_name'],
                                'amount'=>$row2['amount']
                            );
                            $row->product_recondition_cost[] = json_decode(json_encode($t_product_recondition_cost));
                        }
                    }
                    $row->amount = $pr_total_amount;
                }
                //</editor-fold>
                
                //<editor-fold defaultstate="collapsed" desc="Product Sparepart">
                $q = '
                    select distinct rcrf_product.product_id rcrf_product_product_id
                        ,rcrfpsc.*
                        ,u.code unit_code
                        ,p.code product_code
                    from refill_checking_result_form rcrf
                    inner join rcrf_product 
                        on rcrf.id = rcrf_product.refill_checking_result_form_id
                    inner join rcrf_product_sparepart_cost rcrfpsc
                        on rcrf_product.id = rcrfpsc.rcrf_product_id
                    left outer join product p on rcrfpsc.product_id = p.id
                    left outer join unit u on rcrfpsc.unit_id = u.id
                    where rcrf.status > 0
                        and rcrf.refill_checking_result_form_status = "done"
                        and rcrf_product.product_type = "refill_work_order_product"
                        and rcrf_product.product_id in ('.$q_product_id.')
                ';
                
                $t_rs = $db->query_array($q);
                foreach($rwo_product as $idx=>$row){
                    $pr_total_amount = Tools::_float('0');
                    foreach($t_rs as $idx=>$row2){
                        if($row->id === $row2['rcrf_product_product_id']){
                            $pr_total_amount+= Tools::_float($row2['amount']);
                            $t_product_sparepart_cost = array(
                                'reference_type'=>'rcrf_product_sparepart_cost',
                                'reference_id'=>$row2['id'],
                                'product_type'=>$row2['product_type'],
                                'product_id'=>$row2['product_id'],
                                'product_text'=>$row2['product_code'],
                                'unit_text'=>$row2['unit_code'],
                                'unit_id'=>$row2['unit_id'],
                                'qty'=>$row2['qty'],
                                'amount'=>$row2['amount']
                            );
                            $row->product_sparepart_cost[] = json_decode(json_encode($t_product_sparepart_cost));
                        }
                    }
                    $row->amount += $pr_total_amount;
                }
                //</editor-fold>
                
                
                $rwo_product = json_decode(json_encode($rwo_product),true);
                
                $result = $rwo_product;
                break;
        }
        
        
        return $result;
        //</editor-fold>
    }
    
    public static function refill_payment_total_get($refill_invoice_id){
        //<editor-fold defaultstate="collapsed">

        get_instance()->load->helper('refill_receipt_allocation/refill_receipt_allocation_engine');
        $result = Tools::_float('0');

        $refill_receipt = Tools::_float('0');
        $db = new DB();

        $q = '
            select sum(allocated_amount) total
            from refill_receipt_allocation
            where refill_invoice_id = '.$db->escape($refill_invoice_id).'
                and refill_receipt_allocation_status = "invoiced"
        ';

        $rs = $db->query_array($q);
        foreach($rs as $idx=>$val){
            $refill_receipt += Tools::_float($val['total']);
        }

        $result = Tools::_float($refill_receipt);

        return $result;
        //</editor-fold>
    }

    public static function refill_payment_change_amount_get($refill_invoice_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_receipt_allocation/refill_receipt_allocation_engine');
        $result = Tools::_float('0');
        $change_amount = Tools::_float('0');
        $db = new DB();
        $q = '
            select sum(sr.change_amount) total
            from refill_receipt_allocation sra
                inner join refill_receipt sr on sra.refill_receipt_id = sr.id
            where sra.refill_invoice_id = '.$db->escape($refill_invoice_id).'
                and sra.refill_receipt_allocation_status = "invoiced"
        ';

        $rs = $db->query_array($q);
        foreach($rs as $idx=>$val){
            $change_amount = Tools::_float($val['total']);
        }

        $result = Tools::_float($change_amount);

        return $result;
        //</editor-fold>
    }
    
    public static function refill_invoice_outstanding_amount_search($param){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $customer_id = isset($param['customer_id'])?Tools::_str($param['customer_id']):'';
        $lookup_val = isset($param['lookup_val'])?'%'.Tools::_str($param['lookup_val']).'%':'';
        $db = new DB();
        $limit = 10;
        $q = '
            select *
            from refill_invoice t1
            where t1.refill_invoice_status = "invoiced" 
                and t1.outstanding_amount > 0
                and t1.code like '.$db->escape($lookup_val).'
                and t1.customer_id = '.$db->escape($customer_id).'
            order by t1.refill_invoice_date desc
            limit '.$limit.'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){                    
            $result = $rs;
        }
        return $result;
        //</editor-fold>
    }
    
    public function notification_outstanding_amount_get(){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_simple/rpt_simple_data_support');
        $result = array('response'=>null);
        $response = null;
        $temp_result = Rpt_Simple_Data_Support::report_table_refill_invoice_outstanding_amount();        
        if($temp_result['info']['data_count']>0){
            $response = array(
                'icon'=>App_Icon::html_get('fa fa-file-text')
                ,'href'=>get_instance()->config->base_url().'rpt_simple/index/refill_invoice/outstanding_amount'
                ,'msg'=>' '.($temp_result['info']['data_count']).' refill invoice - '.Lang::get(array(array('val'=>'incomplete','grammar'=>'adj'),array('val'=>'payment')),true,false,false,true)
            );
        }
        $result['response'] = $response;
        return $result;
        //</editor-fold>
    }
    
}
?>