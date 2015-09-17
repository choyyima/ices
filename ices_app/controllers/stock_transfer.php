<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stock_Transfer extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Stock Transfer');
        get_instance()->load->helper('stock_transfer/stock_transfer_engine');
        $this->path = Stock_Transfer_Engine::path_get();
        $this->title_icon = App_Icon::stock_transfer();
        
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
        $form = $row->form_add()->form_set('title',Lang::get(array('Stock Transfer','List')))->form_set('span','12');
        $form->form_group_add();
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Stock Transfer')))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"stock_transfer_date","label"=>Lang::get("Date"),"data_type"=>"text"),
            array("name"=>"stock_transfer_status","label"=>Lang::get("Status"),"data_type"=>"text"),
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/stock_transfer')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'reference_type_filter','field'=>'reference_type')
                    ))
                ;        

        $app->render();
    }
    
    
    public function add(){        
        $this->load->helper($this->path->stock_transfer_engine);
        $active_id =Stock_Transfer_Engine::stock_transfer_active_get(); 
        if($active_id!== null){
            redirect($this->path->index.'view/'.$active_id);
        }
        
        $post = $this->input->post();      
        $default_status = SI::status_default_status_get('Stock_Transfer_Engine')['val'];
        $user_id = User_Info::get()['user_id'];
        $db = new DB();
        $q = '
            select t1.id
            from stock_transfer t1
                inner join working_order_info t2 on t1.id = t2.stock_transfer_id
            where t1.stock_transfer_status === '.$db->escape($default_status).'
                and t2.creator_id = '.$db->escape($user_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            redirect(get_instance()->config->base_url().'stock_transfer/view'.$rs[0]);
        }
        
        $this->view('','add');
        
    }
    
    
    public function view($id="",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->stock_transfer_engine);
        $this->load->helper($this->path->stock_transfer_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Stock_Transfer_Engine::stock_transfer_exists($id)){
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
            $app->set_breadcrumb($this->title,'stock_transfer');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','stock_transfer');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Stock_Transfer_Renderer::Stock_Transfer_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Stock_Transfer_Renderer::Stock_Transfer_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
            
            }            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        
        //</editor-fold>
    }
    
    public function ajax_search($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;
        
        switch($method){
            
            case 'stock_transfer':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');                
                $config = array(
                    'additional_filter'=>array(

                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct t1.*
                                from stock_transfer t1                    
                                where t1.status>0
                        ',
                        'where'=>'
                            and (t1.code like '.$lookup_str.'
                            )
                        ',
                        'group'=>'
                            )tfinal
                        ',
                        'order'=>'order by code desc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for($i = 0;$i<count($temp_result['data']);$i++){
                    $temp_result['data'][$i]['stock_transfer_status'] =
                        SI::get_status_attr(
                            SI::status_get('Stock_Transfer_Engine', 
                                $temp_result['data'][$i]['stock_transfer_status']
                            )['label']
                        );
                    
                }
                $result = $temp_result;
                //</editor-fold>
                break;
            
            case 'input_select_warehouse_search':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('master/warehouse_engine');
                $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
                $warehouse_arr = Warehouse_Engine::BOS_search($lookup_data);
                if(count($warehouse_arr)>0){
                    foreach($warehouse_arr as $warehouse_idx=>$warehouse){
                        $response[] = array(
                            'id'=>$warehouse['id'],
                            'text'=>SI::html_tag('strong',$warehouse['code']).' '.
                            $warehouse['name']
                        );
                    }
                }
                //</editor-fold>
                break;
                
            case 'input_select_registered_product_search':
                $response = array();
                $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
                $warehouse_id = isset($data['extra_param']['warehouse_id'])?Tools::_str($data['extra_param']['warehouse_id']):'';
                get_instance()->load->helper('stock_transfer/stock_transfer_data_support');
                $response = Stock_Transfer_Data_Support::registered_product_search($lookup_data, $warehouse_id);
                break;
            
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function data_support($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        //this function only used for urgently data retrieve
        get_instance()->load->helper('stock_transfer/stock_transfer_engine');
        get_instance()->load->helper('stock_transfer/stock_transfer_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            
            case 'stock_transfer_get':
                //<editor-fold defaultstate="collapsed">
                $response =array();
                $db = new DB();
                $stock_transfer_id = Tools::_str($data['data']);
                $q = '
                    select distinct t1.*,
                        t2.code store_code,
                        t2.name store_name,
                        wf.code  warehouse_from_code,
                        wf.name  warehouse_from_name,
                        wt.code  warehouse_to_code,
                        wt.name  warehouse_to_name
                    from stock_transfer t1
                        inner join store t2 on t1.store_id = t2.id
                        inner join warehouse wf on t1.warehouse_from_id  = wf.id
                        inner join warehouse wt on t1.warehouse_to_id = wt.id
                    where t1.id = '.$db->escape($stock_transfer_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    get_instance()->load->helper('product/product_engine');
                    $stock_transfer = $rs[0];    
                    $stock_transfer['stock_transfer_date'] = Tools::_date($stock_transfer['stock_transfer_date'],'F d, Y H:i');
                    $stock_transfer['store_text'] = SI::html_tag('strong',$stock_transfer['store_code'])
                        .' '.$stock_transfer['store_name'];
                    $stock_transfer['warehouse_from_text'] = SI::html_tag('strong',$stock_transfer['warehouse_from_code'])
                        .' '.$stock_transfer['warehouse_from_name'];
                    $stock_transfer['warehouse_to_text'] = SI::html_tag('strong',$stock_transfer['warehouse_to_code'])
                        .' '.$stock_transfer['warehouse_to_name'];
                    
                    $stock_transfer_product = Stock_Transfer_Data_Support::stock_transfer_product_get($stock_transfer['id']);
                    for($i = 0;$i<count($stock_transfer_product);$i++){
                        $stock_transfer_product[$i]['product_img'] = Product_Engine::img_get($stock_transfer_product[$i]['product_id']);
                        $stock_transfer_product[$i]['product_text'] = SI::html_tag('strong',$stock_transfer_product[$i]['product_code']).' '.$stock_transfer_product[$i]['product_name'];
                        $stock_transfer_product[$i]['unit_text'] = SI::html_tag('strong',$stock_transfer_product[$i]['unit_code']).' '.$stock_transfer_product[$i]['unit_name'];
                        $stock_transfer_product[$i]['qty'] = Tools::thousand_separator($stock_transfer_product[$i]['qty']);
                    }
                    $stock_transfer['stock_transfer_status_text'] = SI::get_status_attr(
                            SI::status_get('Stock_Transfer_Engine',$stock_transfer['stock_transfer_status'])['label']
                        );
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Stock_Transfer_Engine',
                            $stock_transfer['stock_transfer_status']
                        );

                    $response['stock_transfer'] = $stock_transfer;
                    $response['stock_transfer_product'] = $stock_transfer_product;
                    $response['stock_transfer_status_list'] = $next_allowed_status_list;
                    
                }
                //</editor-fold>
                break;
            
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function stock_transfer_add(){        
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'stock_transfer_add','primary_data_key'=>'stock_transfer','data_post'=>$post);            
            SI::data_submit()->submit('stock_transfer_engine',$param);
            
        }
        
    }
        
    public function stock_transfer_process($id){
        
        $this->load->helper($this->path->stock_transfer_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'stock_transfer_process','primary_data_key'=>'stock_transfer','data_post'=>$post);            
            SI::data_submit()->submit('stock_transfer_engine',$param);

        }
    }
    
    public function stock_transfer_done($id){
        
        $this->load->helper($this->path->stock_transfer_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'stock_transfer_done','primary_data_key'=>'stock_transfer','data_post'=>$post);            
            SI::data_submit()->submit('stock_transfer_engine',$param);

        }
    }
    
    public function stock_transfer_canceled($id){
        
        $this->load->helper($this->path->stock_transfer_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'stock_transfer_canceled','primary_data_key'=>'stock_transfer','data_post'=>$post);            
            SI::data_submit()->submit('stock_transfer_engine',$param);

        }
    }
    
    public function stock_transfer_print($id,$module,$prm1=''){
        $this->load->helper($this->path->stock_transfer_print);
        $post = $this->input->post();
        switch($module){
            case 'stock_transfer_form':
                Stock_Transfer_Print::stock_transfer_form_print($id);
                break;
        }
    }
}

?>