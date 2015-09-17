<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class product_stock_opname_mail{
    public function start($sys_db,$config){
        //<editor-fold defaultstate="collapsed">
        set_time_limit(30);
        get_instance()->load->helper('product_stock_opname/product_stock_opname_print');
        get_instance()->load->helper('product_stock_opname/product_stock_opname_data_support');
        get_instance()->load->library('email');
        
        $result = array('success'=>1,'msg'=>'');
        $success = 0;
        $msg = '';
        $db = new DB();
        $q = '
            select pso.*
            from product_stock_opname pso
            where pso.id not in ( 
                select pso.id
                from product_stock_opname pso
                inner join cj_product_stock_opname_mail_log psom
                    on pso.id = psom.product_stock_opname_id 
                    and pso.product_stock_opname_status = psom.product_stock_opname_status
            )
        ';
        
        $rs = $db->query_array($q);
        
        if(count($rs)>0){
            $sent_pso = array();
            $pso = $rs;
            foreach($pso as $idx=>$row){
                $moddate = Tools::_date('','Y-m-d H:i:s');
                $pso_id = $row['id'];
                $t_filename = 'pdf_file/'.str_replace('/','',$row['code']).'_'.Tools::_date('','Ymd').'.pdf';
                
                
                $print_param = array(
                    'p_output'=>false,
                    'filename'=>$t_filename
                );
                $temp_result = Product_Stock_Opname_Print::pso_print($pso_id,$print_param);
                $success = $temp_result['success'];
                
                if($success === 1){
                    $sent_pso[] = array(
                        'id'=>$row['id'],
                        'product_stock_opname_status'=>$row['product_stock_opname_status'],
                        'filename'=>$t_filename,
                    );
                }
                else{
                    $msg = $temp_result['msg'];
                }
                
                if($success !== 1) break;
            }
            
            if($success === 1 && count($sent_pso)>0){
                //<editor-fold defaultstate="collapsed">
                $mail_to = 'edw1n_85@yahoo.com';
                $subject = 'Product Stock Opname';
                $message = 'Product Stock Opname';

                $email_engine = new Email_Engine();
                $email = $email_engine->email;

                try{

                    $email_engine->initialize(array('code'=>'system'));
                    $email_engine->to($mail_to);
                    $email_engine->subject($subject);
                    $email_engine->message_set($message);
                    foreach($sent_pso as $idx=>$row){
                        $email_engine->attach($row['filename']);
                    }

                    if(!$email_engine->send()){
                        $success = 0;
                        $msg[] = $email_engine->error_msg_get();
                    }                

                }
                catch(Exception $e){

                }
                //</editor-fold>
            }
            
            
            foreach($sent_pso as $idx=>$row){
                if(file_exists($row['filename'])){
                    unlink($row['filename']);
                }
            }
            
            if($success === 1){
                foreach($sent_pso as $idx=>$row){
                    $psom_param = array(
                        'product_stock_opname_id'=>$row['id'],
                        'product_stock_opname_status'=>$row['product_stock_opname_status'],
                        'moddate'=>$moddate,
                    );
                    if(!$db->insert('cj_product_stock_opname_mail_log', $psom_param)){
                        $success = 0;
                        $msg = $db->_error_message();
                    }
                }
            }
            
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        
        return $result;
        //</editor-fold>
    }
};

?>