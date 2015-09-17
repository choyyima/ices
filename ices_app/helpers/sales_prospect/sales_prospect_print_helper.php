<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class fpdf_sales_prospect_print extends extended_fpdf{
    public function footer(){

    }
}


class Sales_Prospect_Print{

    
    function sales_prospect_header_print($p_engine,$data){
        //<editor-fold defaultstate="collapsed">
        $sp = $data['sp']; //sales invoice
        $sp_info = $data['sp_info']; //sales_invoice_info
        $customer = $data['customer']; //customer
        $expedition = $data['expedition'];
        
        $left_x = $p_engine->fpdf->GetX();
        $top_y = $p_engine->fpdf->GetY();
        $logo_width = $p_engine->fpdf->page_width_get() / 4;
        $logo_height = 20;
        $logo_x = $p_engine->fpdf->page_width_get() + $p_engine->fpdf->lMargin - $logo_width;
        $p_engine->fpdf->Image($data['logo'],$logo_x,null,$logo_width,$logo_height);
        $p_engine->fpdf->SetXY($left_x,$top_y);
        
        $sp_date = Tools::_date($sp['sales_prospect_date'],'d F Y',null,array('LC_TIME'=>'ID'));

        
        $p_engine->fpdf->FontSizePt+=6;
        $p_engine->bold();
        $p_engine->Cell(null,null,'');
        $p_engine->Ln();
        $p_engine->Ln();
        
        $p_engine->Cell(50,null,Lang::prt_get('PROFORMA INVOICE'));
        $p_engine->fpdf->FontSizePt-=6;
        $p_engine->normal();
        $p_engine->Ln();
        
        $p_engine->set_xy($p_engine->fpdf->GetX(),30);
        
        $curr_x = $p_engine->fpdf->GetX();
        $top_x = $p_engine->fpdf->GetX(); 
        $top_y = $p_engine->fpdf->GetY();
        $line_height = $p_engine->fpdf->LineHeight;
        $col_width = $p_engine->fpdf->page_width_get()*12/100;
        $p_engine->MultiCell($col_width,null,
            'Name / Company'."\n".'Address'."\n".'Phone'."\n".'Email',
        0,'R');
        $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);
        
        $curr_x = $p_engine->fpdf->GetX();
        $col_width = $p_engine->fpdf->page_width_get()*48/100;
        $p_engine->MultiCell($col_width,null,
            $customer['name']
            ."\n".$customer['address']
                .(is_null(Tools::empty_to_null($customer['city']))?'':', '.$customer['city'])
            ."\n".$customer['phone']."\n".$customer['email'],
        0,'J');
        $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);
        
        $curr_x = $p_engine->fpdf->GetX();
        $col_width = $p_engine->fpdf->page_width_get()*40/100;
        $p_engine->MultiCell($col_width,null,
            'Mangga Dua B2/11, Jl. Jagir Wonokromo'
            ."\n".'Surabaya, East Java 60244'
            ."\n".'031 848 2008 | 031 843 1499'
            ."\n".'mkt.hanselindo@gmail.com'
            ,
        0,'R');
        $p_engine->set_xy($curr_x,$p_engine->fpdf->GetY() + 10);
        
        
        $curr_x = $p_engine->fpdf->GetX();
        $col_width = $p_engine->fpdf->page_width_get()*40/100;        
        $p_engine->MultiCell($col_width,null,
            'No Quotation: '.$sp['code']
            ."\n".'Quotation Date: '.$sp_date
            ,
        0,'R');
        $p_engine->set_xy($top_x,$p_engine->fpdf->GetY());
        $p_engine->Ln();
        $p_engine->Cell(null,null,'Quotation for:',0,'J');
        $p_engine->Ln();
        //</editor-fold>
    }
        
    function product_table_print($param = array()){
        //<editor-fold defaultstate="collapsed">
        $p_engine = $param['p_engine'];
        $col_width = $param['col_width'];
        $product_table_height  = $param['product_table_height'];
        $p_engine->fpdf->Rect(
            $p_engine->fpdf->GetX(),
            $p_engine->fpdf->GetY(),
            $p_engine->fpdf->page_width_get(),$p_engine->fpdf->LineHeight
        );

        $p_engine->bold();
        $p_engine->fpdf->Rect(
            $p_engine->fpdf->GetX(),
            $p_engine->fpdf->GetY(),
            $col_width['row'],$product_table_height
        );
        $p_engine->Cell($col_width['row'],null,'NO',0,0,'C');

        $p_engine->fpdf->Rect(
            $p_engine->fpdf->GetX(),
            $p_engine->fpdf->GetY(),
            $col_width['product'],$product_table_height
        );
        $p_engine->Cell($col_width['product'],null,'Unit Description',0,0,'C');

        $p_engine->fpdf->Rect(
            $p_engine->fpdf->GetX(),
            $p_engine->fpdf->GetY(),
            $col_width['status'],$product_table_height
        );
        $p_engine->Cell($col_width['status'],null,'Status',0,0,'C');
        
        $p_engine->fpdf->Rect(
            $p_engine->fpdf->GetX(),
            $p_engine->fpdf->GetY(),
            $col_width['qty'],$product_table_height
        );
        $p_engine->Cell($col_width['qty'],null,'Qty',0,0,'R');

        $p_engine->fpdf->Rect(
            $p_engine->fpdf->GetX(),
            $p_engine->fpdf->GetY(),
            $col_width['amount'],$product_table_height
        );
        $p_engine->Cell($col_width['amount'],null,'Unit Price('.Tools::currency_get().')',0,0,'C');

        $p_engine->fpdf->Rect(
            $p_engine->fpdf->GetX(),
            $p_engine->fpdf->GetY(),
            $col_width['subtotal'],$product_table_height
        );
        $p_engine->Cell($col_width['subtotal'],null,'Total('.Tools::currency_get().')',0,0,'C');
        $p_engine->Ln();
        $p_engine->normal();
        //</editor-fold>
    }
    
    function additional_info_table_print($param = array()){
        //<editor-fold defaultstate="collapsed">
        $p_engine = $param['p_engine'];
        $col_width = $param['col_width'];
        $table_height  = $param['table_height'];
        
        $top_x = $p_engine->fpdf->GetX();
        
        $p_engine->fpdf->Rect(
            $p_engine->fpdf->GetX(),
            $p_engine->fpdf->GetY(),
            $col_width['row'],$table_height
        );
        $p_engine->set_xy($p_engine->fpdf->GetX()+$col_width['row'],$p_engine->fpdf->GetY());

        $p_engine->fpdf->Rect(
            $p_engine->fpdf->GetX(),
            $p_engine->fpdf->GetY(),
            $col_width['product'],$table_height
        );
        $p_engine->set_xy($p_engine->fpdf->GetX()+$col_width['product'],$p_engine->fpdf->GetY());

        $p_engine->fpdf->Rect(
            $p_engine->fpdf->GetX(),
            $p_engine->fpdf->GetY(),
            $col_width['status'],$table_height
        );
        $p_engine->set_xy($p_engine->fpdf->GetX()+$col_width['status'],$p_engine->fpdf->GetY());

        
        $p_engine->fpdf->Rect(
            $p_engine->fpdf->GetX(),
            $p_engine->fpdf->GetY(),
            $col_width['qty'],$table_height
        );
        $p_engine->set_xy($p_engine->fpdf->GetX()+$col_width['qty'],$p_engine->fpdf->GetY());

        $p_engine->fpdf->Rect(
            $p_engine->fpdf->GetX(),
            $p_engine->fpdf->GetY(),
            $col_width['amount'],$table_height
        );
        $p_engine->set_xy($p_engine->fpdf->GetX()+$col_width['amount'],$p_engine->fpdf->GetY());

        $p_engine->fpdf->Rect(
            $p_engine->fpdf->GetX(),
            $p_engine->fpdf->GetY(),
            $col_width['subtotal'],$table_height
        );
        $p_engine->set_xy($p_engine->fpdf->GetX()+$col_width['subtotal'],$p_engine->fpdf->GetY());
        
        $p_engine->set_xy($top_x,$p_engine->fpdf->GetY());
        //</editor-fold>
    }
    
    function prospect_print($sales_prospect_id,$file_location='',$dest=''){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('sales_prospect/sales_prospect_data_support');
        get_instance()->load->helper('customer/customer_data_support');
        get_instance()->load->helper('expedition/expedition_data_support');
        get_instance()->load->helper('product_stock_engine');
        $result = array('success'=>1,'msg'=>array());
        $success = 1;
        $msg = array();
        $db = new DB();
        $sp = Sales_Prospect_Data_Support::sales_prospect_get($sales_prospect_id);
        if(!count($sp)>0){
            $success = 0;
            $msg[] = 'Sales Prospect Data does not exist';
        }
        
        if($success === 1 && $sp['sales_prospect_status'] !== 'X'){  
            $sp_info = Sales_Prospect_Data_Support::sales_prospect_info_get($sales_prospect_id);    
            $sp_product = Sales_Prospect_Data_Support::sales_prospect_product_get($sp['id']);
            $customer = Customer_Data_Support::customer_get($sp['customer_id']);
            $additional_cost = Sales_Prospect_Data_Support::additional_cost_get($sp['id']);
            $expedition = Expedition_Data_Support::expedition_get($sp_info['expedition_id']);
                
            $p_engine = new Printer('fpdf_sales_prospect_print');
            $p_engine->paper_set('A4');
            $p_engine->start();
            $p_engine->fpdf->FontSizePt = 8;

            $fName = $p_engine->fpdf->FontFamily;
            $fSize = $p_engine->fpdf->FontSizePt;
            $lHeight = $p_engine->fpdf->LineHeight;
            $pWidth = $p_engine->fpdf->page_width_get();
            $pHeight = $p_engine->fpdf->page_height_get();

            //<editor-fold defaultstate="collapsed" desc="Sales Prospect">
            $header_data = array(
                'logo'=>'img/hanselindo_logo.jpg',
                'sp'=>$sp,
                'sp_info'=>$sp_info,
                'customer'=>$customer,
                'expedition'=>$expedition, 
            );
            
            $footer_data = array('page_number'=>1);
            
            self::sales_prospect_header_print($p_engine,$header_data);
            
            $product_table_col_width = array(
                'row'=>$p_engine->fpdf->page_width_get()*5/100,
                'product'=>$p_engine->fpdf->page_width_get()*35/100,
                'status'=>$p_engine->fpdf->page_width_get()*10/100,
                'qty'=>$p_engine->fpdf->page_width_get()*10/100,
                'amount'=>$p_engine->fpdf->page_width_get()*20/100,
                'subtotal'=>$p_engine->fpdf->page_width_get()*20/100,
            );
            
            
            $product_table_top_y = $p_engine->fpdf->GetY();
            $product_table_height = 80;
            $additional_info_table_height = 40;            
            $footer_start = $p_engine->fpdf->page_height_get()-20;
            $calc_desc_col_width = $p_engine->fpdf->page_width_get() * 20/100;
            $calc_amount_col_width = $p_engine->fpdf->page_width_get() * 20/100;
            $subtotal = Tools::_float('0');
           
            
            for($i = 0;$i<count($sp_product);$i++){
                //<editor-fold defaultstate="collapsed">
                $new_page = false;                    
                $print_table_header = false;
                $print_footer = false;
                
                $product_text = self::product_text_get($sp_product[$i]);
                $curr_line_height = $p_engine->fpdf->NbLines($product_table_col_width['product'],$product_text) * $p_engine->fpdf->LineHeight;

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

                    if( $i+1 <count($sp_product)){
                        $next_line_height = $p_engine->fpdf->NbLines($product_table_col_width['product'],self::product_text_get($sp_product[$i+1])) * $p_engine->fpdf->LineHeight;
                        if (($curr_y + $curr_line_height + $next_line_height)> $footer_start){
                            $print_footer = true;
                        }
                    }
                }
                
                if($new_page){
                    $p_engine->fpdf->AddPage();
                    self::sales_prospect_header_print($p_engine,$header_data);
                    $footer_data['page_number']+=1;
                }

                if($print_table_header){
                    //<editor-fold defaultstate="collapsed">
                    
                    self::product_table_print(
                        array('p_engine'=>$p_engine,
                            'col_width'=>$product_table_col_width,
                            'product_table_height'=>$product_table_height
                        )
                    );
                    
                    //</editor-fold>
                }
                
                $left_x = $p_engine->fpdf->GetX();
                $top_y = $p_engine->fpdf->GetY();
                $curr_x = $p_engine->fpdf->GetX();
                $col_width = $product_table_col_width['row'];
                $p_engine->MultiCell($col_width,null,$i+1,0);
                $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);


                $curr_x = $p_engine->fpdf->GetX();
                $col_width = $product_table_col_width['product'];
                $p_engine->MultiCell($col_width,null,
                    $product_text,
                0,'L');
                $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                $product_status = Product_Stock_Engine::stock_sum_get('stock_sales_available',$sp_product[$i]['product_id'],$sp_product[$i]['unit_id'])>0?
                    'Ready':'Inden';
                $curr_x = $p_engine->fpdf->GetX();
                $col_width = $product_table_col_width['status'];
                $p_engine->MultiCell($col_width,null,
                    $product_status,
                0,'L');
                $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);
                
                $curr_x = $p_engine->fpdf->GetX();
                $col_width = $product_table_col_width['qty'];
                $p_engine->MultiCell($col_width,null,
                    Tools::thousand_separator($sp_product[$i]['qty']).' '.$sp_product[$i]['unit_code'],
                0,'R');
                $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                $curr_x = $p_engine->fpdf->GetX();
                $col_width = $product_table_col_width['amount'];
                $p_engine->MultiCell($col_width,null,
                    Tools::thousand_separator($sp_product[$i]['amount']),
                0,'R');
                $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                $curr_x = $p_engine->fpdf->GetX();
                $col_width = $product_table_col_width['subtotal'];
                $p_engine->MultiCell($col_width,null,
                    Tools::thousand_separator($sp_product[$i]['subtotal']),
                0,'R');
                $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);
                $subtotal+=Tools::_float($sp_product[$i]['subtotal']);
                
                $p_engine->fpdf->SetXY($left_x,$top_y+$curr_line_height);
                
                if($print_footer){
                    self::sales_prospect_footer_print($p_engine,$footer_data);
                }
                //</editor-fold>
                
            }            
            $p_engine->set_xy($p_engine->fpdf->GetX(),$product_table_top_y+$product_table_height);
            
            if(Tools::_float($sp['delivery_cost_estimation'])>Tools::_float('0') || 
                count($additional_cost)>0
            ){
                $additional_info_table_top_y = $p_engine->fpdf->GetY();
                
                self::additional_info_table_print(
                    array('p_engine'=>$p_engine,
                        'col_width'=>$product_table_col_width,
                        'table_height'=>$additional_info_table_height
                    )
                );
            
                $row_num = 1;
                
                if(Tools::_float($sp['delivery_cost_estimation'])>Tools::_float('0')){
                    //<editor-fold defaultstate="collapsed">
                    $left_x = $p_engine->fpdf->GetX();
                    $top_y = $p_engine->fpdf->GetY();
                    $curr_x = $p_engine->fpdf->GetX();
                    $col_width = $product_table_col_width['row'];
                    $p_engine->MultiCell($col_width,null,$row_num,0);
                    $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);


                    $curr_x = $p_engine->fpdf->GetX();
                    $col_width = $product_table_col_width['product']
                        +$product_table_col_width['status']
                        +$product_table_col_width['qty']
                        +$product_table_col_width['amount']
                    ;
                    $p_engine->MultiCell($col_width,null,
                        Lang::get('Delivery Cost Estimation'),
                    0,'L');
                    $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                    $curr_x = $p_engine->fpdf->GetX();
                    $col_width = $product_table_col_width['subtotal'];
                    $p_engine->MultiCell($col_width,null,
                        Tools::thousand_separator($sp['delivery_cost_estimation']),
                    0,'R');
                    $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);
                    $subtotal+=Tools::_float($sp['delivery_cost_estimation']);

                    $p_engine->fpdf->SetXY($left_x,$top_y+$p_engine->fpdf->LineHeight);
                    $row_num+=1;
                    
                    //</editor-fold>
                }
                
                foreach($additional_cost as $i=>$row){
                    //<editor-fold defaultstate="collapsed">
                    $left_x = $p_engine->fpdf->GetX();
                    $top_y = $p_engine->fpdf->GetY();
                    $curr_x = $p_engine->fpdf->GetX();
                    $col_width = $product_table_col_width['row'];
                    $p_engine->MultiCell($col_width,null,$row_num,0);
                    $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);


                    $curr_x = $p_engine->fpdf->GetX();
                    $col_width = $product_table_col_width['product']
                        +$product_table_col_width['status']
                        +$product_table_col_width['qty']
                        +$product_table_col_width['amount']
                    ;
                    $p_engine->MultiCell($col_width,null,
                        $row['description'],
                    0,'L');
                    $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                    $curr_x = $p_engine->fpdf->GetX();
                    $col_width = $product_table_col_width['subtotal'];
                    $p_engine->MultiCell($col_width,null,
                        Tools::thousand_separator($row['amount']),
                    0,'R');
                    $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);
                    $subtotal+=Tools::_float($row['amount']);

                    $p_engine->fpdf->SetXY($left_x,$top_y+$p_engine->fpdf->LineHeight);
                    $row_num+=1;
                    //</editor-fold>
                }
                
                $p_engine->set_xy($p_engine->fpdf->GetX(),$additional_info_table_top_y+$additional_info_table_height);
            }
            
            $left_width_empty = $product_table_col_width['row']
                +$product_table_col_width['status']
                +$product_table_col_width['product']
                +$product_table_col_width['qty']
            ;
            
            
            $p_engine->Cell($left_width_empty,null,'',0,0,'R');
            $p_engine->bold();
            $p_engine->Cell($product_table_col_width['amount'],null,Lang::get('Subtotal'),1,0,'R');
            $p_engine->normal();
            $p_engine->Cell($calc_amount_col_width,null,Tools::thousand_separator($subtotal),1,0,'R');
            $p_engine->Ln();
            
            $p_engine->Cell($left_width_empty,null,'',0,0,'R');
            $p_engine->bold();
            $p_engine->Cell($product_table_col_width['amount'],null,Lang::get('Charge'),1,0,'R');
            $p_engine->normal();
            $p_engine->Cell($calc_amount_col_width,null,Tools::thousand_separator($sp['extra_charge']),1,0,'R');
            $p_engine->Ln();
            
            $p_engine->Cell($left_width_empty,null,'',0,0,'R');
            $p_engine->bold();
            $p_engine->Cell($product_table_col_width['amount'],null,Lang::get('Total'),1,0,'R');
            $p_engine->normal();
            $p_engine->Cell($calc_amount_col_width,null,Tools::thousand_separator($sp['grand_total']),1,0,'R');
            $p_engine->Ln();
            
            $p_engine->MultiCell($p_engine->fpdf->page_width_get()*20/100, null, 
                'Beneficiary'
                ."\n".'Leonard Daniel Tofasey'
                
            );
            
            $top_x = $p_engine->fpdf->GetX();
            $top_y = $p_engine->fpdf->GetY();
            $curr_x = $p_engine->fpdf->GetX();
            $curr_width = $p_engine->fpdf->page_width_get()*40/100;
            $p_engine->MultiCell($curr_width, null, 
                'Bank Mandiri, Ancol, Jakarta'
                ."\n".'Account No. 12000 - 6545 - 649'                
            );
            $p_engine->set_xy($curr_x+$curr_width,$top_y);
            
            $curr_x = $p_engine->fpdf->GetX();
            $curr_width = $p_engine->fpdf->page_width_get()*40/100;
            $p_engine->MultiCell($curr_width, null, 
                'Bank BCA Darmo, Surabaya'
                ."\n".'Account No. 088 - 527 - 8605'                
            );
            $p_engine->Ln();
            
            $p_engine->Cell(null,null,'The order will be executed according to our terms of payment and our conditions of sale and delivery','TB',1);
            $p_engine->Ln();
            
            $p_engine->bold();
            $top_x = $p_engine->fpdf->GetX();
            $top_y = $p_engine->fpdf->GetY();
            $curr_x = $p_engine->fpdf->GetX();
            $curr_width = $p_engine->fpdf->page_width_get()*15/100;
            $p_engine->MultiCell($curr_width, null, 
                'Delivery Point'
                ."\n".'Payment Terms'
                ."\n".'Price & Stocks'
            );
            $p_engine->set_xy($curr_x+$curr_width,$top_y);
            
            $curr_x = $p_engine->fpdf->GetX();
            $curr_width = $p_engine->fpdf->page_width_get()*40/100;
            $p_engine->MultiCell($curr_width, null, 
                'EXW Surabaya'
                ."\n".'Cash Full Amount'
                ."\n".'Tentative'                
            );
            $p_engine->normal();
            
            $p_engine->Cell(null,null,'Unit supply only without installment, maintenance, & training');
            $p_engine->Ln();
                        
            
            $top_x = $p_engine->fpdf->GetX();
            $top_y = $p_engine->fpdf->GetY();
            $curr_x = $p_engine->fpdf->GetX();
            $curr_width = $p_engine->fpdf->page_width_get()*95/100;
            $p_engine->MultiCell($curr_width, null, 
                'Sincerely,'
                ."\n"
                ."\n"
                
                ."\n".User_Info::get()['name'],
                0,'R'
            );
                       
            
            //</editor-fold>
            
            if($dest === 'F'){
                $p_engine->output($file_location,'F');
            }
            else{
                $p_engine->output(str_replace('/','',$sp['code']).'.pdf','I');
            }

        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
    }

    function product_text_get($row){
        $result = '';
        return $row['product_code'].' '.$row['product_name']
            .(Tools::empty_to_null($row['product_additional_info']) === null?
                '':"\n".$row['product_additional_info']);
    }

}

?>