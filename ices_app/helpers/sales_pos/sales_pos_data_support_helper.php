<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Sales_Pos_Data_Support {
        
        public static function reference_detail_get($reference_type, $reference_id){
            //<editor-fold defaultstate="collapsed">
            $result = array();
            switch($reference_type){
                case 'sales_prospect':
                    get_instance()->load->helper('sales_prospect/sales_prospect_engine');
                    get_instance()->load->helper('sales_prospect/sales_prospect_data_support');
                    $sales_prospect_path = Sales_Prospect_Engine::path_get();
                    $sales_prospect = Sales_Prospect_Data_Support::sales_prospect_get($reference_id);
                    if(count($sales_prospect)>0){
                        $result = array(
                            array('id'=>'code','label'=>'Code: ','val'=>'<a href="'.$sales_prospect_path->index.'view/'.$sales_prospect['id'].'" target="_blank">'.$sales_prospect['code'].'</a>'),
                            array('id'=>'type','label'=>'Type: ','val'=>'Sales Prospect'),
                        );
                    }
                    break;
            }
            return $result;
            //</editor-fold>
        }
        
        public static function multiplication_qty_get($product_id, $unit_id){
            //<editor-fold defaultstate="collapsed">
            $result = 0;
            $db = new DB();
            $q = '
                select qty 
                from product_sales_multiplication_qty 
                where product_id = '.$db->escape($product_id).'
                    and unit_id = '.$db->escape($unit_id).'
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0){
                $result = $rs[0]['qty'];
            }
            return $result;
            //</editor-fold>
        }
        
        public static function product_price_get($price_list_id, $product_id, $unit_id, $qty){
            //<editor-fold defaultstate="collapsed">
            $result = '0';
            $db = new DB();
            $q = '
                select min_qty, amount
                from product_price_list_product
                where product_id = '.$db->escape($product_id).'
                    and unit_id = '.$db->escape($unit_id).'
                    and min_qty<= '.$db->escape($qty).'
                    and product_price_list_id = '.$db->escape($price_list_id).'
                order by min_qty desc
                limit 0,1
                ';
            $rs = $db->query_array($q);
            if(count($rs)>0){
                
                $result = $rs[0]['amount'];
            } 
            return $result;
            //</editor-fold>
        }
        
        public static function payment_type_get($customer_id){
            //<editor-fold defaultstate="collapsed">
            get_instance()->load->helper('sales_receipt/sales_receipt_data_support');
            $result = array();
            $temp_sales_receipt = Sales_Receipt_Data_Support::customer_payment_type_get($customer_id);
            
            if(count($temp_sales_receipt)>0){
                for($i = 0;$i<count($temp_sales_receipt);$i++){
                    $result[] = array(
                        'id'=>$temp_sales_receipt[$i]['id'],
                        'text'=>$temp_sales_receipt[$i]['code'],
                    );
                }
            }
            
            return $result;
            //</editor-fold>
        }
        
        public static function expedition_weight_message_get($data){
            //<editor-fold defaultstate="collapsed">
            $weight = '0.00';
            $unit_id_result='3';
            $unit_name_result = 'KG';
            $status = 1;
            $result = array();
            $expedition_id = isset($data['expedition_id'])?$data['expedition_id']:'';
            $product_id = isset($data['product_id'])?$data['product_id']:'';
            $unit_id = isset($data['unit_id'])?$data['unit_id']:'';
            $qty = isset($data['qty'])?$data['qty']:'0';

            $db = new DB();
            if(!SI::record_exists('expedition',array('status'=>'1','id'=>$expedition_id))) $status = 0;
            
            if($status === 1){

                $q_products= ' select '
                        .$db->escape($product_id).' product_id '
                        .', '.$db->escape($unit_id).' unit_id '
                        .', '.$db->escape($qty).' qty '
                ;
                $q = '
                    select distinct 
                        t1.product_id
                        ,t3.code product_code
                        ,t3.name product_name
                        ,tp.qty qty_original
                        ,t4.id unit_id_original
                        ,t4.code unit_code_original
                        ,t4.name unit_name_original
                        ,t1.qty_1 qty_factor
                        , t1.qty_2 qty_calculated
                        ,t5.id unit_id_calculated
                        ,t5.code unit_code_calculated
                        ,t5.code unit_name_calculated
                    from product_unit_conversion t1
                        inner join expedition t2 on t1.expedition_id = t2.id
                            and t1.unit_id_2 = t2.measurement_unit_id
                            and t1.type="sales_expedition_weight"
                        inner join ('.$q_products.') tp on tp.product_id = t1.product_id
                            and tp.unit_id = t1.unit_id_1
                        inner join product t3 on t1.product_id = t3.id
                        inner join unit t4 on t1.unit_id_1 = t4.id
                        inner join unit t5 on t1.unit_id_2 = t5.id
                    where t2.id = '.$db->escape($expedition_id).' 
                        and t1.status>0 and t1.product_unit_conversion_status = "active"
                        order by t1.qty_1 desc
                ';
                $rs = $db->query_array($q);
                
                if(count($rs)>0){
                    $weight = 0;
                    $unit_id_result = $rs[0]['unit_id_calculated'];
                    $unit_name_result = $rs[0]['unit_code_calculated'];    
                    $qty_remaining = $qty;
                    for($i = 0;$i<count($rs);$i++){
                        if($qty_remaining>0){
                            $qty_factor = $rs[$i]['qty_factor'];
                            $qty_calculated = $rs[$i]['qty_calculated'];
                            $weight += ((int)($qty_remaining / $qty_factor) * $qty_calculated);
                            $qty_remaining -= ((int)($qty_remaining / $qty_factor)) * $qty_factor; 
                            
                        }                        
                    }
                    $weight = Tools::thousand_separator($weight,5);
                }
                
            }
            
            $result['weight'] = $weight;
            $result['unit_id'] = $unit_id_result;
            $result['unit_name'] = $unit_name_result;
            return $result;
            
            //</editor-fold>
        }
        
        public static function extra_charge_message_get($data){
            //<editor-fold defaultstate="collapsed">
            $amount = '0.00';
            $msg = '';
            $result = array();
            $is_delivery = isset($data['delivery'])?(Tools::_bool($data['delivery'])?true:false):false;
            
            $condition = array();
            
            if($is_delivery === true){
                
                $moq_message = self::moq_message_generate($data);                
                if($moq_message['status'] === 1 ){
                    $msg .='<div class="form-group"><label>Min. Order Qty</label>';
                    $msg .= '<div class="box" style="border-top:none;"><ul class="todo-list">';            
                    $msg .= $moq_message['msg'];            
                    $msg .= '</ul></div></div>';
                }
                
                $mop_message = self::mop_message_generate($data);
                if($mop_message['status'] === 1){
                    $msg .='<div class="form-group"><label>Min. Order Price</label>';
                    $msg .= '<div class="box" style="border-top:none;"><ul class="todo-list">';            
                    $msg .=$mop_message['msg'];
                    $msg .= '</ul></div></div>';
                }
                
                $condition = array(
                    'mop_mismatch'=>$mop_message['mop_mismatch']
                    ,'mop_status'=>$mop_message['status']
                    ,'moq_mismatch'=>$moq_message['moq_mismatch']
                    ,'moq_status'=>$moq_message['status']
                );
                
            }
            
            $extra_charge = self::extra_charge_get($condition,$data);
            $msg.=$extra_charge['msg'];
            $amount = $extra_charge['amount']; 
            
            $result['amount'] = Tools::thousand_separator($amount);
            $result['msg'] = $msg;
            return $result;
            
            //</editor-fold>
        }
        
        public static function extra_charge_get($condition,$data){
            //<editor-fold defaultstate="collapsed">
            $result = array('msg'=>'','amount'=>0);
            $msg = '';
            $content = '';
            $cont = true;
            
            $price_list_id = isset($data['price_list_id'])?$data['price_list_id']:'';
            $products = isset($data['products'])?$data['products']:null;
            $is_delivery = isset($data['delivery'])?(Tools::_bool($data['delivery'])?true:false):false;

            if($price_list_id === '') $cont = false;
            if(count($products) === 0) $cont = false;

            $extra_charge_units = array();
            $total_extra_charge_amount = 0;
            if($cont){
                $db = new DB();                
                $q = '
                    select distinct t1.unit_id, t2.name unit_name
                    from product_price_list_delivery_extra_charge t1
                        inner join unit t2 on t1.unit_id = t2.id and t2.status>0
                    where t1.status > 0 
                        and t1.product_price_list_id = '.$price_list_id.'
                ';
                $rs = $db->query_array($q);
                if(count($rs) === 0) $cont = false;  
                else $extra_charge_units = $rs; // get all units defined in extra charge
            }
            if($cont){
                get_instance()->load->helper('product_price_list/product_price_list_engine');
                $ppl = Product_Price_List_Engine::get($price_list_id);
                $content = '';
                if($is_delivery){
                    $calc_extra_charge_type = null;
                    if( 
                        $condition['mop_status'] === 1 && $condition['mop_mismatch'] === true
                        && $condition['moq_status'] === 1 && $condition['moq_mismatch'] === true
                    ){
                        $calc_extra_charge_type = 'weight base';
                    }
                    else if (
                        $condition['moq_status'] !== 1 
                        && $condition['mop_status'] === 1 && $condition['mop_mismatch'] === true  
                    ){
                        $calc_extra_charge_type = 'weight base';
                    }
                    else if (
                        $condition['mop_status'] !== 1 
                        && $condition['moq_status'] === 1 && $condition['moq_mismatch'] === true  
                    ){
                        $calc_extra_charge_type = 'weight base';
                    }
                    else if ($condition['moq_status']!== 1 && $condition['mop_status']!== 1){
                        $calc_extra_charge_type = 'basic';
                    }
                    
                    
                    if($calc_extra_charge_type ==='weight base'){
                        //<editor-fold defaultstate="collapsed">
                        for($i = 0;$i<count($extra_charge_units);$i++){
                            $extra_charge_unit_id = $extra_charge_units[$i]['unit_id'];
                            $extra_charge_unit_name = $extra_charge_units[$i]['unit_name'];
                            $q_products = 'select -1 product_id, -1 unit_id, 0 qty';
                            $total_products_weight = 0;
                            $extra_charge_amount = 0;
                            $sub_content = '';
                            $sub_content_header = '';
                            $sub_content_text = '';
                            $sub_content_requirement = '';
                            foreach($products as $idx=>$product){                    
                                $product_id = isset($product['product_id'])?$product['product_id']:'';
                                $unit_id = isset($product['unit_id'])?$product['unit_id']:'';
                                $qty = isset($product['qty'])?$product['qty']:'0';
                                $q_products.= ' union select '
                                        .$db->escape($product_id)
                                        .', '.$db->escape($unit_id)
                                        .', '.$db->escape($qty)
                                ;
                            }
                            $q = '
                                select distinct t1.id, ((tp.qty / t1.qty_1) * t1.qty_2) qty_calculated, t2.name product_name
                                    ,t3.code unit_code_original
                                    ,t4.code unit_code_calculated
                                    ,tp.qty qty_original
                                    ,t1.qty_2 qty_factor
                                from product_unit_conversion t1
                                    inner join ('.$q_products.') tp
                                        on tp.product_id = t1.product_id
                                            and tp.unit_id = t1.unit_id_1
                                            and t1.unit_id_2 = '.$db->escape($extra_charge_unit_id).'   
                                    inner join product t2 on t1.product_id = t2.id
                                    inner join unit t3 on t1.unit_id_1 = t3.id
                                    inner join unit t4 on t1.unit_id_2 = t4.id
                                where t1.type = "sales_real_weight"
                                    and t1.status>0 and t1.product_unit_conversion_status = "active"
                            ';
                            $rs = $db->query_array($q);
                            if(count($rs)>0){
                                for($j = 0;$j<count($rs);$j++){
                                    $qty_calculated = round(floatval($rs[$j]['qty_calculated']),5);
                                    $total_products_weight += $qty_calculated;

                                    $sub_content_text.=''
                                        .'<div>'
                                        .'<span class="text">'
                                            .Tools::thousand_separator($rs[$j]['qty_original']).' '.$rs[$j]['unit_code_original']
                                            .' @'.Tools::thousand_separator($rs[$j]['qty_factor']).' '.$rs[$j]['unit_code_calculated']
                                            .' '.$rs[$j]['product_name']                                    
                                            .'<strong>(Calculation: '.Tools::thousand_separator($qty_calculated)
                                            .' '.$rs[$j]['unit_code_calculated'].')</strong>'
                                        .'</span>'
                                        .'</div>'
                                    .'';
                                }
                            }
                            $extra_charge_min_qty = 0;
                            $q = '
                                select amount, min_qty
                                from product_price_list_delivery_extra_charge t1
                                where t1.status > 0 
                                    and t1.product_price_list_id = '.$price_list_id.'
                                    and t1.min_qty<= '.$db->escape($total_products_weight).'
                                    and t1.unit_id = '.$db->escape($extra_charge_unit_id).'
                                order by t1.min_qty desc
                                limit 1
                            ';
                            $rs = $db->query_array($q);
                            if(count($rs) >0){
                                $extra_charge_amount = $rs[0]['amount'];
                                $extra_charge_min_qty = $rs[0]['min_qty'];
                            }
                            $sub_content_header = ''
                                .'<div style="">'
                                    .'<span class="handle">'
                                        .'<i class="fa fa-ellipsis-v"></i>'
                                        .'<i class="fa fa-ellipsis-v"></i>'
                                    .'</span>'
                                    .'<span class="text"><strong>'.$extra_charge_unit_name.'</strong></span> - '
                                    .'<span class="text"><strong>'.Tools::currency_get().' '.Tools::thousand_separator($extra_charge_amount).'</strong></span>'
                                .'</div>'
                                .'<hr style="margin-top:5px;margin-bottom:5px;"/>'
                            ;
                            $sub_content_requirement = ''

                                .'<div>'
                                    .'<span class="text"><strong>Requirement: Minimum Qty '.Tools::thousand_separator($extra_charge_min_qty)
                                    .' '.$extra_charge_unit_name.'</strong></span> '
                                .'</div>'
                                .'<div>'
                                    .'<span class="text"><strong>Total Qty: '.Tools::thousand_separator($total_products_weight)
                                    .' '.$extra_charge_unit_name.'</strong></span> '
                                .'</div>'

                            .'';
                            $sub_content = '<div class="box box-primary" style=""><ul class="todo-list">'
                                .'<li>'.$sub_content_header.$sub_content_requirement.$sub_content_text.'</li>'
                                .'</ul></div>'
                                ;
                            $content.=$sub_content;
                            $total_extra_charge_amount += floatval($extra_charge_amount);
                        }                    


                        //</editor-fold>
                    }
                    else if($calc_extra_charge_type ==='basic'){
                        //<editor-fold defaultstate="collapsed">
                        $total_extra_charge_amount+=floatval($ppl->delivery_extra_charge);
                        $subcontent = ''
                            .'<div class="box box-primary" style=""><ul class="todo-list"><li>'
                            .'<div style="">'
                                .'<span class="handle">'
                                    .'<i class="fa fa-ellipsis-v"></i>'
                                    .'<i class="fa fa-ellipsis-v"></i>'
                                .'</span>'
                                .'<span class="text"><strong>Basic Delivery Extra Charge</strong></span> - '
                                .'<span class="text"><strong>'.Tools::currency_get().' '.Tools::thousand_separator($ppl->delivery_extra_charge).'</strong></span>'
                            .'</div>'
                            .'</li></ul></div>'
                            ;
                        $content .= $subcontent;
                        //</editor-fold>
                        
                    }

                    if(str_replace(' ','',$content) !== ''){
                        $msg .='<div class="form-group"><label>Extra Charge Calculation</label>';
                        $msg .= '';            
                        $msg .=$content;
                        $msg .= '</div>';
                    }
                }
            }
            $result['msg'] = $msg;
            $result['amount'] = $total_extra_charge_amount;
            return $result;
            //</editor-fold>
        }
        
        public static function moq_message_generate($data){
            //<editor-fold defaultstate="collapsed">
            $result = array('msg'=>'','moq_mismatch'=>false,'status'=>0);
            $msg = '';
            $moq_mismatch = true;
            $product_ever_been_tested = false;
            $status = 0;
            $cont = true;
            $db = new DB();
            $price_list_id = isset($data['price_list_id'])?$data['price_list_id']:'';
            $products = isset($data['products'])?$data['products']:null;
            if($price_list_id === '') $cont = false;
            if(count($products) === 0) $cont = false;
            
            
            if($cont){
                $q = '
                    select t1.*
                    from product_price_list_delivery_moq t1
                    where t1.status>0 and t1.product_price_list_id = '.$db->escape($price_list_id).'
                    order by t1.calculation_type, t1.code
                ';
                $moqs = $db->query_array($q);
                if(count($moqs) === 0){
                    $status = 0;
                    $moq_mismatch = false;
                }
                foreach($moqs as $idx => $moq){
                    $li_active = true;
                    $moq_id = $moq['id'];
                    $li = '';
                    $is_mismatch = true;
                    $content = '';
                    switch($moq['calculation_type']){
                        case 'mixed':
                            //<editor-fold defaultstate="collapsed">
                            $requirement_text='';
                            $mismatch_text = '';
                            $product_text='';
                            $q = '
                                select t1.*, t2.code unit_code
                                from product_price_list_delivery_moq_mixed t1
                                    inner join unit t2 on t1.unit_id = t2.id
                                where t1.product_price_list_delivery_moq_id = '.$db->escape($moq_id).'
                            ';
                            $moq_mixed = $db->query_array($q)[0];
                            $requirement_text.=''
                                .'<div>'
                                    .'<span class="text">'
                                    .'<strong>Requirement: '.  Tools::thousand_separator($moq_mixed['qty'],2)
                                    .' '.$moq_mixed['unit_code']
                                    .'</strong>'
                                    .'</span> '
                                .'</div>'
                            .'';
                            $moq_mixed_products = self::moq_mixed_product_get($moq_id, $products);
                            if( count($moq_mixed_products) == 0){
                                $li_active = false;
                            }
                            $mismatch = 0;
                            if($li_active){
                                $product_ever_been_tested = true;
                                $total_qty = 0;
                                foreach($moq_mixed_products as $mmp_idx=>$mmp_product){
                                    $product_text.=''
                                        .'<div>'
                                            .'<span class="text">'
                                                .Tools::thousand_separator($mmp_product['qty_original'],2,true)
                                                .' '.$mmp_product['unit_code_original']
                                                .' '.$mmp_product['product_name']
                                                .' (Calculation: '.Tools::thousand_separator($mmp_product['qty_calculated'],2,true)
                                                .' '.$mmp_product['unit_code_calculated'].')</span>'
                                        .'</div>'
                                    .'';
                                    $total_qty+=floatVal($mmp_product['qty_calculated']);                                    
                                }
                                if($total_qty >= $moq_mixed['qty']){
                                    $is_mismatch = false;                                            
                                }
                                else{
                                    $mismatch = $moq_mixed['qty'] - $total_qty;
                                }
                                
                                $mismatch_text.=''
                                    .'<div>'
                                        .'<span class="text"><strong>Mismatch: '.  Tools::thousand_separator($mismatch,2,true)
                                        .' '.$moq_mixed['unit_code'].'</strong></span>' 
                                    .'</div>'
                                .'';                            
                                $content = $requirement_text.$mismatch_text.$product_text;

                            }
                            //</editor-fold>
                            break;
                        case 'separated':
                            //<editor-fold defaultstate="collapsed">
                            $moq_separated_products = self::moq_separated_product_get($moq_id, $products);
                            if(count($moq_separated_products) === 0){
                                $li_active = false;
                            }
                            if($li_active){
                                $product_ever_been_tested = true;
                                $all_match = true;
                                foreach($moq_separated_products as $msp_idx=>$msp_product){
                                    $qty = floatVal($msp_product['qty_calculated']);
                                    $qty_req = floatVal($msp_product['qty_req']);
                                    $mismatch = 0;
                                    if($qty<$qty_req){
                                        $all_match = false;
                                        $mismatch = $qty_req - $qty;
                                    }

                                    $content.=''
                                        .'<div>'
                                            .'<span class="text">'
                                                .Tools::thousand_separator($qty,2,true)
                                                .' '.$msp_product['unit_code_original']
                                                .' '.$msp_product['product_name']
                                                .' (<strong>'
                                                . 'Requirement: '.Tools::thousand_separator($qty_req,2,true)
                                                .' '.$msp_product['unit_code_calculated']
                                                . ', Calculation: '.Tools::thousand_separator($qty,2,true)
                                                .' '.$msp_product['unit_code_calculated']
                                                . ', Mismatch: '.Tools::thousand_separator($mismatch,2,true)
                                                .' '.$msp_product['unit_code_calculated']
                                                .' '.'</strong>)</span>'
                                        .'</div>'
                                    .'';
                                }
                                $is_mismatch = !$all_match;
                            }
                            //</editor-fold>
                            break;
                            
                    }
                    $li = ''
                        .'<li class="'.($is_mismatch?'danger':'primary').'">'
                        .'<div style="">'
                            .'<span class="handle">'
                                .'<i class="fa fa-ellipsis-v"></i>'
                                .'<i class="fa fa-ellipsis-v"></i>'
                            .'</span>'
                            .'<span class="text"><strong>'.$moq['code'].'</strong></span> - '
                            .'<span class="text">'.ucfirst($moq['calculation_type']).'</span>'
                        .'</div>'
                        .'<hr style="margin-top:5px;margin-bottom:5px;" />'
                        .''
                        .$content
                        .'</li>'
                    ;
                        
                    
                    if($li_active){
                        $msg.=$li;
                        $status = 1;
                        if(!$is_mismatch){
                            $moq_mismatch = false;
                        }
                        
                    }
                }
                
                if(!$product_ever_been_tested){
                    $status = 0;
                    $moq_mismatch = false;
                }
            }
            $result['msg'] = $msg;
            $result['moq_mismatch'] = $moq_mismatch;
            $result['status'] = $status;
            return $result;
            //</editor-fold>
        }
        
        public static function moq_mixed_product_get($moq_id, $products){
            //<editor-fold defaultstate="collapsed">
            $db = new DB();
            $result = array();
            $q_products=' (select -1 product_id, -1 unit_id, 0 qty';
            foreach($products as $idx=>$product){
                $product_id = isset($product['product_id'])?$product['product_id']:'';
                $unit_id = isset($product['unit_id'])?$product['unit_id']:'';
                $qty = isset($product['qty'])?$product['qty']:'0';
                $q_products.= ' union select '
                        .$db->escape($product_id)
                        .', '.$db->escape($unit_id)
                        .', '.$db->escape($qty)
                ;
            }
            $q_products.=') tp ';
            $q = '
                select distinct t3.id product_id
                    , t3.name product_name
                    , tp.unit_id
                    , t4.code unit_code_original
                    , tp.qty qty_original
                    , t4.code unit_code_calculated
                    , tp.qty qty_calculated
                from product_price_list_delivery_moq_mixed t1
                    inner join product_price_list_delivery_moq_mixed_product t2 
                        on t1.id = t2.product_price_list_delivery_moq_mixed_id
                    inner join '.$q_products.' 
                        on tp.product_id = t2.product_id and tp.unit_id = t1.unit_id
                    inner join product t3 on tp.product_id = t3.id
                    inner join unit t4 on tp.unit_id = t4.id
                where t1.product_price_list_delivery_moq_id = '.$db->escape($moq_id).'
                
            ';
            $rs = $db->query_array($q);
            if(count($rs) > 0){
                $result = $rs;
            }
            
            
            $q_products='(';
            foreach($products as $idx=>$product){
                $product_id = isset($product['product_id'])?$db->escape($product['product_id']):'';
                $unit_id = isset($product['unit_id'])?$db->escape($product['unit_id']):'';
                $qty = isset($product['qty'])?$db->escape($product['qty']):'0';
                $add_query = '';
                if($q_products !=='(') $add_query = ' union ';
                $q_products.= $add_query.'
                    
                    select product_id, unit_id_2, ('.$qty.' / qty_1) * qty_2 qty_2, '.$unit_id.' unit_id, '.$qty.' qty
                    from product_unit_conversion puc
                    where product_id = '.$product_id.' 
                        and unit_id_1 = '.$unit_id.'
                        and type = "sales_moq"
                        and t1.status>0 and t1.product_unit_conversion_status = "active"
                    '
                ;
            }

            $q_products.=') tp ';
            $q = '
                select distinct t3.id product_id
                    , t3.name product_name
                    , tp.unit_id
                    , t5.code unit_code_original
                    , tp.qty qty_original
                    , t4.code unit_code_calculated
                    , tp.qty_2 qty_calculated
                from product_price_list_delivery_moq_mixed t1
                    inner join product_price_list_delivery_moq_mixed_product t2 
                        on t1.id = t2.product_price_list_delivery_moq_mixed_id
                    inner join '.$q_products.' 
                        on tp.product_id = t2.product_id and tp.unit_id_2 = t1.unit_id
                    inner join product t3 on tp.product_id = t3.id
                    inner join unit t4 on tp.unit_id_2 = t4.id
                    inner join unit t5 on tp.unit_id = t5.id
                where t1.product_price_list_delivery_moq_id = '.$db->escape($moq_id).'
                
            ';
            $rs = $db->query_array($q);
            if(count($rs) > 0){
                $result = array_merge($result,$rs);
            }
            
            return $result;
            //</editor-fold>
        }
        
        public static function moq_separated_product_get($moq_id, $products) {
            //<editor-fold defaultstate="collapsed">
            $result = array();
            $db = new DB();
            
            $q_products=' (select -1 product_id, -1 unit_id, 0 qty';
            foreach($products as $idx=>$product){
                $product_id = isset($product['product_id'])?$product['product_id']:'';
                $unit_id = isset($product['unit_id'])?$product['unit_id']:'';
                $qty = isset($product['qty'])?$product['qty']:'0';
                $q_products.= ' union select '
                        .$db->escape($product_id)
                        .', '.$db->escape($unit_id)
                        .', '.$db->escape($qty)
                ;
            }
            $q_products.=') tp ';
            
            $q = '
                select distinct t2.name product_name
                    ,t1.qty qty_req
                    ,t3.code unit_code_original
                    ,tp.qty qty_original
                    ,t3.code unit_code_calculated
                    ,tp.qty qty_calculated                    
                from product_price_list_delivery_moq_separated t1
                    inner join product t2 on t1.product_id = t2.id
                    inner join unit t3 on t1.unit_id = t3.id
                    inner join '.$q_products.' 
                        on tp.product_id = t1.product_id 
                            and tp.unit_id = t1.unit_id
                            and tp.unit_id = t1.unit_id_measurement
                where t1.product_price_list_delivery_moq_id = '.$db->escape($moq_id).'
                    
                
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0){
                $result = $rs;
            }
            
            $q_products='(';
            foreach($products as $idx=>$product){
                $product_id = isset($product['product_id'])?$db->escape($product['product_id']):'';
                $unit_id = isset($product['unit_id'])?$db->escape($product['unit_id']):'';
                $qty = isset($product['qty'])?$db->escape($product['qty']):'0';
                $add_query = '';
                if($q_products !=='(') $add_query = ' union ';
                $q_products.= $add_query.'
                    
                    select product_id, unit_id_2, ('.$qty.' / qty_1) * qty_2 qty_2, '.$unit_id.' unit_id, '.$qty.' qty
                    from product_unit_conversion puc
                    where product_id = '.$product_id.' 
                        and unit_id_1 = '.$unit_id.'
                        and type = "sales_moq"
                        and t1.status>0 and t1.product_unit_conversion_status = "active"
                    '
                ;
            }

            $q_products.=') tp ';
            $q = '
                select distinct t2.name product_name
                    ,t1.qty qty_req
                    ,t3.code unit_code_original
                    ,tp.qty qty_original
                    ,t5.code unit_code_calculated
                    ,tp.qty_2 qty_calculated                    
                from product_price_list_delivery_moq_separated t1
                    inner join product t2 on t1.product_id = t2.id
                    inner join unit t3 on t1.unit_id = t3.id
                    inner join '.$q_products.' 
                        on tp.product_id = t1.product_id 
                            and tp.unit_id = t1.unit_id
                            and tp.unit_id_2 = t1.unit_id_measurement
                    inner join unit t5 on tp.unit_id_2 = t5.id
                where t1.product_price_list_delivery_moq_id = '.$db->escape($moq_id).'
            ';
            $rs = $db->query_array($q);
            if(count($rs) > 0){
                $result = array_merge($result,$rs);
            }
            
            return $result;
            //</editor-fold>
        }
        
        public static function mop_message_generate($data){
            //<editor-fold defaultstate="collapsed">
            $result = array('msg'=>'','mop_mismatch'=>true,'status'=>0);
            $msg = '';
            $mop_mismatch = true;
            $product_ever_been_tested = false;
            $status = 0;
            $cont = true;
            $db = new DB();
            $price_list_id = isset($data['price_list_id'])?$data['price_list_id']:'';
            $products = isset($data['products'])?$data['products']:null;
            if($price_list_id === '') $cont = false;
            if(count($products) === 0) $cont = false;
            if($cont){
                $q = '
                    select t1.*
                    from product_price_list_delivery_mop t1
                    where t1.status>0 and t1.product_price_list_id = '.$db->escape($price_list_id).'
                    order by t1.calculation_type, t1.code
                ';
                $mops = $db->query_array($q);
                if(count($mops) === 0){
                    $status = 0;
                    $moq_mismatch = false;
                }
                foreach($mops as $idx => $mop){
                    $li_active = true;
                    $mop_id = $mop['id'];
                    
                    $li = '';                    
                    

                    $is_mismatch = true;
                    $content = '';
                    switch($mop['calculation_type']){
                        case 'mixed':
                            //<editor-fold defaultstate="collapsed">
                            $requirement_text='';
                            $mismatch_text = '';
                            $product_text='';
                            $q = '
                                select t1.*
                                from product_price_list_delivery_mop_mixed t1
                                where t1.product_price_list_delivery_mop_id = '.$db->escape($mop_id).'
                            ';
                            $mop_mixed = $db->query_array($q)[0];
                            $requirement_text.=''
                                .'<div>'
                                    .'<span class="text"><strong>Requirement ('.Tools::currency_get().'): '
                                    .  Tools::thousand_separator($mop_mixed['amount'],2,true)
                                    .'</strong></span>'
                                .'</div>'
                            .'';
                            $mop_mixed_products = self::mop_mixed_product_get($mop_id, $products);
                            if( count($mop_mixed_products) == 0){
                                $li_active = false;
                            }
                            $mismatch = 0;
                            if($li_active){
                                $product_ever_been_tested = true;
                                $total_amount = 0;
                                foreach($mop_mixed_products as $mmp_idx=>$mmp_product){
                                    $product_text.=''
                                        .'<div>'
                                            .'<span class="text">'
                                                .Tools::thousand_separator($mmp_product['qty'],2)
                                                .' '.$mmp_product['unit_code']
                                                .' @ '.Tools::currency_get().''
                                                .Tools::thousand_separator($mmp_product['amount'],2)
                                                .' '.$mmp_product['product_name']
                                                .' (Calculation: '.Tools::currency_get().Tools::thousand_separator($mmp_product['total_amount'],2)
                                                .' '.')</span>'
                                        .'</div>'
                                    .'';
                                    $total_amount+=floatVal($mmp_product['total_amount']);                                    
                                }
                                if($total_amount>0){
                                    $mop_mixed_amount = floatVal(preg_replace('/[^0-9.]/','',$mop_mixed['amount']));
                                    
                                    
                                    if($total_amount >= $mop_mixed_amount){
                                        $is_mismatch = false;                                            
                                    }
                                    else{
                                        $mismatch = $mop_mixed_amount - $total_amount;
                                    }

                                }
                                $mismatch_text.=''
                                    .'<div>'
                                        .'<span class="text"><strong>Mismatch ('.Tools::currency_get().'): '.  Tools::thousand_separator($mismatch,2,true)
                                        .'</strong></span> '
                                    .'</div>'
                                .'';                            
                                $content = $requirement_text.$mismatch_text.$product_text;
                            }
                            //</editor-fold>
                            break;
                        case 'separated':
                            //<editor-fold defaultstate="collapsed">
                            $mop_separated_products = self::mop_separated_product_get($mop_id, $products);
                            if(count($mop_separated_products) === 0){
                                $li_active = false;
                            }
                            if($li_active){
                                $product_ever_been_tested = true;
                                $all_match = true;
                                foreach($mop_separated_products as $msp_idx=>$msp_product){
                                    $amount = floatVal($msp_product['amount']);
                                    $amount_req = floatVal($msp_product['amount_req']);
                                    $mismatch = 0;
                                    if($amount === 0){
                                        $all_match = false;
                                    }

                                    if($amount<$amount_req){
                                        $all_match = false;
                                        $mismatch = $amount_req - $amount;
                                    }

                                    $content.=''
                                        .'<div>'
                                            .'<span class="text">'
                                                .Tools::thousand_separator($msp_product['qty'],2)
                                                .' '.$msp_product['unit_code']
                                                .' @ '.Tools::currency_get().Tools::thousand_separator($msp_product['amount'],2)
                                                .' '.$msp_product['product_name']
                                                .' (<strong>'
                                                . 'Calculation: '.Tools::currency_get().Tools::thousand_separator($msp_product['total_amount'],2)
                                                . ', Requirement: '.Tools::currency_get().Tools::thousand_separator($amount_req,2)
                                                . ', Mismatch: '.Tools::currency_get().Tools::thousand_separator($mismatch,2)
                                                .' '.'</strong>)</span>'
                                        .'</div>'
                                    .'';
                                }
                                $is_mismatch = !$all_match;
                            }
                            //</editor-fold>
                            break;
                    }
                    $li = '<li class="'.($is_mismatch?'danger':'primary').'">'
                        .'<div style="">'
                            .'<span class="handle">'
                                .'<i class="fa fa-ellipsis-v"></i>'
                                .'<i class="fa fa-ellipsis-v"></i>'
                            .'</span>'
                            .'<span class="text"><strong>'.$mop['code'].'</strong></span> - '
                            .'<span class="text">'.ucfirst($mop['calculation_type']).'</span>' 
                        .'</div>'
                        .'<hr style="margin-top:5px;margin-bottom:5px;" />'
                        .$content
                        .'</li>';
                    
                    
                    if($li_active){
                        $msg.=$li;
                        $status = 1;
                        if(!$is_mismatch){
                            $mop_mismatch = false;
                        }
                    }
                }
                if(!$product_ever_been_tested){
                    $status = 0;
                    $mop_mismatch = false;
                }
            }
            $result['msg'] = $msg;
            $result['mop_mismatch'] = $mop_mismatch;
            $result['status'] = $status;
            return $result;
            //</editor-fold>
        }
        
        public static function mop_mixed_product_get($mop_id, $products){
            //<editor-fold defaultstate="collapsed">
            $db = new DB();
            $result = array();
            $q_products=' (select -1 product_id, -1 unit_id, 0 qty,0 amount';
            foreach($products as $idx=>$product){
                $product_id = isset($product['product_id'])?$product['product_id']:'';
                $unit_id = isset($product['unit_id'])?$product['unit_id']:'';
                $qty = isset($product['qty'])?$product['qty']:'0';
                $amount = isset($product['amount'])?$product['amount']:'0';
                $q_products.= ' union select '
                        .$db->escape($product_id)
                        .', '.$db->escape($unit_id)
                        .', '.$db->escape($qty)
                        .', '.$db->escape($amount)
                ;
            }
            $q_products.=') tp ';
            $q = '
                select distinct t3.id product_id
                    , t3.name product_name
                    , tp.unit_id
                    , t4.code unit_code
                    , tp.qty qty
                    , tp.amount amount
                    , tp.qty * tp.amount total_amount

                from product_price_list_delivery_mop_mixed t1
                    inner join product_price_list_delivery_mop_mixed_product t2 
                        on t1.id = t2.product_price_list_delivery_mop_mixed_id
                    inner join '.$q_products.' 
                        on tp.product_id = t2.product_id
                    inner join product t3 on tp.product_id = t3.id
                    inner join unit t4 on tp.unit_id = t4.id
                where t1.product_price_list_delivery_mop_id = '.$db->escape($mop_id).'
                
            ';
            $rs = $db->query_array($q);
            if(count($rs) > 0){
                $result = $rs;
            }
            
            return $result;
            //</editor-fold>
        }
        
        public static function mop_separated_product_get($mop_id, $products){
            //<editor-fold defaultstate="collapsed">
            $db = new DB();
            $result = array();
            $q_products=' (select -1 product_id, -1 unit_id, 0 qty,0 amount';
            foreach($products as $idx=>$product){
                $product_id = isset($product['product_id'])?$product['product_id']:'';
                $unit_id = isset($product['unit_id'])?$product['unit_id']:'';
                $qty = isset($product['qty'])?$product['qty']:'0';
                $amount = isset($product['amount'])?$product['amount']:'0';
                $q_products.= ' union select '
                        .$db->escape($product_id)
                        .', '.$db->escape($unit_id)
                        .', '.$db->escape($qty)
                        .', '.$db->escape($amount)
                ;
            }
            $q_products.=') tp ';
            $q = '
                select distinct t3.id product_id
                    , t3.name product_name
                    , tp.unit_id
                    , t4.code unit_code
                    , tp.qty qty
                    , tp.amount amount
                    , tp.qty * tp.amount total_amount
                    , t1.amount amount_req
                    
                from product_price_list_delivery_mop_separated t1
                    inner join '.$q_products.' 
                        on tp.product_id = t1.product_id and tp.unit_id = t1.unit_id
                    inner join product t3 on tp.product_id = t3.id
                    inner join unit t4 on tp.unit_id = t4.id
                where t1.product_price_list_delivery_mop_id = '.$db->escape($mop_id).'
                
            ';
            $rs = $db->query_array($q);
            if(count($rs) > 0){
                $result = $rs;
            }
            
            return $result;
            //</editor-fold>
        }
        
        public static function customer_deposit_allocation_get($sales_invoice_id){
            //<editor-fold defaultstate="collapsed">
            get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_engine');
            $result = Tools::_float('0');
            $customer_deposit = Tools::_float('0');
            $db = new DB();
            $q = '
                select sum(allocated_amount) total
                from customer_deposit_allocation
                where sales_invoice_id = '.$db->escape($sales_invoice_id).'
                    and customer_deposit_allocation_status = '
                    .$db->escape(SI::status_default_status_get('Customer_Deposit_Allocation_Engine')['val']).'
            ';
            
            $rs = $db->query_array($q);
            foreach($rs as $idx=>$val){
                $customer_deposit += Tools::_float($val['total']);
            }
            $result = Tools::_float($customer_deposit);
            //</editor-fold>
        }
        
        public static function sales_payment_total_get($sales_invoice_id){
            //<editor-fold defaultstate="collapsed">
            
            get_instance()->load->helper('sales_receipt_allocation/sales_receipt_allocation_engine');
            $result = Tools::_float('0');
            
            $sales_receipt = Tools::_float('0');
            $db = new DB();
            
            $q = '
                select sum(allocated_amount) total
                from sales_receipt_allocation
                where sales_invoice_id = '.$db->escape($sales_invoice_id).'
                    and sales_receipt_allocation_status = '
                    .$db->escape(SI::status_default_status_get('Sales_Receipt_Allocation_Engine')['val']).'
            ';
            
            $rs = $db->query_array($q);
            foreach($rs as $idx=>$val){
                $sales_receipt += Tools::_float($val['total']);
            }
            
            $result = Tools::_float($sales_receipt);
            
            return $result;
            //</editor-fold>
        }
        
        public static function sales_payment_change_amount_get($sales_invoice_id){
            //<editor-fold defaultstate="collapsed">
            get_instance()->load->helper('sales_receipt_allocation/sales_receipt_allocation_engine');
            $result = Tools::_float('0');
            $change_amount = Tools::_float('0');
            $db = new DB();
            $q = '
                select sum(sr.change_amount) total
                from sales_receipt_allocation sra
                    inner join sales_receipt sr on sra.sales_receipt_id = sr.id
                where sra.sales_invoice_id = '.$db->escape($sales_invoice_id).'
                    and sra.sales_receipt_allocation_status = '
                    .$db->escape(SI::status_default_status_get('Sales_Receipt_Allocation_Engine')['val']).'
            ';
            
            $rs = $db->query_array($q);
            foreach($rs as $idx=>$val){
                $change_amount = Tools::_float($val['total']);
            }
            
            $result = Tools::_float($change_amount);
            
            return $result;
            //</editor-fold>
        }
        
        public static function sales_inquiry_by_get(){
            //<editor-fold defaultstate="collapsed" >
            $result = array();
            $db = new DB();
            $q = '
                select t1.* 
                from sales_inquiry_by t1
                where t1.status>0 and t1.sales_inquiry_by_status = "A"
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0) $result = $rs;
            return $result;
            //</editor-fold>
        }
        
        public static function product_movement_outstanding_get($sales_invoice_id){
            //<editor-fold defaultstate="collapsed">
            $result = array();
            $db = new DB();
            $q = '
                select t1.*
                from sales_invoice_product t1
                where t1.movement_outstanding_qty > 0 
                    and t1.sales_invoice_id = '.$db->escape($sales_invoice_id).'
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0) $result = $rs;
            return $result;
            //</editor-fold>
        }
        public static function outstanding_amount_get($sales_invoice_id){
            $result = 0;
            $db = new DB();
            $q = '
                select t1.outstanding_amount
                from sales_invoice t1
                where t1.id = '.$db->escape($sales_invoice_id).'                
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0) $result = $rs[0]['outstanding_amount'];
            return $result;
        }
        
        public static function sales_invoice_get($sales_invoice_id){
            //<editor-fold defaultstate="collapsed">
            $result = array();
            $db = new DB();
            $q = '
                select *
                from sales_invoice t1
                where t1.id = '.$db->escape($sales_invoice_id).'
                    and t1.status>0
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0) $result = $rs[0];
            return $result;
            //</editor-fold>
        }
        
        public static function sales_invoice_info_get($sales_invoice_id){
            //<editor-fold defaultstate="collapsed">
            $result = array();
            $db = new DB();
            $q = '
                select t1.*
                    ,t2.code sales_inquiry_by_code
                    ,t2.name sales_inquiry_by_name
                from sales_invoice_info t1
                    inner join sales_inquiry_by t2 on t1.sales_inquiry_by_id = t2.id
                where t1.sales_invoice_id = '.$db->escape($sales_invoice_id).'
                    
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0) $result = $rs[0];
            return $result;
            //</editor-fold>
        }
        
        public static function sales_invoice_product_get($sales_invoice_id){
            //<editor-fold defaultstate="collapsed">
            $result = array();
            $db = new DB();
            $q = '
                select 
                    t1.*
                    ,t2.code product_code
                    ,t3.code unit_code
                    ,t2.notes product_notes
                    ,t2.additional_info product_additional_info
                from sales_invoice_product t1
                    inner join product t2 on t1.product_id = t2.id
                    inner join unit t3 on t1.unit_id = t3.id
                where t1.sales_invoice_id = '.$db->escape($sales_invoice_id).'
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0){
                
                $result = $rs;
            }
            
            return $result;
            //</editor-fold>
        }
                
        function additional_cost_get($sales_invoice_id){
            //<editor-fold defaultstate="collapsed">
            $result = array();
            $db = new DB();
            $rs = $db->fast_get('sales_invoice_additional_cost',array('sales_invoice_id'=>$sales_invoice_id));
            if(count($rs)>0) $result = $rs;
            return $result;
            //</editor-fold>
        }
        
        public static function sales_invoice_outstanding_amount_search($param){
            //<editor-fold defaultstate="collapsed">
            $result = array();
            $customer_id = isset($param['customer_id'])?Tools::_str($param['customer_id']):'';
            $lookup_val = isset($param['lookup_val'])?'%'.Tools::_str($param['lookup_val']).'%':'';
            $db = new DB();
            $limit = 10;
            $q = '
                select *
                from sales_invoice t1
                where t1.sales_invoice_status = "invoiced" 
                    and t1.outstanding_amount > 0
                    and t1.code like '.$db->escape($lookup_val).'
                    and t1.customer_id = '.$db->escape($customer_id).'
                order by t1.sales_invoice_date desc
                limit '.$limit.'
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0){                    
                $result = $rs;
            }
            return $result;
            //</editor-fold>
        }
        
        function notification_outstanding_amount_get(){
            //<editor-fold defaultstate="collapsed">
            get_instance()->load->helper('rpt_simple/rpt_simple_data_support');
            $result = array('response'=>null);
            $response = null;
            $temp_result = Rpt_Simple_Data_Support::report_table_sales_pos_outstanding_amount();        
            if($temp_result['info']['data_count']>0){
                $response = array(
                    'icon'=>App_Icon::html_get(APP_Icon::sales_pos())
                    ,'href'=>get_instance()->config->base_url().'rpt_simple/index/sales_pos/outstanding_amount'
                    ,'msg'=>' '.($temp_result['info']['data_count']).' sales pos - '.Lang::get(array(array('val'=>'incomplete','grammar'=>'adj'),array('val'=>'payment')),true,false,false,true)
                );
            }
            $result['response'] = $response;
            return $result;
            //</editor-fold>
        }

        function notification_movement_outstanding_product_qty_get(){
            //<editor-fold defaultstate="collapsed">
            get_instance()->load->helper('rpt_simple/rpt_simple_data_support');
            $result = array('response'=>null);
            $response = null;        
            $temp_result = Rpt_Simple_Data_Support::report_table_sales_pos_movement_outstanding_product_qty();        
            if($temp_result['info']['data_count']>0){
                $response = array(
                    'icon'=>App_Icon::html_get(APP_Icon::sales_pos())
                    ,'href'=>get_instance()->config->base_url().'rpt_simple/index/sales_pos/movement_outstanding_product_qty'
                    ,'msg'=>' '.($temp_result['info']['data_count']).' sales pos - '.Lang::get('indent product',true,false)
                );
            }
            $result['response'] = $response;
            return $result;
            //</editor-fold>
        }
    }
?>