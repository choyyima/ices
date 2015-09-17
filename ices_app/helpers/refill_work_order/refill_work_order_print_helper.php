<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Work_Order_Print {
    
    public static function refill_work_order_form_header_print($p_engine,$data){
        //<editor-fold defaultstate="collapsed">
        $rwo = $data['rwo'];
        
        $p_engine->font_set('Times',10,'B');
        $p_engine->Cell(0,null,Lang::get('KODE SPK: ').$rwo['code']);
        $p_engine->Ln();
        if($rwo['refill_work_order_status'] === 'initialized')
            $p_engine->Cell(0,null,'FORM INPUT REFILL TABUNG PEMADAM - '.Tools::_date('','F d, Y'));
        else 
            $p_engine->Cell(0,null,'FORM TANDA TERIMA & SURAT PERINTAH KERJA /SPK PD. HANSELINDO 031-8482008  - '.Tools::_date('','F d, Y'));
        $p_engine->Ln();
        $p_engine->Ln();
        //</editor-fold>
    }
    
    public static function refill_work_order_form_footer_print($p_engine,$rwo_id){
        //<editor-fold defaultstate="collapsed">
        $rwo = Refill_Work_Order_Data_Support::refill_work_order_get($rwo_id);
        $rwo_info = Refill_Work_Order_Data_Support::refill_work_order_info_get($rwo_id);
        
        $ttd_customer = $ttd_customer = $rwo['customer_name'];;
        $creator_name = $rwo_info['creator_name'];
        if($rwo['refill_work_order_status']!=='initialized'){
            

        }
        
        $p_engine->Ln();
        
        if($rwo['refill_work_order_status']!=='initialized'){
            $p_engine->Cell(0,null,'Total Estimasi Ongkos Rp.'.Tools::thousand_separator($rwo['total_estimated_amount']));
            $p_engine->Ln();
            $p_engine->Cell(0,null,'Telah Terima Deposit total sebesar Rp.'.Tools::thousand_separator($rwo['total_deposit_amount']));
        }
        $p_engine->Ln();
        $p_engine->Ln();
        $p_engine->Ln();
        $p_engine->Ln();
        $p_engine->font_set('Times',10,'');
        $p_engine->Cell(50,null,'Alamat & No HP Customer: '.$rwo['customer_address'].' - '.$rwo['customer_phone']);
        $p_engine->Cell(0,null,$ttd_customer.'                    '.$creator_name,0,0,'R');
        $p_engine->Ln();
        $p_engine->Ln();
        $p_engine->Cell(0,null,'*     Nominal deposit belum termasuk biaya pergantian sparepart, rekondisi, atau biaya lainnya yang timbul selama pengerjaan');
        $p_engine->Ln();
        $p_engine->Cell(0,null,'**   Unit wajib diambil & dilunasi paling lambat 2 minggu setelah konfirmasi dengan membawa tanda terima saat pengambilan unit');
        $p_engine->Ln();
        $p_engine->Cell(0,null,'*** Kerusakan/kehilangan pada unit yang belum diambil setelah batas waktu pengambilan merupakan diluar tanggung jawab kami');
        
        
        //</editor-fold>
    }
    
    public static function refill_work_order_form_print($rwo_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        get_instance()->load->helper('refill_work_order/refill_work_order_engine');
        $rwo = Refill_Work_Order_Data_Support::refill_work_order_get($rwo_id);
        $rwo_info = Refill_Work_Order_Data_Support::refill_work_order_info_get($rwo_id);
        if(count($rwo)>0){
            $rwo_product = Refill_Work_Order_Data_support::refill_work_order_product_get($rwo_id);
            $p_engine = new Printer('');
            $p_engine->paper_set('A4');
            $p_engine->start();
            Refill_Work_Order_Print::refill_work_order_form_header_print($p_engine,array('rwo'=>$rwo));
            
            $num_of_products = Tools::_int($rwo_info['number_of_product']);
            $product_limit_per_page = 7;
            
            $row_num_width = 10;
            $desc_width = 190;
            
            for($i = 0;$i<count($rwo_product);$i++){
                
                $new_page = ($i % ($product_limit_per_page) === 0 && $i !== 0 )? true:false;
                $print_table_header = $new_page || $i === 0? true:false;
                $print_footer = ($i+1 === count($rwo_product)) || (($i+1)%$product_limit_per_page===0) ? true:false;
                        
                if($new_page){
                    $p_engine->fpdf->AddPage();
                    Refill_Work_Order_Print::refill_work_order_form_header_print($p_engine,array('rwo'=>$rwo));
                }
                
                if($print_table_header){
                    $p_engine->font_set('Times',10,'');
                    $p_engine->Cell($row_num_width,7,'NO',1,0,'C');
                    $p_engine->Cell($desc_width,7,'Tipe APAR / APAB DCP / GAS CO2/FOAM AB / FOAM AFF / GAS',1,0,'C');
                    $p_engine->Ln();
                }
                
                //<editor-fold desc="Print Table Row" defaultstate="collapsed">
                $p_engine->font_set('Times',8,'');                
                
                $left_x = $p_engine->fpdf->GetX();
                $top_y = $p_engine->fpdf->GetY();
                $curr_x = $p_engine->fpdf->GetX();
                $col_width = $row_num_width;
                $p_engine->fpdf->MultiCell($col_width,30,$i+1,1);
                
                $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);                
                $curr_x = $p_engine->fpdf->GetX();
                $col_width = 80;
                
                
                $merk = '.......';
                $type = '.......';
                $capacity = '.......';
                $unit = 'KG/ Ltr';
                $product_category = 'APAR/APAB/Thermatic';
                $product_condition = 'Selang-Nozzle/Roda//PVC-base/Valve/Safety pin/PG/Sabuk';
                $product_condition_desc = '...........................';
                $staff_checker = "\n"."\n"."\n".'ttd staff';
                $product_medium = 'foam.....'."\n".
                    'gas.....'."\n".
                    'dcp.....'."\n".
                    '**isi & lingkari salah satu';
                
                if($rwo['refill_work_order_status'] !== 'initialized'){
                    $merk = $rwo_product[$i]['product_info_merk'];
                    $type = $rwo_product[$i]['product_info_type'];
                    $capacity = Tools::thousand_separator($rwo_product[$i]['capacity']);
                    $unit = $rwo_product[$i]['capacity_unit_code'];
                    $product_category = $rwo_product[$i]['rpc_code'];
                    $product_medium = "\n".$rwo_product[$i]['rpm_code'].' - '.$rwo_product[$i]['rpm_name'];
                    $product_condition = '';
                    $staff_checker = "\n".$rwo_product[$i]['staff_checker'];
                    foreach(Refill_Work_Order_Engine::$product_condition as $idx=>$pc){
                        if($rwo_product[$i][$pc['val']] === '1'){
                            $product_condition.=($product_condition===''?'':'/').$pc['label'];
                        }
                    }
                    
                    $product_condition_desc = $rwo_product[$i]['product_condition_description'];
                }
                
                
                
                $p_engine->fpdf->MultiCell($col_width,5,
                    'Merek: '.$merk.'                Cap: '.$capacity.' '.$unit."\n".
                    'Tipe: '.$type.'                 '.$product_category."\n".
                    'Kondisi:'.($rwo['refill_work_order_status']==='initialized'?'(coret yang tidak ada - keterngan jika aus)':'')."\n".
                    $product_condition."\n".
                    'Keterangan: '.$product_condition_desc.' *)pipa & cartridge tidak dicek'."\n".
                    '*Pergantian spareparts akan dikonfirmasi setelah pengecekan unit',
                1);
                
                if(in_array($rwo['refill_work_order_status'],array('done','process'))){
                    
                    $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);
                    $curr_x = $p_engine->fpdf->GetX();
                    $col_width = 20;
                    $p_engine->bold();
                    $p_engine->fpdf->MultiCell($col_width,5,
                        'Estimasi'."\n".
                        'Ongkos',
                    1,'C');
                    $p_engine->normal();
                    
                    $p_engine->fpdf->SetXY($curr_x,$top_y+10);                
                    $curr_x = $p_engine->fpdf->GetX();
                    $p_engine->fpdf->MultiCell(20,5,
                        "\n".
                        Tools::thousand_separator($rwo_product[$i]['estimated_amount'])
                        
                    ,0,'C');
                    $p_engine->fpdf->Rect($curr_x,$top_y+10,$col_width,20);
                }
                
                $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);
                $curr_x = $p_engine->fpdf->GetX();
                $col_width = ($rwo['refill_work_order_status'] !== 'initialized')?25:45;
                $p_engine->bold();
                $p_engine->fpdf->MultiCell($col_width,5,
                    'Marking'."\n".
                    'Code',
                1,'C');
                $p_engine->normal();
                
                $p_engine->fpdf->SetXY($curr_x,$top_y+10);                
                $curr_x = $p_engine->fpdf->GetX();
                $p_engine->fpdf->MultiCell($col_width,5,
                    "\n".$rwo_product[$i]['product_marking_code'],
                0,'C');
                $p_engine->fpdf->Rect($curr_x,$top_y+10,$col_width,20);
                
                $p_engine->bold();
                $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);                
                $curr_x = $p_engine->fpdf->GetX();
                $col_width = 40;
                $p_engine->fpdf->MultiCell($col_width,10,
                    'Rencana pengisian',
                1,'C');
                $p_engine->normal();
                
                $p_engine->fpdf->SetXY($curr_x,$top_y+10);                
                $curr_x = $p_engine->fpdf->GetX();
                $p_engine->fpdf->MultiCell($col_width,5,$product_medium,
                    0,($rwo['refill_work_order_status']==='initialized'?'L':'C')
                );
                $p_engine->fpdf->Rect($curr_x,$top_y+10,$col_width,20);
                
                $p_engine->bold();
                $p_engine->fpdf->SetXY($curr_x+$col_width,$top_y);                
                $curr_x = $p_engine->fpdf->GetX();
                $col_width = 25;
                $p_engine->fpdf->MultiCell($col_width,5,
                    'Tanda'."\n".
                    'Terima',
                1,'C');
                $p_engine->normal();
                
                $p_engine->fpdf->SetXY($curr_x,$top_y+10);                
                $curr_x = $p_engine->fpdf->GetX();
                $p_engine->fpdf->MultiCell($col_width,5,
                    $staff_checker,
                0,'C');
                $p_engine->fpdf->Rect($curr_x,$top_y+10,$col_width,20);
                
                $p_engine->fpdf->SetXY($left_x,$top_y+30);
                
                //</editor-fold>
                
                if($print_footer)
                Refill_Work_Order_Print::refill_work_order_form_footer_print($p_engine,$rwo['id']);
                
            }
            
            $p_engine->output(str_replace('/','',$rwo['code']).'.pdf','I');
        }
        //</editor-fold>
    }
        
}
?>