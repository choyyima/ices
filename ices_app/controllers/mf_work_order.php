<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mf_Work_Order extends MY_Controller {
        
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get(array('Manufacturing - Work Order'),true,true,false,false,true);
        get_instance()->load->helper('mf_work_order/mf_work_order_engine');
        $this->path = Mf_Work_Order_Engine::path_get();
        $this->title_icon = App_Icon::mf_work_order();
    }
    
    public function index()
    {           

        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower('mf_work_order'));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get(array('Manufacturing Work Order','List')))
            ->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array(array('val'=>'New','grammar'=>'adj'),array('val'=>'Manufacturing Work Order'))))
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'mf_work_order/add');
        
        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"mf_work_order_date","label"=>"Date","data_type"=>"text")
            ,array("name"=>"mfwo_result_product_total","label"=>"Total Product","data_type"=>"text")
            ,array("name"=>"mf_work_order_status_text","label"=>"Status","data_type"=>"text")
            

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/mf_work_order')
                //->table_ajax_set('controls',$controls)
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
    }
    
    public function add(){
        $this->load->helper($this->path->mf_work_order_engine);
        $post = $this->input->post();        
        $this->view('','add');
        
    }
    
    public function view($id = "",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->mf_work_order_engine);
        $this->load->helper($this->path->mf_work_order_data_support);
        $this->load->helper($this->path->mf_work_order_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Mf_Work_Order_Data_Support::mf_work_order_exists($id)){
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
            $app->set_menu('collapsed',false);
            $app->set_breadcrumb($this->title,strtolower('mf_work_order'));
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Mf_Work_Order_Renderer::mf_work_order_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Mf_Work_Order_Renderer::mf_work_order_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
                $mf_work_process_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#mf_work_process_tab',"value"=>Lang::get('Manufacturing Work Process')));
                $mf_work_process_pane = $mf_work_process_tab->div_add()->div_set('id','mf_work_process_tab')->div_set('class','tab-pane');
                Mf_Work_Order_Renderer::mf_work_process_view_render($app,$mf_work_process_pane,array("id"=>$id),$this->path);
            
            }
            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        //</editor-fold>
    }
    
    
    public function ajax_search($method){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;
        switch($method){
            case 'mf_work_order':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$lookup_data.'%');                
                $config = array(
                    'additional_filter'=>array(
                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select mf_work_order.*, count(1) mfwo_result_product_total
                                from mf_work_order
                                    left outer join mfwo_ordered_product mfworp on mf_work_order.id = mfworp.mf_work_order_id
                                where mf_work_order.status>0
                                
                        ',
                        'where'=>'
                            and (mf_work_order.code like '.$lookup_str.'
                            )
                        ',
                        'group'=>' group by mf_work_order.id
                            )tfinal
                        ',
                        'order'=>'order by code desc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data,array('output_type'=>'object'));
                $t_data = $temp_result->data;
                foreach($t_data as $i=>$row){
                    $row->mf_work_order_status_text =
                        SI::get_status_attr(
                            SI::status_get('Mf_Work_Order_Engine', 
                                $row->mf_work_order_status
                            )['label']
                        );
                    
                }
                $temp_result = json_decode(json_encode($temp_result),true);
                $result = $temp_result;
                //</editor-fold>
                break;
            case 'input_select_ordered_product_search':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('mf_work_order/mf_work_order_data_support');
                $response = array();
                $lookup_str = isset($data['data'])?Tools::_str($data['data']):'';
                $mf_work_order_type = isset($data['extra_param']['mf_work_order_type'])?Tools::_str($data['extra_param']['mf_work_order_type']):'';
                $response = Mf_Work_Order_Data_Support::ordered_product_search($mf_work_order_type,$lookup_str);
                //</editor-fold>
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
        get_instance()->load->helper('mf_work_order/mf_work_order_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[],'response'=>array());
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'mf_work_order_get':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $response = array();
                $q = '
                    select t1.*
                    from mf_work_order t1
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $mf_work_order = $rs[0];
                    $mf_work_order_id = $mf_work_order['id'];
                    $mf_work_order_type = $mf_work_order['mf_work_order_type'];
                    
                    $mf_work_order['mf_work_order_type_text'] = SI::type_get('mf_work_order_engine',$mf_work_order_type)['label'];
                    $mf_work_order['mf_work_order_status_text'] = SI::get_status_attr(
                            SI::status_get('Mf_Work_Order_Engine',$mf_work_order['mf_work_order_status'])['label']
                        );
                    
                    $mfwo_info = Mf_Work_Order_Data_Support::mfwo_info_get($mf_work_order_id);
                    $mfwo_ordered_product = Mf_Work_Order_Data_Support::mfwo_ordered_product_get($mf_work_order_id);
                    $mfwo_ordered_product = json_decode(json_encode($mfwo_ordered_product));
                    foreach($mfwo_ordered_product as $i=>$row){
                        $row->product_img = Product_Engine::img_get($row->product_id);
                        $row->product_text = SI::html_tag('strong',$row->product_code)
                            .' '.$row->product_name;
                        $row->unit_text = SI::html_tag('strong',$row->unit_code)
                            .' '.$row->unit_name;
                        $row->qty = $row->qty;
                        $row->outstanding_qty =$row->outstanding_qty;
                        if($mf_work_order_type === 'normal'){
                            $row->bom_text = SI::html_tag('strong',$row->bom_code)
                                .' '.$row->bom_name;
                        }
                    }
                    $mfwo_ordered_product = json_decode(json_encode($mfwo_ordered_product),true);
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('mf_work_order_engine',
                            $mf_work_order['mf_work_order_status']
                        );
                    
                    $response['mf_work_order'] = $mf_work_order;
                    $response['mfwo_info'] = $mfwo_info;
                    $response['mfwo_ordered_product'] = $mfwo_ordered_product;
                    $response['mf_work_order_status_list'] = $next_allowed_status_list;
                    
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
    
    public function mf_work_order_add(){
        $this->load->helper($this->path->mf_work_order_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'mf_work_order_add','primary_data_key'=>'mf_work_order','data_post'=>$post);            
            SI::data_submit()->submit('mf_work_order_engine',$param);
            
        }        
    }
    
    public function mf_work_order_initialized($id){
        $this->load->helper($this->path->mf_work_order_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'mf_work_order_initialized','primary_data_key'=>'mf_work_order','data_post'=>$post);
            SI::data_submit()->submit('mf_work_order_engine',$param);
        }        
    }
    
    public function mf_work_order_approved($id){
        $this->load->helper($this->path->mf_work_order_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'mf_work_order_approved','primary_data_key'=>'mf_work_order','data_post'=>$post);
            SI::data_submit()->submit('mf_work_order_engine',$param);
        }
        
    }
    public function mf_work_order_rejected($id){
        $this->load->helper($this->path->mf_work_order_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'mf_work_order_rejected','primary_data_key'=>'mf_work_order','data_post'=>$post);
            SI::data_submit()->submit('mf_work_order_engine',$param);
        }
        
    }
    
    public function mf_work_order_canceled($id){
        $this->load->helper($this->path->mf_work_order_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'mf_work_order_canceled','primary_data_key'=>'mf_work_order','data_post'=>$post);
            SI::data_submit()->submit('mf_work_order_engine',$param);
        }
        
    }
}

