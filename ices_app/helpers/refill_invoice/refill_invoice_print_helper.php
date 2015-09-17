<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class fpdf_refill_invoice_print extends extended_fpdf{
        public function footer(){
            
        }
    }
    
    class fpdf_refill_payment_print extends extended_fpdf{
        public function footer(){
            
        }
    }
    
    class Refill_Invoice_Print{
        
        public static function refill_invoice_header_print($p_engine,$data){
            //<editor-fold defaultstate="collapsed">
            $ri = $data['ri']; //refill invoice
            $customer = $data['customer']; //customer
            
            $ri_date = Tools::_date($ri['refill_invoice_date'],'F d, Y',null,array('LC_TIME'=>'ID'));
            
            $p_engine->font_set('Times',8,'');
            $p_engine->bold();
            $p_engine->Cell(20,null,Lang::prt_get('Refill Invoice'));
            $p_engine->normal();
            $p_engine->Cell(60,null,$ri['code'].' - '.$ri_date);
            $p_engine->bold();
            $p_engine->Cell(0,null,Lang::prt_get($data['form_address']),1,0,'C');
            $p_engine->normal();
            $p_engine->Ln();
            
            $p_engine->Cell(0,null,$customer['name'].' - '.$customer['phone'],0,0,'R');
            
            $p_engine->Ln();
            $p_engine->Cell(0,null,substr($customer['address'].' '.$customer['city'],0,40),0,0,'R');
            $p_engine->Ln();

            $p_engine->Ln();
            //</editor-fold>
        }
        
        public static function refill_invoice_footer_print($p_engine,$data){
            //<editor-fold defaultstate="collapsed">
            $p_engine->font_set('Times',7,'');
            $line_height = $p_engine->fpdf->LineHeight;
            
            $p_engine->fpdf->SetXY($p_engine->fpdf->GetX(),140);
            //$p_engine->MultiCell(0,null,'Notice:');

            //$p_engine->MultiCell(0,null,'- Selisih beban ongkos kirim tetap merupakan beban pihak pembeli.');            
            //$p_engine->MultiCell(0,null,'- Kerusakan / kehilangan barang yang tidak diambil lebih dari satu bulan sejak konfirmari diluar tanggung jawab kami.');
            //$p_engine->MultiCell(0,null,'- Apabila barang tidak diambil / pending pengiriman optimal lebih dari 3 hari kerja sejak konfirmari, maka akan dikenakan biaya administrari & sewa gudang sebesar 5% dari nilai barang per minggu atau Rp. 150,000 / (min. 1 Cbm)');
            //$p_engine->MultiCell(0,null,'- Barang yang dibeli tidak dapat dibatalkan');
            
            
            $p_engine->fpdf->SetXY($p_engine->fpdf->GetX(),140);
            $p_engine->Cell(0,null,''.Tools::_date('','F d, Y H:i:s'), 0, 0);
            $p_engine->Cell(0,null,'Page '.$data['page_number'], 0, 0,'R');
            //</editor-fold>
        }
        
        function invoice_print($id){
            //<editor-fold defaultstate="collapsed">
            get_instance()->load->helper('refill_invoice/refill_invoice_data_support');
            get_instance()->load->helper('customer/customer_data_support');
            $success = 1;
            $db = new DB();
            $ri = Refill_Invoice_Data_Support::refill_invoice_get($id);
            if(! count($ri) > 0) $success = 0;

            if($success == 1 && $ri['refill_invoice_status'] !== 'X'){
                $ri_product = Refill_Invoice_Data_Support::ri_product_get($ri['id']);
                $customer = Customer_Data_Support::customer_get($ri['customer_id']);
                $form_address = array('Customer','Archive');
                
                $p_engine = new Printer('fpdf_refill_invoice_print');
                $p_engine->paper_set('thin-man');
                $p_engine->start();
                    
                
                foreach($form_address as $i_form_address=>$row_form_address){
                    if($i_form_address>0){
                        $p_engine->fpdf->AddPage();
                    }
                                        
                    $header_data = array(
                        'ri'=>$ri,
                        'customer'=>$customer,
                        'form_address'=>$row_form_address,
                    );
                    
                    $footer_data = array('page_number'=>1);
                    
                    self::refill_invoice_header_print($p_engine,$header_data);

                    $product_col_width = 25;
                    $qty_col_width = 13;
                    $product_cost_col_width = 32;
                    $amount_col_width = 15;
                                        
                    $footer_start = 140;
                    for($i = 0;$i<count($ri_product);$i++){
                        $new_page = false;                    
                        $print_table_header = false;
                        $print_footer = false;


                        $product_text = $ri_product[$i]['product_marking_code'];
                        $prc_text = self::product_cost_text_get($ri_product[$i]);
                        $curr_line_height = $p_engine->fpdf->NbLines($product_cost_col_width,$prc_text) * $p_engine->fpdf->LineHeight;

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

                            if( $i+1 <count($ri_product)){
                                $next_line_height = $p_engine->fpdf->NbLines($product_cost_col_width,self::product_cost_text_get($ri_product[$i+1])) * $p_engine->fpdf->LineHeight;
                                if (($curr_y + $curr_line_height + $next_line_height)> $footer_start){
                                    $print_footer = true;
                                }
                            }
                        }

                        if($new_page){
                            $p_engine->fpdf->AddPage();
                            self::refill_invoice_header_print($p_engine,$header_data);
                            $footer_data['page_number']+=1;
                        }

                        if($print_table_header){
                            $p_engine->bold();
                            $p_engine->Cell(10,null,'NO',1,0,'C');
                            $p_engine->Cell($product_col_width,null,'Product',1,0,'C');
                            $p_engine->Cell($qty_col_width,null,'Qty',1,0,'C');
                            $p_engine->Cell($product_cost_col_width,null,'Cost Detail',1,0,'C');
                            $p_engine->Cell($amount_col_width,null,'Amount',1,0,'C');
                            
                            $p_engine->Ln();
                            $p_engine->normal();
                        }

                        
                        $p_engine->font_set('Times',7,'');
                        $left_x = $p_engine->fpdf->GetX();
                        $top_y = $p_engine->fpdf->GetY();
                        $curr_x = $p_engine->fpdf->GetX();
                        $col_width = 10;
                        $p_engine->MultiCell($col_width,null,$i+1,0,'C');
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
                        $p_engine->MultiCell($col_width,null,
                            Tools::thousand_separator($ri_product[$i]['qty']).' '.$ri_product[$i]['unit_code'],
                        0,'R');
                        $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                        $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                        $curr_x = $p_engine->fpdf->GetX();
                        $col_width = $product_cost_col_width;
                        $p_engine->MultiCell($col_width,null,
                            $prc_text,
                        0,'L');
                        $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                        $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);
                        
                        $curr_x = $p_engine->fpdf->GetX();
                        $col_width = $amount_col_width;
                        $p_engine->MultiCell($col_width,null,
                            Tools::thousand_separator($ri_product[$i]['amount'],0),
                        0,'R');
                        $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                        $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                        $p_engine->fpdf->SetXY($left_x,$top_y+$curr_line_height);
                        


                        if($print_footer){
                            self::refill_invoice_footer_print($p_engine,$footer_data);
                        }
                    }
                    
                    //<editor-fold defaultstate="collapsed" desc="Calculation">
                    $curr_y = $p_engine->fpdf->GetY();
                    $calculation_start = 120;
                    $calc_desc_col_width = 80;
                    $calc_amount_col_width = 15;
                    if($curr_y> $calculation_start){
                        $p_engine->fpdf->AddPage();
                        self::refill_invoice_header_print($p_engine,$header_data);
                        $footer_data['page_number']+=1;
                    }
                    $grand_total_amount = $ri['grand_total_amount'];
                    
                    $p_engine->bold();
                    $p_engine->Cell($calc_desc_col_width,null,'Total',0,0,'R');
                    $p_engine->normal();
                    $p_engine->Cell($calc_amount_col_width,null,Tools::thousand_separator($grand_total_amount,0),0,0,'R');
                    $p_engine->Ln();
                    
                    $p_engine->Cell(0,null,'-----------------------------------------------',0,0,'R');
                    $p_engine->Ln();
                    
                    
                    $p_engine->bold();
                    $p_engine->Cell($calc_desc_col_width,null,Lang::get('Grand Total Amount'),0,0,'R');
                    $p_engine->normal();
                    $p_engine->Cell($calc_amount_col_width,null,Tools::thousand_separator($grand_total_amount,0),0,0,'R');
                    $p_engine->Ln();

                    //</editor-fold>
                    
                    
                    self::refill_invoice_footer_print($p_engine,$footer_data);
                    
                }
                $p_engine->output(str_replace('/','',$ri['code']).'.pdf','I');
            }
            //</editor-fold>
        }
                
        public static function refill_payment_header_print($p_engine,$data){
            //<editor-fold defaultstate="collapsed">
            $ri = $data['ri']; //refill invoice
            $customer = $data['customer']; //customer
            
            $ri_date = Tools::_date($ri['refill_invoice_date'],'F d, Y',null,array('LC_TIME'=>'ID'));
            
            $p_engine->font_set('Times',8,'');
            $p_engine->bold();
            $p_engine->Cell(20,null,Lang::prt_get('Refill Payment'));
            $p_engine->normal();
            $p_engine->Cell(60,null,'');
            $p_engine->bold();
            $p_engine->Cell(0,null,$data['form_address'],1,0,'C');
            $p_engine->normal();
            $p_engine->Ln();
            
            $p_engine->Cell(0,null,Lang::prt_get('Refill Invoice').': '.$ri['code'].' - ' .$ri_date);
            $p_engine->Cell(0,null,$customer['name'].' - '.$customer['phone'],0,0,'R');
            
            $p_engine->Ln();
            
            
            $p_engine->Cell(0,null,substr($customer['address'].' '.$customer['city'],0,40),0,0,'R');
            $p_engine->Ln();
            
            $p_engine->Ln();
            //</editor-fold>
        }
        
        public static function refill_payment_footer_print($p_engine,$data){
            //<editor-fold defaultstate="collapsed">
            $p_engine->fpdf->SetXY($p_engine->fpdf->GetX(),140);
            $p_engine->Cell(0,null,''.Tools::_date('','F d, Y H:i:s'), 0, 0);
            $p_engine->Cell(0,null,'Page '.$data['page_number'], 0, 0,'R');
            //</editor-fold>
        }
                
        function payment_print($refill_invoice_id){
            //<editor-fold defaultstate="collapsed">
            
            get_instance()->load->helper('refill_invoice/refill_invoice_data_support');
            get_instance()->load->helper('customer/customer_data_support');
            get_instance()->load->helper('expedition/expedition_data_support');
            $success = 1;
            $db = new DB();
            $ri = Refill_Invoice_Data_Support::refill_invoice_get($refill_invoice_id);
            if(! count($ri) > 0) $success = 0;

            if($success == 1){
                $refill_payment = self::refill_payment_get($refill_invoice_id);
                $customer = Customer_Data_Support::customer_get($ri['customer_id']);
                $form_address = array('Customer');
                
                if( count($refill_payment) > 0 ){
                
                    $p_engine = new Printer('fpdf_refill_payment_print');
                    $p_engine->paper_set('thin-man');
                    $p_engine->start();


                    foreach($form_address as $i_form_address=>$row_form_address){
                        if($i_form_address>0){
                            $p_engine->fpdf->AddPage();
                        }

                        $header_data = array(
                            'ri'=>$ri,
                            'customer'=>$customer,
                            'form_address'=>$row_form_address
                        );

                        $footer_data = array('page_number'=>1);

                        self::refill_payment_header_print($p_engine,$header_data);

                        $payment_type_col_width = 25;
                        $payment_date_col_width = 35;
                        $amount_col_width = 25;

                        $footer_start = 140;
                        for($i = 0;$i<count($refill_payment);$i++){
                            $new_page = false;                    
                            $print_table_header = false;
                            $print_footer = false;

                            $curr_line_height = $p_engine->fpdf->LineHeight;

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

                                if( $i+1 <count($refill_payment)){
                                    $next_line_height = $p_engine->fpdf->LineHeight;
                                    if (($curr_y + $curr_line_height + $next_line_height)> $footer_start){
                                        $print_footer = true;
                                    }
                                }
                            }

                            if($new_page){
                                $p_engine->fpdf->AddPage();
                                self::refill_payment_header_print($p_engine,$header_data);
                                $footer_data['page_number']+=1;
                            }

                             if($print_table_header){
                                $p_engine->bold();
                                $p_engine->Cell(10,null,'NO',1,0,'C');
                                $p_engine->Cell($payment_type_col_width,null,'Type',1,0,'C');
                                $p_engine->Cell($payment_date_col_width,null,'Date',1,0,'C');
                                $p_engine->Cell($amount_col_width,null,'Amount',1,0,'C');
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
                            $col_width = $payment_type_col_width;
                            $p_engine->MultiCell($col_width,null,
                                $refill_payment[$i]['payment_type'],
                            0,'L');
                            $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                            $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                            $curr_x = $p_engine->fpdf->GetX();
                            $col_width = $payment_date_col_width;
                            $p_engine->MultiCell($col_width,null,
                                Tools::_date($refill_payment[$i]['payment_date'],'F d, Y'),
                            0,'L');
                            $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                            $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                            $curr_x = $p_engine->fpdf->GetX();
                            $col_width = $amount_col_width;
                            $p_engine->MultiCell($col_width,$curr_line_height,
                                Tools::thousand_separator($refill_payment[$i]['amount']),
                            0,'R');
                            $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                            $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                            $p_engine->fpdf->SetXY($left_x,$top_y+$curr_line_height);

                            if($print_footer){
                                self::refill_payment_footer_print($p_engine,$footer_data);
                            }
                        }

                        //<editor-fold defaultstate="collapsed" desc="Calculation">
                        $curr_y = $p_engine->fpdf->GetY();
                        $calculation_start = 120;
                        $calc_desc_col_width = 70;
                        $calc_amount_col_width = 25;
                        if($curr_y> $calculation_start){
                            $p_engine->fpdf->AddPage();
                            self::refill_payment_header_print($p_engine,$header_data);
                            $footer_data['page_number']+=1;
                        }

                        $total_refill_payment = Tools::_float('0');
                        foreach($refill_payment as $i=>$row){
                            $total_refill_payment+=Tools::_float($row['amount']);
                        }

                        $p_engine->bold();
                        $p_engine->Cell($calc_desc_col_width,null,'Total',0,0,'R');
                        $p_engine->normal();
                        $p_engine->Cell($calc_amount_col_width,null,Tools::thousand_separator($total_refill_payment),0,0,'R');
                        $p_engine->Ln();


                        //</editor-fold>

                        self::refill_payment_footer_print($p_engine,$footer_data);

                    }
                    $p_engine->output(str_replace('/','',$ri['code']).'.pdf','I');
                }
            }

            //</editor-fold>
        }
        
        function refill_payment_get($refill_invoice_id){
            //<editor-fold defaultstate="collapsed">
            $result = array();
            $db = new DB();
            $q = '
                select payment_type, payment_date, sum(allocated_amount) amount
                from(
                    select 
                        "Customer Deposit" payment_type,
                        date_format(cd.customer_deposit_date,"%Y-%m-%d") payment_date,
                        cda.allocated_amount
                    from customer_deposit cd
                        inner join customer_deposit_allocation cda on cd.id = cda.customer_deposit_id
                    where cda.refill_invoice_id = '.$db->escape($refill_invoice_id).'
                        and cda.customer_deposit_allocation_status = "invoiced"
                ) tf
                group by payment_type, payment_date
                order by payment_date desc, payment_type asc
            ';
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                $rs = json_decode(json_encode($rs),true);
                $result = array_merge($result, $rs);
            }
            
            $q = '
                select payment_type, payment_date, sum(allocated_amount) amount
                from(
                    select 
                        pt.code payment_type,
                        date_format(sr.refill_receipt_date,"%Y-%m-%d") payment_date,
                        sra.allocated_amount
                    from refill_receipt sr
                        inner join refill_receipt_allocation sra on sr.id = sra.refill_receipt_id
                        inner join payment_type pt on sr.payment_type_id = pt.id
                    where sra.refill_invoice_id = '.$db->escape($refill_invoice_id).'
                        and sra.refill_receipt_allocation_status = "invoiced"
                ) tf
                group by payment_type, payment_date
                order by payment_date desc, payment_type asc
            ';
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                $rs = json_decode(json_encode($rs),true);
                $result = array_merge($result, $rs);
            }
            
            return $result;
            //</editor-fold>
        }
        
        static function product_cost_text_get($product){
            $result = '';
            foreach($product['product_recondition_cost'] as $idx=>$row){
                $result.=(($result==='')?'':"\n").$row['product_recondition_name'].' - '.Tools::thousand_separator($row['amount'],0);
            }
            
            foreach($product['product_sparepart_cost'] as $idx=>$row){
                $result.=(($result==='')?'':"\n").$row['product_code'].' - '.Tools::thousand_separator($row['amount'],0);
            }
            
            return $result;
        }
    }

?>