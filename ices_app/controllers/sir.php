<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SIR extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('System Investigation Report');
        get_instance()->load->helper('sir/sir_engine');
        $this->path = SIR_Engine::path_get();
        $this->title_icon = App_Icon::sir();
        
    }
    
    public function index()
    {           
        //<editor-fold defaultstate="collapsed">
        $action = "";

        $app = new App();            
        $db = $this->db;

        
        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get(array('System Investigation Report','List')))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array(array('val'=>'New','grammar'=>'adj','uc_first'=>'true'),'System Investigation Report')))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $form->form_group_add();
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"creator","label"=>Lang::get("Creator"),"data_type"=>"text",'attribute'=>array('style'=>"text-align:left"),'row_attrib'=>array('style'=>'text-align:left')),
            array("name"=>"sir_date","label"=>Lang::get("System Investigation Report")." Date","data_type"=>"text"),            
            array("name"=>"description","label"=>Lang::get("Description"),"data_type"=>"text",'attribute'=>array('style'=>"text-align:left"),'row_attrib'=>array('style'=>'text-align:left')),
            
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/sir')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'reference_type_filter','field'=>'reference_type')
                    ))
                ;        
        
        
        $app->render();
        //</editor-fold>
    }
    
    
    public function add(){
        
        $this->load->helper($this->path->sir_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    
    public function view($id="",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->sir_engine);
        $this->load->helper($this->path->sir_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!SIR_Engine::sir_exists($id)){
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
            $app->set_breadcrumb($this->title,'sir');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','sir');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            SIR_Renderer::sir_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                SIR_Renderer::sir_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
                
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
            case 'sir':
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');                
                $config = array(
                    'additional_filter'=>array(
                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct t1.*
                                from sir t1                    
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
                    $temp_result['data'][$i]['sir_status_text'] =
                        SI::get_status_attr(
                            SI::status_get('SIR_Engine', 
                                $temp_result['data'][$i]['sir_status']
                            )['label']
                        );
                    
                }
                $result = $temp_result;
                
                break;
            
            case 'input_select_reference_search':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $db = new DB();
                $limit = 10;
                $lookup_str = $db->escape('%'.(isset($data['data'])?$data['data']:'').'%');
                $extra_param = isset($data['extra_param'])?$data['extra_param']:'';
                $module_name = isset($extra_param['module_name'])?
                    Tools::_str($extra_param['module_name']):'';
                $module_action = isset($extra_param['module_action'])?
                    Tools::_str($extra_param['module_action']):'';
                $reference_detail = array();
                switch($module_name.'_'.$module_action){
                    case 'sales_invoice_pos_cancel':
                        //<editor-fold defaultstate="collapsed">
                        $q = '
                            select distinct t1.id, t1.code, t1.grand_total, t1.sales_invoice_date
                            from sales_invoice t1
                                inner join sales_invoice_info t2 on t1.id = t2.sales_invoice_id
                            where t1.status>0 
                                and t2.sales_invoice_type="sales_invoice_pos"
                                and t1.code like '.$lookup_str.'
                                and t1.sales_invoice_status ="invoiced"
                            order by t1.id desc
                            limit '.$limit.'
                        ';
                        $rs = $db->query_array($q);
                        if(count($rs)>0){
                            for($i = 0;$i<count($rs);$i++){
                                $temp_response = array(
                                    'id'=>$rs[$i]['id'],
                                    'text'=>$rs[$i]['code'],
                                );
                                $response[] = $temp_response;
                            }
                        }
                        //</editor-fold>
                        break;
                    case 'refill_invoice_cancel':
                        //<editor-fold defaultstate="collapsed">
                        $q = '
                            select distinct t1.id, t1.code, t1.grand_total_amount, t1.refill_invoice_date
                            from refill_invoice t1
                            where t1.status>0 
                                and t1.code like '.$lookup_str.'
                                and t1.refill_invoice_status ="invoiced"
                            order by t1.id desc
                            limit '.$limit.'
                        ';
                        $rs = $db->query_array($q);
                        if(count($rs)>0){
                            for($i = 0;$i<count($rs);$i++){
                                $temp_response = array(
                                    'id'=>$rs[$i]['id'],
                                    'text'=>$rs[$i]['code'],
                                );
                                $response[] = $temp_response;
                            }
                        }
                        //</editor-fold>
                        break;
                    
                }
                
                //</editor-fold>
                break;
                
            case 'stock_opname_table_search':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');                
                $config = array(
                    'additional_filter'=>array(                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select t4.id warehouse_id,
                                    t4.code warehouse_code,
                                    t2.id product_id,
                                    t2.code product_code,
                                    t2.name product_name,
                                    t3.id unit_id,
                                    t3.code unit_code,
                                    coalesce(t7.qty,0) qty_stock,
                                    coalesce(t7.qty,0) qty_stock_old,
                                    coalesce(t5.qty,0) qty_stock_sales_available,
                                    coalesce(t5.qty,0) qty_stock_sales_available_old,
                                    coalesce(t6.qty,0) qty_stock_bad,
                                    coalesce(t6.qty,0) qty_stock_bad_old
                                from product_unit t1
                                    cross join warehouse t4 
                                    inner join warehouse_type wt on t4.warehouse_type_id = wt.id and wt.code = "BOS" and t4.status>0
                                    inner join product t2 on t1.product_id = t2.id 
                                    inner join unit t3 on t1.unit_id = t3.id

                                    left outer join product_stock_sales_available t5 
                                            on t1.product_id = t5.product_id and t1.unit_id = t5.unit_id and t4.id = t5.warehouse_id and t5.status>0
                                    left outer join product_stock_bad t6 
                                            on t1.product_id = t6.product_id and t1.unit_id = t6.unit_id and t4.id = t6.warehouse_id and t6.status>0
                                    left outer join product_stock t7
                                            on t1.product_id = t7.product_id and t1.unit_id = t7.unit_id and t4.id = t7.warehouse_id and t7.status>0
                                where 1 = 1
                        ',
                        'where'=>'
                            and (t2.code like '.$lookup_str.'
                                or t2.name like '.$lookup_str.'
                                or t3.code like '.$lookup_str.'
                                or t4.code like '.$lookup_str.'
                            )
                        ',
                        'group'=>'
                            )tfinal
                        ',
                        'order'=>'order by product_code, warehouse_code asc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for($i = 0;$i<count($temp_result['data']);$i++){
                    $temp_result['data'][$i]['product_text'] = SI::html_tag('strong',$temp_result['data'][$i]['product_code'])
                        .' '.$temp_result['data'][$i]['product_name'];
                    $temp_result['data'][$i]['qty_stock'] = Tools::thousand_separator($temp_result['data'][$i]['qty_stock']);
                    $temp_result['data'][$i]['qty_stock_bad'] = Tools::thousand_separator($temp_result['data'][$i]['qty_stock_bad']);
                    $temp_result['data'][$i]['qty_stock_sales_available'] = Tools::thousand_separator($temp_result['data'][$i]['qty_stock_sales_available']);
                    $temp_result['data'][$i]['qty_stock_old'] = Tools::thousand_separator($temp_result['data'][$i]['qty_stock_old']);
                    $temp_result['data'][$i]['qty_stock_bad_old'] = Tools::thousand_separator($temp_result['data'][$i]['qty_stock_bad_old']);
                    $temp_result['data'][$i]['qty_stock_sales_available_old'] = Tools::thousand_separator($temp_result['data'][$i]['qty_stock_sales_available_old']);
                }
                $result = $temp_result;
                //</editor-fold>
                break;
                
            case 'stock_opname_table_search_view':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');                
                $config = array(
                    'additional_filter'=>array(     
                        array('key'=>'product_stock_opname_id','query'=>'and t1.product_stock_opname_id = '),
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select t4.id warehouse_id,
                                        t4.code warehouse_code,
                                        t2.id product_id,
                                        t2.code product_code,
                                        t2.name product_name,
                                        t3.id unit_id,
                                        t3.code unit_code,
                                        sum(case when product_stock_opname_type = "product_stock" then t1.qty else 0 end) qty_stock,
                                        sum(case when product_stock_opname_type = "product_stock" then t1.qty_old else 0 end) qty_stock_old,
                                        sum(case when product_stock_opname_type = "product_stock_bad" then t1.qty else 0 end) qty_stock_bad,
                                        sum(case when product_stock_opname_type = "product_stock_bad" then t1.qty_old else 0 end) qty_stock_bad_old,
                                        sum(case when product_stock_opname_type = "product_stock_sales_available" then t1.qty else 0 end) qty_stock_sales_available,
                                        sum(case when product_stock_opname_type = "product_stock_sales_available" then t1.qty_old else 0 end) qty_stock_sales_available_old

                                from product_stock_opname_product t1
                                        inner join product t2 on t1.product_id = t2.id
                                        inner join unit t3 on t1.unit_id = t3.id
                                        inner join warehouse t4 on t4.id = t1.warehouse_id
                                where 1 = 1 
                        ',
                        'where'=>'
                            and (t2.code like '.$lookup_str.'
                                or t2.name like '.$lookup_str.'
                                or t3.code like '.$lookup_str.'
                                or t4.code like '.$lookup_str.'
                            )
                        ',
                        'group'=>'
                            group by 
                                t4.id,
                                t2.id,
                                t3.id
                            )tfinal
                        ',
                        'order'=>'order by product_code, warehouse_code asc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for($i = 0;$i<count($temp_result['data']);$i++){
                    $temp_result['data'][$i]['product_text'] = SI::html_tag('strong',$temp_result['data'][$i]['product_code']);
                    
                    if(Tools::_float($temp_result['data'][$i]['qty_stock']) === Tools::_float('0')&&
                        Tools::_float($temp_result['data'][$i]['qty_stock_old']) === Tools::_float('0')
                    ){
                        $temp_result['data'][$i]['qty_stock'] = '-';
                    }    
                    else{
                        $temp_result['data'][$i]['qty_stock'] = Tools::thousand_separator($temp_result['data'][$i]['qty_stock']);
                    }
                    
                    if(Tools::_float($temp_result['data'][$i]['qty_stock_bad']) === Tools::_float('0') &&
                        Tools::_float($temp_result['data'][$i]['qty_stock_bad_old']) === Tools::_float('0')){
                        $temp_result['data'][$i]['qty_stock_bad'] = '-';
                    }else{
                        $temp_result['data'][$i]['qty_stock_bad'] = Tools::thousand_separator($temp_result['data'][$i]['qty_stock_bad']);
                    }
                    
                    if(Tools::_float($temp_result['data'][$i]['qty_stock_sales_available']) === Tools::_float('0') &&
                       Tools::_float($temp_result['data'][$i]['qty_stock_sales_available_old']) === Tools::_float('0')     
                    ){
                        $temp_result['data'][$i]['qty_stock_sales_available'] = '-';
                    }
                    else{
                        $temp_result['data'][$i]['qty_stock_sales_available'] = Tools::thousand_separator($temp_result['data'][$i]['qty_stock_sales_available']);
                        
                    }
                    
                }
                $result = $temp_result;
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
        //this function only used for urgently data retrieve
        get_instance()->load->helper('sir/sir_engine');
        get_instance()->load->helper('sir/sir_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'sir_get':
                //<editor-fold defaultstate="collapsed">
                $response =array();
                $db = new DB();
                $sir_id = isset($data['data'])?$data['data']:'';
                $q = '
                    select t1.*,
                        t2.code store_code,
                        t2.name store_name
                    from sir t1
                        inner join store t2 on t1.store_id = t2.id

                    where t1.id = '.$db->escape($sir_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $sir = $rs[0];                    
                    $module_name = $sir['module_name'];
                    $module_action = $sir['module_action'];
                    $reference = array('id'=>'','text'=>'');
                    $reference_detail = array();
                    $extra_data = array();
                    switch($module_name.'_'.$module_action){
                        case 'sales_invoice_pos_cancel':
                            $q = '
                                select t1.id, t1.code
                                from sales_invoice t1
                                where t1.id = '.$db->escape($sir['reference_id']).'
                            ';
                            $rs = $db->query_array($q);
                            if(count($rs)>0){
                                $reference['id'] = $rs[0]['id'];
                                $reference['text'] = $rs[0]['code'];
                            }
                            $reference_detail = SIR_Data_Support::reference_detail_get($module_name, $module_action, $sir['reference_id']);

                            break;
                        case 'refill_invoice_cancel':
                            $q = '
                                select t1.id, t1.code
                                from refill_invoice t1
                                where t1.id = '.$db->escape($sir['reference_id']).'
                            ';
                            $rs = $db->query_array($q);
                            if(count($rs)>0){
                                $reference['id'] = $rs[0]['id'];
                                $reference['text'] = $rs[0]['code'];
                            }
                            $reference_detail = SIR_Data_Support::reference_detail_get($module_name, $module_action, $sir['reference_id']);

                            break;
                        case '':
                            
                            break;
                            
                    }
                    $sir['module_name_text'] = SIR_Data_Support::module_get($module_name)['name']['label'];
                    $sir['module_action_text'] = SIR_Data_Support::module_action_get($module_name, $module_action)['label'];
                    $sir['sir_date'] = Tools::_date($sir['sir_date'],'F d, Y H:i');
                    $sir['store_text'] = SI::html_tag('strong',$sir['store_code'])
                        .' '.$sir['store_name'];
                    $sir['sir_status_text'] = SI::get_status_attr(
                            SI::status_get('SIR_Engine',$sir['sir_status'])['label']
                        );
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('SIR_Engine',
                            $sir['sir_status']
                        );
                    
                    $response['reference'] = $reference;
                    $response['reference_detail'] = $reference_detail;
                    $response['sir'] = $sir;
                    $response['sir_status_list'] = $next_allowed_status_list;
                    $response['extra_data'] = $extra_data;
                }
                //</editor-fold>
                break;
            
            case 'input_select_module_name_get':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $module_list = SIR_Data_Support::module_list_get();
                for($i = 0;$i<count($module_list);$i++){
                    $temp_response = array(
                        'id'=>$module_list[$i]['name']['val'],
                        'text'=>$module_list[$i]['name']['label'],
                        'action'=>array()
                    );
                    for($j = 0;$j<count($module_list[$i]['action']);$j++){
                        $temp_response['action'][] = array(
                            'id'=>$module_list[$i]['action'][$j]['val'],
                            'text'=>$module_list[$i]['action'][$j]['label'],
                        );
                    }
                    $response[] = $temp_response;
                }
                //</editor-fold>
                break;
            
            case 'input_select_reference_detail_get':
                $module_name = isset($data['module_name'])?Tools::_str($data['module_name']):'';
                $module_action = isset($data['module_action'])?Tools::_str($data['module_action']):'';
                $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                $reference_detail = SIR_Data_Support::reference_detail_get($module_name, $module_action, $reference_id);
                $response = $reference_detail;
                break;
            case 'module_method_get':
                $response = '';
                $module_name = isset($data['module_name'])?Tools::_str($data['module_name']):'';
                $module_action = isset($data['module_action'])?Tools::_str($data['module_action']):'';
                $t_response = SIR_Data_Support::module_name_action_method_get($module_name,$module_action);
                if($t_response === ''){
                    $success =  0;
                    $msg[] = 'Submit data failed';
                    $response = '';
                }
                else{
                    $response = $t_response;
                }
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
    }
    
    public function product_stock_opname_add(){
        
        $this->load->helper($this->path->sir_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'product_stock_opname_add','primary_data_key'=>'sir','data_post'=>$post);
            SI::data_submit()->submit('sir_engine',$param);
            
        }
        
    }
    
    public function sales_invoice_pos_cancel_add(){
        $this->load->helper($this->path->sir_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'sales_invoice_pos_cancel_add','primary_data_key'=>'sir','data_post'=>$post);
            SI::data_submit()->submit('sir_engine',$param);
            
        }
    }
    
    public function refill_invoice_cancel_add(){
        $this->load->helper($this->path->sir_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'refill_invoice_cancel_add','primary_data_key'=>'sir','data_post'=>$post);
            SI::data_submit()->submit('sir_engine',$param);
            
        }
    }
    
    public function sir_done($id){
        
        $this->load->helper($this->path->sir_engine);
        $post = $this->input->post();
        if($post!= null){
            SIR_Engine::submit($id,'sir_done',$post);
        }
        
    }
    
    
}

?>