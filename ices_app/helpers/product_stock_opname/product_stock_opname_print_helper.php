<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class fpdf_pso_print extends extended_fpdf{
    public function footer(){

    }
}

class Product_Stock_Opname_Print{
    function __construct(){}
    
    
    
    private static function pso_header_print($p_engine,$opt){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product_stock_opname/product_stock_opname_engine');
        $pso = $opt['pso'];
        $warehouse = $opt['warehouse'];
        $pso_date = Tools::_date($opt['pso']['product_stock_opname_date'],'F d, Y H:i:s',null,array('LC_TIME'=>'ID'));
        $pso_status_text = SI::type_get('Product_Stock_Opname_Engine', $pso['product_stock_opname_status'],'$status_list')['label'];
        $p_engine->font_set('Times',10);
        $p_engine->bold();
        $p_engine->Cell(50,null,Lang::prt_get('PRODUCT STOCK OPNAME'));
        
        $p_engine->normal();
        
        $p_engine->font_set('Times',8);
        $p_engine->Cell(40,null,$pso['code'].' - '.$pso_status_text,0,0,'L');        
        $p_engine->Cell(30,null,$pso_date,0,0,'L');
        $p_engine->Cell(0,null,'Warehouse:'.$warehouse['code'],0,0,'L');
        $p_engine->normal();
        $p_engine->Ln();
        
        $p_engine->Ln();
        //</editor-fold>
    }
    
    private static function pso_footer_print($p_engine,$opt){
        //<editor-fold defaultstate="collapsed">
        $p_engine->font_set('Times',8);
        $p_engine->set_xy($p_engine->fpdf->GetX(),$opt['footer_start']);
        $p_engine->Cell(0,null,''.Tools::_date('','F d, Y H:i:s'), 0, 0);
        $p_engine->Cell(0,null,'Page '.$opt['page_number'], 0, 0,'R');
        $p_engine->Ln();
        //</editor-fold>
    }
    
    public static function pso_print($pso_id,$opt = array('p_engine'=>null,'p_output'=>true)){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product_stock_opname/product_stock_opname_data_support');
        $success = 1;
        $msg = array();
        $db = new DB();
        
        $pso = Product_Stock_Opname_Data_Support::pso_get($pso_id);
        if(is_null($pso)){
            $success = 0;
            $msg[] = 'Data invalid';
        }
        if($success === 1){
            $warehouse = json_decode(json_encode(Warehouse_Engine::get($pso['warehouse_id'])),true);
            
            $product_list = Product_Stock_Opname_Data_Support::pso_product_get($pso_id);
        
            $p_engine = isset($opt['p_engine'])?$opt['p_engine']:null;
            $p_output = isset($opt['p_output'])?$opt['p_output']:true;
            $filename = isset($opt['filename'])?$opt['filename']:'';
            if($p_engine === null){
                $p_engine = new Printer('fpdf_pso_print');
                $p_engine->paper_set('A4');
                $p_engine->set_orientation('L');
                $p_engine->start();
            }

            $footer_start = 195;
            $header_data = array('warehouse'=>$warehouse,'pso'=>$pso);
            $footer_data = array('page_number'=>1,'footer_start'=>$footer_start);
            $product_col_width = array(
                'row_num'=>10,
                'product'=>35,
                'unit'=>10,
                'outstanding_qty'=>17,
                'floor_qty'=>17,
                'total_floor_qty'=>17,
                'bad_stock_qty'=>17,
            );

            Product_Stock_Opname_Print::pso_header_print($p_engine,$header_data);        
            $p_engine->font_set('Times',9,'');
            
            foreach($product_list as $pl_idx=>$pl_row){
                //<editor-fold defaultstate="collapsed">
                $new_page = false;                    
                $print_table_header = false;
                $print_footer = false;

                $curr_line_height = $p_engine->fpdf->LineHeight;
                $row_num = $pl_idx === 0?1:$row_num+1;

                if($pl_idx === 0){
                    $row_num = 1;
                    $print_table_header = true;
                }
                else{
                    $curr_y = $p_engine->fpdf->GetY();

                    if((Tools::_float($curr_y)+Tools::_float($curr_line_height)) > Tools::_float($footer_start)){
                        $new_page = true;
                        $print_table_header = true;
                    }
                    
                    if( $pl_idx+1 <count($product_list) && !$new_page){
                        $next_line_height = $p_engine->fpdf->LineHeight;

                        if ((Tools::_float($curr_y) + Tools::_float($curr_line_height) + Tools::_float($next_line_height))> Tools::_float($footer_start)){
                            $print_footer = true;
                        }
                    }
                }

                if($new_page){
                    $p_engine->fpdf->AddPage();
                    Product_Stock_Opname_Print::pso_header_print($p_engine,$header_data);
                    $footer_data['page_number']+=1;
                }

                if($print_table_header){
                    $p_engine->bold();
                    $p_engine->Cell($product_col_width['row_num'],null,'No',1,0,'C');
                    $p_engine->Cell($product_col_width['product'],null,'Product',1,0,'C');
                    $p_engine->Cell($product_col_width['unit'],null,'Unit',1,0,'C');
                    $p_engine->Cell($product_col_width['outstanding_qty'],null,'Old Outst.',1,0,'C');
                    $p_engine->Cell($product_col_width['outstanding_qty'],null,'Outstanding',1,0,'C');                    
                    $p_engine->Cell($product_col_width['outstanding_qty'],null,'Diff Outst.',1,0,'C');
                    $p_engine->Cell($product_col_width['floor_qty'],null,'Lt 1',1,0,'C');
                    $p_engine->Cell($product_col_width['floor_qty'],null,'Lt 2',1,0,'C');
                    $p_engine->Cell($product_col_width['floor_qty'],null,'Lt 3',1,0,'C');
                    $p_engine->Cell($product_col_width['floor_qty'],null,'Lt 4',1,0,'C');
                    $p_engine->Cell($product_col_width['total_floor_qty'],null,'Old Total',1,0,'C');
                    $p_engine->Cell($product_col_width['total_floor_qty'],null,'Total',1,0,'C');                    
                    $p_engine->Cell($product_col_width['total_floor_qty'],null,'Diff Total.',1,0,'C');
                    $p_engine->Cell($product_col_width['bad_stock_qty'],null,'Old BS',1,0,'C');
                    $p_engine->Cell($product_col_width['bad_stock_qty'],null,'Bad Stock',1,0,'C');                    
                    $p_engine->Cell($product_col_width['bad_stock_qty'],null,'Diff BS',1,0,'C');
                    $p_engine->Ln();
                    $p_engine->normal();
                }

                $p_engine->Cell($product_col_width['row_num'],$curr_line_height,$row_num,1,0,'C');
                $p_engine->Cell($product_col_width['product'],$curr_line_height,$pl_row['product_code'],1,0,'L');
                $p_engine->Cell($product_col_width['unit'],$curr_line_height,$pl_row['unit_code'],1,0,'L');
                $p_engine->Cell($product_col_width['outstanding_qty'],$curr_line_height,round($pl_row['outstanding_qty_old'],5),1,0,'R');
                $p_engine->Cell($product_col_width['outstanding_qty'],$curr_line_height,round($pl_row['outstanding_qty'],5),1,0,'R');                
                $p_engine->Cell($product_col_width['outstanding_qty'],$curr_line_height,round(Tools::_float($pl_row['outstanding_qty']) - Tools::_float($pl_row['outstanding_qty_old']),5),1,0,'R');
                $p_engine->Cell($product_col_width['floor_qty'],$curr_line_height,round($pl_row['ssa_floor_1_qty']),1,0,'R');
                $p_engine->Cell($product_col_width['floor_qty'],$curr_line_height,round($pl_row['ssa_floor_2_qty']),1,0,'R');
                $p_engine->Cell($product_col_width['floor_qty'],$curr_line_height,round($pl_row['ssa_floor_3_qty']),1,0,'R');
                $p_engine->Cell($product_col_width['floor_qty'],$curr_line_height,round($pl_row['ssa_floor_4_qty']),1,0,'R');
                $p_engine->Cell($product_col_width['total_floor_qty'],$curr_line_height,round($pl_row['stock_sales_available_qty_old']),1,0,'R');
                $p_engine->Cell($product_col_width['total_floor_qty'],$curr_line_height,round($pl_row['stock_sales_available_qty']),1,0,'R');                
                $p_engine->Cell($product_col_width['total_floor_qty'],$curr_line_height,round(Tools::_float($pl_row['stock_sales_available_qty']) - Tools::_float($pl_row['stock_sales_available_qty_old']),5),1,0,'R');
                $p_engine->Cell($product_col_width['bad_stock_qty'],$curr_line_height,round($pl_row['stock_bad_qty']),1,0,'R');
                $p_engine->Cell($product_col_width['bad_stock_qty'],$curr_line_height,round($pl_row['stock_bad_qty_old']),1,0,'R');
                $p_engine->Cell($product_col_width['bad_stock_qty'],$curr_line_height,round(Tools::_float($pl_row['stock_bad_qty']) - Tools::_float($pl_row['stock_bad_qty_old']),5),1,0,'R');
                $p_engine->Ln();

                if($print_footer){
                    Product_Stock_Opname_Print::pso_footer_print($p_engine,$footer_data);
                }

                //</editor-fold>
            }
            
            Product_Stock_Opname_Print::pso_footer_print($p_engine,$footer_data);

            if($p_output){
                $p_engine->output(str_replace('/','',$pso['code']).'.pdf','I');
            }
            else{
                $p_engine->output($filename,'F');
            }
        }
        $result = array(
            'msg'=>$msg,
            'success'=>$success
        );
        return $result;
        //</editor-fold>
    }
    
}

?>