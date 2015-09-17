<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mf_Work_Order_Data_Support{
    
    
    public static function mf_work_order_get($id){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = null;
        $q = '
            select *
            from mf_work_order
            where id = '.$db->escape($id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
    
    public static function mfwo_info_get($mf_work_order_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
           select mfwoi.*
           from mfwo_info mfwoi
           where mfwoi.mf_work_order_id = '.$db->escape($mf_work_order_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
    
    public static function mfwo_ordered_product_get($mf_work_order_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $rs = array();
        $mf_work_order_db = Mf_Work_Order_Data_Support::mf_work_order_get($mf_work_order_id);
        if(count($mf_work_order_db)>0){
            $mf_work_order_type = $mf_work_order_db['mf_work_order_type'];
            if($mf_work_order_type === 'normal'){
                //<editor-fold defaultstate="collapsed">
                $q = '
                    select mfwoop.*
                        ,bom.code bom_code
                        ,bom.name bom_name
                        ,p.code product_code
                        ,p.name product_name
                        ,u.code unit_code
                        ,u.name unit_name
                    from mfwo_ordered_product mfwoop
                        inner  join product p on mfwoop.product_id = p.id
                        inner join unit u on mfwoop.unit_id = u.id
                        inner join bom on mfwoop.bom_id = bom.id
                    where mfwoop.mf_work_order_id = '.$db->escape($mf_work_order_id).'
                ';
                $rs_op = $db->query_array($q);
                if(count($rs_op)>0){
                    $rs = $rs_op;
                }
                //</editor-fold>
            }
            else if(in_array($mf_work_order_type,array('good_stock_transform','bad_stock_transform'))){
                //<editor-fold defaultstate="collapsed">
                $q = '
                    select mfwoop.*
                        ,p.code product_code
                        ,p.name product_name
                        ,u.code unit_code
                        ,u.name unit_name
                    from mfwo_ordered_product mfwoop
                        inner  join product p on mfwoop.product_id = p.id
                        inner join unit u on mfwoop.unit_id = u.id
                    where mfwoop.mf_work_order_id = '.$db->escape($mf_work_order_id).'
                ';
                $rs_op = $db->query_array($q);
                if(count($rs_op)>0){
                    $rs = $rs_op;
                }
                //</editor-fold>
            }
        }
        
        if(count($rs)>0){
            $result = $rs;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function mf_work_order_exists($id=""){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from mf_work_order 
                where status > 0 && id = '.$db->escape($id).'
            ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function ordered_product_search($mf_work_order_type, $lookup_str=''){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        get_instance()->load->helper('product/product_data_support');
        get_instance()->load->helper('product/product_engine');
        $db = new DB();
        $q = '';
        $rs = array();
        switch($mf_work_order_type){
            case 'normal':
                //<editor-fold defaultstate="collapsed">
                $q = '
                    select distinct
                        p.id product_id
                        ,p.code product_code
                        ,p.name product_name
                    from bom 
                        inner join bom_result_product brp 
                            on bom.id = brp.bom_id and bom.status>0 and bom.bom_status = "active"
                        inner join product p on brp.product_id = p.id
                        inner join unit u on brp.unit_id = u.id
                    where p.product_status = "active"
                        and p.status>0 and u.status > 0
                        and (
                            p.code like '.$db->escape('%'.$lookup_str.'%').'
                            or p.name like '.$db->escape('%'.$lookup_str.'%').'
                        )
                ';
                $rs_prod = $db->query_array($q);
                if(count($rs_prod)>0){
                    $rs_prod = json_decode(json_encode($rs_prod));
                    foreach($rs_prod as $i=>$row){
                        $row->id = $row->product_id;
                        $row->text = SI::html_tag('strong',$row->product_code)
                            .' '.$row->product_name;
                        $row->product_img = Product_Engine::img_get($row->product_id);
                        $row->product_type = 'registered_product';
                        $row->unit = array();
                        $q = '
                            select u.id unit_id
                                ,u.code unit_code
                                ,u.name unit_name
                            from product_unit pu
                                inner join unit u on pu.unit_id = u.id
                            where u.status > 0 
                                and pu.product_id = '.$db->escape($row->product_id).'
                        ';
                        $rs_u = $db->query_array_obj($q);
                        if(count($rs_u) > 0 ){
                            foreach($rs_u as $i2=>$row2){
                                $row2->id = $row2->unit_id;
                                $row2->text = SI::html_tag('strong',$row2->unit_code)
                                    .' '.$row2->unit_name;
                                $row2->bom = array();
                                $q = '
                                    select distinct 
                                        bom.id bom_id
                                        ,bom.code bom_code
                                        ,bom.name bom_name
                                    from bom
                                        inner join bom_result_product brp on bom.id = brp.bom_id
                                    where brp.product_id = '.$db->escape($row->product_id).'
                                        and brp.unit_id = '.$db->escape($row2->unit_id).'
                                ';
                                $rs_bom = $db->query_array_obj($q);
                                if(count($rs_bom)>0){
                                    foreach($rs_bom as $i3=>$row3){
                                        $row3->id = $row3->bom_id;
                                        $row3->text = SI::html_tag('strong',$row3->bom_code)
                                            .' '.$row3->bom_name;
                                        $row2->bom[] = $row3;
                                    }
                                }
                                $row->unit[] = $row2;
                            }
                        }
                    }
                    $rs = json_decode(json_encode($rs_prod),true);
                }
                //</editor-fold>
                break;
            case 'bad_stock_transform':
            case 'good_stock_transform':
                $rs = Product_Data_Support::registered_product_search($lookup_str,array('product_status'=>'active'));
                if(count($rs)>0){
                    $rs = json_decode(json_encode($rs));
                    foreach($rs as $i=>$row){
                        $row->id = $row->product_id;
                        $row->text = SI::html_tag('strong',$row->product_code)
                            .' '.$row->product_name;
                        foreach($row->unit as $i2=>$row2){
                            $row2->id = $row2->unit_id;
                            $row2->text = SI::html_tag('strong',$row2->unit_code)
                            .' '.$row2->unit_name;
                        }
                        $row->product_img = Product_Engine::img_get($row->product_id);
                        $row->product_type = 'registered_product';
                    }
                    $rs = json_decode(json_encode($rs),true);
                }
                break;
        }
        $result = $rs;
        
        
        return $result;
        //</editor-fold>
    }
    
    public static function result_product_get($mf_work_order_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select brp.*,
                p.code product_code,
                p.name product_name,
                u.code unit_code,
                u.name unit_name
                
            from mf_work_order_result_product brp 
                inner join product p on brp.product_id = p.id
                inner join unit u on brp.unit_id = u.id
            where brp.mf_work_order_id = '.$db->escape($mf_work_order_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function mf_work_order_search($lookup_data, $opt = array()){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $mf_work_order_status = isset($opt['mf_work_order_status'])?Tools::_str($opt['mf_work_order_status']):'';
        $db = new DB();
        $q = '
            select mfwo.*,count(1) ordered_product_total
            from mf_work_order mfwo
                inner join mfwo_ordered_product mfwoop on mfwo.id = mfwoop.mf_work_order_id
            where mfwo.mf_work_order_status = '.$db->escape($mf_work_order_status).'
                and mfwo.status > 0
                and (
                    mfwo.code like '.$db->escape('%'.$lookup_data.'%').'
                )
            group by mfwo.id
            order by mfwo.id desc
            limit 10;
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs;
        
        return $result;
        //</editor-fold>
    }
    
    public static function available_ordered_product_get($mf_work_order_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select
                mfwoop.*
                ,p.code product_code
                ,p.name product_name
                ,u.code unit_code
                ,u.name unit_name
                ,bom.code bom_code
                ,bom.name bom_name
            from mfwo_ordered_product mfwoop
                inner join product p on mfwoop.product_id = p.id and mfwoop.product_type ="registered_product"
                inner join unit u on mfwoop.unit_id = u.id
                left outer join bom on mfwoop.bom_id = bom.id
            where mfwoop.mf_work_order_id = '.$db->escape($mf_work_order_id).'
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