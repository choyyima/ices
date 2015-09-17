<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class fpdf_intake_print extends extended_fpdf{
    public function footer(){

    }
}

class Intake_Print{
    function __construct(){}

    public static function intake_header_print($p_engine,$data=array()){
        //<editor-fold defaultstate="collapsed">
        $intake = $data['intake'];

        $intake_warehouse_from = $data['intake_warehouse_from'];
        $form_type = $data['form_type'];
        
        $warehouse_from = Warehouse_Engine::warehouse_get($intake_warehouse_from['warehouse_id']);
        
        $intake_date = Tools::_date($intake['intake_date'],'F d, Y',null,array('LC_TIME'=>'ID'));
        
        $p_engine->font_set('Times',8);
        $p_engine->bold();
        $p_engine->Cell(65,null,Lang::prt_get('Product Intake Note'));
        $p_engine->normal();
        $p_engine->bold();
        $p_engine->Cell(0,null,Lang::prt_get($form_type),1,0,'C');
        $p_engine->normal();
        $p_engine->Ln();
        
        
        $intake_left_info = $intake['code'].' - '.$intake_date;   
        $intake_right_info = '';
                
        switch($intake['intake_type']){
            
            case 'sales_invoice':
                get_instance()->load->helper('intake_final/intake_final_data_support');
                get_instance()->load->helper('customer/customer_data_support');
                $intakef = Intake_Data_Support::intake_final_get($intake['id']);
                $si = Intake_Final_Data_Support::sales_invoice_get($intakef['id']);
                $si_info = Sales_Pos_Data_Support::sales_invoice_info_get($si['id']);
                $customer = Customer_Data_Support::customer_get($si['customer_id']);                
                
                
                $intake_left_info .= "\n".Lang::prt_get('From').': '.$warehouse_from['name']
                        ."\n".$warehouse_from['phone']
                        ."\n".$warehouse_from['address'].' '.$warehouse_from['city']
                        ;
                
                $intake_right_info = $customer['name']." - ".$customer['phone']
                    ."\n".$customer['address'].' '.$customer['city']
                    ;
                break;            
        }
        
        $start_x = $p_engine->fpdf->GetX();
        $start_y = $p_engine->fpdf->GetY();
        $col_width = $p_engine->fpdf->page_width_get()/2;
        $block_height = $p_engine->fpdf->NbLines($col_width,$intake_left_info)>$p_engine->fpdf->NbLines($col_width, $intake_right_info)?
        $p_engine->fpdf->NbLines($col_width, $intake_left_info) * $p_engine->fpdf->LineHeight:
        $p_engine->fpdf->NbLines($col_width, $intake_right_info) * $p_engine->fpdf->LineHeight;
        $p_engine->MultiCell($col_width,null,$intake_left_info,0,'L');
        $p_engine->fpdf->SetXY($start_x+$col_width,$start_y);
        $p_engine->MultiCell($col_width,null,$intake_right_info,0,'R');
        $p_engine->fpdf->SetXY($start_x,$start_y+$block_height);
        $p_engine->Ln();
        
        //</editor-fold>
    }

    public static function intake_footer_print($p_engine,$data){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('intake/intake_data_support');
        $intake = $data['intake'];
        $intake_type = $intake['intake_type'];
        $form_address = $data['form_address'];
        
        $p_engine->font_set('Times',7);
        
        $start_x = $p_engine->fpdf->GetX();
        
        $p_engine->fpdf->SetXY($start_x,135);        
        switch($intake_type){

            case 'sales_invoice':
                if(strtolower($form_address) ==='archive'){
                    $p_engine->Cell($p_engine->fpdf->page_width_get()/2,null,'(ttd '.Lang::prt_get('Receiver').')',0,0,'C');
                    $p_engine->Cell($p_engine->fpdf->page_width_get()/2,null,'(ttd '.Lang::prt_get('Local Warehouse').')',0,0,'C');
                    $p_engine->Ln();
                }

                break;
        }
        
        $p_engine->fpdf->SetXY($start_x,140);
        $p_engine->Cell(0,null,''.Tools::_date('','F d, Y H:i:s'), 0, 0);
        $p_engine->Cell(0,null,'Page '.$data['page_number'], 0, 0,'R');
        //</editor-fold>
    }

    public static function intake_print($id,$opt = array('p_engine'=>null,'p_output'=>true)){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('intake/intake_data_support');
        $success = 1;
        $db = new DB();
        $intake = Intake_Data_Support::intake_get($id);
        
        if(count($intake)>0 ){
            if(in_array($intake['intake_status'],array('process','done'))){
                $intake_warehouse_from = Intake_Data_Support::warehouse_from_get($id);
                $intake_product = Intake_Data_Support::intake_product_get($intake['id']);
                $p_engine = isset($opt['p_engine'])?$opt['p_engine']:null;
                $p_output = isset($opt['p_output'])?$opt['p_output']:true;
                if($p_engine === null){
                    $p_engine = new Printer('fpdf_intake_print');
                    $p_engine->paper_set('thin-man');
                    $p_engine->start();
                }

                $form_type_arr = array();
                switch($intake['intake_type']){
                    case 'sales_invoice':
                        $form_type_arr = array();
                        if($intake['intake_status'] === 'process'){
                            $form_type_arr = array('Customer');
                        }
                        else if ($intake['intake_status'] === 'done'){
                            $form_type_arr = array('Archive');
                        }
                        break;
                }

                foreach($form_type_arr as $form_type_idx=>$form_type){
                    if($form_type_idx>0){
                        $p_engine->fpdf->AddPage();
                    }

                    $header_data = array(
                        'intake'=>$intake,
                        'intake_warehouse_from'=>$intake_warehouse_from,
                        'form_type'=>$form_type
                    );

                    $footer_data = array(
                        'intake'=>$intake,
                        'form_address'=>$form_type,
                        'page_number'=>1
                    );

                    Intake_Print::intake_header_print($p_engine,$header_data);

                    $row_num_col_width = 10;
                    $product_col_width = 65;
                    $qty_col_width = 20;
                    $footer_start = 135;

                    for($i = 0;$i<count($intake_product);$i++){
                        $new_page = false;                    
                        $print_table_header = false;
                        $print_footer = false;

                        $product_text = self::product_text_get($intake['intake_type'], $intake_product[$i]);
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

                            if( $i+1 <count($intake_product)){
                                $next_line_height = $p_engine->fpdf->NbLines($product_col_width,self::product_text_get($intake['intake_type'], $intake_product[$i+1])) * $p_engine->fpdf->LineHeight;
                                if (($curr_y + $curr_line_height + $next_line_height)> $footer_start){
                                    $print_footer = true;
                                }
                            }
                        }

                        if($new_page){
                            $p_engine->fpdf->AddPage();
                            Intake_Print::intake_header_print($p_engine,$header_data);
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
                            Tools::thousand_separator($intake_product[$i]['qty']).' '.$intake_product[$i]['unit_code'],
                        0,'R');
                        $p_engine->fpdf->Rect($curr_x,$top_y,$col_width,$curr_line_height);
                        $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);

                        $p_engine->fpdf->SetXY($left_x,$top_y+$curr_line_height);    

                        if($print_footer){
                            Intake_Print::intake_footer_print($p_engine,$footer_data);
                        }
                    }      

                    Intake_Print::intake_footer_print($p_engine,$footer_data);
                }

                if($p_output){
                    $p_engine->output(str_replace('/','',$intake['code']).'.pdf','I');
                }
            }
        }
        //</editor-fold>
    }
 
    function product_text_get($intake_type, $row){
        //<editor-fold defaultstate="collapsed">
        $result = '';
        switch($intake_type){
            case 'sales_invoice':
                $result = $row['product_code'].' '.$row['product_name'];
                break;
        }
        
        return $result;
        //</editor-fold>
    }

}

?>