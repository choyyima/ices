<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class fpdf_sales_invoice_print extends extended_fpdf{
        public function footer(){
            
        }
    }
    
    class fpdf_sales_payment_print extends extended_fpdf{
        public function footer(){
            
        }
    }
    
    class Sales_Pos_Print{
        
        public static function sales_invoice_header_print($p_engine,$data){
            //<editor-fold defaultstate="collapsed">
            $si = $data['si']; //sales invoice
            $si_info = $data['si_info']; //sales_invoice_info
            $customer = $data['customer']; //customer
            $expedition = $data['expedition'];
            
            $si_date = Tools::_date($si['sales_invoice_date'],'F d, Y',null,array('LC_TIME'=>'ID'));
            
            $p_engine->font_set('Times',8,'');
            $p_engine->bold();
            $p_engine->Cell(20,null,Lang::prt_get('Sales Invoice'));
            $p_engine->normal();
            $p_engine->Cell(60,null,$si['code'].' - '.$si_date);
            $p_engine->bold();
            $p_engine->Cell(0,null,Lang::prt_get($data['form_address']),1,0,'C');
            $p_engine->normal();
            $p_engine->Ln();
            
            $p_engine->Cell(0,null,'Inquiry By: '.$si_info['sales_inquiry_by_name']);
            $p_engine->Cell(0,null,$customer['name'].' - '.$customer['phone'],0,0,'R');
            
            $p_engine->Ln();
            
            if(count($expedition)>0){
                $p_engine->Cell(0,null,'Expedition : '.$expedition['name']);      
            }
            $p_engine->Cell(0,null,substr($customer['address'].' '.$customer['city'],0,40),0,0,'R');
            $p_engine->Ln();

            $p_engine->Ln();
            //</editor-fold>
        }
        
        public static function sales_invoice_footer_print($p_engine,$data){
            //<editor-fold defaultstate="collapsed">
            $p_engine->font_set('Times',7,'');
            $line_height = $p_engine->fpdf->LineHeight;
            
            $p_engine->fpdf->SetXY($p_engine->fpdf->GetX(),120);
            $p_engine->MultiCell(0,null,'Notice:');

            $p_engine->MultiCell(0,null,'- Selisih beban ongkos kirim tetap merupakan beban pihak pembeli.');            
            $p_engine->MultiCell(0,null,'- Kerusakan / kehilangan barang yang tidak diambil lebih dari satu bulan sejak konfirmasi diluar tanggung jawab kami.');
            //$p_engine->MultiCell(0,null,'- Apabila barang tidak diambil / pending pengiriman optimal lebih dari 3 hari kerja sejak konfirmasi, maka akan dikenakan biaya administrasi & sewa gudang sebesar 5% dari nilai barang per minggu atau Rp. 150,000 / (min. 1 Cbm)');
            $p_engine->MultiCell(0,null,'- Barang yang dibeli tidak dapat dibatalkan');
            
            
            $p_engine->fpdf->SetXY($p_engine->fpdf->GetX(),140);
            $p_engine->Cell(0,null,''.Tools::_date('','F d, Y H:i:s'), 0, 0);
            $p_engine->Cell(0,null,'Page '.$data['page_number'], 0, 0,'R');
            //</editor-fold>
        }
        
        function invoice_print($id,$param = array()){
            //<editor-fold defaultstate="collapsed">
            get_instance()->load->helper('sales_pos/sales_pos_data_support');
            get_instance()->load->helper('customer/customer_data_support');
            get_instance()->load->helper('expedition/expedition_data_support');
            $result = array('success'=>1,'msg'=>array());
            $success = 1;
            $msg = array();
            $db = new DB();
            $file_location = isset($param['file_location'])?$param['file_location']:'';
            $dest = isset($param['dest'])?$param['dest']:'I';
            $form_address = isset($param['form_address'])?
                $param['form_address']:
                array('Customer','Archive')
            ;
            
            $si = Sales_Pos_Data_Support::sales_invoice_get($id);
            if(! count($si) > 0){
                $success = 0;
                $msg[] = 'Sales Invoice does not exist';
            }

            if($success == 1 && $si['sales_invoice_status'] !== 'X'){
                $si_info = Sales_Pos_Data_Support::sales_invoice_info_get($id);    
                $si_product = Sales_Pos_Data_Support::sales_invoice_product_get($si['id']);
                $customer = Customer_Data_Support::customer_get($si['customer_id']);
                $additional_cost = Sales_Pos_Data_Support::additional_cost_get($si['id']);
                $expedition = Expedition_Data_Support::expedition_get($si_info['expedition_id']);                    
                
                
                $p_engine = new Printer('fpdf_sales_invoice_print');
                $p_engine->paper_set('thin-man');
                $p_engine->start();
                    
                
                foreach($form_address as $i_form_address=>$row_form_address){
                    if($i_form_address>0){
                        $p_engine->fpdf->AddPage();
                    }
                                        
                    $header_data = array(
                        'si'=>$si,
                        'si_info'=>$si_info,
                        'customer'=>$customer,
                        'expedition'=>$expedition, 
                        'form_address'=>$row_form_address,
                        
                    );
                    
                    $footer_data = array('page_number'=>1);
                    
                    self::sales_invoice_header_print($p_engine,$header_data);

                    $product_col_width = 25;
                    $qty_col_width = 15;
                    $amount_col_width = 20;
                    $subtotal_col_width = 25;

                    $footer_start = 120;
                    for($i = 0;$i<count($si_product);$i++){
                        $new_page = false;                    
                        $print_table_header = false;
                        $print_footer = false;


                        $product_text = self::product_text_get($si_product[$i]);
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

                            if( $i+1 <count($si_product)){
                                $next_line_height = $p_engine->fpdf->NbLines($product_col_width,self::product_text_get($si_product[$i+1])) * $p_engine->fpdf->LineHeight;
                                if (($curr_y + $curr_line_height + $next_line_height)> $footer_start){
                                    $print_footer = true;
                                }
                            }
                        }

                        if($new_page){
                            $p_engine->fpdf->AddPage();
                            self::sales_invoice_header_print($p_engine,$header_data);
                            $footer_data['page_number']+=1;
                        }

                        if($print_table_header){
                            $p_engine->bold();
                            $p_engine->Cell(10,null,'NO',1,0,'C');
                            $p_engine->Cell($product_col_width,null,'Product',1,0,'C');
                            $p_engine->Cell($qty_col_width,null,'Qty',1,0,'R');
                            $p_engine->Cell($amount_col_width,null,'Amount',1,0,'C');
                            $p_engine->Cell($subtotal_col_width,null,'Subtotal',1,0,'C');
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
                            Tools::thousand_separator($si_product[$i]['qty']).' '.$si_product[$i]['unit_code'],
                        0,'R');
                        $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                        $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                        $curr_x = $p_engine->fpdf->GetX();
                        $col_width = $amount_col_width;
                        $p_engine->MultiCell($col_width,$curr_line_height,
                            Tools::thousand_separator($si_product[$i]['amount']),
                        0,'R');
                        $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                        $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                        $curr_x = $p_engine->fpdf->GetX();
                        $col_width = $subtotal_col_width;
                        $p_engine->MultiCell($col_width,$curr_line_height,
                            Tools::thousand_separator($si_product[$i]['subtotal']),
                        0,'R');
                        $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                        $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);


                        $p_engine->fpdf->SetXY($left_x,$top_y+$curr_line_height);
                        


                        if($print_footer){
                            self::sales_invoice_footer_print($p_engine,$footer_data);
                        }
                    }

                    //<editor-fold defaultstate="collapsed" desc="Calculation">
                    $curr_y = $p_engine->fpdf->GetY();
                    $calculation_start = 120;
                    $calc_desc_col_width = 70;
                    $calc_amount_col_width = 25;
                    if($curr_y> $calculation_start){
                        $p_engine->fpdf->AddPage();
                        self::sales_invoice_header_print($p_engine,$header_data);
                        $footer_data['page_number']+=1;
                    }

                    $p_engine->bold();
                    $p_engine->Cell($calc_desc_col_width,null,'Total',0,0,'R');
                    $p_engine->normal();
                    $p_engine->Cell($calc_amount_col_width,null,Tools::thousand_separator($si['total_product']),0,0,'R');
                    $p_engine->Ln();

                    if(Tools::_float($si['extra_charge'])>Tools::_float('0')){
                        $p_engine->bold();
                        $p_engine->Cell($calc_desc_col_width,null,'Extra Charge',0,0,'R');
                        $p_engine->normal();
                        $p_engine->Cell($calc_amount_col_width,null,Tools::thousand_separator($si['extra_charge']),0,0,'R');
                        $p_engine->Ln();
                    }

                    if(Tools::_float($si['delivery_cost_estimation'])>Tools::_float('0')){
                        $p_engine->bold();
                        $p_engine->Cell($calc_desc_col_width,null,Lang::prt_get('Delivery Cost Estimation'),0,0,'R');
                        $p_engine->normal();
                        $p_engine->Cell($calc_amount_col_width,null,Tools::thousand_separator($si['delivery_cost_estimation']),0,0,'R');
                        $p_engine->Ln();
                    }

                    foreach($additional_cost as $i=>$row){
                        if(Tools::_float($row['amount'])>Tools::_float('0')){
                            $p_engine->bold();
                            $p_engine->Cell($calc_desc_col_width,null,$row['description'],0,0,'R');
                            $p_engine->normal();
                            $p_engine->Cell($calc_amount_col_width,null,Tools::thousand_separator($row['amount']),0,0,'R');
                            $p_engine->Ln();
                        }
                    }

                    $p_engine->Cell(0,null,'---------------------------------------------',0,0,'R');
                    $p_engine->Ln();

                    $grand_total = $si['grand_total'];
                    $p_engine->bold();
                    $p_engine->Cell($calc_desc_col_width,null,Lang::get('Grand Total'),0,0,'R');
                    $p_engine->normal();
                    $p_engine->Cell($calc_amount_col_width,null,Tools::thousand_separator($grand_total),0,0,'R');
                    $p_engine->Ln();

                    $customer_deposit_amount = Sales_Pos_Data_Support::customer_deposit_allocation_get($si['id']);
                    if(Tools::_float($customer_deposit_amount)>Tools::_float('0')){
                        $p_engine->bold();
                        $p_engine->Cell($calc_desc_col_width,null,Lang::get('Customer Deposit Amount'),0,0,'R');
                        $p_engine->normal();
                        $p_engine->Cell($calc_amount_col_width,null,Tools::thousand_separator($customer_deposit_amount),0,0,'R');
                        $p_engine->Ln();
                    }

                    $payment_amount = Sales_Pos_Data_Support::sales_payment_total_get($si['id']);
                    $change_amount = Sales_Pos_Data_Support::sales_payment_change_amount_get($si['id']);
                    $payment_total_amount = $payment_amount+$change_amount;

                    $p_engine->bold();
                    $p_engine->Cell($calc_desc_col_width,null,Lang::get('Payment Amount'),0,0,'R');
                    $p_engine->normal();
                    $p_engine->Cell($calc_amount_col_width,null,Tools::thousand_separator($payment_total_amount),0,0,'R');
                    $p_engine->Ln();


                    if(Tools::_float($change_amount) > Tools::_float('0')){
                        $p_engine->bold();
                        $p_engine->Cell($calc_desc_col_width,null,Lang::get('Change Amount'),0,0,'R');
                        $p_engine->normal();
                        $p_engine->Cell($calc_amount_col_width,null,Tools::thousand_separator($change_amount),0,0,'R');
                        $p_engine->Ln();
                    }


                    //</editor-fold>

                    self::sales_invoice_footer_print($p_engine,$footer_data);
                    
                }
                
                if($dest === 'F'){
                    $p_engine->output($file_location,'F');
                }
                else{
                    $p_engine->output(str_replace('/','',$si['code']).'.pdf','I');
                }
            }
            $result['success'] = $success;
            $result['msg'] = $msg;
            return $result;
            //</editor-fold>
        }
                
        public static function sales_payment_header_print($p_engine,$data){
            //<editor-fold defaultstate="collapsed">
            $si = $data['si']; //sales invoice
            $si_info = $data['si_info']; //sales_invoice_info
            $customer = $data['customer']; //customer
            $expedition = $data['expedition'];
            
            $si_date = Tools::_date($si['sales_invoice_date'],'F d, Y',null,array('LC_TIME'=>'ID'));
            
            $p_engine->font_set('Times',8,'');
            $p_engine->bold();
            $p_engine->Cell(20,null,Lang::prt_get('Sales Payment'));
            $p_engine->normal();
            $p_engine->Cell(60,null,'');
            $p_engine->bold();
            $p_engine->Cell(0,null,$data['form_address'],1,0,'C');
            $p_engine->normal();
            $p_engine->Ln();
            
            $p_engine->Cell(0,null,Lang::prt_get('Sales Invoice').': '.$si['code'].' - ' .$si_date);
            $p_engine->Cell(0,null,$customer['name'].' - '.$customer['phone'],0,0,'R');
            
            $p_engine->Ln();
            
            
            $p_engine->Cell(0,null,substr($customer['address'].' '.$customer['city'],0,40),0,0,'R');
            $p_engine->Ln();
            
            $p_engine->Ln();
            //</editor-fold>
        }
        
        public static function sales_payment_footer_print($p_engine,$data){
            //<editor-fold defaultstate="collapsed">
            $p_engine->fpdf->SetXY($p_engine->fpdf->GetX(),140);
            $p_engine->Cell(0,null,''.Tools::_date('','F d, Y H:i:s'), 0, 0);
            $p_engine->Cell(0,null,'Page '.$data['page_number'], 0, 0,'R');
            //</editor-fold>
        }
                
        function payment_print($sales_invoice_id){
            //<editor-fold defaultstate="collapsed">
            
            get_instance()->load->helper('sales_pos/sales_pos_data_support');
            get_instance()->load->helper('customer/customer_data_support');
            get_instance()->load->helper('expedition/expedition_data_support');
            $success = 1;
            $db = new DB();
            $si = Sales_Pos_Data_Support::sales_invoice_get($sales_invoice_id);
            if(! count($si) > 0) $success = 0;

            if($success == 1){
                $si_info = Sales_Pos_Data_Support::sales_invoice_info_get($sales_invoice_id);
                $sales_payment = self::sales_payment_get($sales_invoice_id);
                $customer = Customer_Data_Support::customer_get($si['customer_id']);
                $additional_cost = Sales_Pos_Data_Support::additional_cost_get($si['id']);
                $expedition = Expedition_Data_Support::expedition_get($si_info['expedition_id']);                    
                $form_address = array('Customer');
                
                if( count($sales_payment) > 0 ){
                
                    $p_engine = new Printer('fpdf_sales_payment_print');
                    $p_engine->paper_set('thin-man');
                    $p_engine->start();


                    foreach($form_address as $i_form_address=>$row_form_address){
                        if($i_form_address>0){
                            $p_engine->fpdf->AddPage();
                        }

                        $header_data = array(
                            'si'=>$si,
                            'si_info'=>$si_info,
                            'customer'=>$customer,
                            'expedition'=>$expedition, 
                            'form_address'=>$row_form_address
                        );

                        $footer_data = array('page_number'=>1);

                        self::sales_payment_header_print($p_engine,$header_data);

                        $payment_type_col_width = 25;
                        $payment_date_col_width = 35;
                        $amount_col_width = 25;

                        $footer_start = 140;
                        for($i = 0;$i<count($sales_payment);$i++){
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

                                if( $i+1 <count($sales_payment)){
                                    $next_line_height = $p_engine->fpdf->LineHeight;
                                    if (($curr_y + $curr_line_height + $next_line_height)> $footer_start){
                                        $print_footer = true;
                                    }
                                }
                            }

                            if($new_page){
                                $p_engine->fpdf->AddPage();
                                self::sales_payment_header_print($p_engine,$header_data);
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
                                $sales_payment[$i]['payment_type'],
                            0,'L');
                            $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                            $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                            $curr_x = $p_engine->fpdf->GetX();
                            $col_width = $payment_date_col_width;
                            $p_engine->MultiCell($col_width,null,
                                Tools::_date($sales_payment[$i]['payment_date'],'F d, Y'),
                            0,'L');
                            $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                            $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                            $curr_x = $p_engine->fpdf->GetX();
                            $col_width = $amount_col_width;
                            $p_engine->MultiCell($col_width,$curr_line_height,
                                Tools::thousand_separator($sales_payment[$i]['amount']),
                            0,'R');
                            $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                            $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                            $p_engine->fpdf->SetXY($left_x,$top_y+$curr_line_height);

                            if($print_footer){
                                self::sales_payment_footer_print($p_engine,$footer_data);
                            }
                        }

                        //<editor-fold defaultstate="collapsed" desc="Calculation">
                        $curr_y = $p_engine->fpdf->GetY();
                        $calculation_start = 120;
                        $calc_desc_col_width = 70;
                        $calc_amount_col_width = 25;
                        if($curr_y> $calculation_start){
                            $p_engine->fpdf->AddPage();
                            self::sales_payment_header_print($p_engine,$header_data);
                            $footer_data['page_number']+=1;
                        }

                        $total_sales_payment = Tools::_float('0');
                        foreach($sales_payment as $i=>$row){
                            $total_sales_payment+=Tools::_float($row['amount']);
                        }

                        $p_engine->bold();
                        $p_engine->Cell($calc_desc_col_width,null,'Total',0,0,'R');
                        $p_engine->normal();
                        $p_engine->Cell($calc_amount_col_width,null,Tools::thousand_separator($total_sales_payment),0,0,'R');
                        $p_engine->Ln();


                        //</editor-fold>

                        self::sales_payment_footer_print($p_engine,$footer_data);

                    }
                    $p_engine->output(str_replace('/','',$si['code']).'.pdf','I');
                }
            }

            //</editor-fold>
        }
        
        function movement_print($sales_invoice_id,$f_movement_id){
            get_instance()->load->helper('sales_pos/sales_pos_data_support');
            get_instance()->load->helper('delivery_order_final/delivery_order_final_print');
            get_instance()->load->helper('intake_final/intake_final_print');
            
            $success = 1;
            $db = new DB();
            $si = Sales_Pos_Data_Support::sales_invoice_get($sales_invoice_id);
            $si_info = Sales_Pos_Data_Support::sales_invoice_info_get($sales_invoice_id);
            if(!count($si)>0) $success = 0;
            if($success === 1){                                
                $is_delivery = $si_info['is_delivery']==='1'?true:false;
                $module='';
                $module_name = '';
                $module_engine='';
                if($is_delivery){
                    Delivery_Order_Final_Print::delivery_order_final_print(null,$f_movement_id);
                    
                }
                else{
                    Intake_Final_Print::intake_final_print(null,$f_movement_id);
                }
                
            }
        }
        
        function sales_payment_get($sales_invoice_id){
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
                    where cda.sales_invoice_id = '.$db->escape($sales_invoice_id).'
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
                        date_format(sr.sales_receipt_date,"%Y-%m-%d") payment_date,
                        sra.allocated_amount
                    from sales_receipt sr
                        inner join sales_receipt_allocation sra on sr.id = sra.sales_receipt_id
                        inner join payment_type pt on sr.payment_type_id = pt.id
                    where sra.sales_invoice_id = '.$db->escape($sales_invoice_id).'
                        and sra.sales_receipt_allocation_status = "invoiced"
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
        
        function product_text_get($row){
            $result = '';
            return $row['product_code']
                .(Tools::empty_to_null($row['product_additional_info']) === null?
                    '':"\n".$row['product_additional_info']);
        }
    }

?>