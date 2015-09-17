<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class DOFC extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Delivery Order Final Confirmation');
        get_instance()->load->helper('dofc/dofc_engine');
        $this->path = DOFC_Engine::path_get();
        $this->title_icon = App_Icon::dofc();
        
    }
    
    public function index()
    {           
        $action = "";

        $app = new App();            
        $db = $this->db;

        
        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower('dofc'));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get(array('Delivery Order Final Confirmation','List')))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Delivery Order Final Confirmation')))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $reference_type_list = array(
            array('value'=>'','label'=>'ALL')
        );
        $module_list = SI::type_list_get('DOFC_Engine');
        foreach($module_list as $module_idx=>$module){
            $reference_type_list[] = array('value'=>$module['val'],'label'=>$module['label']);
        }
        
        $form->select_add()
                ->select_set('id','reference_type_filter')
                ->select_set('options_add',$reference_type_list)
                ;
        
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"dofc_type","label"=>Lang::get("Type"),"data_type"=>"text"),
            array("name"=>"dofc_date","label"=>Lang::get("Date"),"data_type"=>"text"),
            array("name"=>"dofc_status","label"=>Lang::get("Status"),"data_type"=>"text"),
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/dofc')
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
        $this->load->helper($this->path->dofc_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    public function view($id="",$method="view"){
        
        $this->load->helper($this->path->dofc_engine);
        $this->load->helper($this->path->dofc_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!DOFC_Engine::dofc_exists($id)){
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
            $app->set_breadcrumb($this->title,'dofc');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','dofc');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            DOFC_Renderer::dofc_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                DOFC_Renderer::dofc_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
                $customer_bill_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#customer_bill_tab',"value"=>"Customer Bill"));
                $customer_bill_pane = $customer_bill_tab->div_add()->div_set('id','customer_bill_tab')->div_set('class','tab-pane');
                DOFC_Renderer::dofc_customer_bill_render($app,$customer_bill_pane,array("id"=>$id),$this->path);
                
                
                $customer_deposit_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#customer_deposit_tab',"value"=>"Customer Deposit"));
                $customer_deposit_pane = $customer_deposit_tab->div_add()->div_set('id','customer_deposit_tab')->div_set('class','tab-pane');
                DOFC_Renderer::dofc_customer_deposit_render($app,$customer_deposit_pane,array("id"=>$id),$this->path);                
                
                
            }            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        
        
    }
    
    public function ajax_search($method="",$submethod=""){
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $limit = 10;
        switch($method){
            
            case 'dofc':
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');                
                $config = array(
                    'additional_filter'=>array(
                        array('key'=>'reference_type','query'=>'and t1.delivery_order_type ='),
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct t1.*
                                from delivery_order_final_confirmation t1                    
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
                    $temp_result['data'][$i]['dofc_date'] =
                        Tools::_date(
                            $temp_result['data'][$i]['delivery_order_final_confirmation_date']
                        ,'F d, Y H:i:s');
                    $temp_result['data'][$i]['dofc_status'] =
                        SI::get_status_attr(
                            SI::status_get('DOFC_Engine', 
                                $temp_result['data'][$i]['delivery_order_final_confirmation_status']
                            )['label']
                        );
                    $temp_result['data'][$i]['dofc_type'] =
                        SI::type_get('DOFC_Engine',
                            $temp_result['data'][$i]['delivery_order_final_confirmation_type']
                        )['label'];
                }
                $result = $temp_result;
                
                break;
                
            case 'input_select_reference_search':
                $response = array();
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $limit = 10;
                $q = '
                    select distinct t1.id id
                        ,t1.code code
                        ,t1.delivery_order_final_date
                        ,t1.delivery_order_final_type
                    from delivery_order_final t1
                        inner join dof_info dofi
                            on t1.id = dofi.delivery_order_final_id
                    where t1.delivery_order_final_status ="done"
                        and dofi.confirmation_required = 1
                    order by t1.delivery_order_final_date desc
                    limit '.$limit.'
                        
                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $t_response = array();
                    $t_response['id'] = $rs[$i]['id'];
                    $t_response['reference_type'] = $rs[$i]['delivery_order_final_type'];
                    $t_response['reference_type_name'] = SI::type_get('DOFC_Engine', 
                        $rs[$i]['delivery_order_final_type'])['label'];
                    $t_response['reference_code'] = $rs[$i]['code'];
                    $t_response['text'] = ''
                            .SI::html_tag('strong',$rs[$i]['code'])
                            .' '
                            .Tools::_date($rs[$i]['delivery_order_final_date'],'F d, Y H:i')
                        ;
                    
                    $response[] = $t_response;
                }
                $result['response'] = $response;
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
    }
    
    public function data_support($method="",$submethod=""){
        //this function only used for urgently data retrieve
        get_instance()->load->helper('delivery_order_final/delivery_order_final_engine');
        get_instance()->load->helper('dofc/dofc_engine');
        get_instance()->load->helper('dofc/dofc_data_support');
        get_instance()->load->helper('product/product_engine');
        get_instance()->load->helper('product_stock_engine');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'reference_dependency_get':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $ref_id = isset($data['ref_id'])?Tools::_str($data['ref_id']):'';
                $ref_type = isset($data['ref_type'])?Tools::_str($data['ref_type']):'';
                $response = DOFC_Data_Support::reference_dependency_get($ref_type,$ref_id);
                
                //</editor-fold>
                break;
            case 'dofc_init_get':
                $dofc_id = isset($data['data'])?$data['data']:'';
                $db = new DB();
                $response = array();
                $q = '
                    select distinct t1.id id, t1.code, t1.delivery_order_final_confirmation_type dofc_type
                    from delivery_order_final_confirmation t1
                    where t1.id = '.$db->escape($dofc_id).'
                ';
                $rs = $db->query_array($q);
                
                if(count($rs)>0){
                    $dofc_type = $rs[0]['dofc_type'];
                    $q = '
                        select t1.*
                        from delivery_order_final t1
                            inner join dof_dofc t2 on t1.id = t2.delivery_order_final_id
                        where t2.delivery_order_final_confirmation_id = '.$db->escape($dofc_id).'
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        $dof_id = $rs[0]['id'];
                        $reference = array();
                        
                        $reference['id'] = $rs[0]['id'];
                        $reference['text']= $rs[0]['code'];
                        $reference['reference_type'] = $dofc_type;// mandatory                            
                        $reference['reference_type_name'] = SI::type_get('DOFC_Engine', $dofc_type)['label'];
                        
                        $reference_dependency = DOFC_Data_Support::reference_dependency_get($dofc_type, $dof_id);
                        
                        $reference['mail_to'] = $reference_dependency['mail_to'];
                        $response['reference'] = $reference;
                        $response['reference_detail'] = $reference_dependency['reference_detail'];
                    }
                    
                }
                
                break;
                
            case 'dofc_get':
                $response =array();
                $db = new DB();
                $dofc_id = $data['data'];
                $q = '
                    select t1.*,
                        t4.delivery_order_final_id,
                        t1.delivery_order_final_confirmation_status dofc_status,
                        t2.code store_code,
                        t2.name store_name,
                        t3.expedition_name,
                        t3.driver_name,
                        t3.driver_assistant_name,
                        t3.receiver_name,
                        t3.expedition_name,
                        t3.receipt_number,
                        t3.receiver_name
                    from delivery_order_final_confirmation t1
                        inner join store t2 on t1.store_id = t2.id
                        inner join delivery_order_final_confirmation_info t3 
                            on t1.id = t3.delivery_order_final_confirmation_id
                        inner join dof_dofc t4 on t4.delivery_order_final_confirmation_id = t1.id
                    where t1.id = '.$db->escape($dofc_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $dofc = $rs[0];                    
                    $dof = array();
                    $dofc_additional_cost = array();
                    
                    $dof_id = $dofc['delivery_order_final_id'];
                    $dofc['dofc_date'] = Tools::_date($dofc['delivery_order_final_confirmation_date'],'F d, Y H:i');
                    $dofc['store_text'] = SI::html_tag('strong',$dofc['store_code'])
                        .' '.$dofc['store_name'];
                    $dofc['dofc_status_text'] = SI::get_status_attr(
                            SI::status_get('DOFC_Engine',$dofc['dofc_status'])['label']
                        );
                    $dofc['delivery_cost'] = Tools::thousand_separator($dofc['delivery_cost'],2);
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('DOFC_Engine',
                            $dofc['dofc_status']
                        );
                    
                    $q = '
                        select t1.*
                        from delivery_order_final_confirmation_additional_cost t1
                        where t1.delivery_order_final_confirmation_id = '.$db->escape($dofc_id).'
                    ';
                    
                    $rs = $db->query_array_obj($q);
                    if(count($rs)>0){ 
                        foreach($rs as $idx=>$row){
                            $row->amount = Tools::thousand_separator($row->amount);
                        }
                    }
                    $dofc_additional_cost = json_decode(json_encode($rs),true);
                    
                    $response['dofc'] = $dofc;
                    $response['dofc_additional_cost'] = $dofc_additional_cost;
                    $response['dofc_status_list'] = $next_allowed_status_list;
                }
                
                break;
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
    }
    
    public function dofc_mail($module=''){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->dofc_engine);
        $post = $this->input->post();
        $data = json_decode($post,TRUE);
        $result = array('success'=>1,'msg'=>array());
        $success = 1;        
        $msg = array();
        switch($module){
            case 'dofc':
                $temp_result = DOFC_Engine::dofc_mail($data);
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];                
                
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function dofc_print($module='',$id='',$prm1=''){
        $this->load->helper($this->path->dofc_print);
        $post = $this->input->post();
        switch($module){
            case 'dofc':
                DOFC_Print::dofc_print($id);
                break;
        }
    }
    
    public function dofc_add(){
        $this->load->helper($this->path->dofc_engine);
        $post = $this->input->post();
        if($post!= null){
            DOFC_Engine::submit('','dofc_add',$post);
        }
    }
    
    public function dofc_process($id){
        $this->load->helper($this->path->dofc_engine);
        $post = $this->input->post();
        if($post!= null){
            DOFC_Engine::submit($id,'dofc_process',$post);
        }
    }
    
    public function dofc_done($id){
        $this->load->helper($this->path->dofc_engine);
        $post = $this->input->post();
        if($post!= null){
            DOFC_Engine::submit($id,'dofc_done',$post);
        }
    }
    
    public function dofc_canceled($id){
        $this->load->helper($this->path->dofc_engine);
        $post = $this->input->post();
        if($post!= null){
            DOFC_Engine::submit($id,'dofc_canceled',$post);
        }
    }
}

?>