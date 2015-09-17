<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_Price_List extends MY_Controller {
        

    private $title='';
    private $title_icon = 'fa fa-dollar';
    private $path = array(
        
    );
    
    function __construct(){
        parent::__construct();
        $this->title = 'Product Price List';
        get_instance()->load->helper('product_price_list/product_price_list_engine');
        $this->path = Product_Price_List_Engine::path_get();
        $this->title_icon = App_Icon::product_price_list();
        
        
        
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
        $form = $row->form_add()->form_set('title',Lang::get(array('Product Price List','List')))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Product Price List')))
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'product_price_list/add');

        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"name","label"=>"Name","data_type"=>"text")
            ,array("name"=>"num_of_products","label"=>"Number of Products","data_type"=>"text")
            ,array("name"=>"status_name","label"=>"Status","data_type"=>"text")           

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/price_list')
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
        
    }
    
    public function view($id="",$method="view"){
        
        $this->load->helper($this->path->product_price_list_engine);
        $this->load->helper($this->path->product_price_list_renderer);
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Product_Price_List_Engine::product_price_list_exists($id)){
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
            Product_Price_List_Renderer::product_price_list_render($app,$detail_pane,array("id"=>$id),$method);
            if($method === 'view'){
                $moq_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#delivery_moq_tab',"value"=>"Delivery Min. Order Qty"));
                $moq_pane = $moq_tab->div_add()->div_set('id','delivery_moq_tab')->div_set('class','tab-pane');
                Product_Price_List_Renderer::delivery_moq_view_render($app,$moq_pane,array("id"=>$id));
                
                $mop_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#delivery_mop_tab',"value"=>"Delivery Min. Order Price"));
                $mop_pane = $mop_tab->div_add()->div_set('id','delivery_mop_tab')->div_set('class','tab-pane');
                Product_Price_List_Renderer::delivery_mop_view_render($app,$mop_pane,array("id"=>$id));
                
                $dextra_charge_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#delivery_extra_charge_tab',"value"=>"Delivery Extra Charge"));
                $dextra_charge_pane = $dextra_charge_tab->div_add()->div_set('id','delivery_extra_charge_tab')->div_set('class','tab-pane');
                Product_Price_List_Renderer::delivery_extra_charge_view_render($app,$dextra_charge_pane,array("id"=>$id));
                                
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Product_Price_List_Renderer::product_price_list_status_log_render($app,$history_pane,array("id"=>$id));
            }
            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        
        
    }
    
    
    public function ajax_search($method){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $result =array();
        $row_limit = 15;
        switch($method){
            case 'product':
                $db= new DB();
                $exceptions = '';
                $q = '
                    select distinct t1.id id, t1.name text 
                    from product t1
                    inner join product_unit t2 
                        on t1.id = t2.product_id
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
            case 'product_unit':
                $db= new DB();
                $q = '
                    select t1.id product_id, t1.name product_name, t3.id unit_id, t3.name unit_name
                    from product t1 
                    inner join product_unit t2 on t1.id = t2.product_id 
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
                    select t1.id, t1.code, t1.name, t1.notes
                        , count(distinct t2.product_id,t2.unit_id) num_of_products
                        ,t1.product_price_list_status
                    from product_price_list t1
                    inner join product_price_list_product t2 
                        on t1.id = t2.product_price_list_id
                    where t1.status>0
                ';
                $q_group = ' group by t1.id, t1.code, t1.name, t1.notes,t1.product_price_list_status ';
                $q_where=' ';
                
                $extra='';
                if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                else {$extra.=' order by t1.code asc';}
                $extra .= '  limit '.(($page-1)*$records_page).', '.($records_page);
                $q_total_row = $q.$q_where.$q_group;
                $q_data = $q.$q_where.$q_group.$extra;
                $total_rows = $db->select_count($q_total_row,null,null);
                $result = array("header"=>array("total_rows"=>$total_rows),"data"=>$db->query_array($q_data));
                $result['data'] = json_decode(json_encode($result['data']));
                foreach($result['data'] as $i=>$row){
                    $row->status_name = SI::get_status_attr(
                        SI::status_get('product_price_list_engine', $row->product_price_list_status)['label']
                    );
                }
                $result['data'] = json_decode(json_encode($result['data']),true);
                break;
            case 'input_select_product_search':
                $db= new DB();
                $exceptions = '';
                $q = '
                    select distinct t1.id id, t1.name, t1.code
                    from product t1
                    inner join product_unit t2 on t1.id = t2.product_id
                    where t1.status>0 
                        and( 
                            t1.name like '.$db->escape('%'.$data['data'].'%').'
                            or t1.code like '.$db->escape('%'.$data['data'].'%').'
                        )
                    order by t1.name
                    limit 0, '.$row_limit.'
                    ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['text'] = '<strong>'.$rs[$i]['code'].'</strong> '.$rs[$i]['name'];
                }
                $result['response'] = $rs;
                
                break;
            
            
        }
        
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function data_support($method="",$submethod="",$submethod2=""){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        switch($method){
            case 'product_unit_get':
                $db= new DB();
                $product_id = isset($data['product_id'])?$data['product_id']:'';
                $q = '
                    select t1.id product_id, t1.code product_code, t1.name product_name, t3.id unit_id, t3.name unit_name
                    from product t1 
                    inner join product_unit t2 on t1.id = t2.product_id 
                    inner join unit t3 on t3.id = t2.unit_id and t3.status>0                    
                    where  t1.id = '.$db->escape($product_id).'
                    limit 100
                    ';
                $rs = null;
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $filename = 'img/product/'.$product_id.'.jpg';                    
                    $rs[$i]['product_img'] = '';
                    $rs[$i]['product_text'] = SI::html_tag('strong',$rs[$i]['product_code'])
                        .' '.$rs[$i]['product_name'];
                    //$rs[$i]['product_img']='<img src = "'.Tools::img_load($filename,false).'" class="product-img"></img>';
                    
                }
                $result['response'] = $rs;
                break;
            case 'product_price_list_get':
                $db = new DB();
                $id = isset($data['data'])?$data['data']:'';
                $rs = null;
                $q = '
                    select t1.* 
                    from product_price_list t1
                    where t1.id = '.$db->escape($id).'
                ';
                $rs = $db->query_array($q);
                $product_price_list = array();
                
                if(count($rs)>0){
                    $product_price_list = $rs[0];
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('product_price_list_engine',
                            $product_price_list['product_price_list_status']
                        );
                    
                    $product_price_list['product_price_list_status_text'] = SI::get_status_attr(
                        SI::status_get('product_price_list_engine',
                            $product_price_list['product_price_list_status']
                        )['label']
                    );
                    
                    $result['response']['product_price_list'] = $product_price_list;
                    $result['response']['product_price_list_status_list'] = $next_allowed_status_list;
                }
                
                
                break;
            case 'product_price_list_product_get':
                $db = new DB();
                $id = isset($data['data'])?$data['data']:'';
                $rs = null;
                $q = '
                    select t1.*,t2.code product_code,t2.name product_name
                    , t3.name unit_name
                    from product_price_list_product t1
                        inner join product t2 on t1.product_id = t2.id
                        inner join unit t3 on t1.unit_id = t3.id
                    where t1.product_price_list_id = '.$db->escape($id).'
                    order by t2.code,t2.name, t1.unit_id, t1.min_qty
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    for($i = 0;$i<count($rs);$i++){
                        $rs[$i]['product_img']='';
                        $rs[$i]['product_text'] = SI::html_tag('strong',$rs[$i]['product_code'])
                            .' '.$rs[$i]['product_name'];
                    }
                }
                $result['response'] = $rs;
                break;
            case 'delivery_moq':
                //<editor-fold defaultstate="collapsed">
                switch($submethod){
                    case 'delivery_moq_get':
                        $db = new DB();
                        $id = isset($data['delivery_moq_id'])?
                                $data['delivery_moq_id']:'';
                        $q = '
                            select t1.*
                            from product_price_list_delivery_moq t1
                            where t1.id = '.$db->escape($id).'
                        ';
                        $response = array();
                        $rs = $db->query_array($q);
                        if(count($rs)>0){
                            $response = $rs[0];
                            $response['calculation_type_name'] = ucfirst($response['calculation_type']);
                        }
                        $result['response'] = $response;
                        break;
                    case 'mixed':
                        switch($submethod2){
                            case 'product_get':
                                $db = new DB();
                                $product_price_list_id = isset($data['product_price_list_id'])?$data['product_price_list_id']:'';
                                $response = array();
                                $q = ' 
                                    select distinct t2.id product_id
                                        ,t2.code product_code
                                        ,t2.name product_name
                                    from product_price_list_product t1
                                        inner join product t2 on t1.product_id = t2.id
                                    where t1.product_price_list_id = '.$db->escape($product_price_list_id).'
                                    order by t2.name asc
                                ';
                                $rs = $db->query_array($q);
                                if(count($rs)>0){
                                    $rs = json_decode(json_encode($rs));
                                    foreach($rs as $i=>$row){
                                        $row->product_text = SI::html_tag('strong',$row->product_code)
                                            .' '.$row->product_name;
                                    }
                                    $rs = json_decode(json_encode($rs),true);
                                    $response = $rs;
                                }
                                $result['response'] = $response;
                                break;
                            
                            case 'delivery_moq_mixed_get':
                                $db = new DB();
                                $id = isset($data['delivery_moq_id'])?
                                        $data['delivery_moq_id']:'';
                                $q = '
                                    select distinct t1.qty, t3.id unit_id, t3.code unit_code
                                    from product_price_list_delivery_moq_mixed t1
                                        inner join product_price_list_delivery_moq t2 
                                            on t2.id = t1.product_price_list_delivery_moq_id
                                        inner join unit t3 on t3.id = t1.unit_id
                                    where t2.id = '.$db->escape($id).'
                                ';
                                $response = array();
                                $rs = $db->query_array($q);
                                if(count($rs)>0){
                                    $response = $rs[0];
                                }
                                $result['response'] = $response;
                                break;
                                
                            case 'delivery_moq_mixed_product_get':
                                $db = new DB();
                                $id = isset($data['delivery_moq_id'])?
                                        $data['delivery_moq_id']:'';
                                $q = '
                                    select t1.product_id
                                    from product_price_list_delivery_moq_mixed_product t1
                                        inner join product_price_list_delivery_moq_mixed t2 
                                            on t1.product_price_list_delivery_moq_mixed_id = t2.id
                                    where t2.product_price_list_delivery_moq_id = '.$db->escape($id).'
                                ';
                                $response = array();
                                $rs = $db->query_array($q);
                                if(count($rs)>0){
                                    $response = $rs;
                                }
                                $result['response'] = $response;
                                break;
                        }                        
                        break;
                    case 'separated':
                        switch($submethod2){
                            case 'product_get':
                                $db = new DB();
                                $product_price_list_id = isset($data['product_price_list_id'])?
                                        $data['product_price_list_id']:'';
                                $response = array();
                                $q = ' 
                                    select distinct t2.id product_id
                                        ,t2.name product_name
                                        ,t3.id unit_id
                                        ,t3.name unit_name
                                    from product_price_list_product t1
                                        inner join product t2 on t1.product_id = t2.id
                                        inner join unit t3 on t1.unit_id = t3.id
                                    where t1.product_price_list_id = '.$db->escape($product_price_list_id).'
                                    order by t2.name asc
                                ';
                                $rs = $db->query_array($q);
                                if(count($rs)>0){
                                    $response = $rs;
                                }
                                $result['response'] = $response;
                                break;

                            case 'delivery_moq_separated_get':
                                $db = new DB();
                                $id = isset($data['delivery_moq_id'])?
                                        $data['delivery_moq_id']:'';
                                $q = '
                                    select t1.product_id, t1.unit_id, t1.qty
                                        ,t2.id unit_id_measurement, t2.name unit_name_measurement
                                    from product_price_list_delivery_moq_separated t1
                                        inner join unit t2 on t1.unit_id_measurement = t2.id
                                    where t1.product_price_list_delivery_moq_id = '.$db->escape($id).'
                                ';
                                $response = array();
                                $rs = $db->query_array($q);
                                if(count($rs)>0){
                                    $response = $rs;
                                }
                                $result['response'] = $response;
                                break;
                             case 'unit_list_get':
                                $db = new DB();
                                $response =  array();
                                $q = 'select * from unit where status > 0';
                                $rs = $db->query_array($q);
                                if(count($rs)>0){
                                    for($i = 0;$i<count($rs);$i++){
                                        $response[] = array(
                                            'id'=>$rs[$i]['id']
                                            ,'text'=>SI::html_tag('strong',$rs[$i]['code']).' '.$rs[$i]['name'] 
                                        );
                                    }
                                }
                                $result['response'] = $response;
                                break;
                        }
                        break;
                    
                }
                //</editor-fold>
                break;
            case 'delivery_mop':
                //<editor-fold defaultstate="collapsed">
                switch($submethod){
                    case 'delivery_mop_get':
                        $db = new DB();
                        $id = isset($data['delivery_mop_id'])?
                                $data['delivery_mop_id']:'';
                        $q = '
                            select t1.*
                            from product_price_list_delivery_mop t1
                            where t1.id = '.$db->escape($id).'
                        ';
                        $response = array();
                        $rs = $db->query_array($q);
                        if(count($rs)>0){
                            $response = $rs[0];
                            $response['calculation_type_name'] = ucfirst($response['calculation_type']);
                        }
                        $result['response'] = $response;
                        break;
                    case 'mixed':
                        switch($submethod2){
                            case 'product_get':
                                $db = new DB();
                                $product_price_list_id = isset($data['product_price_list_id'])?$data['product_price_list_id']:'';
                                $response = array();
                                $q = ' 
                                    select distinct t2.id product_id
                                        ,t2.name product_name
                                    from product_price_list_product t1
                                        inner join product t2 on t1.product_id = t2.id
                                    where t1.product_price_list_id = '.$db->escape($product_price_list_id).'
                                    order by t2.name asc
                                ';
                                $rs = $db->query_array($q);
                                if(count($rs)>0){
                                    $response = $rs;
                                }
                                $result['response'] = $response;
                                break;
                            
                            case 'delivery_mop_mixed_get':
                                $db = new DB();
                                $id = isset($data['delivery_mop_id'])?
                                        $data['delivery_mop_id']:'';
                                $q = '
                                    select distinct t1.amount
                                    from product_price_list_delivery_mop_mixed t1
                                        inner join product_price_list_delivery_mop t2 
                                            on t2.id = t1.product_price_list_delivery_mop_id
                                    where t2.id = '.$db->escape($id).'
                                ';
                                $response = array();
                                $rs = $db->query_array($q);
                                if(count($rs)>0){
                                    $rs[0]['amount'] = Tools::thousand_separator($rs[0]['amount'],2);
                                    $response = $rs[0];
                                }
                                $result['response'] = $response;
                                break;
                                
                            case 'delivery_mop_mixed_product_get':
                                $db = new DB();
                                $id = isset($data['delivery_mop_id'])?
                                        $data['delivery_mop_id']:'';
                                $q = '
                                    select t1.product_id
                                    from product_price_list_delivery_mop_mixed_product t1
                                        inner join product_price_list_delivery_mop_mixed t2 
                                            on t1.product_price_list_delivery_mop_mixed_id = t2.id
                                    where t2.product_price_list_delivery_mop_id = '.$db->escape($id).'
                                ';
                                $response = array();
                                $rs = $db->query_array($q);
                                if(count($rs)>0){
                                    $response = $rs;
                                }
                                $result['response'] = $response;
                                break;
                        }                        
                        break;
                    case 'separated':
                        switch($submethod2){
                            case 'product_get':
                                $db = new DB();
                                $product_price_list_id = isset($data['product_price_list_id'])?
                                        $data['product_price_list_id']:'';
                                $response = array();
                                $q = ' 
                                    select distinct t2.id product_id
                                        ,t2.name product_name
                                        ,t3.id unit_id
                                        ,t3.name unit_name
                                    from product_price_list_product t1
                                        inner join product t2 on t1.product_id = t2.id
                                        inner join unit t3 on t1.unit_id = t3.id
                                    where t1.product_price_list_id = '.$db->escape($product_price_list_id).'
                                    order by t2.name asc
                                ';
                                $rs = $db->query_array($q);
                                if(count($rs)>0){
                                    $response = $rs;
                                }
                                $result['response'] = $response;
                                break;

                            case 'delivery_mop_separated_get':
                                $db = new DB();
                                $id = isset($data['delivery_mop_id'])?
                                        $data['delivery_mop_id']:'';
                                $q = '
                                    select t1.product_id, t1.unit_id, t1.amount
                                    from product_price_list_delivery_mop_separated t1
                                    where t1.product_price_list_delivery_mop_id = '.$db->escape($id).'
                                ';
                                $response = array();
                                $rs = $db->query_array($q);
                                if(count($rs)>0){
                                    $response = $rs;
                                }
                                $result['response'] = $response;
                                break;
                           
                        }
                        break;
                    
                }
                //</editor-fold>
                break;
            case 'delivery_extra_charge':
                switch($submethod){
                    case 'delivery_extra_charge_get':
                        $db = new DB();
                        $response = array();
                        $id = $data['delivery_extra_charge_id'];
                        $q = '
                            select t1.*, t2.name unit_name
                            from product_price_list_delivery_extra_charge t1
                                inner join unit t2 on t1.unit_id = t2.id
                            where t1.status>0 and t1.id = '.$db->escape($id).' 
                        ';
                        $rs = $db->query_array($q);
                        if(count($rs)>0){
                            $response = $rs[0];
                        }
                        $result['response'] = $response;

                        }
                        break;                
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function download_excel($id){
        get_instance()->load->helper($this->path->product_price_list_engine);
        Product_Price_List_Engine::download_excel($id);
    }
    
    
    public function add(){
        $this->load->helper($this->path->product_price_list_engine);
        $post = $this->input->post();        
        
        if($post!= null){
            Product_Price_List_Engine::submit('','add',$post);
        }
        else{
            $this->view('','add');
        }
        
    }
    
    public function active($id){
        $this->load->helper($this->path->product_price_list_engine);
        $post = $this->input->post();
        if($post!= null){
            Product_Price_List_Engine::submit($id,'active',$post);
        }        
    }
    
    public function inactive($id){
        $this->load->helper($this->path->product_price_list_engine);
        $post = $this->input->post();
        if($post!= null){
            Product_Price_List_Engine::submit($id,'inactive',$post);
        }        
    }
    
    public function delivery_moq_delete($id='',$parent_id=''){
        $this->load->helper($this->path->delivery_moq_engine);
        $path = Delivery_MOQ_Engine::delete($id);
        redirect($this->path->index.'view/'.$parent_id);
    }
    
    public function delivery_moq_mixed_add(){
        $this->load->helper($this->path->delivery_moq_engine);
        $path = Delivery_MOQ_Engine::path_get();
        $this->load->helper($path->delivery_moq_mixed_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_MOQ_Mixed_Engine::submit('','add',$post);
        }        
    }

    public function delivery_moq_mixed_update($id){
        $this->load->helper($this->path->delivery_moq_engine);
        $path = Delivery_MOQ_Engine::path_get();
        $this->load->helper($path->delivery_moq_mixed_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_MOQ_Mixed_Engine::submit($id,'update',$post);
        }        
    }
    
    public function delivery_moq_separated_add(){
        $this->load->helper($this->path->delivery_moq_engine);
        $path = Delivery_MOQ_Engine::path_get();
        $this->load->helper($path->delivery_moq_separated_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_MOQ_Separated_Engine::submit('','add',$post);
        }        
    }

    public function delivery_moq_separated_update($id){
        $this->load->helper($this->path->delivery_moq_engine);
        $path = Delivery_MOQ_Engine::path_get();
        $this->load->helper($path->delivery_moq_separated_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_MOQ_Separated_Engine::submit($id,'update',$post);
        }        
    }
    
    //----
    
    public function delivery_mop_delete($id='',$parent_id=''){
        $this->load->helper($this->path->delivery_mop_engine);
        $path = Delivery_MOP_Engine::delete($id);
        redirect($this->path->index.'view/'.$parent_id);
    }
    
    public function delivery_mop_mixed_add(){
        $this->load->helper($this->path->delivery_mop_engine);
        $path = Delivery_MOP_Engine::path_get();
        $this->load->helper($path->delivery_mop_mixed_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_MOP_Mixed_Engine::submit('','add',$post);
        }        
    }

    public function delivery_mop_mixed_update($id){
        $this->load->helper($this->path->delivery_mop_engine);
        $path = Delivery_MOP_Engine::path_get();
        $this->load->helper($path->delivery_mop_mixed_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_MOP_Mixed_Engine::submit($id,'update',$post);
        }        
    }
    
    public function delivery_mop_separated_add(){
        $this->load->helper($this->path->delivery_mop_engine);
        $path = Delivery_MOP_Engine::path_get();
        $this->load->helper($path->delivery_mop_separated_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_MOP_Separated_Engine::submit('','add',$post);
        }        
    }

    public function delivery_mop_separated_update($id){
        $this->load->helper($this->path->delivery_mop_engine);
        $path = Delivery_MOP_Engine::path_get();
        $this->load->helper($path->delivery_mop_separated_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_MOP_Separated_Engine::submit($id,'update',$post);
        }        
    }
    
    public function delivery_extra_charge_add(){
        $this->load->helper($this->path->product_price_list_engine);
        $path = Product_Price_List_Engine::path_get();
        $this->load->helper($path->delivery_extra_charge_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_Extra_Charge_Engine::submit('','add',$post);
        }        
    }

    public function delivery_extra_charge_update($id){
        $this->load->helper($this->path->product_price_list_engine);
        $path = Product_Price_List_Engine::path_get();
        $this->load->helper($path->delivery_extra_charge_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_Extra_Charge_Engine::submit($id,'update',$post);
        }        
    }
    
    public function delivery_extra_charge_delete($id,$parent_id){
        $this->load->helper($this->path->product_price_list_engine);
        $path = Product_Price_List_Engine::path_get();
        $this->load->helper($path->delivery_extra_charge_engine);
        Delivery_Extra_Charge_Engine::delete($id);
        redirect($this->path->index.'view/'.$parent_id);
        
    }
    
}

