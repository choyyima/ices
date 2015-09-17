<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Helper extends MY_Controller {
        
    private $index_url= "";
    
    function __construct(){
        parent::__construct();
        $this->index_url=  get_instance()->config->base_url().'helper';
    }
    public function add(){
        $this->edit();
    }

    public function edit($id=""){
        $title = "Helper";
        $action = "Add";
        if(strlen($id)>0) $action = 'Edit';

        $data = array(
            'id'=>''
            ,'name'=>""
            ,'method'=>""
        );
        $post = $this->input->post();
        $app = new App();            
        $app->set_title($title);
        $app->set_breadcrumb($title,strtolower($title));
        $app->set_content_header($title,$action);
        $init_state = true;

        if($post != null){
            $init_state = false;
            $this->load->helper('security/helper_engine');
            $data['id'] = $id;
            $data['name'] = $post['name'];
            $data['method'] = $post['method'];

            if(Helper_Engine::save($data) == 1) redirect($this->index_url);

        }

        if(strlen($id)>0 && $init_state){ 
            $db = $this->db;
            $rs = $db->query('select * from security_helper where id = '.$db->escape($id))->result_array();

            foreach($rs as $row){
                $data['id'] = $row['id'];
                $data['name'] = $row['name'];
                $data['method'] = $row['method'];
            }
        }

        $row = $app->engine->div_add()->div_set('class','row');
        $form = $row->form_add()->form_set('title','Detail')->form_set('span','12');
        $form->input_add()->input_set('label','Name')->input_set('name','name')->input_set('input_mask_type','code')->input_set('value',$data['name']);
        $form->input_add()->input_set('label','Method')->input_set('name','method')->input_set('input_mask_type','code')->input_set('value',$data['method']);

        $form->control_set($method='button','','primary','submit','','Submit');
        $form->control_set($method='button','','danger','button',get_instance()->config->base_url().'helper','Cancel');
        $app->render();

    }

    public function delete($id=""){
        $data = array(
            "id"=>$id
            ,"status"=>0
        );
        $this->load->helper('security/helper_engine');
        if(Helper_Engine::Save($data) == 1){redirect($this->index_url);}
    }

    public function index()
    {           

        $title = "Helper";
        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($title);
        $app->set_breadcrumb($title,strtolower($title));
        $app->set_content_header($title,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title','Helper List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value','New Helper')
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'helper/add');
        $table = $form->form_group_add()->table_add();
        $q = '
            select *
            from security_helper
            
        ';
        $rs = $db->query($q)->result_array();
        $table->table_set('data',$rs);
        $table->table_set('columns',array("name"=>"id","label"=>"Id","is_key"=>true));
        $table->table_set('columns',array("name"=>"name","label"=>"Method"));
        $table->table_set('columns',array("name"=>"method","label"=>"Name"));
        $table->table_set('control',array("name"=>'Delete',"href"=>"helper/delete/","confirmation"=>true));
        $table->table_set('base href',$this->index_url.'/edit/');
        $table->table_set('data key','id');
        $app->render();


    }
}

