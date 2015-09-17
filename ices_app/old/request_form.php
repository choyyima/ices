<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Request_Form extends MY_Controller {
    
    private $title='Request Form Mutation';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        get_instance()->load->helper('request_form/request_form_engine');
        $this->path = Request_Form_Engine::path_get();
        $this->title_icon = App_Icon::request_form();
        
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
        $form = $row->form_add()->form_set('title','Request Form List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value','New Request Form')
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $status_filter_opts = array(
            array('value'=>'','label'=>'ALL')
            ,array('value'=>'O','label'=>'OPENED')
            ,array('value'=>'C','label'=>'CLOSED')
            ,array('value'=>'X','label'=>'CANCELED')
            
        );
        
        $form->select_add()
                ->select_set('id','request_form_status_filter')
                ->select_set('options_add',$status_filter_opts)
                ;
        
        $cols = array(
            array("name"=>"request_form_code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"request_form_type_name","label"=>"Type","data_type"=>"text")
            ,array("name"=>"request_form_date","label"=>"Request Form <br/> Date","data_type"=>"text")
            ,array("name"=>"requester","label"=>"Requester","data_type"=>"text")            
            ,array("name"=>"request_form_status_name","label"=>"Status","data_type"=>"text")            
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/request_form')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'request_form_status_filter','field'=>'request_form_status')
                    ))
                ;        
        $js = ' $("#request_form_status_filter").on("change",function(){
                    ajax_table.methods.data_show(1);
                }); 
            ';
        $app->js_set($js);
        $app->render();
        
    }
    
    
    
    public function add(){
        $this->view('','add');
    }
    
    public function view($id="",$method="view"){

        $this->load->helper($this->path->request_form_engine);
        $this->load->helper($this->path->request_form_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Request_Form_Engine::request_form_exists($id)){
                    Message::set('error',array("Data doesn't exist"));
                    $cont = false;
                }
            }
        }
        
        if($cont){
        
            if($method=='add') $id = '';
            $data = array(
                'id'=>$id
            );
            
            $app = new App();            
            $app->set_title($this->title);
            $app->set_breadcrumb($this->title,strtolower($this->title));
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Request_Form_Renderer::request_form_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Request_Form_Renderer::request_form_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
            }
            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        
        
    }
    
    
    
    
    public function ajax_search($method=""){
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'request_form':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $additional_filter = '1=1';
                $final_rs = array();
                if($data['additional_filter']['request_form_status'] != '')
                    $additional_filter = 'request_form_status = '.$db->escape($data['additional_filter']['request_form_status']);
                $q = '
                select * from (
                    select distinct
                        t1.id
                        ,case t1.request_form_status 
                            when "O" then "OPENED"
                            when "X" then "CANCELED"
                            when "C" then "CLOSED"
                            end request_form_status_name
                        ,t1.code request_form_code
                        ,t1.request_form_date
                        ,concat(t6.first_name, " ",t6.last_name) requester
                        ,t4.name request_form_type_name
                        
                    from request_form t1
                        inner join request_form_type t4 on t1.request_form_type_id = t4.id
                        inner join user_login t6 on t1.requester_id = t6.id
                    where t1.status>0
                ';
                $q_group = ' )tfinal
                    ';
                $q_where=' 
                    and (t1.code like '.$lookup_str.'
                        or t6.name like '.$lookup_str.'
                    )
                    and '.$additional_filter.'
                ';

                $extra='';
                if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                else {$extra.=' order by request_form_code desc';}
                $extra .= '  limit '.(($page-1)*$records_page).', '.($records_page);
                $q_total_row = $q.$q_where.$q_group;
                $q_data = $q.$q_where.$q_group.$extra;
                $total_rows = $db->select_count($q_total_row,null,null);
                $rs = $db->query_array($q_data);


                $total_rs = count($rs);

                for($i = 0;$i<$total_rs;$i++){
                    $rs[$i]['request_form_status_name'] 
                            = SI::get_status_attr(
                                    $rs[$i]['request_form_status_name']
                                );
                }
                $final_rs = $rs;
                $result = array("header"=>array("total_rows"=>$total_rows),"data"=>$final_rs);
                
                break;
            case 'request_form_mutation_product':
                $db= new DB();
                $q = '
                    select distinct t1.id id, t1.name text 
                    from product t1
                    inner join product_unit t2 on t1.id = t2.product_id
                    where t1.status>0 
                        and( 
                            t1.name like '.$db->escape('%'.$data['data'].'%').'
                            or t1.code like '.$db->escape('%'.$data['data'].'%').'
                        )
                    order by t1.name
                    limit 100
                    ';
                $result = $db->query_array($q);
                break;
            
            case 'request_form_mutation_product_get':
                $db = new DB();
                $q = '
                    select t1.id product_id, t1.name product_name
                    from product t1
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $filename = 'img/product/'.$data['data'].'.jpg';
                    $rs[0]['product_img'] = '<img class = "product-img" src = "'.Tools::img_load($filename,false).'"></img>';
                    $result['product'] = $rs[0];
                }
                $q = '
                    select t3.id unit_id, t3.name unit_name
                    from product t1
                        inner join product_unit t2 on t1.id = t2.product_id
                        inner join unit t3 on t3.id = t2.unit_id
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0) $result['unit'] = $rs;
                
                break;
                
            case 'request_form_request_form_type_ajax_get':
                $db = new DB();
                $q = '
                    select t2.id, t2.name 
                    from request_form t1
                        inner join request_form_type t2 on t1.request_form_type_id = t2.id
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array_obj($q);
                $result = $rs[0];
                break;
            
            case 'request_form_request_form_mutation_ajax_get':
                get_instance()->load->helper('request_form/request_form_engine');
                $db = new DB();
                $q = '
                    select distinct t1.code
                        ,t1.request_form_date
                        ,t1.request_form_status                        
                        ,t3.id request_form_mutation_warehouse_to_id
                        ,t3.name request_form_mutation_warehouse_to_name
                        ,t5.id request_form_mutation_warehouse_from_id
                        ,t5.name request_form_mutation_warehouse_from_name
                        ,t1.notes
                        ,concat(t6.first_name, " ",t6.last_name) requester_name
                    from request_form t1
                        inner join request_form_mutation_warehouse_to t2 on t1.id = t2.request_form_id
                        inner join warehouse t3 on t3.id = t2.warehouse_id
                        inner join request_form_mutation_warehouse_from t4 on t1.id = t4.request_form_id
                        inner join warehouse t5 on t5.id = t4.warehouse_id
                        inner join user_login t6 on t1.requester_id = t6.id
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $rs[0]['request_form_status_name'] = SI::get_status_attr(
                            Request_Form_Engine::request_form_mutation_status_get($rs[0]['request_form_status'])['label']
                        );
                    $result = $rs[0];
                    
                }
                break;
                
            case 'request_form_request_form_mutation_product_ajax_get':
                $db = new DB();
                $q = '
                    select t4.name product_name
                        ,t3.name unit_name
                        ,t2.product_id
                        ,t2.qty
                    from request_form t1
                        inner join request_form_mutation_product t2 on t1.id = t2.request_form_id
                        inner join unit t3 on t2.unit_id = t3.id
                        inner join product t4 on t2.product_id = t4.id 
                    where t1.id = '.$db->escape($data['data']).'                
                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $filename = 'img/product/'.$data['data'].'.jpg';
                    $rs[$i]['product_img'] = '<img src = "'.Tools::img_load($filename,false).'"></img>';
                    $rs[$i]['qty'] = Tools::thousand_separator($rs[$i]['qty'],2,true);
                }
                $result = $rs;
                break;
            
        }
        
        echo json_encode($result);
    }
    
    public function data_support($method=""){
        //this function only used for urgently data retrieve
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'default_mutation_status_get':
                get_instance()->load->helper('request_form/request_form_engine');
                $result = Request_Form_Engine::request_form_mutation_status_default_status_get();
                if(isset($result['label'])){
                    $result['label'] = SI::get_status_attr($result['label']);
                }
                break;
            case 'next_allowed_mutation_status':
                get_instance()->load->helper('request_form/request_form_engine');
                $curr_status_val = isset($data['data'])?$data['data']:'';
                $result = Request_Form_Engine::request_form_mutation_status_next_allowed_status_get($curr_status_val);
                $num_of_result = count($result);
                for($i = 0;$i<$num_of_result;$i++){
                    if(Security_Engine::get_controller_permission(
                        User_Info::get()['user_id']
                            ,'request_form'
                            ,strtolower($result[$i]['method']))){
                            $result[$i]['label'] = SI::get_status_attr($result[$i]['label']);
                    }
                    else{
                        unset($result[$i]);
                    }
                }
                break;
            case 'request_form_current_status':
                $db = new DB();
                $q = 'select request_form_status from request_form where id = '.$db->escape($data['data']);
                $rs = $db->query_array_obj($q);
                if($rs>0){
                    $result = $rs[0]->request_form_status;
                }                  
                else $result = null;
                break;
            
        }
        
        echo json_encode($result);
    }
    
    public function mutation_add(){
        $this->load->helper($this->path->request_form_engine);
        $post = $this->input->post();
        if($post!= null){
            Request_Form_Engine::mutation_submit('','mutation_add',$post);
        }
    }
    
    public function mutation_opened($id){
        $this->load->helper($this->path->request_form_engine);
        $post = $this->input->post();
        if($post!= null){
            Request_Form_Engine::mutation_submit($id,'mutation_opened',$post);
        }
    }
    
    public function mutation_closed($id){
        $this->load->helper($this->path->request_form_engine);
        $post = $this->input->post();
        if($post!= null){
            Request_Form_Engine::mutation_submit($id,'mutation_closed',$post);
        }
    }
    
    public function mutation_canceled($id){
        $this->load->helper($this->path->request_form_engine);
        $post = $this->input->post();
        if($post!= null){
            Request_Form_Engine::mutation_submit($id,'mutation_canceled',$post);
        }
    }
    
    public function printing($method="",$id=''){
        get_instance()->load->helper($this->path->request_form_print);
        switch($method){
            case 'request_form':
                Request_Form_Print::print_request_form($id);
                break;
        }
    }
    
}

?>