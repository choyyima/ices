<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mf_Work_Process extends MY_Controller {
        
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get(array('Manufacturing - Work Process'),true,true,false,false,true);
        get_instance()->load->helper('mf_work_process/mf_work_process_engine');
        $this->path = Mf_Work_Process_Engine::path_get();
        $this->title_icon = App_Icon::mf_work_process();
    }
    
    public function index()
    {           

        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower('mf_work_process'));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get(array('Manufacturing Work Order','List')))
            ->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array(array('val'=>'New','grammar'=>'adj'),array('val'=>'Manufacturing Work Order'))))
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'mf_work_process/add');
        
        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"start_date","label"=>"Start Date","data_type"=>"text")
            ,array("name"=>"end_date","label"=>"End Date","data_type"=>"text")
            ,array("name"=>"mf_work_process_status_text","label"=>"Status","data_type"=>"text")
            

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/mf_work_process')
                //->table_ajax_set('controls',$controls)
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
    }
    
    public function add(){
        $this->load->helper($this->path->mf_work_process_engine);
        $post = $this->input->post();        
        $this->view('','add');
        
    }
    
    public function view($id = "",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->mf_work_process_engine);
        $this->load->helper($this->path->mf_work_process_data_support);
        $this->load->helper($this->path->mf_work_process_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Mf_Work_Process_Data_Support::mf_work_process_exists($id)){
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
            $app->set_breadcrumb($this->title,strtolower('mf_work_process'));
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Mf_Work_Process_Renderer::mf_work_process_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Mf_Work_Process_Renderer::mf_work_process_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
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
            case 'mf_work_process':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$lookup_data.'%');                
                $config = array(
                    'additional_filter'=>array(
                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select mf_work_process.*
                                    ,mfwp_info.start_date
                                    ,mfwp_info.end_date
                                from mf_work_process
                                    inner join mfwp_info on mf_work_process.id = mfwp_info.mf_work_process_id
                                where mf_work_process.status>0
                                
                        ',
                        'where'=>'
                            and (mf_work_process.code like '.$lookup_str.'
                            )
                        ',
                        'group'=>' group by mf_work_process.id
                            )tfinal
                        ',
                        'order'=>'order by id desc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data,array('output_type'=>'object'));
                $t_data = $temp_result->data;
                foreach($t_data as $i=>$row){
                    $row->mf_work_process_status_text =
                        SI::get_status_attr(
                            SI::status_get('Mf_Work_Process_Engine', 
                                $row->mf_work_process_status
                            )['label']
                        );
                    
                }
                $temp_result = json_decode(json_encode($temp_result),true);
                $result = $temp_result;
                //</editor-fold>
                break;
            case 'input_select_reference_search':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
                
                //<editor-fold defaultstate="collapsed" desc="Approved Work Order">
                get_instance()->load->helper('mf_work_process/mf_work_process_data_support');
                $temp_result = Mf_Work_Process_Data_Support::reference_search($lookup_data);
                if(count($temp_result)>0){
                    $ref = array();
                    foreach($temp_result as $i=>$row){
                        $ref[] = array(
                            'reference_type'=>$row['reference_type'],
                            'id'=>$row['id'],
                            'text' => SI::html_tag('strong',$row['code'])
                        );
                    }
                    $response = $ref;
                }
                //</editor-fold>
                //</editor-fold>
                break;
            case 'input_select_component_product_search':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('mf_work_process/mf_work_process_data_support');
                $response = array();
                $lookup_str = isset($data['data'])?Tools::_str($data['data']):'';
                $warehouse_id = isset($data['extra_param']['warehouse_id'])?
                    Tools::_str($data['extra_param']['warehouse_id']):'';
                $response = Mf_Work_Process_Data_Support::product_search($lookup_str,$warehouse_id);
                //</editor-fold>
                break;
            case 'input_select_result_product_search':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('mf_work_process/mf_work_process_data_support');
                $response = array();
                $lookup_str = isset($data['data'])?Tools::_str($data['data']):'';
                $warehouse_id = isset($data['extra_param']['warehouse_id'])?
                    Tools::_str($data['extra_param']['warehouse_id']):'';
                $response = Mf_Work_Process_Data_Support::product_search($lookup_str,$warehouse_id);
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
        get_instance()->load->helper('mf_work_process/mf_work_process_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[],'response'=>array());
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'mf_work_process_get':
                get_instance()->load->helper('mf_work_process/mf_work_process_engine');
                get_instance()->load->helper('product/product_engine');
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $response = array();
                $q = '
                    select t1.*
                        ,t2.id store_id
                        ,t2.code store_code
                        ,t2.name store_name
                    from mf_work_process t1
                        inner join store t2 on t1.store_id = t2.id
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $rs[0]['store_text'] = $rs[0]['store_name'];
                    $mf_work_process = $rs[0];
                    $mf_work_process_id = $mf_work_process['id'];
                    $mf_work_process_type = $mf_work_process['mf_work_process_type'];
                    
                    $reference = Mf_Work_Process_Data_Support::reference_get($mf_work_process_id);
                    
                    $mf_work_process['mf_work_process_type_text'] = SI::type_get('mf_work_process_engine',$mf_work_process_type)['label'];
                    $mf_work_process['mf_work_process_status_text'] = SI::get_status_attr(
                            SI::status_get('Mf_Work_Process_Engine',$mf_work_process['mf_work_process_status'])['label']
                        );
                    
                    $mfwp_info = Mf_Work_Process_Data_Support::mfwp_info_get($mf_work_process_id);
                    $mfwp_worker = Mf_Work_Process_Data_Support::mfwp_worker_get($mf_work_process_id);
                    $mfwp_expected_result_product = Mf_Work_Process_Data_Support::mfwp_expected_result_product_get($mf_work_process_id);
                    
                    $mfwp_expected_result_product = json_decode(json_encode($mfwp_expected_result_product));
                    foreach($mfwp_expected_result_product as $i=>$row){
                        $row->product_img = Product_Engine::img_get($row->product_id);
                        $row->product_text = SI::html_tag('strong',$row->product_code)
                            .' '.$row->product_name;
                        $row->unit_text = SI::html_tag('strong',$row->unit_code)
                            .' '.$row->unit_name;
                        $row->bom_text = SI::html_tag('strong',$row->bom_code)
                            .' '.$row->bom_name;
                    }
                    $mfwp_expected_result_product = json_decode(json_encode($mfwp_expected_result_product),true);
                    
                    $mfwp_result_product = Mf_Work_Process_Data_Support::mfwp_result_product_get($mf_work_process_id);
                    $mfwp_result_product = Mf_Work_Process_Data_Support::mfwp_result_product_get($mf_work_process_id);
                    $mfwp_result_product = json_decode(json_encode($mfwp_result_product));
                    foreach($mfwp_result_product as $i=>$row){
                        $row->product_text = SI::html_tag('strong',$row->product_code)
                            .' '.$row->product_name;
                        $row->unit_text = SI::html_tag('strong',$row->unit_code)
                            .' '.$row->unit_name;
                        $row->stock_location_text = SI::type_get('mf_work_process_engine',$row->stock_location,'$stock_location_list')['label'];
                        $row->product_img = Product_Engine::img_get($row->product_id);
                    }
                    $mfwp_result_product = json_decode(json_encode($mfwp_result_product),true);
                    
                    $mfwp_component_product = Mf_Work_Process_Data_Support::mfwp_component_product_get($mf_work_process_id);
                    $mfwp_component_product = json_decode(json_encode($mfwp_component_product));
                    foreach($mfwp_component_product as $i=>$row){
                        $row->product_text = SI::html_tag('strong',$row->product_code)
                            .' '.$row->product_name;
                        $row->unit_text = SI::html_tag('strong',$row->unit_code)
                            .' '.$row->unit_name;
                        $row->stock_location_text = SI::type_get('mf_work_process_engine',$row->stock_location,'$stock_location_list')['label'];
                        $row->product_img = Product_Engine::img_get($row->product_id);
                    }
                    $mfwp_component_product = json_decode(json_encode($mfwp_component_product),true);
                    
                    $mfwp_scrap_product = Mf_Work_Process_Data_Support::mfwp_scrap_product_get($mf_work_process_id);
                    $mfwp_scrap_product = Mf_Work_Process_Data_Support::mfwp_scrap_product_get($mf_work_process_id);
                    $mfwp_scrap_product = json_decode(json_encode($mfwp_scrap_product));
                    foreach($mfwp_scrap_product as $i=>$row){
                        $row->product_text = SI::html_tag('strong',$row->product_code)
                            .' '.$row->product_name;
                        $row->unit_text = SI::html_tag('strong',$row->unit_code)
                            .' '.$row->unit_name;
                        $row->stock_location_text = SI::type_get('mf_work_process_engine',$row->stock_location,'$stock_location_list')['label'];
                        $row->product_img = Product_Engine::img_get($row->product_id);
                    }
                    $mfwp_scrap_product = json_decode(json_encode($mfwp_scrap_product),true);
                    
                    $sir = Mf_Work_Process_Data_Support::sir_get($mf_work_process_id);
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('mf_work_process_engine',
                            $mf_work_process['mf_work_process_status']
                        );
                    
                    $response['reference'] = $reference;
                    $response['mf_work_process'] = $mf_work_process;
                    $response['mfwp_info'] = $mfwp_info;
                    $response['mfwp_worker'] = $mfwp_worker;
                    $response['mfwp_expected_result_product'] = $mfwp_expected_result_product;
                    $response['mfwp_result_product'] = $mfwp_result_product;
                    $response['mfwp_component_product'] = $mfwp_component_product;
                    $response['mfwp_scrap_product'] = $mfwp_scrap_product;
                    $response['sir'] = $sir;
                    $response['mf_work_process_status_list'] = $next_allowed_status_list;
                    
                }
                
                
                //</editor-fold>
                break;
            case 'reference_dependency_get':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $reference_type = isset($data['reference_type'])?Tools::_str($data['reference_type']):'';
                $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                $reference_detail = Mf_Work_Process_Data_Support::reference_detail_get($reference_type, $reference_id);
                $response['reference_detail'] = $reference_detail;
                //</editor-fold>
                break;
            case 'available_expected_result_product_get':
                //<editor-fold defaultstate="collapsed">
                $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                $warehouse_id = isset($data['warehouse_id'])?Tools::_str($data['warehouse_id']):'';
                $t_prod = Mf_Work_Process_Data_Support::available_expected_result_product_get($reference_id,$warehouse_id);
                if(count($t_prod)>0){
                    $t_prod = json_decode(json_encode($t_prod));
                    foreach($t_prod as $i=>$row){
                        $row->product_text = SI::html_tag('strong',$row->product_code)
                            .' '.$row->product_name;
                        $row->unit_text = SI::html_tag('strong',$row->unit_code)
                            .' '.$row->unit_name;
                        $row->bom_text = SI::html_tag('strong',$row->bom_code)
                            .' '.$row->bom_name;
                        
                    }
                    $t_prod = json_decode(json_encode($t_prod),true);
                    $response = $t_prod;
                }
                //</editor-fold>
                break;
            case 'available_component_product_get':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $module_type = isset($data['module_type'])?Tools::_str($data['module_type']):'';
                $warehouse_id = isset($data['warehouse_id'])?Tools::_str($data['warehouse_id']):'';
                $expected_product = isset($data['expected_product'])?Tools::_arr($data['expected_product']):array();
                
                $t_comp_prod = Mf_Work_Process_Data_Support::available_component_product_get(
                    $module_type,$warehouse_id,$expected_product
                );
                
                if(count($t_comp_prod)>0){
                    $t_comp_prod = json_decode(json_encode($t_comp_prod));
                    foreach($t_comp_prod as $i=>$row){
                        $row->product_text = SI::html_tag('strong',$row->product_code)
                            .' '.$row->product_name;
                        $row->unit_text = SI::html_tag('strong',$row->unit_code)
                            .' '.$row->unit_name;
                        $row->product_img = Product_Engine::img_get($row->product_id);
                    }
                    $response = $t_comp_prod;
                }
                $response = $t_comp_prod;
                //</editor-fold>
                break;
            case 'available_result_product_get':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $mf_work_process_id = isset($data['mf_work_process_id'])?Tools::_str($data['mf_work_process_id']):'';
                
                $t_res_prod = Mf_Work_Process_Data_Support::available_result_product_get(
                    $mf_work_process_id
                );
                
                if(count($t_res_prod)>0){
                    $t_res_prod = json_decode(json_encode($t_res_prod));
                    foreach($t_res_prod as $i=>$row){
                        $row->product_text = SI::html_tag('strong',$row->product_code)
                            .' '.$row->product_name;
                        $row->unit_text = SI::html_tag('strong',$row->unit_code)
                            .' '.$row->unit_name;
                        $row->product_img = Product_Engine::img_get($row->product_id);
                        $row->stock_location_text = SI::type_get('mf_work_process_engine', $row->stock_location,'$stock_location_list')['label'];
                    }
                    $response = $t_res_prod;
                }
                $response = $t_res_prod;
                //</editor-fold>
                break;
            case 'available_scrap_product_get':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $mf_work_process_id = isset($data['mf_work_process_id'])?Tools::_str($data['mf_work_process_id']):'';
                
                $t_res_prod = Mf_Work_Process_Data_Support::available_scrap_product_get(
                    $mf_work_process_id
                );
                
                if(count($t_res_prod)>0){
                    $t_res_prod = json_decode(json_encode($t_res_prod));
                    foreach($t_res_prod as $i=>$row){
                        $row->product_text = SI::html_tag('strong',$row->product_code)
                            .' '.$row->product_name;
                        $row->unit_text = SI::html_tag('strong',$row->unit_code)
                            .' '.$row->unit_name;
                        $row->product_img = Product_Engine::img_get($row->product_id);
                        $row->stock_location_text = SI::type_get('mf_work_process_engine', $row->stock_location,'$stock_location_list')['label'];
                    }
                    $response = $t_res_prod;
                }
                $response = $t_res_prod;
                //</editor-fold>
                break;
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function mf_work_process_add(){
        $this->load->helper($this->path->mf_work_process_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'mf_work_process_add','primary_data_key'=>'mf_work_process','data_post'=>$post);            
            SI::data_submit()->submit('mf_work_process_engine',$param);
            
        }        
    }
    
    public function mf_work_process_process($id=''){
        $this->load->helper($this->path->mf_work_process_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'mf_work_process_process','primary_data_key'=>'mf_work_process','data_post'=>$post);
            SI::data_submit()->submit('mf_work_process_engine',$param);
        }        
    }
    
    public function mf_work_process_done($id=''){
        $this->load->helper($this->path->mf_work_process_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'mf_work_process_done','primary_data_key'=>'mf_work_process','data_post'=>$post);
            SI::data_submit()->submit('mf_work_process_engine',$param);
        }
        
    }
    
    public function mf_work_process_canceled($id=''){
        $this->load->helper($this->path->mf_work_process_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'mf_work_process_canceled','primary_data_key'=>'mf_work_process','data_post'=>$post);
            SI::data_submit()->submit('mf_work_process_engine',$param);
        }
        
    }
}

