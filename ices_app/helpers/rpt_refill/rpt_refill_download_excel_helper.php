<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rpt_Refill_Download_Excel{
    public static function refill_invoice($param = array()){
        $start_date = Tools::_date((isset($param['start_date'])?Tools::_str($param['start_date']):''),'Y-m-d H:i:s');
        $end_date = Tools::_date((isset($param['end_date'])?Tools::_str($param['end_date']):''),'Y-m-d H:i:s');
        $excel = new Excel();
        
        $excel::$objPHPExcel->getActiveSheet()->getDefaultStyle()->applyFromArray(
            array(
                'font'=>array(
                    'bold'=>true,
                    'size'=>9,
                    'name'=>'Calibri',
                    'color' => array('rgb' => '002060'),
                )
            )
        );
        
        
        $title = 'REFILL TABUNG '.Tools::_date($start_date,'d F Y').' s/d '.Tools::_date($end_date,'d F Y');
        $excel::file_info_set('title',$title);
        $excel::$objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
        $excel::$objPHPExcel->getActiveSheet()->setCellValue('A1', $title);
        $excel::$objPHPExcel->getActiveSheet()->getStyle("A1")->getFont()->setSize(14);
        $excel::$objPHPExcel->getActiveSheet()->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        //define column size
        $excel::column_width_set('A',14.4);
        $excel::$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $excel::$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $excel::$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $excel::$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $excel::column_width_set('F',21);
        $excel::$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $excel::$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $excel::$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $excel::$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        
        
        
        $db = new DB();
        $q = '
            select ri.*,c.code customer_code,c.name customer_name
            from refill_invoice ri
            inner join customer c on ri.customer_id = c.id
            where ri.refill_invoice_date between '.$db->escape($start_date).' and '.$db->escape($end_date).'
            order by ri.refill_invoice_date
        ';
        $ri = $db->query_array($q);
        
        $ri_active_row = 3;
        $weekly_calculation_row_start = $ri_active_row;
        $weekly_sales = array();
        $curr_date = '1900-01-01';
        
        foreach($ri as $ri_idx=>$ri_row){
            $week_change = false;
            $last_day_in_week = false;
            if($ri_idx === 0){
                $week_change = true;
                $weekly_calculation_row_start = $ri_active_row+1;
            }
            
            if($ri_idx>0){
                $curr_date_week = (new DateTime($ri_row['refill_invoice_date']))->format('W');
                $last_date_week = (new DateTime($ri[$ri_idx-1]['refill_invoice_date']))->format('W');
                if($last_date_week !== $curr_date_week) $week_change = true;
            }
            
            if($ri_idx !== count($ri)-1){
                $curr_date_week = (new DateTime($ri_row['refill_invoice_date']))->format('W');
                $next_date_week = (new DateTime($ri[$ri_idx+1]['refill_invoice_date']))->format('W');
                if($next_date_week !== $curr_date_week) $last_day_in_week = true;
            }
            
            $print_table_header = false;
            if($week_change) $print_table_header = true;
            
            if($print_table_header){
                $excel::$objPHPExcel->getActiveSheet()->getStyle('A'.$ri_active_row.':'.'J'.$ri_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $excel::array_to_text(array('TGL','No. Invoice','CUSTOMER','Nama Barang','Qty','Harga/pcs','Jumlah Total','Transfer','Tunai','Keterangan'),'A'.$ri_active_row,0);
                $ri_active_row+=1;
            }
            
            $last_date = $curr_date;
            $curr_date = Tools::_str(Tools::_date($curr_date,'Y-m-d')) !==  Tools::_str(Tools::_date($ri_row['refill_invoice_date'])) ?
                    Tools::_date($ri_row['refill_invoice_date'],'Y-m-d'):$curr_date;
            
            if($curr_date != $last_date){
                $excel::$objPHPExcel->getActiveSheet()->getStyle("A".$ri_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $excel::array_to_text(array($curr_date),'A'.$ri_active_row,0);
            }
            $excel::array_to_text(array($ri_row['code']),'B'.$ri_active_row,0);
            $excel::$objPHPExcel->getActiveSheet()->getStyle("B".$ri_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            
            $rip_active_row = $ri_active_row;
            if($ri_row['refill_invoice_status'] === 'X'){
                $excel::array_to_text(array('VOID'),'C'.$ri_active_row,0);
            }
            else{
                
                $excel::array_to_text(array($ri_row['customer_code'].' '.$ri_row['customer_name']),'C'.$ri_active_row,0);

                $product_amount_to_pay = array();
                //<editor-fold defaultstate="collapsed" desc="Product">
                $q = '
                    select distinct rip.*,
                        u.code unit_code,
                        u.name unit_name,
                        rwop.product_marking_code
                    from ri_product rip
                    inner join refill_work_order_product rwop
                        on rip.product_id = rwop.id
                    inner join unit u on rip.unit_id = u.id
                    where rip.refill_invoice_id = '.$db->escape($ri_row['id']).'
                ';

                $rip = $db->query_array($q);

                
                foreach($rip as $rip_idx=>$rip_row){
                    $product_amount_to_pay[] = Tools::_float($rip_row['subtotal']);
                    
                    $excel::array_to_text(array($rip_row['product_marking_code']),'D'.$rip_active_row,0);

                    $excel::array_to_text(array($rip_row['qty']),'E'.$rip_active_row,0);
                    $excel::$objPHPExcel->getActiveSheet()->getStyle("E".$rip_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $excel::$objPHPExcel->getActiveSheet()->getStyle("E".$rip_active_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                    $excel::array_to_text(array($rip_row['amount']),'F'.$rip_active_row,0);
                    $excel::$objPHPExcel->getActiveSheet()->getStyle("F".$rip_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $excel::$objPHPExcel->getActiveSheet()->getStyle("F".$rip_active_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                    $excel::array_to_text(array($rip_row['subtotal']),'G'.$rip_active_row,0);
                    $excel::$objPHPExcel->getActiveSheet()->getStyle("G".$rip_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $excel::$objPHPExcel->getActiveSheet()->getStyle("G".$rip_active_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                    if($rip_idx!== count($rip)-1){
                        $rip_active_row += 1;
                    }
                }


                $extra_product = array(
                );
                
                foreach($extra_product as $ep_idx=>$ep_row){
                    //<editor-fold defaultstate="collapsed">
                    if(Tools::_float($ep_row['amount'])>Tools::_float('0')){
                        $rip_active_row+=1;
                        $product_amount_to_pay[] = Tools::_float($ep_row['subtotal']);
                        
                        $excel::array_to_text(array($ep_row['product']),'D'.$rip_active_row,0);

                        $excel::array_to_text(array($ep_row['qty']),'E'.$rip_active_row,0);
                        $excel::$objPHPExcel->getActiveSheet()->getStyle("E".$rip_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $excel::$objPHPExcel->getActiveSheet()->getStyle("E".$rip_active_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                        $excel::array_to_text(array($ep_row['amount']),'F'.$rip_active_row,0);
                        $excel::$objPHPExcel->getActiveSheet()->getStyle("F".$rip_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $excel::$objPHPExcel->getActiveSheet()->getStyle("F".$rip_active_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                        $excel::array_to_text(array($ep_row['subtotal']),'G'.$rip_active_row,0);
                        $excel::$objPHPExcel->getActiveSheet()->getStyle('G'.$rip_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $excel::$objPHPExcel->getActiveSheet()->getStyle('G'.$rip_active_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                    }
                    //</editor-fold>
                }
                //</editor-fold>
                
                //<editor-fold defaultstate="collapsed" desc="PAYMENT TRANSFER">
                $sra_active_row = $ri_active_row;
                
                //<editor-fold defaultstate="collapsed" desc="Query">
                $q = '
                    select * from (
                        select distinct sra.id
                            ,sra.allocated_amount
                            ,sra.allocated_amount "saldo_allocated_amount"
                            ,sr.deposit_date
                            ,pt.code payment_type_code
                            ,bba.code bos_bank_account_code
                        from refill_receipt_allocation sra
                        inner join refill_receipt sr on sra.refill_receipt_id = sr.id
                        inner join payment_type pt on sr.payment_type_id = pt.id
                        left outer join bos_bank_account bba on bba.id = sr.bos_bank_account_id
                        where sra.refill_invoice_id = '.$db->escape($ri_row['id']).'
                            and sra.refill_receipt_allocation_status = "invoiced"
                            and pt.code = "TRANSFER"
                        order by sr.id, sra.id      
                    ) t1
                    union all 
                    select * from (
                        select distinct cda.id
                            ,cda.allocated_amount
                            ,cda.allocated_amount "saldo_allocated_amount"
                            ,null deposit_date
                            ,"TRANSFER" payment_type_code
                            ,bba.code bos_bank_account_code
                        from customer_deposit_allocation cda
                        inner join customer_deposit cd on cda.customer_deposit_id = cd.id
                        inner join payment_type pt on cd.payment_type_id = pt.id
                        left outer join bos_bank_account bba on bba.id = cd.bos_bank_account_id
                        where cda.refill_invoice_id = '.$db->escape($ri_row['id']).'
                            and cda.customer_deposit_allocation_status = "invoiced"
                            and pt.code = "TRANSFER"
                        order by cd.id, cda.id
                    ) t2
                    
                ';
                //</editor-fold>
                
                $sra = $db->query_array($q);
                $last_product_fully_paid= false;
                foreach($product_amount_to_pay as $patp_idx=>$patp_row){
                    $product_amount = Tools::_float($patp_row);
                    $unpaid_amount = $product_amount;
                    $paid_amount = Tools::_float('0');
                    foreach($sra as $sra_idx=>$sra_row){
                        $print_payment = false;
                        $saldo_allocated_amount = Tools::_float($sra_row['saldo_allocated_amount']);
                        
                        $sa_amount_used = ($unpaid_amount <= $saldo_allocated_amount?
                            $unpaid_amount:$saldo_allocated_amount);
                        
                        $paid_amount += $sa_amount_used;
                        
                        $saldo_allocated_amount -= $sa_amount_used;
                        
                        $unpaid_amount -= $sa_amount_used;
                        
                        $sra[$sra_idx]['saldo_allocated_amount'] = $saldo_allocated_amount;
                        
                        if($unpaid_amount === Tools::_float(0) || $sra_idx == count($sra)-1){
                            $product_amount_to_pay[$patp_idx] = Tools::_float($unpaid_amount);
                            $print_payment = true;
                        }
                                                
                        if($print_payment){ 
                            $col = 'H';
                            $excel::array_to_text(array($paid_amount),$col.$sra_active_row,0);
                            $excel::$objPHPExcel->getActiveSheet()->getStyle($col.$sra_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                            $excel::$objPHPExcel->getActiveSheet()->getStyle($col.$sra_active_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                            
                            if(Tools::_float($unpaid_amount) === Tools::_float('0')){
                                $excel::array_to_text(array('Lunas',' ',$sra_row['bos_bank_account_code']),'J'.$sra_active_row,0);
                            }
                        }
                        
                        if($sra_idx < count($sra)-1){
                            if($print_payment){
                                $sra_active_row+=1;
                            }
                        }
                        else{
                            if(Tools::_float($saldo_allocated_amount)>Tools::_float('0')){
                                $sra_active_row +=1; 
                            }
                        }
                        
                        if($sra_idx === count($sra)-1){
                            if(Tools::_float($unpaid_amount)===Tools::_float('0')){
                                $last_product_fully_paid = true;
                            }
                        }
                        
                        if($unpaid_amount === Tools::_float(0)){
                            break;
                        }
                    }


                    foreach($sra as $sra_idx=>$sra_row){
                        if(Tools::_float($saldo_allocated_amount) === Tools::_float('0')){
                            unset($sra[$sra_idx]);
                        }
                    }                    
                    $sra = array_values($sra);
                }
                
                //</editor-fold>
                
                foreach($product_amount_to_pay as $patp_idx=>$patp_row){
                    if(Tools::_float($patp_row) === Tools::_float('0')){
                        unset($product_amount_to_pay[$patp_idx]);
                    }
                }                    
                $product_amount_to_pay = array_values($product_amount_to_pay);
                
                //<editor-fold defaultstate="collapsed" desc="PAYMENT CASH">
                $sra_active_row = $last_product_fully_paid?$sra_active_row+=1:$sra_active_row;
                
                //<editor-fold defaultstate="collapsed" desc="Query">
                $q = '
                    select * from (
                        select distinct sra.id
                            ,sra.allocated_amount
                            ,sra.allocated_amount "saldo_allocated_amount"
                            ,sr.deposit_date
                            ,pt.code payment_type_code
                        from refill_receipt_allocation sra
                        inner join refill_receipt sr on sra.refill_receipt_id = sr.id
                        inner join payment_type pt on sr.payment_type_id = pt.id
                        where sra.refill_invoice_id = '.$db->escape($ri_row['id']).'
                            and sra.refill_receipt_allocation_status = "invoiced"
                            and pt.code = "CASH"
                        order by sr.id, sra.id      
                    ) t1
                    union all 
                    select * from (
                        select distinct cda.id
                            ,cda.allocated_amount
                            ,cda.allocated_amount "saldo_allocated_amount"
                            ,cd.deposit_date
                            ,pt.code payment_type_code
                        from customer_deposit_allocation cda
                        inner join customer_deposit cd on cda.customer_deposit_id = cd.id
                        inner join payment_type pt on cd.payment_type_id = pt.id
                        left outer join bos_bank_account bba on bba.id = cd.bos_bank_account_id
                        where cda.refill_invoice_id = '.$db->escape($ri_row['id']).'
                            and cda.customer_deposit_allocation_status = "invoiced"
                            and pt.code = "CASH"
                        order by cd.id, cda.id
                    ) t2
                    
                ';
                //</editor-fold>
                
                $sra = $db->query_array($q);
                
                foreach($product_amount_to_pay as $patp_idx=>$patp_row){
                    $product_amount = Tools::_float($patp_row);
                    $unpaid_amount = $product_amount;
                    $paid_amount = Tools::_float('0');
                    foreach($sra as $sra_idx=>$sra_row){
                        $print_payment = false;
                        $saldo_allocated_amount = Tools::_float($sra_row['saldo_allocated_amount']);
                        $sa_amount_used = ($unpaid_amount <= $saldo_allocated_amount?
                            $unpaid_amount:$saldo_allocated_amount);
                        
                        $paid_amount += $sa_amount_used;

                        $saldo_allocated_amount -= $sa_amount_used;
                        
                        $unpaid_amount -= $sa_amount_used;
                        
                        $sra[$sra_idx]['saldo_allocated_amount'] = $saldo_allocated_amount;
                        
                        if($unpaid_amount === Tools::_float(0) || $sra_idx == count($sra)-1){
                            $product_amount_to_pay[$patp_idx] = Tools::_float($unpaid_amount);
                            $print_payment = true;
                        }
                                                
                        if($print_payment){
                            $col = 'I';
                            $excel::array_to_text(array($paid_amount),$col.$sra_active_row,0);
                            $excel::$objPHPExcel->getActiveSheet()->getStyle($col.$sra_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                            $excel::$objPHPExcel->getActiveSheet()->getStyle($col.$sra_active_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                            
                            $deposit_date = Tools::empty_to_null($sra_row['deposit_date']);
                            $excel::array_to_text(array(is_null($deposit_date)?'':'Str '.Tools::_date($deposit_date,'d/m/Y')),'J'.$sra_active_row,0);                            
                        }
                        
                        if($sra_idx < count($sra)-1){
                            if($print_payment){
                                $sra_active_row+=1;
                            }
                        }
                        else{
                            if(Tools::_float($saldo_allocated_amount)>Tools::_float('0'))
                                $sra_active_row +=1;
                        }
                        
                        if($unpaid_amount === Tools::_float(0)){
                            break;
                        }
                    }
                    
                    foreach($sra as $sra_idx=>$sra_row){
                        if(Tools::_float($saldo_allocated_amount) === Tools::_float('0')){
                            unset($sra[$sra_idx]);
                        }
                    }
                    
                    $sra = array_values($sra);
                }
                
                //</editor-fold>
                
                
            }
            
            $ri_active_row = max(array(Tools::_float($ri_active_row),Tools::_float($rip_active_row),Tools::_float($sra_active_row)));
            
            //<editor-fold defaultstate="collapsed" desc="Check Write Weekly Calculation">
            $print_weekly_calculation = $ri_idx === count($ri)-1?true:false;
            if(!$print_weekly_calculation){
                if($last_day_in_week) $print_weekly_calculation = true;
            }
            
            if($print_weekly_calculation){
                $ri_active_row+=1;
                $week_start_date= date( "w", strtotime($ri_row['refill_invoice_date']))!== '1'?
                    date('d M Y',strtotime('last monday',strtotime($ri_row['refill_invoice_date']))):
                    date('d M Y',strtotime($ri_row['refill_invoice_date']));
                $week_end_date= date( "w", strtotime($ri_row['refill_invoice_date']))!== '6'?
                    date('d M Y',strtotime('this sunday',strtotime($ri_row['refill_invoice_date']))):
                    date('d M Y',strtotime($ri_row['refill_invoice_date']));
                $weekly_name = $week_start_date.' - '.$week_end_date;
                
                $weekly_sales[] = array('name'=>$weekly_name,'amount'=>'='.'G'.$ri_active_row);
                
                $excel::array_to_text(array('TOTAL MINGGUAN '.$weekly_name),'D'.$ri_active_row,0);
                
                $excel::array_to_text(array('=sum('.'G'.$weekly_calculation_row_start.':'.'G'.($ri_active_row-1).')'),'G'.$ri_active_row,0);
                $excel::$objPHPExcel->getActiveSheet()->getStyle('G'.$ri_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $excel::$objPHPExcel->getActiveSheet()->getStyle('G'.$ri_active_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                
                $excel::array_to_text(array('=sum('.'H'.$weekly_calculation_row_start.':'.'H'.($ri_active_row-1).')'),'H'.$ri_active_row,0);
                $excel::$objPHPExcel->getActiveSheet()->getStyle('H'.$ri_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $excel::$objPHPExcel->getActiveSheet()->getStyle('H'.$ri_active_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                
                $excel::array_to_text(array('=sum('.'I'.$weekly_calculation_row_start.':'.'I'.($ri_active_row-1).')'),'I'.$ri_active_row,0);
                $excel::$objPHPExcel->getActiveSheet()->getStyle('I'.$ri_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $excel::$objPHPExcel->getActiveSheet()->getStyle('I'.$ri_active_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                
                
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                          'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                
                $excel::$objPHPExcel->getActiveSheet()->getStyle('A'.($weekly_calculation_row_start-1).':J'.$ri_active_row)->applyFromArray($styleArray);
                $excel::$objPHPExcel->getActiveSheet()->getStyle('A'.$ri_active_row.':J'.$ri_active_row)
                    ->getBorders()
                    ->getBottom()
                    ->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
                
                $ri_active_row+=1;
                $weekly_calculation_row_start = $ri_active_row+2;
            }
            //</editor-fold>
            
            
            
            if( $ri_idx !== count($ri)-1){
                $ri_active_row +=1;
            }
        }
        
        $weekly_sales_active_row = $ri_active_row+2;
        if(count($weekly_sales)>0){
            
            $excel::array_to_text(array('REFILL '.Tools::_date($start_date,'d F Y').' s/d '.Tools::_date($end_date,'d F Y')),'A'.$weekly_sales_active_row,0);
            $ws_calculate_start = $weekly_sales_active_row+1;
            foreach($weekly_sales as $ws_idx=>$ws_row){
                $weekly_sales_active_row+=1;
                $excel::array_to_text(array($ws_row['name']),'A'.$weekly_sales_active_row,0);
                
                $excel::array_to_text(array($ws_row['amount']),'C'.$weekly_sales_active_row,0);
                $excel::$objPHPExcel->getActiveSheet()->getStyle('C'.$weekly_sales_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $excel::$objPHPExcel->getActiveSheet()->getStyle('C'.$weekly_sales_active_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            }
            $excel::$objPHPExcel->getActiveSheet()->getStyle('A'.$weekly_sales_active_row.':C'.$weekly_sales_active_row)
                    ->getBorders()
                    ->getBottom()
                    ->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
            
            $weekly_sales_active_row+=1;
            $excel::array_to_text(array('=sum(C'.$ws_calculate_start.':C'.($weekly_sales_active_row-1).')'),'C'.$weekly_sales_active_row,0);
            $excel::$objPHPExcel->getActiveSheet()->getStyle('C'.$weekly_sales_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $excel::$objPHPExcel->getActiveSheet()->getStyle('C'.$weekly_sales_active_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        }
        
        //<editor-fold defaultstate="collapsed" desc="Refill Receipt && Customer Deposit">
        $q = '
            select distinct 
                sr.code
                ,sr.deposit_date
                ,sr.amount - sr.change_amount pure_amount
            from refill_receipt sr
            inner join payment_type pt on sr.payment_type_id = pt.id
            where sr.status>0
                and sr.refill_receipt_status = "invoiced"
                and pt.code = "CASH"
                and sr.refill_receipt_date between '.$db->escape($start_date).' and '.$db->escape($end_date).'
                and sr.deposit_date is not null
            
            union all
                
            select distinct 
                cd.code
                ,cd.deposit_date
                ,cd.amount pure_amount
            from customer_deposit cd
            inner join payment_type pt on cd.payment_type_id = pt.id
            where cd.status>0
                and cd.customer_deposit_type = "refill_work_order"
                and cd.customer_deposit_status = "invoiced"
                and pt.code = "CASH"
                and cd.customer_deposit_date between '.$db->escape($start_date).' and '.$db->escape($end_date).'
                and cd.deposit_date is not null
        ';
        $weekly_refill_receipt = $db->query_array($q);
        
        $weekly_refill_receipt_active_row =  $ri_active_row+2;
        if(count($weekly_refill_receipt)>0){
            
            $excel::array_to_text(array('JUMLAH SETORAN TUNAI BANK BCA '.Tools::_date($start_date,'d F Y').' s/d '.Tools::_date($end_date,'d F Y')),'F'.$weekly_refill_receipt_active_row,0);
            $weekly_refill_receipt_active_row+=1;
            $excel::array_to_text(array('Tanggal','Sales Receipt / Customer Deposit Code','Jumlah'),'F'.$weekly_refill_receipt_active_row,0);
            $wsr_start_row = $weekly_refill_receipt_active_row+=1;
            foreach($weekly_refill_receipt as $wsr_idx=>$wsr_row){
                $weekly_refill_receipt_active_row+=1;
                $excel::array_to_text(array($wsr_row['deposit_date']),'F'.$weekly_refill_receipt_active_row,0);
                
                $excel::array_to_text(array($wsr_row['code']),'G'.$weekly_refill_receipt_active_row,0);
                
                $excel::array_to_text(array($wsr_row['pure_amount']),'H'.$weekly_refill_receipt_active_row,0);
                $excel::$objPHPExcel->getActiveSheet()->getStyle('H'.$weekly_refill_receipt_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $excel::$objPHPExcel->getActiveSheet()->getStyle('H'.$weekly_refill_receipt_active_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            }
          
            
            $weekly_refill_receipt_active_row+=1;

            $excel::array_to_text(array('=sum('.'H'.$wsr_start_row.':H'.($weekly_refill_receipt_active_row-1).')'),'H'.$weekly_refill_receipt_active_row,0);
            $excel::$objPHPExcel->getActiveSheet()->getStyle('H'.$weekly_refill_receipt_active_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $excel::$objPHPExcel->getActiveSheet()->getStyle('H'.$weekly_refill_receipt_active_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            
            $excel::$objPHPExcel->getActiveSheet()->getStyle('F'.($weekly_refill_receipt_active_row-1).':H'.($weekly_refill_receipt_active_row-1))
                    ->getBorders()
                    ->getBottom()
                    ->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
        }
        
        
        //</editor-fold>
        
        $ri_active_row = max($weekly_sales_active_row,$weekly_refill_receipt_active_row);
        
        $excel::save('LP Refill '.Tools::_str(Tools::_date($start_date,'Ymd')).'-'.Tools::_str(Tools::_date($end_date,'Ymd')).' '.(string)Date('Ymd His'));
    }
}

?>