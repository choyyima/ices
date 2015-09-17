<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class fpdf_dof_print extends extended_fpdf{
    public function footer(){

    }
}

class Delivery_Order_Final_Print{
    function __construct(){}
    
    
    static function dof_header_print($p_engine,$data){
        //<editor-fold defaultstate="collapsed">
            $dof = $data['dof']; //sales invoice
            
            $dof_type = $dof['delivery_order_final_type'];
            
            $p_engine->font_set('Times',8,'');
            $p_engine->bold();
            $p_engine->Cell(65,null,Lang::prt_get('Delivery Note').' Final');
            $p_engine->bold();
            $p_engine->Cell(0,null,Lang::prt_get($data['form_address']),1,0,'C');
            $p_engine->normal();
            $p_engine->Ln();
            
            $delivery_order_final_date = Tools::_date($dof['delivery_order_final_date'],'F d, Y');
            $left_block_info = $dof['code'].' - '.$delivery_order_final_date;
            
            if($dof_type === 'sales_invoice'){
                
                get_instance()->load->helper('sales_pos/sales_pos_data_support');
                get_instance()->load->helper('expedition/expedition_data_support');
                get_instance()->load->helper('customer/customer_data_support');
                $si = Delivery_Order_Final_Data_Support::sales_invoice_get($dof['id']);
                $si_info = Sales_Pos_Data_Support::sales_invoice_info_get($si['id']);
                
                $curr_x = $p_engine->fpdf->GetX();
                $curr_y = $p_engine->fpdf->GetY();
                $col_width = $p_engine->fpdf->page_width_get()/2;
                if(Tools::empty_to_null($si_info['expedition_id']) !== null){
                    /*
                    $expedition = Expedition_Data_Support::expedition_get($si_info['expedition_id']);
                    $left_block_info .= 
                        "\n".Lang::get('Expedition').': '.$expedition['name'].' - '.$expedition['phone']
                        ."\n".$expedition['address'].' '.$expedition['city']
                        ;
                    */
                    $dof_warehouse_to = Delivery_Order_Final_Data_Support::delivery_order_final_warehouse_to($dof['id']);
                    $left_block_info .= 
                        "\n".Lang::get('Expedition').': '.$dof_warehouse_to['contact_name'].' - '.$dof_warehouse_to['phone']
                        ."\n".$dof_warehouse_to['address']
                        ;
                }
                
                $p_engine->MultiCell($col_width,null,$left_block_info,0,'L');
                $next_y = $p_engine->fpdf->GetY();
                $p_engine->fpdf->SetXY($curr_x,$curr_y);
                    
                $customer = Customer_Data_Support::customer_get($si['customer_id']);
                $p_engine->Cell(0,null,$customer['name'].' - '.$customer['phone'],0,0,'R');
                $p_engine->Ln();
                $p_engine->Cell(0,null,substr($customer['address'].' '.$customer['city'],0,40),0,0,'R');
                $p_engine->Ln();
                if($next_y < $p_engine->fpdf->GetY()){
                    $next_y = $p_engine->fpdf->GetY();
                }
                $p_engine->fpdf->SetXY($curr_x,$next_y);
            }
            
            $p_engine->Ln();
            //</editor-fold>
        
    }
    
    public static function dof_footer_print($p_engine,$data){
        //<editor-fold defaultstate="collapsed">
        $form_address = $data['form_address'];
        
        $p_engine->font_set('Times',7,'');
        $line_height = $p_engine->fpdf->LineHeight;

        $start_x = $p_engine->fpdf->GetX();
        
        switch(strtolower($form_address)){
            case 'archive':
                $p_engine->fpdf->SetXY($start_x,120);
                $p_engine->Cell(($p_engine->fpdf->page_width_get()/2),null,'(ttd '.Lang::prt_get('Receiver').')',0,0,'C');
                $p_engine->Cell(($p_engine->fpdf->page_width_get()/2),null,'(ttd '.Lang::prt_get('Distributing Warehouse').')',0,0,'C');
                
                break;
        }
       
        
        $p_engine->fpdf->SetXY($start_x,125);
        $p_engine->MultiCell(0,null,'Notice:');
        $p_engine->MultiCell(0,null,'- Segala kerusakan / kehilangan selama pengiriman diluar tanggung jawab pengirim.');
        $p_engine->MultiCell(0,null,'- Selisih beban ongkos kirim merupakan beban pihak pembeli.');
        
        $p_engine->fpdf->SetXY($p_engine->fpdf->GetX(),140);
        $p_engine->Cell(0,null,''.Tools::_date('','F d, Y H:i:s'), 0, 0);
        $p_engine->Cell(0,null,'Page '.$data['page_number'], 0, 0,'R');
        //</editor-fold>
    }
    
    public static function delivery_order_final_print($p_engine,$delivery_order_final_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('delivery_order_final/delivery_order_final_data_support');
        get_instance()->load->helper('delivery_order/delivery_order_print');
        
        
        $success = 1;
        $db = new DB();
        $dof = Delivery_Order_Final_Data_Support::delivery_order_final_get($delivery_order_final_id);
        if(!count($dof)>0) $success = 0;
        
        if($success === 1){
            if(in_array($dof['delivery_order_final_status'],array('done'))){
                $do = Delivery_Order_Final_Data_Support::delivery_order_get($dof['id']);
                $dof_type = $dof['delivery_order_final_type'];
                $dof_product = Delivery_Order_Final_Data_Support::delivery_order_final_product_get($dof['id']);
                $form_address = array();

                if($dof_type === 'sales_invoice'){
                    get_instance()->load->helper('sales_pos/sales_pos_data_support');
                    $si = Delivery_Order_Final_Data_Support::sales_invoice_get($delivery_order_final_id);
                    $si_info = Sales_Pos_Data_Support::sales_invoice_info_get($si['id']);

                    if(!is_null($si_info['expedition_id'])){
                        $form_address = array('Archive','Distributing Warehouse','Expedition');            
                    }
                    else{
                        $form_address = array('Archive','Distributing Warehouse','Customer');
                    }
                }

                if(is_null($p_engine)){
                    $p_engine = new Printer('fpdf_dof_print');
                    $p_engine->paper_set('thin-man');
                    $p_engine->start();
                }

                //<editor-fold defaultstate="collapsed" desc="Delivery Order Final">
                foreach($form_address as $i_form_address=>$row_form_address){
                    if($i_form_address>0){
                        $p_engine->fpdf->AddPage();
                    }

                    $header_data = array(
                        'dof'=>$dof,
                        'form_address'=>$row_form_address
                    );

                    $footer_data = array('page_number'=>1,'form_address'=>$row_form_address);

                    self::dof_header_print($p_engine,$header_data);

                    $product_col_width = 65;
                    $qty_col_width = 20;

                    $footer_start = 120;
                    for($i = 0;$i<count($dof_product);$i++){
                        $new_page = false;                    
                        $print_table_header = false;
                        $print_footer = false;

                        $product_text = self::product_text_get($dof_product[$i]);
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

                            if( $i+1 <count($dof_product)){
                                $next_line_height = $p_engine->fpdf->NbLines($product_col_width,self::product_text_get($dof_product[$i+1])) * $p_engine->fpdf->LineHeight;
                                if (($curr_y + $curr_line_height + $next_line_height)> $footer_start){
                                    $print_footer = true;
                                }
                            }
                        }

                        if($new_page){
                            $p_engine->fpdf->AddPage();
                            self::dof_header_print($p_engine,$header_data);
                            $footer_data['page_number']+=1;
                        }

                        if($print_table_header){
                            $p_engine->bold();
                            $p_engine->Cell(10,null,'NO',1,0,'C');
                            $p_engine->Cell($product_col_width,null,'Product',1,0,'C');
                            $p_engine->Cell($qty_col_width,null,'Qty',1,0,'C');                        
                            $p_engine->Ln();
                            $p_engine->normal();
                        }

                        $p_engine->font_set('Times',7,'');
                        $left_x = $p_engine->fpdf->GetX();
                        $top_y = $p_engine->fpdf->GetY();
                        $curr_x = $p_engine->fpdf->GetX();
                        $col_width = 10;
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
                            Tools::thousand_separator($dof_product[$i]['qty']).' '.$dof_product[$i]['unit_code'],
                        0,'R');
                        $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                        $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                        $p_engine->fpdf->SetXY($left_x,$top_y+$curr_line_height);                        

                        if($print_footer){
                            self::dof_footer_print($p_engine,$footer_data);
                        }

                    }

                    self::dof_footer_print($p_engine,$footer_data);

                }
                //</editor-fold>

                //<editor-fold defaultstate="collapsed" desc="Delivery Order">
                foreach($do as $i=>$row){
                    $p_engine->fpdf->AddPage();
                    Delivery_Order_Print::delivery_order_print($row['id'],array('p_engine'=>$p_engine,'p_output'=>false));
                }            
                //</editor-fold>

                $p_engine->output();

            }
        }
        //</editor-fold>
    }
    
    function product_text_get($row){
        $result = '';
        return $row['product_code']
            .' - '.$row['product_name'];
    }
    
    
    
    
}
?>