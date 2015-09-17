<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sign_In extends MY_Extended_Controller {
        
	
	public function index()
	{
            $post = $this->input->post();
            if($post){
                $this->load->helper('user/security_engine');
                
                $user_id = Security_Engine::get_user_id($post['username'],$post['password']);
                
                if($user_id>0){
                    User_Info::set($user_id);
                    $url = get_instance()->config->base_url().'dashboard';
                    
                    redirect($url);                    
                }
                else{
                    
                    $this->load->view('sign_in',array('login_failed'=>true));
                }
            }
            else{         
                Security_Engine::sign_out();
                $this->load->view('sign_in');
            }
	}
        
        public function sign_out()
        {
            
            $this->load->helper('user/security_engine');
            Security_Engine::sign_out();
            redirect(get_instance()->config->base_url().'sign_in');
        }
}


