<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Product_Price_List_Data_Support {

    public static function refill_product_category_dependency_get($refill_product_category_id){
        //<editor-fold defaultstate="collapsed"> 
        $db = new DB();
        $q = '
            select t2.id refill_product_medium_id, 
                t2.code refill_product_medium_code, 
                t2.name refill_product_medium_name,
                t3.id capacity_unit_id,
                t3.code capacity_unit_code,
                t3.name capacity_unit_name

            from rpc_rpm_cu t1
                inner join refill_product_medium t2 on t1.refill_product_medium_id = t2.id
                inner join unit t3 on t1.capacity_unit_id = t3.id
            where t1.refill_product_category_id = '.$db->escape($refill_product_category_id).'
                order by t2.id, t3.id
        ';
        $rs = $db->query_array($q);
        $result = array();
        if(count($rs)>0){                        
            foreach($rs as $idx=>$rs_item){
                if(count(Tools::array_extract($result,array(),
                    array('data'=>array(
                        array('id'=>$rs_item['refill_product_medium_id']),
                        )
                    )
                ))===0){
                    $result[] = array(
                        'id'=>$rs_item['refill_product_medium_id'],
                        'text'=>SI::html_tag('strong',$rs_item['refill_product_medium_code']).
                            ' '.$rs_item['refill_product_medium_name'],
                        'capacity_unit'=>array(
                            array(
                                'id'=>$rs_item['capacity_unit_id'],
                                'text'=>SI::html_tag('strong',$rs_item['capacity_unit_code']).
                                    ' '.$rs_item['capacity_unit_name'],
                            )
                        )
                    );
                }
                else{
                    $result[count($result)-1]['capacity_unit'][] = array(
                        'id'=>$rs_item['capacity_unit_id'],
                            'text'=>SI::html_tag('strong',$rs_item['capacity_unit_code']).
                        ' '.$rs_item['capacity_unit_name'],
                    );
                }
            }                        
        }
        return $result;
        //</editor-fold>
    }
        
    public static function price_function_get($price_raw){
        //<editor-fold defaultstate="collapsed">
        $result = 'C+0.00';
        $result = preg_replace('/[^0-9.+-\/\*^cC()]/','',$price_raw);
        $result_test = str_replace('c','3',strtolower($result));
        if(!@eval('return '.$result_test.';')) $result = 'C+0.00'; 
        return $result;
        //</editor-fold>
    }
    
    public static function price_list_function_is_valid($idata){
        //<editor-fold defaultstate="collapsed">
        $result = array('valid'=>true,'msg'=>array());
        $valid = true;$msg = array();
        
        $temp_data = array();
        foreach($idata as $idx=>$row){
            $min_cap = isset($row['min_cap'])?Tools::_str($row['min_cap']):Tools::_str('');
            $max_cap = isset($row['max_cap'])?Tools::_str($row['max_cap']):Tools::_str('');
            $price_raw = isset($row['price'])?Tools::_str(strtolower($row['price'])):'';
            
            if($min_cap === '' || $max_cap ==='' || $price_raw === ''){
                $valid = false;
                $msg[] = 'Min Cap / Max Cap / Price empty';
                break;
            }
            
            $price_raw = Refill_Product_Price_List_Data_Support::price_function_get($price_raw);
            $price_test = str_replace('c','3',strtolower($price_raw));
            $price_val = Tools::_float(@eval('return '.$price_test.';'));
            
            if(Tools::_float($max_cap) < Tools::_float($min_cap)){
                $valid  = false;
                $msg[] = 'Max. Cap is lower than Min. Cap';
                break;
            }
            
            if(
                Tools::_float($max_cap)< Tools::_float('0') ||
                Tools::_float($min_cap)< Tools::_float('0') ||
                Tools::_float($price_val) < Tools::_float('0')
            ){
                $valid  = false;
                $msg[] = 'Min. Cap / Max. Cap / Price is lower than 0';
                break;
            }
            
            $temp_data[] = array(
                'min_cap'=>$min_cap,
                'max_cap'=>$max_cap,
                'price'=>$price_raw,
            );
        }
        
        if($valid){
            for($i = 0;$i<count($temp_data);$i++){
                $min_cap_i = $temp_data[$i]['min_cap'];
                $max_cap_i = $temp_data[$i]['max_cap'];
                $price_i = $temp_data[$i]['price'];
                for($j = 0;$j<count($temp_data);$j++){
                    $min_cap_j = $temp_data[$j]['min_cap'];
                    $max_cap_j = $temp_data[$j]['max_cap'];
                    $price_j = $temp_data[$j]['price'];
                    if($i !== $j){
                        if(Tools::between(Tools::_float($min_cap_j), Tools::_float($max_cap_j), 
                            Tools::_float($min_cap_i) ) ||
                        Tools::between(Tools::_float($min_cap_j),Tools::_float($max_cap_j),
                            Tools::_float($max_cap_i))
                        ){
                            $valid = false;
                            $msg[] = 'Overlap Min / Max Cap';
                            break;
                        }
                    }
                }
                
                if(!$valid) break;
            }
        }
        
        $result['valid'] = $valid;
        $result['msg'] = $msg;
        
        return $result;
        //</editor-fold>
    }

    public static function product_price_get($customer_id,$product_category_id,$product_medium_id,$cap_unit_id,$capacity){
        //<editor-fold>
        $result = Tools::_float('0');
        $db = new DB();
        $q = '
            select distinct rpplpp.price
            from refill_product_price_list rppl 
                inner join rppl_product rpplp on rppl.id = rpplp.refill_product_price_list_id
                inner join rppl_product_price rpplpp on rpplp.id = rpplpp.rppl_product_id
                inner join customer_type_refill_product_price_list ctrppl on rppl.id = ctrppl.refill_product_price_list_id
                inner join customer_customer_type cct 
                    on cct.customer_type_id = ctrppl.customer_type_id 
                    and cct.customer_id = '.$db->escape($customer_id).'                
            where rppl.refill_product_price_list_status = "active"
                and rpplp.refill_product_category_id = '.$db->escape($product_category_id).'
                and rpplp.refill_product_medium_id = '.$db->escape($product_medium_id).'
                and rpplp.capacity_unit_id = '.$db->escape($cap_unit_id).'
                and '.$db->escape($capacity).' between rpplpp.min_cap and rpplpp.max_cap
                
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = eval('return '.str_replace('c',Tools::_float($capacity),strtolower($rs[0]['price'])).';');
        }
        return $result;
        //</editor-fold>
    }
}
?>