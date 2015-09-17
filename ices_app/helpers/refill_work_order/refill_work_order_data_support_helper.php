<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Work_Order_Data_Support {

    public function customer_detail_get($customer_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*
            from customer t1
            where t1.id = '.$db->escape($customer_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = array(
                    array('id'=>'customer_name','label'=>'Name: ','val'=>$rs[0]['name']),
                    array('id'=>'customer_address','label'=>'Address: ','val'=>$rs[0]['address']),
                    array('id'=>'customer_phone','label'=>'Phone: ','val'=>$rs[0]['phone']),

            );
        }
        return $result;
        //</editor-fold>
    }

    public static function refill_work_order_get($id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*
                ,t2.code customer_code
                ,t2.name customer_name
                ,t2.address customer_address
                ,t2.phone customer_phone
            from refill_work_order t1
                inner join customer t2 on t1.customer_id = t2.id
            where t1.id = '.$db->escape($id).'
                and t1.status > 0
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }

    public static function refill_work_order_info_get($refill_work_order_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*, concat(t2.first_name," ",t2.last_name) creator_name
            from refill_work_order_info t1
                inner join user_login t2 on t1.creator_id = t2.id
            where t1.refill_work_order_id = '.$db->escape($refill_work_order_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs[0];
        return $result;
        //</editor-fold>
    }
    
    public static function refill_work_order_product_get($rwo_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*
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
            from refill_work_order_product t1
                left outer join refill_product_category rpc 
                    on t1.refill_product_category_id = rpc.id
                left outer join refill_product_medium rpm 
                    on t1.refill_product_medium_id = rpm.id
                left outer join unit u
                    on t1.capacity_unit_id = u.id
                inner join unit mu
                    on t1.unit_id = mu.id
            where t1.refill_work_order_id = '.$db->escape($rwo_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs;
        return $result;
        //</editor-fold>
    }
    
    public function rpc_rpm_cu_all_exist($data_arr,$rpc_status="active",$rpm_status="active"){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q_product = 'select -1 rpc_id, -1 rpm_id, -1 capacity_unit_id';
        foreach($data_arr as $idx=>$data){
            $rpc_id = isset($data['rpc_id'])?Tools::_str($data['rpc_id']):'';
            $rpm_id = isset($data['rpm_id'])?Tools::_str($data['rpm_id']):'';
            $capacity_unit_id = isset($data['capacity_unit_id'])?Tools::_str($data['capacity_unit_id']):'';
            $q_product.=' union all select '.$db->escape($rpc_id).', '.$db->escape($rpm_id).
                ', '.$db->escape($capacity_unit_id);
        }

        $q = '
            select count(1) total
            from rpc_rpm_cu t1
                inner join ('.$q_product.') tp 
                    on t1.refill_product_category_id = tp.rpc_id 
                        and t1.refill_product_medium_id = tp.rpm_id
                        and t1.capacity_unit_id = tp.capacity_unit_id
                inner join refill_product_category t3 
                    on t1.refill_product_category_id = t3.id 
                        and t3.status>0
                inner join refill_product_medium t4 
                    on t1.refill_product_medium_id = t4.id
                        and t4.status>0
                inner join unit t5 
                    on t1.capacity_unit_id = t5.id
                        and t5.status>0

            where 1 = 1 
            '.
                ($rpc_status===''?'':
                    'and t3.refill_product_category_status = '.$db->escape($rpc_status)).
                ($rpm_status===''?'':
                    'and t4.refill_product_medium_status = '.$db->escape($rpm_status)).
            '
        ';
        $total = $db->query_array($q)[0]['total'];
        if(Tools::_int(count($data_arr))===Tools::_int($total)) $result = true;
        return $result;
        //</editor-fold>
    }
    
    public function rwo_total_deposit_incomplete_search($lookup_data){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $db = new DB();
        $q = '
            select rwo.*,
                c.id customer_id,
                c.code customer_code,
                c.name customer_name,
                c.phone customer_phone
            from refill_work_order rwo
                inner join customer c on rwo.customer_id = c.id
            where rwo.code like '.$db->escape('%',$lookup_data,'%').'
                and rwo.status>0
                and rwo.total_estimated_amount >= rwo.total_deposit_amount
                and rwo.refill_work_order_status = "process"
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs;
        
        return $result;
        //</editor-fold>
    }
 
    public static function rwo_search($lookup_data, $opt = array()){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $rwo_status = isset($opt['refill_work_order_status'])?Tools::_str($opt['refill_work_order_status']):'';
        $db = new DB();
        $q = '
            select distinct rwo.*
            from refill_work_order rwo
            inner join refill_work_order_product rwop 
                on rwo.id = rwop.refill_work_order_id
            where rwo.refill_work_order_status = '.$db->escape($rwo_status).'
                and rwo.status > 0
                and (
                    rwo.code like '.$db->escape('%'.$lookup_data.'%').'
                    or rwop.product_marking_code like '.$db->escape('%'.$lookup_data.'%').'
                )
                

            order by rwo.id desc
            limit 10;
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs;
        
        return $result;
        //</editor-fold>
    }
    
    function rwo_product_search($lookup_data,$param = array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_work_order/refill_work_order_engine');
        $result = array();
        $rwo_product_status = isset($param['rwo_product_status'])?
            $param['rwo_product_status']:
            (SI::type_default_type_get('refill_work_order_engine','$rwo_product_status'));
        
        $module = Tools::empty_to_null(isset($param['module'])?$param['module']:'');
        $q_additional_from = '';
        $q_additional_where = '';
        switch($module){
            case 'refill_subcon_work_order':
                $rwo_product_status = 'process';
                $q_additional_from = '
                    inner join delivery_order_product dop 
                        on dop.product_id = rwop.id and dop.product_type = "refill_work_order_product"
                    inner join delivery_order do 
                        on do.id = dop.delivery_order_id and do.delivery_order_status = "done"
                        and do.status > 0
                ';
                $q_additional_where = '';
                break;
        }
        $lookup_str = '%'.$lookup_data.'%';
        $db = new DB();
        $q = '
            select 
                rwop.id id,
                "refill_work_order_product" product_type,
                rwop.product_marking_code,
                rwop.qty,
                rwop.qty_stock,
                rwop.id rwo_product_id,
                rwop.unit_id,
                rpc.id rpc_id,
                rpc.code rpc_code,
                rpc.name rpc_name,
                rpm.id rpm_id,
                rpm.code rpm_code,
                rpm.name rpm_name,
                u.id capacity_unit_id,
                u.name capacity_unit_name,
                u.code capacity_unit_code,
                rwop.capacity,
                rwop.estimated_amount,
                rwop.product_info_merk,
                rwop.product_info_type
            from refill_work_order_product rwop
                inner join refill_work_order rwo on rwop.refill_work_order_id = rwo.id
                inner join refill_product_category rpc on rwop.refill_product_category_id = rpc.id
                inner join refill_product_medium rpm on rwop.refill_product_medium_id = rpm.id
                inner join unit u on rwop.capacity_unit_id = u.id
                '.$q_additional_from.'
            where rwo.status > 0
                and rpc.status> 0 and rpc.refill_product_category_status="active"
                and rpm.status> 0 and rpm.refill_product_medium_status="active"
                and u.status > 0
                and rwo.refill_work_order_status = "process"
                and rwop.refill_work_order_product_status = '.$db->escape($rwo_product_status).'
                and (
                    rwop.product_marking_code like '.$db->escape($lookup_str).' 
                )
                '.$q_additional_where.'
            order by rwop.id desc
            limit 10
        ';
        $rs_rwop = $db->query_array($q);
        if(count($rs_rwop)>0){
            for ($i = 0;$i<count($rs_rwop);$i++){
                $temp_p = $rs_rwop[$i];
                $temp_p['unit'] = array();
                $q = '
                    select u.id unit_id
                        ,u.code unit_code
                        ,u.name unit_name
                    from unit u
                    where u.status > 0 
                        and u.id = '.$db->escape($temp_p['unit_id']).'
                ';
                $rs_u = $db->query_array($q);
                if(count($rs_u) > 0 ){
                    for($j = 0;$j<count($rs_u);$j++){
                        $temp_p['unit'][] = $rs_u[$j];
                    }
                }
                $result[] = $temp_p;
            }
        }
        return $result;
        //</editor-fold>
    }
    
    public static function rwo_get_by_product_id($rwo_product,$config){
        //<editor-fold defaultstate="collapsed" desc="Get all RWO">
        get_instance()->load->helper('refill_work_order/refill_work_order_engine');
        $result = array();
        $db = new DB();
        $q_product_id='';
        $q_additional = isset($config['refill_work_order_status'])?
            ('and rwo.refill_work_order_status ='.$db->escape($config['refill_work_order_status'])):
            ('');
        foreach($rwo_product as $idx=>$row){
            $product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
            $q_product_id.= (($q_product_id === '')?'':',').$db->escape($product_id);
        }
        
        $q = '
            select distinct rwo.id
            from refill_work_order_product rwop
                inner join refill_work_order rwo on rwop.refill_work_order_id = rwo.id
            where rwop.id in ('.$q_product_id.')
                and rwo.status > 0
                '.$q_additional.'

        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            foreach($rs as $idx=>$row){
                $result[] = self::refill_work_order_get($row['id']);
            }
        }
        return $result;
        //</editor-fold>
    }
    
    public static function rwo_product_get_by_product_id($rwo_product,$config){
        //<editor-fold defaultstate="collapsed" desc="Get all RWO Product">
        get_instance()->load->helper('refill_work_order/refill_work_order_engine');
        $result = array();
        $db = new DB();
        $q_product_id='';
        $q_additional = isset($config['refill_work_order_status'])?
            ('and rwo.refill_work_order_status ='.$db->escape($config['refill_work_order_status'])):
            ('');
        foreach($rwo_product as $idx=>$row){
            $product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
            $q_product_id.= (($q_product_id === '')?'':',').$db->escape($product_id);
        }
        
        $q = '
            select rwop.*,rwop.id product_id
            from refill_work_order_product rwop
                inner join refill_work_order rwo on rwop.refill_work_order_id = rwo.id
            where rwop.id in ('.$q_product_id.')
                and rwo.status > 0
                '.$q_additional.'

        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs;
        return $result;
        //</editor-fold>
    }
    
    public static function customer_deposit_get($rwo_id,$opt=array()){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $customer_deposit_status = Tools::empty_to_null(isset($opt['customer_deposit_status'])?$opt['customer_deposit_status']:'');
        $q_additional = $customer_deposit_status !== ''?
            'and cd.customer_deposit_status='.$db->escape($customer_deposit_status):''
        ;
        
        $q = '
            select distinct cd.*
            from rwo_cd
            inner join customer_deposit cd 
                on rwo_cd.customer_deposit_id = cd.id
            where cd.status > 0
                and rwo_cd.refill_work_order_id='.$db->escape($rwo_id).'
                '.$q_additional.'
        ';
        
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs;
        }
        return $result;
        //</editor-fold>
    }
    
}
?>