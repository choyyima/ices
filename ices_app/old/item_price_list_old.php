<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Item_Price_List extends MY_Controller {
        

    private $title='Item Price List';
    private $title_icon = 'fa fa-dollar';
    private $path = array(
        'index'=>''
        ,'item_price_list_engine'=>''
        ,'ajax_search'=>''
        ,'item_price_list_js'=>''
    );
    
    function __construct(){
        parent::__construct();
        $this->path = json_decode(json_encode($this->path));
        $this->path->index=  get_instance()->config->base_url().'item_price_list/';
        $this->path->item_price_list_engine=  'master/item_price_list_engine';
        $this->path->ajax_search=  $this->path->index.'ajax_search/';
        $this->path->item_price_list_js = "master/item/item_price_list/item_price_list_js";
        
        
    }
    public function add(){
        $this->edit();
    }

    public function edit($id=""){
        $this->load->helper($this->path->item_price_list_engine);

        $action = "Add";
        if(strlen($id)>0) $action = 'Edit';
        if($action != 'Add' && Item_Price_List_Engine::get($id) == null){
            Message::set('error',array("Data doesn't exist"));
            redirect($this->path->index);
        }
        

        $data = array(
            'item_price_list'=>array(
                'id'=>''
                ,'code'=>''
                ,'name'=>''
                ,'notes'=>''
            )
            ,'item_price_list_detail' => array()
        );
        
        
        $post = $this->input->post();

        $app = new App();            
        $app->set_title($this->title);
        
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);
        $init_state = true;
        
        if($post != null){
            $init_state = false;
            $post = json_decode($post,TRUE);
            $data['item_price_list']['id'] = $id;
            $data['item_price_list']['code'] = $post['item_price_list']['code'];
            $data['item_price_list']['name'] = $post['item_price_list']['name'];
            $data['item_price_list']['notes'] = $post['item_price_list']['notes'];
            $data['item_price_list_detail'] = $post['item_price_list_detail'];
            $result = Item_Price_List_Engine::save($data);
            echo json_encode($result);
            die();
        }

        if(strlen($id)>0 && $init_state){ 
            $db = new DB();
            $q = '
                select * 
                from item_price_list t1
                where t1.status>0 and t1.id = '.$db->escape($id).'
            ';
            $rs = $db->query_array_obj($q);

            foreach($rs as $row){
                $data['item_price_list']['id'] = $row->id;
                $data['item_price_list']['code'] = $row->code;
                $data['item_price_list']['name'] = $row->name;            
                $data['item_price_list']['notes'] = $row->notes;  
            }
            
            $q = '
                select  t2.id item_id, t2.name item_name
                    , t3.id unit_id, t3.name unit_name
                    ,t1.price_from, t1.price_to
                from item_price_list_detail t1
                    inner join item t2 on t1.item_id = t2.id
                    inner join unit t3 on t1.unit_id = t3.id
                where item_price_list_id =  '.$db->escape($id).'
                order by item_id, unit_id    
                    
                '
            ;
            $rs = $db->query_array($q);
            $data['item_price_list_detail'] = $rs;
        }

        $row = $app->engine->div_add()->div_set('class','row')->div_add();
        $main_div =$row->div_add()->div_set("span","12"); 
        $nav_tab = $main_div->nav_tab_add();
        $detail_tab = $nav_tab->nav_tab_set('items_add'
                ,array("id"=>'#detail',"value"=>"Detail"));
        $controller_tab = $nav_tab->nav_tab_set('items_add'
                ,array("id"=>'#price_list',"value"=>"Price List",'class'=>'active'));

        $detail_pane = $detail_tab->div_add()->div_set('id','detail')->div_set('class','tab-pane');
        $price_list_pane = $controller_tab->div_add()->div_set('id','price_list')->div_set('class','tab-pane active');
        
        Item_Price_List_Engine::detail_edit_render($detail_pane,$data);
        Item_Price_List_Engine::price_list_edit_render($price_list_pane,$data,$this->path,$app);
        
        $navigation = $nav_tab->div_add();
        
        $navigation->hr_add();
        $navigation->button_add()->button_set('value','Submit')
                ->button_set('id','item_price_list_submit')
                ->button_set('icon',App_Icon::detail_btn_save())
                
                ;
        $navigation->button_add()->button_set('value','Cancel')
                    ->button_set('icon',App_Icon::detail_btn_cancel())
                    ->button_set('href',$this->path->index)
                    ->button_set('class','btn btn-danger')
                ;
        
        
        
        
        $app->render();

    }

    public function delete($id=""){
        $data = array(
            'item_price_list'=>array(
                "id"=>$id
                ,"status"=>0
            )
            ,'item_price_list_detail'=>array()
        );
        $this->load->helper($this->path->item_price_list_engine);
        $result = Item_Price_list_Engine::Save($data);
        if($result['success'] == 1){redirect($this->path->index);}
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
        $form = $row->form_add()->form_set('title','Item Price List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value','New Item')
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'item_price_list/add');

        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"name","label"=>"Name","data_type"=>"text")
            ,array("name"=>"num_of_items","label"=>"Number of Items Defined","data_type"=>"text")
            ,array("name"=>"notes","label"=>"Notes","data_type"=>"text")           

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/price_list')
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
        
    }
    
    public function view($id = ""){
        $this->load->helper($this->path->item_price_list_engine);
        $action = "View";
        
        if(Item_Price_List_Engine::get($id) == null){
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
        Item_Price_List_Engine::detail_render($detail_pane,array("id"=>$id),$this->path);
        //Item_Price_List_Engine::price_list_render($detail_pane,array("id"=>$id));
        
        $app->render();
        
        
        
    }
    
    
    public function ajax_search($method){
        $data = json_decode(file_get_contents('php://input'), true);
        $result =array();
        switch($method){
            case 'item':
                $db= new DB();
                $q = '
                    select distinct t1.id id, t1.name text 
                    from item t1
                    inner join item_unit t2 on t1.id = t2.item_id
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
            case 'unit_item':
                $db= new DB();
                $q = '
                    select t1.id item_id, t1.name item_name, t3.id unit_id, t3.name unit_name
                    from item t1 
                    inner join item_unit t2 on t1.id = t2.item_id 
                    inner join unit t3 on t3.id = t2.unit_id and t3.status>0
                    
                    where  t1.id = '.$db->escape($data['data']).'
                    limit 100
                    ';
                $result = $db->query_array($q);
                break;
            case 'price_list':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $q = '
                    select t1.id, t1.code, t1.name, t1.notes, count(distinct t2.item_id) num_of_items
                    from item_price_list t1
                    inner join item_price_list_detail t2 on t1.id = t2.item_price_list_id
                    where t1.status>0
                ';
                $q_group = ' group by t1.id, t1.code, t1.name, t1.notes ';
                $q_where=' ';
                
                $extra='';
                if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                else {$extra.=' order by t1.code asc';}
                $extra .= '  limit '.(($page-1)*$records_page).', '.($records_page);
                $q_total_row = $q.$q_where.$q_group;
                $q_data = $q.$q_where.$q_group.$extra;
                $total_rows = $db->select_count($q_total_row,null,null);
                $result = array("header"=>array("total_rows"=>$total_rows),"data"=>$db->query_array($q_data));
                
                break;
        }
        
        echo json_encode($result);
    }
}

