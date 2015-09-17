<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Receive_Product_Print{
        
        public static function print_receive_product_header($p_engine,$setting,$data=array()){
            $movement = $data['movement'];
            $p_engine->SetFont('Times','B',14);
            $p_engine->Cell(0,0,'RECEIVE PRODUCT',0,0,'C');
            $p_engine->Ln(10);
            $p_engine->SetFont('Times','',12);
            $p_engine->Cell($setting->max_width / 2,$setting->d_height,'Code: '.$movement->code);
            $p_engine->Cell($setting->max_width / 2,$setting->d_height,'Printed at '.Date('Y-m-d H:i:s'),0,0,'R');
            $p_engine->Ln();
            $p_engine->Cell($setting->max_width,$setting->d_height,'Movement Date: '.$movement->movement_date);
            $p_engine->Ln();
            $p_engine->Cell($setting->max_width/2,$setting->d_height,'Movement To: '.$movement->movement_to_warehouse_name);
            //$p_engine->Cell($setting->max_width/2,$setting->d_height,'Movement Status: '.$movement->movement_status_name,0,0,'R');
            $p_engine->Ln();
            $p_engine->SetFont('Times','B',12);
            $p_engine->Cell($setting->max_width*5/100,$setting->d_height,'#',1);
            $p_engine->Cell($setting->max_width*62.5/100,$setting->d_height,'Product',1,0,'C');
            $p_engine->Cell($setting->max_width*20/100,$setting->d_height,'Qty',1,0,'C');
            $p_engine->Cell($setting->max_width*12.5/100,$setting->d_height,'Unit',1,0,'C');
            $p_engine->Ln();
        }
        
        public static function print_receive_product_footer($p_engine,$setting,$data=array()){
            $p_engine->SetFont('Times','',12);
            //$p_engine->SetY(-50);
            $p_engine->AddPage();
            $p_engine->Ln();
            $p_engine->Cell($setting->max_width / 3,$setting->d_height,'Logistic',0,0,'C');
            $p_engine->Cell($setting->max_width / 3,$setting->d_height,'Driver',0,0,'C');
            $p_engine->Cell($setting->max_width / 3,$setting->d_height,'Admin',0,0,'C');
            $p_engine->Ln();
            $p_engine->Ln();
            $p_engine->Ln();

            $p_engine->Cell($setting->max_width /3,$setting->d_height,'(.......................)',0,0,'C');
            $p_engine->Cell($setting->max_width /3,$setting->d_height,'(.......................)',0,0,'C');
            $p_engine->Cell($setting->max_width /3,$setting->d_height,  User_Info::get()['name'],0,0,'C');
            $p_engine->Ln();
        }
                
        public static function print_receive_product($id){
            $success = 1;
            $db = new DB();
            $q = '
                select t1.* 
                    ,t2.name movement_to_warehouse_name
                    ,case t1.movement_status when "O" then "OPENED"
                        when "D" then "DELIVERED"
                        when "X" then "CANCELED" end movement_status_name
                from movement t1
                    inner join warehouse t2 on t1.movement_to_warehouse_id = t2.id
                where t1.movement_status ="D" and t1.id = '.$db->escape($id).'
                
            ';
            $rs = $db->query_array_obj($q);
            $movement = count($rs)>0?$rs[0]:$rs;
            
            $q = '
                select t2.name product_name, t3.name unit_name, t1.qty
                from movement_product t1
                    inner join product t2 on t1.product_id = t2.id
                    inner join unit t3 on t1.unit_id = t3.id
                where t1.movement_id = '.$db->escape($id).'    
                    order by t2.name
            ';
            $movement_product = $db->query_array_obj($q);
            
            if($success == 1){
                $setting = array(
                    'page_width' =>0
                    ,'page_size' => array()
                    ,'d_height'=>0
                    ,'margin_left'=>0
                    ,'margin_right'=>0
                    ,'max_width' =>0
                );
                $setting = json_decode(json_encode($setting));
                $setting->page_width = 200;
                $setting->page_size = array($setting->page_width,100);
                $setting->d_height=7;
                $setting->margin_left=5;
                $setting->margin_right=5;
                $setting->max_width = $setting->page_width - $setting->margin_left - $setting->margin_left;
                $p_engine = new Printer('L','mm',$setting->page_size);
                $p_engine->SetMargins($setting->margin_left,10,$setting->margin_right);
                $p_engine->SetAutoPageBreak(true, 10);
                $p_engine->AddPage();
                $p_engine->AliasNbPages();
                self::print_receive_product_header($p_engine,$setting,array('movement'=>$movement));
                
                for($i = 0;$i<count($movement_product);$i++){
                    if($i%5 ===0 && $i !== 0){
                        //self::print_receive_product_footer($p_engine,$setting,array('movement'=>$movement));
                        $p_engine->AddPage();
                        self::print_receive_product_header($p_engine,$setting,array('movement'=>$movement));
                        
                    }
                    $p_engine->SetFont('Times','',10);
                    $row = $movement_product[$i];
                    $p_engine->Cell($setting->max_width*5/100,$setting->d_height,$i+1,1);
                    $p_engine->Cell($setting->max_width*62.5/100,$setting->d_height,$row->product_name,1);
                    $p_engine->Cell($setting->max_width*20/100,$setting->d_height,Tools::thousand_separator($row->qty,2,true),1,0,'R');
                    $p_engine->Cell($setting->max_width*12.5/100,$setting->d_height,$row->unit_name,1);
                    $p_engine->Ln();
                }
                
                self::print_receive_product_footer($p_engine,$setting,array('movement'=>$movement));
                $p_engine->Output();
            }
        }
    }

?>