<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class fpdf_delivery_order_print extends extended_fpdf{
    public function footer(){

    }
}

class Delivery_Order_Print{
    function __construct(){}

    public static function delivery_order_header_print($p_engine,$data=array()){
        //<editor-fold defaultstate="collapsed">
        $do = $data['do'];
        $do_warehouse_to = $data['do_warehouse_to'];
        $do_warehouse_from = $data['do_warehouse_from'];
        $form_type = $data['form_type'];
        
        $warehouse_to = Warehouse_Engine::warehouse_get($do_warehouse_to['warehouse_id']);
        $warehouse_from = Warehouse_Engine::warehouse_get($do_warehouse_from['warehouse_id']);
        
        $do_date = Tools::_date($do['delivery_order_date'],'F d, Y',null,array('LC_TIME'=>'ID'));
        
        $p_engine->font_set('Times',8);
        $p_engine->bold();
        $p_engine->Cell(65,null,Lang::prt_get('DELIVERY NOTE'));
        $p_engine->normal();
        $p_engine->bold();
        $p_engine->Cell(0,null,Lang::prt_get($form_type),1,0,'C');
        $p_engine->normal();
        $p_engine->Ln();
        
        
        $do_left_info = $do['code'].' - '.$do_date;   
        $do_right_info = '';
                
        switch($do['delivery_order_type']){
            case 'refill_subcon_work_order':
                $do_left_info .= "\n".Lang::prt_get('From').': '.$warehouse_from['name']
                    ."\n".Lang::prt_get('To').': '.$warehouse_to['name'];
                $do_right_info = $do_warehouse_to['contact_name']
                    ."\n".$do_warehouse_to['address']
                    ."\n".$do_warehouse_to['phone'];
                
                break;
            case 'sales_invoice':
                get_instance()->load->helper('delivery_order_final/delivery_order_final_data_support');
                get_instance()->load->helper('customer/customer_data_support');
                $dof = Delivery_Order_Data_Support::delivery_order_final_get($do['id']);
                $si = Delivery_Order_Final_Data_Support::sales_invoice_get($dof['id']);
                $si_info = Sales_Pos_Data_Support::sales_invoice_info_get($si['id']);
                $customer = Customer_Data_Support::customer_get($si['customer_id']);                
                
                
                $do_left_info .= "\n".Lang::prt_get('From').': '.$warehouse_from['name']
                        ."\n".Lang::prt_get('To').': '.$do_warehouse_to['contact_name']
                        ."\n".$do_warehouse_to['phone']
                        ."\n".$do_warehouse_to['address']
                        ;
                
                $do_right_info = $customer['name']." - ".$customer['phone']
                    ."\n".$customer['address'].' '.$customer['city']
                    ;
                break;            
        }
        
        $start_x = $p_engine->fpdf->GetX();
        $start_y = $p_engine->fpdf->GetY();
        $col_width = $p_engine->fpdf->page_width_get()/2;
        $block_height = $p_engine->fpdf->NbLines($col_width,$do_left_info)>$p_engine->fpdf->NbLines($col_width, $do_right_info)?
            $p_engine->fpdf->NbLines($col_width, $do_left_info) * $p_engine->fpdf->LineHeight:
            $p_engine->fpdf->NbLines($col_width, $do_right_info) * $p_engine->fpdf->LineHeight;
        $p_engine->MultiCell($col_width,null,$do_left_info,0,'L');
        $p_engine->fpdf->SetXY($start_x+$col_width,$start_y);
        $p_engine->MultiCell($col_width,null,$do_right_info,0,'R');
        $p_engine->fpdf->SetXY($start_x,$start_y+$block_height);
        $p_engine->Ln();
        
        //</editor-fold>
    }

    public static function delivery_order_footer_print($p_engine,$data){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('delivery_order/delivery_order_data_support');
        $do = $data['do'];
        $do_type = $do['delivery_order_type'];
        $form_address = $data['form_address'];
        
        $p_engine->font_set('Times',7);
        
        $start_x = $p_engine->fpdf->GetX();
        
        $p_engine->fpdf->SetXY($start_x,135);        
        switch($do_type){
            case 'refill_subcon_work_order':
                if(strtolower($form_address) === 'archive'){
                    $p_engine->Cell($p_engine->fpdf->page_width_get()/2,null,'(ttd '.Lang::prt_get('Receiver').')',0,0,'C');
                    $p_engine->Cell($p_engine->fpdf->page_width_get()/2,null,'(ttd '.Lang::prt_get('Local Warehouse').')',0,0,'C');
                    $p_engine->Ln();
                }
                break;
            case 'sales_invoice':
                $p_engine->Cell($p_engine->fpdf->page_width_get()/2,null,'(ttd '.Lang::prt_get('Driver').')',0,0,'C');
                $p_engine->Cell($p_engine->fpdf->page_width_get()/2,null,'(ttd '.Lang::prt_get('Local Warehouse').')',0,0,'C');
                $p_engine->Ln();

                break;
        }
        
        $p_engine->fpdf->SetXY($start_x,140);
        $p_engine->Cell(0,null,''.Tools::_date('','F d, Y H:i:s'), 0, 0);
        $p_engine->Cell(0,null,'Page '.$data['page_number'], 0, 0,'R');
        //</editor-fold>
    }

    public static function delivery_order_print($id,$opt = array('p_engine'=>null,'p_output'=>true)){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('delivery_order/delivery_order_data_support');
        $success = 1;
        $db = new DB();
        $do = Delivery_Order_Data_Support::delivery_order_get($id);
        
        if(count($do)>0){
            if(in_array($do['delivery_order_status'],array('done'))){
                //<editor-fold defaultstate="collapsed" desc="Load Library">
                if($do['delivery_order_type'] === 'refill_subcon_work_order'){
                    get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_data_support');
                }
                //</editor-fold>
                $do_warehouse_to = Delivery_Order_Data_Support::warehouse_to_get($id);
                $do_warehouse_from = Delivery_Order_Data_Support::warehouse_from_get($id);
                $dop = self::delivery_order_product_get($do['id']);
                $p_engine = isset($opt['p_engine'])?$opt['p_engine']:null;
                $p_output = isset($opt['p_output'])?$opt['p_output']:true;
                if($p_engine === null){
                    $p_engine = new Printer('fpdf_delivery_order_print');
                    $p_engine->paper_set('thin-man');
                    $p_engine->start();
                }

                $form_type_arr = array();
                switch($do['delivery_order_type']){
                    case 'refill_subcon_work_order':
                        $form_type_arr = array('Archive','Local Warehouse','Receiver');
                        break;
                    case 'sales_invoice':
                        $form_type_arr = array('Local Warehouse','Distributing Warehouse');
                        break;
                }

                foreach($form_type_arr as $form_type_idx=>$form_type){
                    if($form_type_idx>0){
                        $p_engine->fpdf->AddPage();
                    }
                    $header_data = array('do'=>$do,'do_warehouse_to'=>$do_warehouse_to,'do_warehouse_from'=>$do_warehouse_from,'form_type'=>$form_type);
                    $footer_data = array('do'=>$do,'form_address'=>$form_type,'page_number'=>1);
                    Delivery_Order_Print::delivery_order_header_print($p_engine,$header_data);

                    $row_num_col_width = 10;
                    $product_col_width = 65;
                    $qty_col_width = 20;
                    $footer_start = 135;

                    for($i = 0;$i<count($dop);$i++){
                        $new_page = false;                    
                        $print_table_header = false;
                        $print_footer = false;

                        $product_text = $dop[$i]['product_text'];
                        $curr_line_height = $p_engine->fpdf->NbLines($product_col_width,$product_text) * $p_engine->fpdf->LineHeight;

                        if($i === 0){
                            $new_page = false;
                            $print_table_header = true;
                        }
                        else{
                            $curr_y = $p_engine->fpdf->GetY();
                            if($curr_y+$curr_line_height > $footer_start){
                                $new_page = true;
                                $print_table_header = true;
                            }

                            if( $i+1 <count($dop)){
                                $next_line_height = $p_engine->fpdf->NbLines($product_col_width,$product_text) * $p_engine->fpdf->LineHeight;
                                if (($curr_y + $curr_line_height + $next_line_height)> $footer_start){
                                    $print_footer = true;
                                }
                            }
                        }

                        if($new_page){
                            $p_engine->fpdf->AddPage();
                            Delivery_Order_Print::delivery_order_header_print($p_engine,$header_data);
                            $footer_data['page_number']+=1;
                        }

                        if($print_table_header){
                            $p_engine->font_set('Times',7,'');
                            $p_engine->bold();
                            $p_engine->Cell($row_num_col_width,null,'No',1,0,'C');
                            $p_engine->Cell($product_col_width,null,'Product',1,0,'C');
                            $p_engine->Cell($qty_col_width,null,'Qty',1,0,'C');
                            $p_engine->Ln();
                        }

                        $p_engine->font_set('Times',7,'');  
                        $left_x = $p_engine->fpdf->GetX();
                        $top_y = $p_engine->fpdf->GetY();
                        $curr_x = $p_engine->fpdf->GetX();
                        $col_width = $row_num_col_width;
                        $p_engine->MultiCell($col_width,$curr_line_height,$i+1,0);
                        $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                        $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                        $curr_x = $p_engine->fpdf->GetX();
                        $col_width = $product_col_width;
                        $p_engine->MultiCell($col_width,null,
                            $product_text,
                        0,'L');
                        $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                        $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                        $curr_x = $p_engine->fpdf->GetX();
                        $col_width = $qty_col_width;
                        $p_engine->MultiCell($col_width,$curr_line_height,
                            Tools::thousand_separator($dop[$i]['qty']).' '.$dop[$i]['unit_code'],
                        0,'R');
                        $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                        $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                        $p_engine->fpdf->SetXY($left_x,$top_y+$curr_line_height);    

                        if($print_footer){
                            Delivery_Order_Print::delivery_order_footer_print($p_engine,$footer_data);
                        }
                    }                
                    Delivery_Order_Print::delivery_order_footer_print($p_engine,$footer_data);
                }

                if($p_output){
                    $p_engine->output(str_replace('/','',$do['code']).'.pdf','I');
                }
            }
        }
        //</editor-fold>
    }
 
    private static function delivery_order_product_get($do_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $do = Delivery_Order_Data_Support::delivery_order_get($do_id);
        $dop_db = Delivery_Order_Data_Support::delivery_order_product_get($do_id);
        
        switch($do['delivery_order_type']){
            case 'sales_invoice':
                foreach($dop_db as $idx=>$row){
                    $dop_db[$idx]['product_text'] = $row['product_code'].' '.$row['product_name'];
                }
                $result = $dop_db;
                break;
            case 'refill_subcon_work_order':
                //<editor-fold defaultstate="collapsed">
                foreach($dop_db as $idx=>$row){
                    $product_exists = false;
                    foreach($result as $idx2=>$row2){
                        if($row['product_type'] === $row2['product_type']
                            && $row['product_id'] === $row2['product_id']
                            && $row['unit_id'] === $row['unit_id']
                        ){
                            $result[$idx2]['qty'] = Tools::_float($result[$idx2]['qty'])+Tools::_float($row['qty']);
                            $product_exists = true;
                            break;
                        }
                    }
                    
                    if(!$product_exists){
                        switch($row['product_type']){
                            case 'registered_product':
                                $row['product_text'] = $row['product_code'].' '.$row['product_name'];
                                break;
                            case 'refill_work_order_product':
                                $row['product_text'] = $row['product_marking_code'];
                                break;
                        }
                        
                        $result[] = $row;
                    }
                }
                //</editor-fold>
                break;
                                
        }
        
        return $result;
        //</editor-fold>
    }
    
}

?>