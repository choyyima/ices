<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Checking_Result_Form extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get(array('Refill - ','Checking Result Form'),true,true,false,false,true);
        get_instance()->load->helper('refill_checking_result_form/refill_checking_result_form_engine');
        $this->path = Refill_Checking_Result_Form_Engine::path_get();
        $this->title_icon = App_Icon::refill_checking_result_form();
        
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
        $form = $row->form_add()->form_set('title',Lang::get(array('Checking Result Form','List')))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array(array('val'=>'New','grammar'=>'adj','uc_first'=>'true'),'Checking Result Form')))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $form->form_group_add();
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"refill_checking_result_form_date","label"=>Lang::get(array("Checking Result Form","Date")),"data_type"=>"text"),            
            array("name"=>"refill_checking_result_form_status_text","label"=>Lang::get(array("Status")),"data_type"=>"text"),            
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/refill_checking_result_form')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'reference_type_filter','field'=>'reference_type')
                    ))
                ;        
        
        
        $app->render();
    }
    
    
    public function add(){
        
        $this->load->helper($this->path->refill_checking_result_form_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    
    public function view($id="",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->refill_checking_result_form_engine);
        $this->load->helper($this->path->refill_checking_result_form_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Refill_Checking_Result_Form_Engine::refill_checking_result_form_exists($id)){
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
            $app->set_breadcrumb($this->title,'refill_checking_result_form');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','refill_checking_result_form');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Refill_Checking_Result_Form_Renderer::refill_checking_result_form_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Refill_Checking_Result_Form_Renderer::refill_checking_result_form_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
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
        $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;
        switch($method){            
            case 'refill_checking_result_form':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$lookup_data.'%');                
                $config = array(
                    'additional_filter'=>array(
                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct rcrf.*
                                from refill_checking_result_form rcrf
                                inner join rcrf_product rcrfp on rcrf.id = rcrfp.refill_checking_result_form_id
                                left outer join refill_work_order_product rwop 
                                    on rwop.id = rcrfp.product_id and rcrfp.product_type = "refill_work_order_product"
                                left outer join product p
                                    on p.id = rcrfp.product_id and rcrfp.product_type = "registered_product"
                                where rcrf.status>0
                        ',
                        'where'=>'
                            and (rcrf.code like '.$lookup_str.'
                                or rwop.product_marking_code like '.$lookup_str.'
                                or p.code like '.$lookup_str.'
                                or p.name like '.$lookup_str.'
                            )
                        ',
                        'group'=>'
                            )tfinal
                        ',
                        'order'=>'order by id desc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for($i = 0;$i<count($temp_result['data']);$i++){
                    $temp_result['data'][$i]['refill_checking_result_form_status_text'] =
                        SI::get_status_attr(
                            SI::status_get('Refill_Checking_Result_Form_Engine', 
                                $temp_result['data'][$i]['refill_checking_result_form_status']
                            )['label']
                        );
                    
                }
                $result = $temp_result;
                //</editor-fold>
                break;
            
            case 'input_select_product_marking_code_search':
                $response = array();        
                get_instance()->load->helper('refill_checking_result_form/refill_checking_result_form_data_support');
                $response = Refill_Checking_Result_Form_Data_Support::product_marking_code_search($lookup_data);
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
        get_instance()->load->helper('refill_checking_result_form/refill_checking_result_form_engine');
        get_instance()->load->helper('refill_checking_result_form/refill_checking_result_form_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'refill_checking_result_form_get':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('product/product_data_support');
                $response =array();
                $db = new DB();
                $rcrf_id = $data['data'];
                $q = '
                    select t1.*,
                        t2.code store_code,
                        t2.name store_name
                    from refill_checking_result_form t1
                        inner join store t2 on t1.store_id = t2.id
                    where t1.id = '.$db->escape($rcrf_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $rcrf = $rs[0];                    
                    $rcrf['refill_checking_result_form_date'] = Tools::_date($rcrf['refill_checking_result_form_date'],'F d, Y H:i');
                    $rcrf['store_text'] = SI::html_tag('strong',$rcrf['store_code'])
                        .' '.$rcrf['store_name'];
                    $rcrf['refill_checking_result_form_status_text'] = SI::get_status_attr(
                            SI::status_get('Refill_Checking_Result_Form_Engine',$rcrf['refill_checking_result_form_status'])['label']
                        );
                    
                    $rcrf_product = Refill_Checking_Result_Form_Data_Support::rcrf_product_get($rcrf['id']);
                    for($i = 0;$i<count($rcrf_product);$i++){
                        //<editor-fold defaultstate="collapsed" desc="Product">
                        $p_type = $rcrf_product[$i]['product_type'];
                        switch($p_type ){
                            case 'refill_work_order_product':
                                $rcrf_product[$i]['product_info']= Product_Data_Support::product_type_get('refill_work_order_product')['label']
                                    .' - '.$rcrf_product[$i]['product_info_merk']
                                    .' '.$rcrf_product[$i]['product_info_type']
                                    .' '.$rcrf_product[$i]['rpc_code']
                                    .' '.$rcrf_product[$i]['rpm_code']
                                    .' '.Tools::thousand_separator($rcrf_product[$i]['capacity'])
                                    .' '.$rcrf_product[$i]['capacity_unit_code']
                                ;
                                $rcrf_product[$i]['product_condition_text'] = SI::type_get('Refill_Checking_Result_Form_Engine', $rcrf_product[$i]['product_condition'], '$product_condition')['label'];
                                break;
                        }
                        
                        //</editor-fold>
                    }
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Refill_Checking_Result_Form_Engine',
                            $rcrf['refill_checking_result_form_status']
                        );
                    
                    
                    $response['rcrf'] = $rcrf;
                    $response['rcrf_product'] = $rcrf_product;
                    $response['refill_checking_result_form_status_list'] = $next_allowed_status_list;
                }
                //</editor-fold>
                break;
            case 'input_select_product_marking_code_dependency_get':
                //<editor-fold defaultstate="collapsed">
                $product_id = isset($data['product_id'])?Tools::_str($data['product_id']):'';
                $product_type = isset($data['product_type'])?Tools::_str($data['product_type']):'';
                $response = Refill_Checking_Result_Form_Data_Support::product_marking_code_dependency_get($product_type,$product_id);
                //</editor-fold>
                break;
                
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function rcrf_add(){
        // <editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->refill_checking_result_form_engine);
        $post = $this->input->post();
        if ($post != null) {
            $param = array('id' => '', 'method' => 'rcrf_add',
                'primary_data_key' => 'rcrf',
                'data_post' => $post
            );
            SI::data_submit()->submit('refill_checking_result_form_engine', $param);
        }
        // </editor-fold>
    }
    
    public function rcrf_done($id){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->refill_checking_result_form_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'rcrf_done',
                'primary_data_key'=>'rcrf',
                'data_post'=>$post
            );
            SI::data_submit()->submit('refill_checking_result_form_engine',$param);

        }
        //</editor-fold>
    }
    
    public function rcrf_canceled($id){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->refill_checking_result_form_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'rcrf_canceled',
                'primary_data_key'=>'rcrf',
                'data_post'=>$post
            );
            SI::data_submit()->submit('refill_checking_result_form_engine',$param);

        }
        //</editor-fold>
    }
}

?>