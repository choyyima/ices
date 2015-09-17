<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Approval extends MY_Controller {
        

    private $title='Approval';
    private $title_icon = 'fa fa-archive';
    private $path = array(
        'index'=>''
        ,'approval_engine'=>''
        ,'ajax_search'=>''
        ,'approval_js'=>''
    );
    
    function __construct(){
        parent::__construct();
        $this->path = json_decode(json_encode($this->path));
        $this->path->index=  get_instance()->config->base_url().'approval/';
        $this->path->approval_engine=  'approval/approval_engine';
        $this->path->ajax_search=  $this->path->index.'ajax_search/';

        
        
    }
    public function add(){
        $this->edit();
    }

    public function edit($id=""){
        $this->load->helper($this->path->approval_engine);
        if($id!=""){redirect($this->path->index.'view/'.$id);}
        $action = "Add";
        if(strlen($id)>0) $action = 'Edit';
        if($action != 'Add' && Approval_Engine::get($id) == null){
            Message::set('error',array("Data doesn't exist"));
            redirect($this->path->index);
        }
        

        $data = array(
            'id'=>''
            ,'code'=>'[AUTO GENERATE]'
            ,'name'=>''
            ,'due_date'=>date('Y-m-d').'T23:59:59'
            ,'notes'=>''
            ,'limit'=>''
            ,'approval_type_id'=>''
        );
        
        
        $post = $this->input->post();

        $app = new App();            
        $app->set_title($this->title);
        
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);
        $init_state = true;
        $db = new DB();
        if($post != null){
            $init_state = false;
            $data['id'] = $id;
            $data['name'] = $post['name'];
            $data['due_date'] = $post['due_date'];
            $data['notes'] = $post['notes'];
            $data['limit'] = $post['limit'];
            $data['approval_type_id'] = $post['approval_type_id'];
            $result = Approval_Engine::save($data);
            
            if( $result['success']== 1){
                $id = $result['id'];
                redirect($this->path->index.'view/'.$id);
            }
        }

        if(strlen($id)>0 && $init_state){ 
            $db = new DB();
            $q = '
                select t1.*, t2.id approval_type_id 
                from approval t1
                    inner join approval_type t2 on t1.approval_type_id = t2.id
                where t1.status>0 and t1.id = '.$db->escape($id).'
            ';
            $rs = $db->query_array_obj($q);

            foreach($rs as $row){
                $data['id'] = $row->id;
                $data['code'] = $row->code;
                $data['name'] = $row->name;            
                $data['due_date'] = str_replace(' ','T',$row->due_date);
                $data['notes'] = $row->notes;  
                $data['approval_type_id'] = $row->approval_type_id;  
            }
            
        }

        $row = $app->engine->div_add()->div_set('class','row');
        $form = $row->form_add()->form_set('title','Detail')->form_set('span','12');
        
        $form->input_add()//->input_set('name','code')
                ->input_set('label','Code')
                ->input_set('icon','fa fa-info')
                ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                ->input_set('value',$data['code'])
                ;  

        $q = 'select id id, name data from approval_type ';
        $approval_type = $db->query_array($q);
        
        $selected_approval_type = array("id"=>"","data"=>"");
        foreach($approval_type as $approval){
            if($approval['id'] == $data['approval_type_id']){
                $selected_approval_type['id'] = $approval['id'];
                $selected_approval_type['data'] = $approval['data'];
            }
        }
        $approval_type = $form->input_select_add()->input_select_set('name','approval_type_id')
                ->input_select_set('id','approval_type_id')
                ->input_select_set('label','Approval Type')
                ->input_select_set('icon','fa fa-tag')
                ->input_select_set('min_length','0')
                ->input_select_set('data_add',$approval_type)
                ->input_select_set('value',$selected_approval_type);
        
        $name = $form->input_add()->input_set('label','Name')->input_set('name','name')
                ->input_set('icon','fa fa-user')
                ->input_set('id','name')
                ->input_set('value',$data['name']);
        
        $form->input_add()->input_set('label','Limit')->input_set('name','limit')
                ->input_set('icon','fa fa-user')
                ->input_set('id','name')
                ->input_set('value',$data['limit']);
        
        $form->datetimepicker_add()->datetimepicker_set('label',Lang::get('Due Date'))
                    ->datetimepicker_set('id','due_date')
                    ->datetimepicker_set('name','due_date')
                    ->datetimepicker_set('value',(string)date('Y-m-d H:i')) 
                    ->div_set('id','div_die_date')
                ;  
        
        $notes = $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('name','notes')
                    ->textarea_set('value',$data['notes'])
                    ->textarea_set('attrib',array());                    
                    
        
        $form->control_set('button','customer_button_save','primary','submit','','Submit','fa fa-save');
        $form->control_set('button','','danger','button',$this->path->index,'Cancel','fa fa-times');

        
        
        $app->render();

    }

    public function delete($id=""){
        $data = array(
            "id"=>$id
            ,"status"=>0
        );
        $this->load->helper($this->path->approval_engine);
        $result = Approval_Engine::Save($data);
        if($result['success'] == 1){redirect($this->path->index);}
        else redirect($this->path->index.'view/'.$id);
    }

    public function index()
    {           
        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title','Approval')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value','New Approval')
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'approval/add');

        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"name","label"=>"Name","data_type"=>"text")
            ,array("name"=>"approval_type_name","label"=>"Type","data_type"=>"text")
            ,array("name"=>"limit","label"=>"Limit","data_type"=>"text")           
            ,array("name"=>"use","label"=>"Usage","data_type"=>"text")           

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/approval')
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
        
    }
    
    public function view($id = ""){
        $this->load->helper($this->path->approval_engine);
        $action = "View";
        
        if( Approval_Engine::get($id) == null){
            Message::set('error',array("Data doesn't exist"));
            redirect($this->path->index);
        }
        
        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);
        $row = $app->engine->div_add()->div_set('class','row');            
        
        $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

        $detail_tab = $nav_tab->nav_tab_set('items_add'
                ,array("id"=>'#detail',"value"=>"Detail",'class'=>'active'));
        $detail_pane = $detail_tab->div_add()->div_set('id','detail')->div_set('class','tab-pane active');        
        Approval_Engine::detail_render($detail_pane,array("id"=>$id));
        
        $app->render();
        
    }
    
    
    public function ajax_search($method){
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'approval':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $q = '
                    select * from (
                    select t1.*, t2.name approval_type_name
                    from approval t1
                        inner join approval_type t2 on t1.approval_type_id = t2.id
                    where t1.status>0
                ';
                
                $q_where=' and (t1.name like '.$lookup_str.' 
                        or t1.code like '.$lookup_str.' 
                        or t1.notes like '.$lookup_str.' 
                        or t2.name like '.$lookup_str.'
                        )';
                $q_group = ' ';
                $extra='';
                if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                else {$extra.=') tf order by moddate desc';}
                $extra .= ' limit '.(($page-1)*$records_page).', '.($records_page);
                $q_total_row = $q.$q_where.$q_group;
                $q_data = $q.$q_where.$q_group.$extra;
                $total_rows = $db->select_count($q_total_row,null,null);
                $result = array("header"=>array("total_rows"=>$total_rows),"data"=>$db->query_array($q_data));
                break;
        }
        
        echo json_encode($result);
    }
}

