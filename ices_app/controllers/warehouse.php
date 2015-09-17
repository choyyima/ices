<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Warehouse extends MY_Controller {
        
    private $index_url= "";
    private $title='Warehouse';
    private $title_icon = 'fa fa-cubes';
    
    function __construct(){
        parent::__construct();
        $this->index_url=  get_instance()->config->base_url().'warehouse';
    }
    public function add(){
        $this->edit();
    }

    public function edit($id=""){
        $this->load->helper('master/warehouse_engine');
        $db = new DB();
        $action = "Add";
        if(strlen($id)>0) $action = 'Edit';
        if($action != 'Add' && Warehouse_Engine::get($id) == null){
            Message::set('error',array("Data doesn't exist"));
            redirect($this->index_url);
        }

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
            ,'warehouse_manager'=>array()
            ,'warehouse_type'=>array()

        );
        
        //$selected_u_group=array("id"=>"","data"=>"");
        
        $post = $this->input->post();
        $app = new App();            
        $app->set_title($this->title);
        
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);
        $init_state = true;

        if($post != null){
            $init_state = false;
            
            $data['id'] = $id;
            $data['code'] = $post['code'];
            $data['name'] = $post['name'];            
            $data['address'] = $post['address'];
            $data['city'] = $post['city'];
            $data['country'] = $post['country'];
            $data['phone'] = $post['phone'];
            $data['email'] = $post['email'];
            $data['notes'] = $post['notes'];
            $data['warehouse_manager_id'] = $post['warehouse_manager_id'];
            $data['warehouse_type_id'] = $post['warehouse_type_id'];
            
            if(strlen($data['warehouse_manager_id'])>0){
                $manager_id = $data['warehouse_manager_id'];
                $rs_manager = $db->query_array('select id id, concat(first_name, " ",last_name) data from user_login where id = '.$db->escape($manager_id));
                if(count($rs_manager)>0){
                    $data['warehouse_manager'] = array('id'=>$rs_manager[0]['id'],'data'=>$rs_manager[0]['data']);
                }
            }
            
            if(strlen($data['warehouse_type_id'])>0){
                $type_id = $data['warehouse_type_id'];
                $rs_type = $db->query_array('select id id, concat(code," ",name) data from warehouse_type where id = '.$db->escape($type_id));
                if(count($rs_type)>0){
                    $data['warehouse_type'] = array('id'=>$rs_type[0]['id'],'data'=>$rs_type[0]['data']);
                }
            }
            
            if(Warehouse_Engine::save($data) == 1) redirect($this->index_url);

        }

        if(strlen($id)>0 && $init_state){ 
            $q = '
                select * 
                from warehouse
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
            
            $warehouse_manager_id = $rs[0]->warehouse_manager_id;
            $q = 'select * from user_login where id = '.$db->escape($warehouse_manager_id).'';
            $rs_user_login = $db->query_array_obj($q);
            if(count($rs_user_login)>0){
                $data['warehouse_manager'] = array('id'=>$rs_user_login[0]->id
                    ,'data'=>$rs_user_login[0]->first_name.' '.$rs_user_login[0]->last_name);
            }
            
            $warehouse_type_id = $rs[0]->warehouse_type_id;
            $q = 'select * from warehouse_type where id = '.$db->escape($warehouse_type_id).'';
            $rs_warehouse_type = $db->query_array_obj($q);
            $data['warehouse_type'] = array('id'=>$rs_warehouse_type[0]->id
                    ,'data'=>$rs_warehouse_type[0]->code.' '.$rs_warehouse_type[0]->name);
            
            
        }

        $row = $app->engine->div_add()->div_set('class','row');
        $form = $row->form_add()->form_set('title','Detail')->form_set('span','12');
        $form->input_add()->input_set('label','Code')->input_set('name','code')
                ->input_set('icon','fa fa-info')
                ->input_set('value',$data['code']);
        $form->input_add()->input_set('label','Name')->input_set('name','name')
                ->input_set('icon','fa fa-cubes')
                ->input_set('value',$data['name']);
        
        
        $q = '
            select id id, concat(code, " - ",name) data 
            from warehouse_type
            order by name
        ';
        $rs = $db->query_array($q);
        $warehouse_type = $rs;

        if(count($data['warehouse_type']) === 0){
            $data['warehouse_type'] = $warehouse_type[0];
        }
        
        $form->input_select_add()
                ->input_select_set('id','warehouse_type')
                ->input_select_set('name','warehouse_type_id')
                ->input_select_set('label','Warehouse Type')
                ->input_select_set('icon','fa fa-user')
                ->input_select_set('min_length','0')
                ->input_select_set('data_add',$warehouse_type)
                ->input_select_set('value',$data['warehouse_type'])
                ;
        
        $form->input_select_add()
                ->input_select_set('id','warehouse_manager')
                ->input_select_set('name','warehouse_manager_id')
                ->input_select_set('label','Warehouse Manager')
                ->input_select_set('icon','fa fa-user')
                ->input_select_set('min_length','1')
                ->input_select_set('ajax_url',$this->index_url.'/ajax_search/warehouse_manager_search')
                ->input_select_set('value',$data['warehouse_manager'])
                ;
        
        $form->input_add()->input_set('label','Address')->input_set('name','address')
                ->input_set('icon','fa fa-location-arrow')
                ->input_set('value',$data['address']);
        $form->input_add()->input_set('label','City')->input_set('name','city')
                ->input_set('icon','fa fa-location-arrow')
                ->input_set('value',$data['city']);
        $form->input_add()->input_set('label','Country')->input_set('name','country')
                ->input_set('icon','fa fa-location-arrow')
                ->input_set('value',$data['country']);
        $form->input_add()->input_set('label','Phone')->input_set('name','phone')
                ->input_set('icon','fa fa-phone') 
                ->input_set('value',$data['phone']);
        $form->input_add()->input_set('label','Email')->input_set('name','email')
                ->input_set('icon','fa fa-envelope')
                ->input_set('value',$data['email']);
         
        $form->textarea_add()->textarea_set('label','Notes')->textarea_set('name','notes')
                ->textarea_set('value',$data['notes'])
                ;
        
        
        
        $form->control_set($method='button','','primary','submit','','Submit','fa fa-save');
        $form->control_set($method='button','','default','button',$this->index_url,'Back',App_Icon::btn_back());
        $app->render();

    }

    public function delete($id=""){
        $data = array(
            "id"=>$id
            ,"status"=>0
        );
        $this->load->helper('master/warehouse_engine');
        if(Warehouse_Engine::Save($data) == 1){redirect($this->index_url);}
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
        $form = $row->form_add()->form_set('title','Warehouse List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value','New Warehouse')
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'warehouse/add');
        /*
        $controls = array(
            array(
                "label"=>'Edit'
                ,"base_url"=>$this->index_url.'/edit'
                ,"confirmation"=>false
            )
            ,array(
                "label"=>'Delete'
                ,"base_url"=>$this->index_url.'/delete'
                ,"confirmation"=>true
            )
        );
        */
        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"warehouse_type_code","label"=>"Type","data_type"=>"text")
            ,array("name"=>"name","label"=>"Name","data_type"=>"text")
            ,array("name"=>"address","label"=>"Address","data_type"=>"text")
            ,array("name"=>"city","label"=>"City","data_type"=>"text")
            ,array("name"=>"country","label"=>"Country","data_type"=>"text")
            ,array("name"=>"notes","label"=>"Notes","data_type"=>"text")
            
            

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->index_url.'/view')
                ->table_ajax_set('lookup_url',$this->index_url.'/ajax_search/warehouse')
                //->table_ajax_set('controls',$controls)
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
    }
    
    public function view($id = ""){
        $this->load->helper('master/warehouse_engine');
        $action = "View";
        
        if(Warehouse_Engine::get($id) == null){
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
        Warehouse_Engine::detail_render($detail_pane,array("id"=>$id));
        
        
        
        
        $app->render();
        
        
        
    }
    
    
    public function ajax_search($method){
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'warehouse':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $q = '
                    select t1.*, t2.code warehouse_type_code
                    from warehouse t1
                        left outer join warehouse_type t2 on t1.warehouse_type_id = t2.id
                    where t1.status>0
                ';
                
                $q_where=' and (t1.name like '.$lookup_str.' 
                        or t1.code like '.$lookup_str.' 
                        or t1.notes like '.$lookup_str.' 
                        )';
                
                $extra='';
                if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                else {$extra.=' order by t1.code asc';}
                $extra .= ' limit '.(($page-1)*$records_page).', '.($records_page);
                $q_total_row = $q.$q_where;
                $q_data = $q.$q_where.$extra;
                $total_rows = $db->select_count($q_total_row,null,null);
                $result = array("header"=>array("total_rows"=>$total_rows),"data"=>$db->query_array($q_data));
                break;
                
            case 'warehouse_manager_search':
                $db = new DB();
                $q = '
                    select id id, concat(first_name, " ",last_name) text 
                    from user_login 
                    where name like '.$db->escape('%'.$data['data'].'%').'
                        and status>0
                        and is_system  = 0
                ';
                $rs = $db->query_array($q);
                $result = $rs;
                break;
            

        }
        
        echo json_encode($result);
    }
}

