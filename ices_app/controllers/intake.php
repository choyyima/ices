<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Intake extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get(array('Product Intake'));
        get_instance()->load->helper('intake/intake_engine');
        $this->path = Intake_Engine::path_get();
        $this->title_icon = App_Icon::intake();
        
    }
    
    public function index()
    {           
        $action = "";

        $app = new App();            
        $db = $this->db;

        
        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower('intake'));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get(array('Product Intake','List')))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Product Intake')))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $reference_type_list = array(
            array('value'=>'','label'=>'ALL')
        );
        $module_list = SI::type_list_get('Intake_Engine');
        foreach($module_list as $module_idx=>$module){
            $reference_type_list[] = array('value'=>$module['val'],'label'=>$module['label']);
        }
        
        
        $form->select_add()
                ->select_set('id','reference_type_filter')
                ->select_set('options_add',$reference_type_list)
                ;
        
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Intake Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"intake_type","label"=>Lang::get("Type"),"data_type"=>"text"),
            //,array("name"=>"supplier_name","label"=>Lang::get("Supplier"),"data_type"=>"text"),
            array("name"=>"intake_date","label"=>Lang::get("Intake Date"),"data_type"=>"text"),
            array("name"=>"intake_warehouse_from_name","label"=>Lang::get("From Warehouse"),"data_type"=>"text"),
            //array("name"=>"intake_warehouse_to_name","label"=>Lang::get("To Warehouse"),"data_type"=>"text"),        
            array("name"=>"intake_status","label"=>Lang::get("Status"),"data_type"=>"text"),
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/intake')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'reference_type_filter','field'=>'reference_type')
                    ))
                ;        
        $js = ' $("#reference_type_filter").on("change",function(){
                    ajax_table.methods.data_show(1);
                }); 
            ';
        $app->js_set($js);
        $app->render();
        
    }
    
    public function add(){
        $this->load->helper($this->path->intake_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    

    
    public function view($id="",$method="view"){

        $this->load->helper($this->path->intake_engine);
        $this->load->helper($this->path->intake_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Intake_Engine::intake_exists($id)){
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
            $app->set_menu('collapsed',true);
            $app->set_title($this->title);
            $app->set_breadcrumb($this->title,strtolower($this->title));
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Intake_Renderer::intake_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Intake_Renderer::intake_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
            }
            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        
        
    }
    
    
    public function ajax_search($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $result =array('success' =>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        switch($method){
            case 'intake':
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');
                
                $config = array(
                    'reference_type'=>array(
                        array('val'=>'sales_invoice','query'=>'and t1.intake_type = "sales_invoice"'),
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct t1.*
                                    ,t6.name intake_warehouse_from_name
                                from intake t1                    
                                    inner join intake_warehouse_from t8 on t8.intake_id = t1.id
                                    inner join warehouse t6 on t8.warehouse_id = t6.id
                                where t1.status>0
                        ',
                        'where'=>'
                            and (t1.code like '.$lookup_str.'
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
                    $temp_result['data'][$i]['intake_status'] =
                        SI::get_status_attr(
                            SI::status_get('Intake_Engine', 
                                $temp_result['data'][$i]['intake_status']
                            )['label']
                        );
                    $temp_result['data'][$i]['intake_type'] =
                        SI::type_get('Intake_Engine',
                            $temp_result['data'][$i]['intake_type']
                        )['label'];
                }
                $result = $temp_result;
                break;
            
            case 'input_select_reference_search':
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $limit = 10;
                $q = '
                    select distinct t1.id id
                        ,t1.code code
                        ,t3.id supplier_id
                        ,t3.name supplier_name
                    from rma t1    
                        inner join rma_supplier t2 on t1.id = t2.rma_id
                        inner join supplier t3 on t3.id = t2.supplier_id
                    where t1.rma_status = "O"
                        and t1.status>0
                        and t1.rma_type = "purchase_invoice"
                        and (
                            t1.code like '.$lookup_str.'
                                or t3.name like '.$lookup_str.'
                        )
                    limit 0,'.$limit.'
                        
                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['reference_type'] = 'rma';
                    $rs[$i]['reference_type_name'] = 'Return Merchandise Authorization';
                    $rs[$i]['reference_code'] = $rs[$i]['code'];
                    $rs[$i]['text'] = ''
                            .$rs[$i]['code']
                            .' <span class="pull-right">'
                            .' Supplier: <strong>'.$rs[$i]['supplier_name'].'</strong>'
                            .'</span>'
                        ;
                }
                $result['response'] = $rs;
                break;

            
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function data_support($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        //this function only used for urgently data retrieve
        $data = json_decode($this->input->post(), true);
        get_instance()->load->helper('intake/intake_engine');
        get_instance()->load->helper('intake/intake_data_support');
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        switch($method){
            case 'intake_get':
                get_instance()->load->helper('product/product_engine');
                $response =array();
                $db = new DB();
                $intake_id = $data['data'];
                $q = '
                    select t1.*,
                        t2.code store_code,
                        t2.name store_name,
                        t4.id warehouse_from_id,
                        t4.code warehouse_from_code,
                        t4.name warehouse_from_name,
                        t1.intake_status
                    from intake t1
                        inner join store t2 on t1.store_id = t2.id
                        inner join intake_warehouse_from t3 on t1.id = t3.intake_id
                        inner join warehouse t4 on t4.id = t3.warehouse_id
                    where t1.id = '.$db->escape($intake_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $intake = $rs[0];
                    $product = array();
                    $reference = array();
                    $reference_detail = array();
                    switch($intake['intake_type']){
                        case 'rma':
                            $q = '
                                select t1.*
                                from rma t1
                                    inner join rma_intake t2 on t1.id = t2.rma_id
                                where t2.intake_id='.$db->escape($rs[0]['intake_id']).'
                            ';
                            $rs = $db->query_array_obj($q)[0];
                            if(count($rs)>0){
                                $reference['id'] = $rs->id;
                                $reference['text']= $rs->code;
                                $reference['reference_type'] = 'rma';// mandatory                            
                            }
                            break;
                        case 'sales_invoice':
                            $q= '
                                select t1.*
                                from sales_invoice t1
                                    inner join sales_invoice_intake_final t2
                                        on t1.id = t2.sales_invoice_id
                                    inner join intake_final_intake t3
                                        on t2.intake_final_id = t3.intake_final_id
                                            and t3.intake_id = '.$db->escape($intake_id).'
                            ';
                            $rs = $db->query_array($q);
                            if(count($rs)>0){
                                $reference['id'] = $rs[0]['id'];
                                $reference['text']= $rs[0]['code'];
                                $reference['reference_type'] = 'sales_invoice';// mandatory                            
                            }
                            break;
                    }
                    
                    $reference_detail = Intake_Data_Support::reference_detail_get(
                        $reference['reference_type'],
                        $reference['id'],
                        $intake_id
                    );
                    
                    $intake['warehouse_from_text'] = SI::html_tag('strong',
                        $intake['warehouse_from_code']).' '.$intake['warehouse_from_name'];
                    $intake['intake_date'] = Tools::_date($intake['intake_date'],'F d, Y H:i');
                    $intake['store_text'] = SI::html_tag('strong',$intake['store_code'])
                        .' '.$intake['store_name'];
                    $intake['intake_status_text'] = SI::get_status_attr(
                            SI::status_get('Intake_Engine',$intake['intake_status'])['label']
                        );

                    $product = Intake_Data_Support::intake_product_get($intake_id);
                    $next_allowed_status_list = SI::form_data()
                    ->status_next_allowed_status_list_get('Intake_Engine',
                        $intake['intake_status']
                    );

                    $response['reference'] = $reference;
                    $response['reference_detail'] = $reference_detail;
                    $response['product'] = $product;
                    $response['intake'] = $intake;
                    $response['intake_status_list'] = $next_allowed_status_list;
                }
                $result['response'] = $response;
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function intake_print($id='',$module){
        $this->load->helper('intake/intake_print');
        $post = $this->input->post();
        switch($module){
            case 'intake_form':
                Intake_Print::intake_print($id);
                break;
        }
        
    }
    
    public function intake_process($id){
        $this->load->helper($this->path->intake_engine);
        $post = $this->input->post();
        if($post!= null){
            Intake_Engine::submit($id,'intake_process',$post);
        }
    }
    
    public function intake_add($id=''){
        $this->load->helper($this->path->intake_engine);
        $post = $this->input->post();
        if($post!= null){
            Intake_Engine::submit($id,'intake_add',$post);
        }
    }
    
    public function intake_done($id=''){
        $this->load->helper($this->path->intake_engine);
        $post = $this->input->post();
        if($post!= null){
            Intake_Engine::submit($id,'intake_done',$post);
        }
    }
    
    public function intake_canceled($id=''){
        $this->load->helper($this->path->intake_engine);
        $post = $this->input->post();
        if($post!= null){
            Intake_Engine::submit($id,'intake_canceled',$post);
        }
    }
    
}

?>