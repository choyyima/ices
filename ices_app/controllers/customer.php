<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer extends MY_Controller {
        
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = 'Customer';
        get_instance()->load->helper('customer/customer_engine');
        $this->path = Customer_Engine::path_get();
        $this->title_icon = App_Icon::customer();
    }
    
    public function index()
    {           
        //<editor-fold defaultstate="collapsed">
        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower('customer'));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get(array('Customer','List')))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Customer')))
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'customer/add');
        
        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"name","label"=>"Name","data_type"=>"text")
            ,array("name"=>"notes","label"=>"Notes","data_type"=>"text")
            ,array("name"=>"customer_status_name","label"=>"Status","data_type"=>"text")

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/customer')
                //->table_ajax_set('controls',$controls)
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
        //</editor-fold>
    }
    
    public function add(){
        $this->load->helper($this->path->customer_engine);
        $post = $this->input->post();        
        
        if($post!= null){
            $param = array('id'=>'','method'=>'customer_add','primary_data_key'=>'customer','data_post'=>$post);            
            SI::data_submit()->submit('customer_engine',$param);
        }
        else{
            $this->view('','add');
        }
    }
    
    public function view($id = "",$method="view"){
        
        $this->load->helper($this->path->customer_engine);
        $this->load->helper($this->path->customer_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Customer_Engine::customer_exists($id)){
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
            Customer_Renderer::customer_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Customer_Renderer::customer_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
                $customer_type_history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#type_log_tab',"value"=>"Type Log"));
                $customer_type_history_pane = $customer_type_history_tab->div_add()->div_set('id','type_log_tab')->div_set('class','tab-pane');
                Customer_Renderer::customer_type_log_render($app,$customer_type_history_pane,array("id"=>$id),$this->path);
            }
            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
    }
    
    
    public function ajax_search($method=''){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'customer':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $q = '
                    select *
                        ,case customer_status when "A" then "ACTIVE" when "I" then "INACTIVE" end customer_status_name
                    from customer t1
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
                //</editor-fold>
                break;
            case 'customer_type':
                $db = new DB();
                $q = 'select * from customer_type where id = '.$db->escape($data['data']);
                $result = $db->query_array($q);
                break;
                
            

        }
        
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function data_support($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        switch($method){
            case 'customer_get':
                $db = new DB();
                $result = null;
                $q = '
                    select *
                    , case customer_status when "A" then "ACTIVE"
                        when "I" then "INACTIVE" end customer_status_name
                    ,case is_credit when "1" then "True"
                        else "False" end is_credit_text
                    ,case is_sales_receipt_outstanding when "1" then "True"
                        else "False" end is_sales_receipt_outstanding_text
                    from customer
                    where id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $rs[0]['customer_credit'] = Tools::thousand_separator($rs[0]['customer_credit']);
                    $rs[0]['customer_debit'] = Tools::thousand_separator($rs[0]['customer_debit']);
                    $result['response'] = $rs[0];
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('customer_engine',
                            $result['response']['customer_status']
                        );
                                        
                    $result['response']['customer_status_list'] = $next_allowed_status_list;
                }
                
                break;
            
            case 'customer_type_get':
                $db = new DB();
                $result = null;
                $q = '
                    select t3.id, t3.name
                    
                    from customer t1
                        inner join customer_customer_type t2 on t1.id = t2.customer_id
                        inner join customer_type t3 on t3.id = t2.customer_type_id
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array_obj($q);
                if(count($rs)>0) $result['response'] = $rs;
                
                
                break;
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function customer_active($id=''){
        $this->load->helper($this->path->customer_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'customer_active','primary_data_key'=>'customer','data_post'=>$post);
            SI::data_submit()->submit('customer_engine',$param);
        }        
    }
    
    public function customer_inactive($id=''){
        $this->load->helper($this->path->customer_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'customer_inactive','primary_data_key'=>'customer','data_post'=>$post);
            SI::data_submit()->submit('customer_engine',$param);
        }
        
    }
    
    
}

