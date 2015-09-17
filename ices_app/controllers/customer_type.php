<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Type extends MY_Controller {
        
    private $index_url= "";
    private $title='';
    private $title_icon = 'fa fa-users';
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Customer Type');
        $this->index_url=  get_instance()->config->base_url().'customer_type';
    }
    public function add(){
        $this->edit();
    }

    public function edit($id=""){
        $this->load->helper('master/customer_type_engine');
        $db = new DB();
        $action = "Add";
        if(strlen($id)>0) $action = 'Edit';
        if($action != 'Add' && Customer_Type_Engine::get($id) == null){
            Message::set('error',array("Data doesn't exist"));
            redirect($this->index_url);
        }

        $data = array(
            'id'=>''
            ,'code'=>''
            ,'name'=>''
            ,'notes'=>''
            ,'product_price_list'=>array()
            ,'refill_product_price_list'=>array()
        );
        $selected_product_price_list = array();
        $selected_refill_product_price_list = array();
        
        $post = $this->input->post();
        $app = new App();            
        $app->set_title($this->title);
        
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);
        $init_state = true;

        if($post != null){
            $ajax_post = false;
            
            if(is_string($post)){
                if(json_decode($post)!= null){
                    $post = json_decode($post,true);
                }
            }
            if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
            $init_state = false;
            $data['id'] = $id;
            $data['code'] = $post['code'];
            $data['name'] = $post['name'];            
            $data['notes'] = $post['notes'];    
            $data['product_price_list'] = $post['product_price_list'];
            $data['refill_product_price_list'] = $post['refill_product_price_list'];
            
            $result = Customer_Type_Engine::save($data);
            
            if(!$ajax_post){
                if( $result['success']== 1) redirect($this->index_url);
            }            
            else{
                echo json_encode($result);
                die();
            }
            

        }

        if(strlen($id)>0 && $init_state){ 
            $db = new DB();
            $q = '
                select * 
                from customer_type
                where id = '.$db->escape($id).'
            ';
            $rs = $db->query_array_obj($q);

            foreach($rs as $row){
                $data['id'] = $row->id;
                $data['code'] = $row->code;
                $data['name'] = $row->name;            
                $data['notes'] = $row->notes;  
            }
            
            $q = '
                select t2.product_price_list_id id 
                from customer_type t1 
                    inner join customer_type_product_price_list t2 on t2.customer_type_id = t1.id
                    inner join product_price_list t3 on t2.product_price_list_id = t3.id
                where t1.id = '.$db->escape($id).'
            ';
            $rs = $db->query_array_obj($q);
            
            foreach($rs as $row){
                $selected_product_price_list[] = $row->id;
            }
            
            $q = '
                select t2.refill_product_price_list_id id 
                from customer_type t1 
                    inner join customer_type_refill_product_price_list t2 on t2.customer_type_id = t1.id
                    inner join refill_product_price_list t3 on t2.refill_product_price_list_id = t3.id
                where t1.id = '.$db->escape($id).'
            ';
            $rs = $db->query_array_obj($q);
            
            foreach($rs as $row){
                $selected_refill_product_price_list[] = $row->id;
            }
        }

        $row = $app->engine->div_add()->div_set('class','row');
        $form = $row->form_add()->form_set('title','Detail')->form_set('span','12');
        $form->input_add()->input_set('label','Code')->input_set('name','code')
                ->input_set('id','code')
                ->input_set('icon','fa fa-info')
                ->input_set('value',$data['code']);
        $form->input_add()->input_set('label','Name')->input_set('name','name')
                ->input_set('id','name')
                ->input_set('icon','fa fa-tag')
                ->input_set('value',$data['name']);  
        
        $product_price_list_columns = array(
            array(
                "name"=>"code"
                ,"label"=>"Code"
            )
            ,array(
                "name"=>"name"
                ,"label"=>"Name"
            )
        );
        
        $q ='
            select id id,name data
            from product_price_list
            where status>0
        ';
        
        $product_price_list = $db->query_array($q);
        
        $product_price_list_ist = $form->input_select_table_add();
        $product_price_list_ist->input_select_set('name','unit_id')
                ->input_select_set('id','input_select')
                ->input_select_set('label','Product Price List')
                ->input_select_set('icon','fa fa-tag')
                ->input_select_set('min_length','0')
                ->input_select_set('data_add',$product_price_list)
                ->input_select_set('value',array("id"=>"","data"=>""))
                ->table_set('columns',$product_price_list_columns)
                ->table_set('id',"product_price_list_table")
                ->table_set('ajax_url',get_instance()->config->base_url().'customer_type/ajax_search/product_price_list')
                ->table_set('column_key','id')
                ->table_set('allow_duplicate_id',false)
                ->table_set('selected_value',$selected_product_price_list);
                ;
        
        $refill_product_price_list_columns = array(
            array(
                "name"=>"code"
                ,"label"=>"Code"
            )
            ,array(
                "name"=>"name"
                ,"label"=>"Name"
            )
        );        
        
        $q ='
            select id id,name data
            from refill_product_price_list
            where status>0
        ';
        
        $refill_product_price_list = $db->query_array($q);
        
        $refill_product_price_list_ist = $form->input_select_table_add();
        $refill_product_price_list_ist->input_select_set('name','')
                ->input_select_set('id','input_select_refill_product_price_list')
                ->input_select_set('label','Refill Product Price List')
                ->input_select_set('icon','fa fa-tag')
                ->input_select_set('min_length','0')
                ->input_select_set('data_add',$refill_product_price_list)
                ->input_select_set('value',array("id"=>"","data"=>""))
                ->table_set('columns',$refill_product_price_list_columns)
                ->table_set('id',"refill_product_price_list_table")
                ->table_set('ajax_url',get_instance()->config->base_url().'customer_type/ajax_search/refill_product_price_list')
                ->table_set('column_key','id')
                ->table_set('allow_duplicate_id',false)
                ->table_set('selected_value',$selected_refill_product_price_list);
                ;
                
        $form->textarea_add()->textarea_set('label','Notes')->textarea_set('name','notes')
                ->textarea_set('id','notes')
                ->textarea_set('value',$data['notes'])
                ;
        
        
        
        $form->control_set($method='button','customer_type_button_save','primary','submit','','Submit','fa fa-save');
        $form->control_set($method='button','','default','button',$this->index_url,'BACK',  App_Icon::btn_back());
        
        $param = array(
            'index'=>get_instance()->config->base_url().'customer_type/'
        );
        $js = get_instance()->load->view('master/customer/customer_type/customer_type_js',$param,TRUE);
        $app->js_set($js);
        
        $app->render();

    }

    public function delete($id=""){
        $data = array(
            "id"=>$id
            ,"status"=>0
        );
        $this->load->helper('master/customer_type_engine');
        if(Customer_Type_Engine::save($data)['success'] == 1){redirect($this->index_url);}
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
        $form = $row->form_add()->form_set('title',Lang::get(array('Customer Type','List')))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Customer Type')))
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'customer_type/add');
        
        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"name","label"=>"Name","data_type"=>"text")
            ,array("name"=>"notes","label"=>"Notes","data_type"=>"text")
            

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->index_url.'/view')
                ->table_ajax_set('lookup_url',$this->index_url.'/ajax_search/customer_type')
                //->table_ajax_set('controls',$controls)
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
    }
    
    public function view($id = ""){
        $this->load->helper('master/customer_type_engine');
        $action = "View";
        
        if(Customer_Type_Engine::get($id) == null){
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
        Customer_Type_Engine::detail_render($detail_pane,array("id"=>$id));
        
        
        
        
        $app->render();
        
        
        
    }
    
    
    public function ajax_search($method){
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'customer_type':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $q = '
                    select *

                    from customer_type t1
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
                
            case 'product_price_list':
                $db = new DB();
                $q = 'select * from product_price_list where id = '.$db->escape($data['data']);
                $result = $db->query_array($q);
                break;
            
            case 'refill_product_price_list':
                $db = new DB();
                $q = 'select * from refill_product_price_list where id = '.$db->escape($data['data']);
                $result = $db->query_array($q);
                break;
        }
        
        echo json_encode($result);
    }
}

