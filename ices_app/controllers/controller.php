<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Controller extends MY_Controller {
        
    private $index_url= "";
    
    function __construct(){
        parent::__construct();
        $this->index_url=  get_instance()->config->base_url().'controller';
    }
    public function add(){
        $this->edit();
    }

    public function edit($id=""){
        $title = "Controller";
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
        $app->set_content_header($title,'',$action);
        $init_state = true;

        if($post != null){
            $init_state = false;
            $this->load->helper('security/controller_engine');
            $data['id'] = $id;
            $data['name'] = $post['name'];
            $data['method'] = $post['method'];

            if(Controller_Engine::save($data) == 1) redirect($this->index_url);

        }

        if(strlen($id)>0 && $init_state){ 
            $db = $this->db;
            $rs = $db->query('select * from security_controller where id = '.$db->escape($id))->result_array();

            foreach($rs as $row){
                $data['id'] = $row['id'];
                $data['name'] = $row['name'];
                $data['method'] = $row['method'];
            }
        }

        $row = $app->engine->div_add()->div_set('class','row');
        $form = $row->form_add()->form_set('title','Detail')->form_set('span','12');
        $form->input_add()->input_set('label','Name')->input_set('name','name')->input_set('input_mask_type','code')->input_set('value',$data['name']);
        $form->input_add()->input_set('label','Method')->input_set('name','method')->input_set('input_mask_type','code')->input_set('value',$data['method'])->input_set('maxlength','100');

        $form->control_set($method='button','','primary','submit',  '','Submit',App_Icon::btn_save());
        $form->control_set($method='button','','default','button',get_instance()->config->base_url().'controller','Back',  App_Icon::btn_back());
        $app->render();

    }

    public function delete($id=""){
        $data = array(
            "id"=>$id
            ,"status"=>0
        );
        $this->load->helper('security/controller_engine');
        if(Controller_Engine::Save($data) == 1){redirect($this->index_url);}
    }

    public function index()
    {           

        $title = "Controller";
        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($title);
        $app->set_breadcrumb($title,strtolower($title));
        $app->set_content_header($title,'',$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title','Controller List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value','New Controller')
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'controller/add');
        
        
         
        $cols = array(
            array("name"=>"name","label"=>"Name","data_type"=>"text","is_key"=>true)
            ,array("name"=>"method","label"=>"Method","data_type"=>"text")
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->index_url.'/view')
                ->table_ajax_set('lookup_url',$this->index_url.'/ajax_search/controller')
                ->table_ajax_set('columns',$cols);
        $app->render();


    }
    
    public function view($id = ""){
        $this->load->helper('security/controller_engine');
        $title = "Controller";
        $action = "View";
        
        if(Controller_Engine::get($id) == null){
            Message::set('error',array("Data doesn't exist"));
            redirect($this->index_url);
        }
        
        get_instance()->load->helper('security/u_group_engine');
        
        $app = new App();            
        $db = $this->db;

        $app->set_title($title);
        $app->set_breadcrumb($title,strtolower($title));
        $app->set_content_header($title,$action);
        $row = $app->engine->div_add()->div_set('class','row');            
        
        $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

        $detail_tab = $nav_tab->nav_tab_set('items_add'
                ,array("id"=>'#detail',"value"=>"Detail",'class'=>'active'));
        $detail_pane = $detail_tab->div_add()->div_set('id','detail')->div_set('class','tab-pane active');        
        Controller_Engine::detail_render($detail_pane,array("id"=>$id));
        $app->render();
    }
    
        public function ajax_search($method){
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'controller':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $q = '
                    select * from (
                    select *
                    from security_controller t1
                    where 1 = 1
                ';
                
                $q_where=' and (t1.name like '.$lookup_str.' 
                            or t1.method like '.$lookup_str.'
                        )) tfinal ';
                
                $extra=' ';
                if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                else {$extra.=' order by name asc';}
                $extra .= ' limit '.(($page-1)*$records_page).', '.($records_page);
                $q_total_row = $q.$q_where;
                $q_data = $q.$q_where.$extra;
                $total_rows = $db->select_count($q_total_row,null,null);
                $result = array("header"=>array("total_rows"=>$total_rows),"data"=>$db->query_array($q_data));
                break;

        }
        
        echo json_encode($result);
    }

    
    
}

