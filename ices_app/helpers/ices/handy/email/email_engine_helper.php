<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Email_Engine{
    
 
    public $email = null;
    
    public function __construct(){
        get_instance()->load->library('MY_PHPMailer');
        $this->email = new PHPMailer();
        $this->initialize();
    }
    
    public function initialize($param = array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('comp_mail_manager/comp_mail_manager_data_support');
        $company_mail_code = isset($param['code'])?$param['code']:'default';
        
        $company_mail = Comp_Mail_Manager_Data_Support::company_mail_by_code_get($company_mail_code);
        if(count($company_mail)>0){
            if($company_mail['protocol'] === 'smtp'){
                $this->email->isSmtp();
            }
            
            if($company_mail['mailtype'] === 'html'){
                $this->email->isHTML(true); 
            }
            
            $this->email->Host = $company_mail['smtp_host'];
            $this->email->SMTPAuth = true;
            $this->email->Username = $company_mail['username'];
            $this->email->Password = $company_mail['password'];
            $this->email->SMTPSecure = $company_mail['smtp_crypto'];
            $this->email->Port = $company_mail['smtp_port'];                        
            $this->email->From = $company_mail['username'];
            $this->email->FromName = $company_mail['username'];
            $this->email->addAddress('edw1n_85@yahoo.com');
            
        }
        
        //</editor-fold>
    }
    
    public function message_set($data){
        $data = preg_replace("/\r\n|\r|\n/",'<br>',$data);
        $this->email->Body = $data;
    }
    
    public function to($to_addr,$to_name=''){
        $this->email->addAddress($to_addr,$to_name);
    }
    
    public function subject($data){
        $this->email->Subject=$data;
    }
    
    public function attach($data){
        $this->email->addAttachment($data);
    }
    
    public function send(){
        return $this->email->send();
    }
    
    public function error_msg_get(){
        return $this->email->ErrorInfo;
    }
}
?>