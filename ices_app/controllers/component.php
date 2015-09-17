<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Component extends MY_Controller {
        
    private $index_url= "";
    
    function __construct(){
        parent::__construct();
        $this->index_url=  get_instance()->config->base_url().'component';
    }
    public function add(){
        $this->edit();
    }

    public function edit($id=""){
        $title = "Component";
        $action = "Add";
        if(strlen($id)>0) $action = 'Edit';

        $data = array(
            'id'=>''
            ,'module'=>""
            ,'comp_id'=>""
        );
        $post = $this->input->post();
        $app = new App();            
        $app->set_title($title);
        $app->set_breadcrumb($title,strtolower($title));
        $app->set_content_header($title,'',$action);
        $init_state = true;

        if($post != null){
            $init_state = false;
            $this->load->helper('security/security_component_engine');
            $data['id'] = $id;
            $data['module'] = $post['module'];
            $data['comp_id'] = $post['comp_id'];

            if(Security_Component_Engine::save($data) == 1) redirect($this->index_url);

        }

        if(strlen($id)>0 && $init_state){ 
            $db = $this->db;
            $rs = $db->query('select * from security_component where id = '.$db->escape($id))->result_array();

            foreach($rs as $row){
                $data['id'] = $row['id'];
                $data['module'] = $row['module'];
                $data['comp_id'] = $row['comp_id'];
            }
        }

        $row = $app->engine->div_add()->div_set('class','row');
        $form = $row->form_add()->form_set('title','Detail')->form_set('span','12');
        $form->input_add()->input_set('label','Module')->input_set('name','module')->input_set('input_mask_type','code')->input_set('value',$data['module']);
        $form->input_add()->input_set('label','Comp. ID')->input_set('name','comp_id')->input_set('input_mask_type','code')->input_set('value',$data['comp_id']);

        $form->control_set($method='button','','primary','submit',  '','Submit',App_Icon::btn_save());
        $form->control_set($method='button','','default','button',get_instance()->config->base_url().'component','Back',  App_Icon::btn_back());
        $app->render();

    }

    public function delete($id=""){
        $data = array(
            "id"=>$id
            ,"status"=>0
        );
        $this->load->helper('security/security_component_engine');
        if(Security_Component_Engine::Save($data) == 1){redirect($this->index_url);}
    }

    public function index()
    {           

        $title = "Component";
        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($title);
        $app->set_breadcrumb($title,strtolower($title));
        $app->set_content_header($title,'',$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title','Component List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value','New Component')
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'component/add');
        
        /*
        $table = $form->form_group_add()->table_add();
        $q = '
            select *
            from security_component
            
        ';
        $rs = $db->query($q)->result_array();
        $table->table_set('data',$rs);
        $table->table_set('columns',array("name"=>"id","label"=>"Id","is_key"=>true));
        $table->table_set('columns',array("name"=>"name","label"=>"Method"));
        $table->table_set('columns',array("name"=>"method","label"=>"Name"));
        $table->table_set('control',array("name"=>'Delete',"href"=>"component/delete/","confirmation"=>true));
        $table->table_set('base href',$this->index_url.'/view/');
        $table->table_set('data key','id');
         * */
         
        $cols = array(
            array("name"=>"module","label"=>"Module","data_type"=>"text","is_key"=>true)
            ,array("name"=>"comp_id","label"=>"Comp. ID","data_type"=>"text")
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->index_url.'/view')
                ->table_ajax_set('lookup_url',$this->index_url.'/ajax_search/component')
                ->table_ajax_set('columns',$cols);
        $app->render();


    }
    
    public function view($id = ""){
        $this->load->helper('security/security_component_engine');
        $title = "Component";
        $action = "View";
        
        if(Security_Component_Engine::get($id) == null){
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
        Security_Component_Engine::detail_render($detail_pane,array("id"=>$id));
        $app->render();
    }
    
        public function ajax_search($method){
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'component':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $q = '
                    select * from (
                    select *
                    from security_component t1
                    where 1 = 1
                ';
                
                $q_where=' and (t1.module like '.$lookup_str.' 
                            or t1.comp_id like '.$lookup_str.'
                        )) tfinal ';
                
                $extra=' ';
                if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                else {$extra.=' order by module asc';}
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

