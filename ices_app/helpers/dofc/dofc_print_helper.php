<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class fpdf_dofc_print extends extended_fpdf{
    public function footer(){

    }
}

class DOFC_Print{
    function __construct(){}
    
    function dofc_header_print($p_engine,$data){
        //<editor-fold defaultstate="collapsed">
        
        $p_engine->fpdf->FontSizePt+=2;
        $p_engine->bold();
        $p_engine->Cell(null,null,'');
        $p_engine->Ln();
        $p_engine->set_xy($p_engine->fpdf->GetX(),5);
        
        
        $p_engine->Cell(null,null,Lang::get('Delivery Order Final Confirmation'));
        $p_engine->fpdf->FontSizePt-=2;
        $p_engine->normal();
        $p_engine->Ln();
        $p_engine->Ln();
        $p_engine->Ln();
        $p_engine->Ln();
        //</editor-fold>
    }
    
    public static function dofc_footer_print($p_engine,$data){
        //<editor-fold defaultstate="collapsed">
        $p_engine->fpdf->SetXY($p_engine->fpdf->GetX(),280);
        $p_engine->Cell(0,null,''.Tools::_date('','F d, Y H:i:s'), 0, 0);
        $p_engine->Cell(0,null,'Page 1', 0, 0,'R');
        //</editor-fold>
    }
    
    static function dofc_print($dofc_id,$file_location='',$dest=''){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('delivery_order_final/delivery_order_final_data_support');
        get_instance()->load->helper('dofc/dofc_data_support');
        get_instance()->load->helper('customer/customer_data_support');
        get_instance()->load->helper('expedition/expedition_data_support');
        
        $result = array('success'=>1,'msg'=>array());
        $success = 1;
        $msg = array();
        $db = new DB();
        $dofc = DOFC_Data_Support::dofc_get($dofc_id);
        if(!count($dofc)>0){
            $success = 0;
            $msg[] = Lang::get('Delivery Order Final Confirmation').' does not exist';
        }
        
        if($success === 1 && $dofc['delivery_order_final_confirmation_status'] !== 'X'){
            $q = '
                select delivery_order_final_id
                from dof_dofc
                where delivery_order_final_confirmation_id = '.$db->escape($dofc_id).'
            ';
            $rs = $db->query_array($q);
            $dof = Delivery_Order_Final_Data_Support::delivery_order_final_get($rs[0]['delivery_order_final_id']);
            $dofc_info = DOFC_Data_Support::dofc_info_get($dofc['id']);
                            
            $p_engine = new Printer('fpdf_dofc_print');
            $p_engine->paper_set('A4');
            $p_engine->start();
            $p_engine->fpdf->FontSizePt = 12;
            
            $fName = $p_engine->fpdf->FontFamily;
            $fSize = $p_engine->fpdf->FontSizePt;
            $lHeight = $p_engine->fpdf->LineHeight;
            $pWidth = $p_engine->fpdf->page_width_get();
            $pHeight = $p_engine->fpdf->page_height_get();
            
            //<editor-fold defaultstate="collapsed" desc="Sales Prospect">
            $header_data = array(
                
            );
            $footer_data = array(
                
            );
            
            $footer_data = array('page_number'=>1);
            
            self::dofc_header_print($p_engine,$header_data);
            $p_engine->Cell(null,null,'Code: '.$dofc['code']);
            $p_engine->Ln();
            $p_engine->Ln();
            $p_engine->Cell(null,null,'Confirmation Date: '.$dofc['delivery_order_final_confirmation_date']);
            $p_engine->Ln();
            $p_engine->Ln();
            $p_engine->Cell(null,null,Lang::get('Receipt Number').': '.$dofc_info['receipt_number']);
            $p_engine->Ln();
            $p_engine->Ln();
            $p_engine->Cell(null,null,Lang::get('Expedition').': '.$dofc_info['expedition_name']);
            $p_engine->Ln();
            $p_engine->Ln();
            $p_engine->Cell(null,null,Lang::get('Receiver Name').': '.$dofc_info['receiver_name']);
            $p_engine->Ln();
            $p_engine->Ln();
            self::dofc_footer_print($p_engine, $footer_data);
            //</editor-fold>
            
            if($dest === 'F'){
                $p_engine->output($file_location,'F');
            }
            else{
                $p_engine->output(str_replace('/','',$dofc['code']).'.pdf','I');
            }

        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
    }
}
?>