<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_SubCategory extends MY_Controller {
        
    private $index_url= "";
    private $title='Product Sub Category';
    private $title_icon = 'fa fa-tags';
    
    function __construct(){
        parent::__construct();
        $this->index_url=  get_instance()->config->base_url().'product_subcategory';
    }
    public function add(){
        $this->edit();
    }

    public function edit($id=""){
        $this->load->helper('master/product_subcategory_engine');

        $action = "Add";
        if(strlen($id)>0) $action = 'Edit';
        if($action != 'Add' && Product_SubCategory_Engine::get($id) == null){
            Message::set('error',array("Data doesn't exist"));
            redirect($this->index_url);
        }
        

        $data = array(
            'id'=>''
            ,'code'=>''
            ,'name'=>''
            ,'notes'=>''
            ,'product_category_id' => ''
        );
        $selected_product_category=array("id"=>'','data'=>'');
        
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
            $data['notes'] = $post['notes'];
            $data['product_category_id'] = $post['product_category_id'];
            if(Product_SubCategory_Engine::save($data) == 1) redirect($this->index_url);

        }

        if(strlen($id)>0 && $init_state){ 
            $db = new DB();
            $q = '
                select * 
                from product_subcategory
                where id = '.$db->escape($id).'
            ';
            $rs = $db->query_array_obj($q);

            foreach($rs as $row){
                $data['id'] = $row->id;
                $data['code'] = $row->code;
                $data['name'] = $row->name;            
                $data['notes'] = $row->notes;  
                $data['product_category_id'] = $row->product_category_id;
            }
        }

        $row = $app->engine->div_add()->div_set('class','row');
        $form = $row->form_add()->form_set('title','Detail')->form_set('span','12');
        $form->input_add()->input_set('label','Code')->input_set('name','code')
                ->input_set('icon','fa fa-info')
                ->input_set('value',$data['code']);
        $form->input_add()->input_set('label','Name')->input_set('name','name')
                ->input_set('icon','fa fa-tags')
                ->input_set('value',$data['name']);
        
        get_instance()->load->helper('master/product_category_engine');
        $product_category = Product_Category_Engine::get($data['product_category_id']);
        if(count($product_category)>0){                    
            $selected_product_category['id'] = $product_category->id;
            $selected_product_category['data'] = $product_category->name;
        }
        
        $product_category = $form->input_select_add();
        $product_category->input_select_set('name','product_category_id')
                ->input_select_set('id','product_category')
                ->input_select_set('label','Product Category')
                ->input_select_set('icon','fa fa-tag')
                ->input_select_set('min_length','1')
                ->input_select_set('ajax_url',$this->index_url.'/ajax_search/product_category')
                ->input_select_set('value',$selected_product_category)
                ;
        
        $form->textarea_add()->textarea_set('label','Notes')->textarea_set('name','notes')
                ->textarea_set('value',$data['notes'])
                ;
        
        
        
        $form->control_set($method='button','','primary','submit','','Submit','fa fa-save');
        $form->control_set($method='button','','danger','button',$this->index_url,'Cancel','fa fa-times');
        $app->render();

    }

    public function delete($id=""){
        $data = array(
            "id"=>$id
            ,"status"=>0
        );
        $this->load->helper('master/product_subcategory_engine');
        if(Product_SubCategory_Engine::Save($data) == 1){redirect($this->index_url);}
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
        $form = $row->form_add()->form_set('title','Product Sub Category List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value','New Product Sub Category')
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'product_subcategory/add');

        $cols = array(
            array("name"=>"product_category_name","label"=>"Category","data_type"=>"text")
            ,array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"name","label"=>"Name","data_type"=>"text")
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->index_url.'/view')
                ->table_ajax_set('lookup_url',$this->index_url.'/ajax_search/product_subcategory')
                //->table_ajax_set('controls',$controls)
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
    }
    
    public function view($id = ""){
        $this->load->helper('master/product_subcategory_engine');
        $action = "View";
        
        if(Product_SubCategory_Engine::get($id) == null){
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
        Product_SubCategory_Engine::detail_render($detail_pane,array("id"=>$id));
        
        
        
        
        $app->render();
        
        
        
    }
    
    
    public function ajax_search($method){
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'product_category':
                $db= new DB();
                $q = '
                    select id id, name text 
                    from product_category 
                    where status>0 
                        and( 
                            name like '.$db->escape('%'.$data['data'].'%').'
                            or code like '.$db->escape('%'.$data['data'].'%').'
                        )
                    ';
                $result = $db->query_array($q);
                break;
            case 'product_subcategory':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $q = '
                    select t1.*,t2.name product_category_name

                    from product_subcategory t1
                    inner join product_category t2 on t2.id = t1.product_category_id
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

        }
        
        echo json_encode($result);
    }
}

