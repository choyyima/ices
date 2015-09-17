<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Security_Menu extends MY_Controller {
        
    private $title='Menu';
    
    private $path = array(
        'index'=>''
        ,'security_menu_engine'=>''
        ,'ajax_search'=>''
        ,'menu_js'
    );
    
    function __construct(){
        parent::__construct();
        $this->path = json_decode(json_encode($this->path));
        $this->path->index=  get_instance()->config->base_url().'security_menu/';
        $this->path->security_menu_engine=  'security/security_menu_engine';
        $this->path->ajax_search=  $this->path->index.'ajax_search/';
        $this->path->menu_js = 'security/security_menu/security_menu_js';
        
    }
    
    public function index()
    {           
        $this->load->helper($this->path->security_menu_engine);
        $title = "Menu";
        $action = "";
        

        $data = array(
            'menu'=>array()
            ,'u_group_id'=>""
            
        );
        
        $selected_u_group = array('id'=>'','data'=>'');
                
        $post = $this->input->post();
        $app = new App();            
        $app->set_title($title);
        $app->set_breadcrumb($title,strtolower($title));
        $app->set_content_header($title,App_Icon::menu(),$action);
        $init_state = true;

        if($post != null){
            $init_state = false;
            $post = json_decode($post,TRUE);
            $result =  Security_Menu_Engine::save($post);
            echo json_encode($result);
            die();
        }

        $row = $app->engine->div_add()->div_set('class','row');
        $form = $row->form_add()->form_set('title','Detail')->form_set('span','12');
        
        $form->input_select_add()
                ->input_select_set('name','u_group_id')
                ->input_select_set('label','User Group')
                ->input_select_set('icon','fa fa-user')
                ->input_select_set('min_length','1')
                ->input_select_set('ajax_url',$this->path->ajax_search.'u_group')
                ->input_select_set('value',$selected_u_group)
                ->input_select_set('id','u_group_id')
                ;
        
        $menu_array = Security_Menu_Engine::get_menu_array();
        
        $form->table_add()
                ->table_set('id','menu_table')
                ->table_set('data',$menu_array)
                ->table_set('columns',array('col_attrib'=>array('style'=>'text-align:center'),"attribute"=>'style="text-align:center;width:50px"',"name"=>"selected","label"=>'<input type="checkbox" id="check_all">','element_tag'=>'input','element_attribute'=>'type="checkbox"'))
                ->table_set('columns',array("name"=>"id","label"=>"Menu ID",'attribute'=>'style="display:none"','col_attrib'=>array('style'=>"display:none")))
                ->table_set('columns',array("name"=>"menu","label"=>"Menu",'col_attrib'=>array('style'=>'text-align:left'),'attribute'=>'style="text-align:left"'))
                ;
        

        $form->control_set($method='button','menu_submit','primary','submit','','Submit',App_Icon::btn_save());
        $form->control_set($method='button','','default','button',$this->path->index,'Back',App_Icon::btn_back());
        
        $param = array("submit_ajax_url"=>$this->path->index,'ajax_search_menu'=>$this->path->ajax_search.'menu');
        $js = get_instance()->load->view($this->path->menu_js,$param,TRUE);
        $app->js_set($js);
        
        $app->render();

    }
    
    public function view($id = ""){
        
    }
    
    public function ajax_search($method){
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'u_group':
                $db= new DB();
                $q = '
                    select id id, name text 
                    from u_group 
                    where status>0 
                        and( 
                            name like '.$db->escape('%'.$data['data'].'%').'
                            
                        )
                    ';
                $result = $db->query_array($q);
                break;
            
        
            case 'menu':
                $db= new DB();
                $q = '
                    select menu_id 
                    from security_menu 
                    where u_group_id = '.$db->escape($data['data']).'
                    ';
                $result = $db->query_array($q);
                break;
        }
        
        echo json_encode($result);
    }

    
    
}

