<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Store extends MY_Controller {
        
    private $title='Store';
    
    private $path = array(
        'index'=>''
        ,'store_engine'=>''
        ,'ajax_search'=>''
        ,'store_js'
    );
    
    private $title_icon = '';
    
    function __construct(){
        parent::__construct();
        $this->path = json_decode(json_encode($this->path));
        $this->path->index=  get_instance()->config->base_url().'store/';
        $this->path->store_engine=  'company/store_engine';
        $this->path->ajax_search=  $this->path->index.'ajax_search/';
        $this->path->store_js=  'company/store_js';
        $this->title_icon = App_Icon::store();
    }
    public function add(){
        $this->edit();
    }
    
    public function edit($id=""){
        $this->load->helper($this->path->store_engine);

        $action = "Add";
        if(strlen($id)>0) $action = 'Edit';
        if($action != 'Add' && Store_Engine::get($id) == null){
            Message::set('error',array("Data doesn't exist"));
            redirect($this->path->index);
        }
        $db = new DB();
        $data = array(
            'id'=>''
            ,'code'=>''
            ,'name'=>''
            ,'address'=>''
            ,'phone'=>''
            ,'address'=>''
            ,'city'=>''
            ,'country'=>''            
            ,'email'=>''
            ,'notes'=>''
        );
        
        $selected_warehouse=array();
        
        $post = $this->input->post();
        $app = new App();            
        $app->set_title($this->title);
        
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);
        $init_state = true;

        if($post != null){
            $init_state = false;
            
            $ajax_post = false;
            
            if(is_string($post)){
                if(json_decode($post)!= null){
                    $post = json_decode($post,true);
                }
            }
            
            if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
            
            $data['id'] = $id;
            $data['code'] = $post['code'];
            $data['name'] = $post['name'];            
            $data['address'] = $post['address'];
            $data['city'] = $post['city'];
            $data['country'] = $post['country'];
            $data['phone'] = $post['phone'];
            $data['email'] = $post['email'];
            $data['notes'] = $post['notes'];
            $data['warehouse_id'] = $post['warehouse_id'];

            $result = Store_Engine::save($data);
            
            if(!$ajax_post){
                if( $result['success']== 1) redirect($this->index_url);
            }            
            else{
                echo json_encode($result);
                die();
            }
        }

        if(strlen($id)>0 && $init_state){ 
            
            $q = '
                select * 
                from store
                where id = '.$db->escape($id).'
            ';
            $rs = $db->query_array_obj($q);

            foreach($rs as $row){
                $data['id'] = $row->id;
                $data['code'] = $row->code;
                $data['name'] = $row->name;            
                $data['address'] = $row->address;
                $data['city'] = $row->city;
                $data['country'] = $row->country;
                $data['phone'] = $row->phone;
                $data['email'] = $row->email;
                $data['notes'] = $row->notes;
            }
            
            $q = '
                select t2.id id
                from store_warehouse t1 inner join warehouse t2 on t1.warehouse_id = t2.id
                where t1.store_id = '.$db->escape($id).'
            ';
            $rs = $db->query_array_obj($q);
            
            foreach($rs as $row){
                $selected_warehouse[] = $row->id;
            }
            
        }

        $row = $app->engine->div_add()->div_set('class','row');
        $form = $row->form_add()->form_set('title','Detail')->form_set('span','12');
        $form->input_add()->input_set('label','Code')->input_set('id','code')
                ->input_set('icon','fa fa-info')
                ->input_set('value',$data['code']);
        $form->input_add()->input_set('label','Name')->input_set('id','name')
                ->input_set('icon','fa fa-cubes')
                ->input_set('value',$data['name']);
        $form->input_add()->input_set('label','Address')->input_set('id','address')
                ->input_set('icon','fa fa-location-arrow')
                ->input_set('value',$data['address']);
        $form->input_add()->input_set('label','City')->input_set('id','city')
                ->input_set('icon','fa fa-location-arrow')
                ->input_set('value',$data['city']);
        $form->input_add()->input_set('label','Country')->input_set('id','country')
                ->input_set('icon','fa fa-location-arrow')
                ->input_set('value',$data['country']);
        $form->input_add()->input_set('label','Phone')->input_set('id','phone')
                ->input_set('icon','fa fa-phone') 
                ->input_set('value',$data['phone']);
        $form->input_add()->input_set('label','Email')->input_set('id','email')
                ->input_set('icon','fa fa-envelope')
                ->input_set('value',$data['email']);         
        
        
        $q ='
            select id id,name data
            from warehouse
            where status>0
        ';
        
        $warehouse_list = $db->query_array($q);
        
        $warehouse_columns = array(
            array(
                "name"=>"code"
                ,"label"=>"Code"
            )
            ,array(
                "name"=>"name"
                ,"label"=>"Name"
            )
        );
        
        $warehouse_ist = $form->input_select_table_add();
        $warehouse_ist->input_select_set('name','unit_id')
                ->input_select_set('id','input_select')
                ->input_select_set('label','Warehouse')
                ->input_select_set('icon','fa fa-tag')
                ->input_select_set('min_length','1')
                ->input_select_set('data_add',$warehouse_list)
                ->input_select_set('value',array("id"=>"","data"=>""))
                ->table_set('columns',$warehouse_columns)
                ->table_set('id',"warehouse_table")
                ->table_set('ajax_url',$this->path->ajax_search.'warehouse')
                ->table_set('column_key','id')
                ->table_set('allow_duplicate_id',false)
                ->table_set('selected_value',$selected_warehouse);
                ;
        
        $form->textarea_add()->textarea_set('label','Notes')->textarea_set('id','notes')
            ->textarea_set('value',$data['notes'])
            ;
                
                
        $form->control_set('button','store_button_save','primary','submit','','Submit','fa fa-save');
        $form->control_set('button','','danger','button',$this->path->index,'Cancel','fa fa-times');
        
        $param = array('index'=>$this->path->index);
        $js = get_instance()->load->view($this->path->store_js,$param,TRUE);
        $app->js_set($js);
        $app->render();

    }

    public function delete($id=""){
        $data = array(
            "id"=>$id
            ,"status"=>0
        );
        $this->load->helper('company/store_engine');
        if(Store_Engine::Save($data)['success'] == 1){redirect($this->path->index);}
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
        $form = $row->form_add()->form_set('title','Store List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value','New Store')
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'store/add');

        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"name","label"=>"Name","data_type"=>"text")
            ,array("name"=>"warehouse","label"=>"Warehouse","data_type"=>"text")

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'/ajax_search/store')
                //->table_ajax_set('controls',$controls)
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
    }
    
    public function view($id = ""){
        $this->load->helper($this->path->store_engine);
        $action = "View";
        
        if(Store_Engine::get($id) == null){
            Message::set('error',array("Data doesn't exist"));
            redirect($this->index_url);
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
        Store_Engine::detail_render($detail_pane,array("id"=>$id));
        
        $app->render();
        
        
        
    }
    
    
    public function ajax_search($method){
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'store':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $q = '
                    select t1.id, t1.code,t1.name, group_concat(t3.name SEPARATOR ", ") warehouse
                    from store t1
                        left outer join store_warehouse t2 on t1.id = t2.store_id
                        left outer join warehouse t3 on t2.warehouse_id = t3.id
                    where t1.status>0
                ';
                
                $q_where=' and (t1.name like '.$lookup_str.' 
                        or t1.code like '.$lookup_str.' 
                        or t1.notes like '.$lookup_str.' 
                        )';
                
                $q_group = ' group by t1.code, t1.id, t1.name ';
                $extra='';
                
                if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                else {$extra.=' order by t1.code asc';}
                $extra .= ' limit '.(($page-1)*$records_page).', '.($records_page);
                $q_total_row = $q.$q_where.$q_group;
                $q_data = $q.$q_where.$q_group.$extra;
                $total_rows = $db->select_count($q_total_row,null,null);
                $result = array("header"=>array("total_rows"=>$total_rows),"data"=>$db->query_array($q_data));
                break;
                
            case 'warehouse':
                $db = new DB();
                $q = 'select * from warehouse where id = '.$db->escape($data['data']);
                $result = $db->query_array($q);
                break;

        }
            
        
        echo json_encode($result);
    }
    
    public function data_support($method="",$submethod=""){
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[],'response'=>array());
        $msg=[];
        $success = 1;
        $response = array();
        $db = new DB();

        switch($method){
            case 'default_store_get':
                $response = array();
                $store_id = isset(User_Info::get()['default_store_id'])?User_Info::get()['default_store_id']:'';
                $q = 'select id id, name name from store where status>0 order by id';          
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $response = array('id'=>$rs[0]['id'],'name'=>$rs[0]['name']);
                    foreach($rs as $i=>$row){
                        if($row['id'] === $store_id){
                            $response = array(
                                'id'=>$row['id'],
                                'name' => $row['name']
                            );
                        }
                    }
                }
                break;
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
    }
}

