<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class fpdf_stock_opname_print extends extended_fpdf{
    public function footer(){

    }
}

class Print_Form_Print{
    function __construct(){}
    
    
    
    private static function stock_opname_header_print($p_engine,$opt){
        //<editor-fold defaultstate="collapsed">

        $curr_date = Tools::_date('','F d, Y H:i:s',null,array('LC_TIME'=>'ID'));
        
        $p_engine->font_set('Times',10);
        $p_engine->bold();
        $p_engine->Cell(30,null,Lang::prt_get('STOCK OPNAME'));
        $p_engine->normal();
        
        $p_engine->font_set('Times',8);
        $p_engine->Cell(30,null,' - '.Lang::prt_get($curr_date),0,0,'L');
        
        $p_engine->Cell(0,null,'Warehouse: ..................................',0,0,'L');
        $p_engine->normal();
        $p_engine->Ln();
        
        $p_engine->Ln();
        //</editor-fold>
    }
    
    private static function stock_opname_footer_print($p_engine,$opt){
        //<editor-fold defaultstate="collapsed">
        $p_engine->font_set('Times',8);
        $p_engine->Ln();
        $p_engine->set_xy($p_engine->fpdf->GetX(),$opt['footer_start']);
        $p_engine->Cell(0,null,''.Tools::_date('','F d, Y H:i:s'), 0, 0);
        $p_engine->Cell(0,null,'Page '.$opt['page_number'], 0, 0,'R');
        $p_engine->Ln();
        //</editor-fold>
    }
    
    public static function stock_opname_print($opt = array('p_engine'=>null,'p_output'=>true)){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('print_form/print_form_data_support');
        $success = 1;
        $db = new DB();
        
        $product_list = Print_Form_Data_Support::product_stock_opname_product_list_get();
        
        $p_engine = isset($opt['p_engine'])?$opt['p_engine']:null;
        $p_output = isset($opt['p_output'])?$opt['p_output']:true;
        if($p_engine === null){
            $p_engine = new Printer('fpdf_stock_opname_print');
            $p_engine->paper_set('A4');
            $p_engine->start();
        }
        
        $footer_start = 277;
        $header_data = array();
        $footer_data = array('page_number'=>1,'footer_start'=>$footer_start);
        $product_col_width = array(
            'row_num'=>4,
            'product'=>19,
            'unit'=>7,
            'outstanding_qty'=>8,
            'floor_qty'=>10,
            'total_floor_qty'=>10,
            'bad_stock_qty'=>8,
        );
        
        Print_Form_Print::stock_opname_header_print($p_engine,$header_data);
        $font_content_size = 5;
        $p_engine->font_set('Times',$font_content_size,'');
        $cont_col_x_pos = array(5,105);
        $cont_col_x_idx = 0;
        $cont_col_y_pos = $p_engine->fpdf->GetY();
        
        foreach($product_list as $pl_idx=>$pl_row){
            //<editor-fold defaultstate="collapsed">
            $new_page = false;                    
            $print_table_header = false;
            $print_footer = false;
            $switch_col = false;
            
            $curr_line_height = $p_engine->fpdf->LineHeight;
            $header_line_height = $p_engine->fpdf->LineHeight * 3;
            $row_num = $pl_idx === 0?1:$row_num+1;
            
            if($pl_idx === 0){
                $row_num = 1;
                $print_table_header = true;
            }
            else{
                $curr_y = $p_engine->fpdf->GetY();
                $p_subcat_name_last = $pl_idx>0?($product_list[$pl_idx-1]['product_subcategory_name']):$product_list[$pl_idx]['product_subcategory_name'];
                $p_subcat_name = $pl_row['product_subcategory_name'];
                
                if($cont_col_x_idx < count($cont_col_x_pos)-1){
                    if((Tools::_float($curr_y)+Tools::_float($curr_line_height)) > Tools::_float($footer_start)){
                        $switch_col = true;
                    }
                    else if ($p_subcat_name_last !== $p_subcat_name){
                        $print_table_header = true;
                    }
                    
                    if($print_table_header){
                        if((Tools::_float($curr_y)+Tools::_float($header_line_height)+Tools::_float($curr_line_height)) > Tools::_float($footer_start)){
                            $switch_col = true;
                        }
                    }
                }
                else{
                    if((Tools::_float($curr_y)+Tools::_float($curr_line_height)) > Tools::_float($footer_start)){
                        $new_page = true;
                    }
                    else if ($p_subcat_name_last !== $p_subcat_name){
                        $print_table_header = true;
                        $row_num = 1;
                    }
                    
                    if($print_table_header){
                        if((Tools::_float($curr_y)+Tools::_float($header_line_height)+Tools::_float($curr_line_height)) > Tools::_float($footer_start)){
                            $new_page = true;                            
                        }
                    }
                }
                
                if($new_page) $print_table_header;
                if($switch_col) $print_table_header = true;
                if($print_table_header) $row_num = 1;
               
                
                if( $pl_idx+1 <count($product_list) && !$new_page && ($cont_col_x_idx === count($cont_col_x_pos)-1)){
                    $next_line_height = $p_engine->fpdf->LineHeight;
                    
                    if ((Tools::_float($curr_y) + Tools::_float($curr_line_height) + Tools::_float($next_line_height))> Tools::_float($footer_start)){
                        $print_footer = true;
                    }
                }
                
            }

            if($new_page){
                $p_engine->fpdf->AddPage();
                Print_Form_Print::stock_opname_header_print($p_engine,$header_data);
                $p_engine->font_set('Times',$font_content_size,'');
                $cont_col_x_idx = 0;
                $footer_data['page_number']+=1;
            }
            
            if($switch_col){
                $cont_col_x_idx+=1;
                $p_engine->set_xy($cont_col_x_pos[$cont_col_x_idx],$cont_col_y_pos);
            }
            
            if($print_table_header){
                $p_engine->bold();
                $p_engine->Ln();
                $p_engine->set_xy($cont_col_x_pos[$cont_col_x_idx],$p_engine->fpdf->GetY());
                $p_engine->Cell(0,null,$pl_row['product_subcategory_name'],0,0,'L');
                $p_engine->Ln();
                $p_engine->set_xy($cont_col_x_pos[$cont_col_x_idx],$p_engine->fpdf->GetY());
                $p_engine->Cell($product_col_width['row_num'],null,'No',1,0,'C');
                $p_engine->Cell($product_col_width['product'],null,'Product',1,0,'C');
                $p_engine->Cell($product_col_width['unit'],null,'Unit',1,0,'C');
                $p_engine->Cell($product_col_width['outstanding_qty'],null,'Outstd',1,0,'C');
                $p_engine->Cell($product_col_width['floor_qty'],null,'Lt 1',1,0,'C');
                $p_engine->Cell($product_col_width['floor_qty'],null,'Lt 2',1,0,'C');
                $p_engine->Cell($product_col_width['floor_qty'],null,'Lt 3',1,0,'C');
                $p_engine->Cell($product_col_width['floor_qty'],null,'Lt 4',1,0,'C');
                $p_engine->Cell($product_col_width['total_floor_qty'],null,'Total',1,0,'C');
                $p_engine->Cell($product_col_width['bad_stock_qty'],null,'Bad Stock',1,0,'C');
                $p_engine->Ln();
                $p_engine->normal();
            }
            
            $p_engine->set_xy($cont_col_x_pos[$cont_col_x_idx],$p_engine->fpdf->GetY());
            $p_engine->Cell($product_col_width['row_num'],$curr_line_height,$row_num,1,0,'C');
            $p_engine->Cell($product_col_width['product'],$curr_line_height,$pl_row['product_code'],1,0,'L');
            $p_engine->Cell($product_col_width['unit'],$curr_line_height,$pl_row['unit_code'],1,0,'L');
            $p_engine->Cell($product_col_width['outstanding_qty'],$curr_line_height,'',1,0,'C');
            $p_engine->Cell($product_col_width['floor_qty'],$curr_line_height,'',1,0,'C');
            $p_engine->Cell($product_col_width['floor_qty'],$curr_line_height,'',1,0,'C');
            $p_engine->Cell($product_col_width['floor_qty'],$curr_line_height,'',1,0,'C');
            $p_engine->Cell($product_col_width['floor_qty'],$curr_line_height,'',1,0,'C');
            $p_engine->Cell($product_col_width['total_floor_qty'],$curr_line_height,'',1,0,'C');
            $p_engine->Cell($product_col_width['bad_stock_qty'],$curr_line_height,'',1,0,'C');
            $p_engine->Ln();
            
            if($print_footer){
                Print_Form_Print::stock_opname_footer_print($p_engine,$footer_data);
                $p_engine->font_set('Times',$font_content_size,'');
            }
            
            //</editor-fold>
        }
        
        Print_Form_Print::stock_opname_footer_print($p_engine,$footer_data);
        
        if($p_output){
            $p_engine->output('Stock Opname Form.pdf','I');
        }
        
        //</editor-fold>
    }
    
}

?>