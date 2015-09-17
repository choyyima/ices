<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class fpdf_intake_final_print extends extended_fpdf{
    public function footer(){

    }
}

class Intake_Final_Print{
    function __construct(){}
        
    public static function intake_final_print($p_engine,$intake_final_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('intake_final/intake_final_data_support');
        get_instance()->load->helper('intake/intake_print');
        
        
        $success = 1;
        $db = new DB();
        $intake_final = Intake_Final_Data_Support::intake_final_get($intake_final_id);
        if(!count($intake_final)>0) $success = 0;
        
        if($success === 1){
            if(in_array($intake_final['intake_final_status'],array('done','process'))){
                $intake = Intake_Final_Data_Support::intake_get($intake_final['id']);

                if(is_null($p_engine)){
                    $p_engine = new Printer('fpdf_intake_final_print');
                    $p_engine->paper_set('thin-man');
                    $p_engine->start();
                }

                //<editor-fold defaultstate="collapsed" desc="Intake">
                foreach($intake as $i=>$row){
                    if($i>0)$p_engine->fpdf->AddPage();
                    Intake_Print::intake_print($row['id'],array('p_engine'=>$p_engine,'p_output'=>false));
                }            
                //</editor-fold>

                $p_engine->output();
            }

        }
        //</editor-fold>
    }

}
?>