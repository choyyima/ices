<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_Return extends MY_Controller {
    
    private $title='Purchase Return';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        get_instance()->load->helper('transaction/purchase/return/purchase_return_engine');
        $this->path = Purchase_Return_Engine::path_get();
        $this->title_icon = App_Icon::info();
        
    }
    
    public function index()
    {           
        get_instance()->load->helper($this->path->purchase_return_engine);
        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title','Purchase Return List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value','New Purchase Return')
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $raw_list = Purchase_Return_Engine::purchase_return_status_list_get();
        
        $status_filter_opts = array();
        foreach($raw_list as $list){
            $status_filter_opts[] = array(
                'value'=>$list['val']
                ,'label'=>$list['label']
                );
        }
        
        $form->select_add()
                ->select_set('id','purchase_return_status_filter')
                ->select_set('options_add',$status_filter_opts)
                ;
        
        $cols = array(
            array("name"=>"purchase_return_code","label"=>"Purchase Return Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"purchase_invoice_code","label"=>"Purchase Invoice Code","data_type"=>"text")
            ,array("name"=>"purchase_return_date","label"=>"Purchase Return Date","data_type"=>"text")
            ,array("name"=>"grand_total","label"=>"Grand Total","data_type"=>"text",'col_attrib'=>array('style'=>'text-align:right'))            
            ,array("name"=>"purchase_return_status_name","label"=>"Status","data_type"=>"text")            
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/purchase_return_index/purchase_return')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'purchase_return_status_filter','field'=>'purchase_return_status')
                    ))
                ;        
        $js = ' $("#purchase_return_status_filter").on("change",function(){
                    ajax_table.methods.data_show(1);
                }); 
            ';
        $app->js_set($js);
        
        $app->render();
        
    }
    
    
    
    public function add(){
        $this->view('','add');
    }
    
    public function view($id="",$method="view"){

        $this->load->helper($this->path->purchase_return_engine);
        $this->load->helper($this->path->purchase_return_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Purchase_Return_Engine::purchase_return_exists($id)){
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
            Purchase_Return_Renderer::purchase_return_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                //Purchase_Return_Renderer::purchase_return_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
            }
            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        
        
    }
    
    public function ajax_search($method="", $submethod = ""){
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>array(),'response'=>null);
        $success = 1;
        $msg = array();
        switch($method){
            case 'purchase_return_index':
                switch($submethod){
                    case 'purchase_return':
                        $db = new DB();
                        $records_page = $data['records_page'];
                        $page = $data['page'];
                        $lookup_str = $db->escape('%'.$data['data'].'%');
                        $additional_filter = '1=1';
                        if($data['additional_filter']['purchase_return_status'] != '')
                            $additional_filter = 'purchase_return_status = '.$db->escape($data['additional_filter']['purchase_return_status']);
                        $q = '
                            select * from (
                                select t1.id,t5.id purchase_invoice_id 
                                    ,t5.code purchase_invoice_code
                                    , t1.code purchase_return_code
                                    ,cast(t1.purchase_return_date as date) purchase_return_date
                                    ,case purchase_return_status 
                                        when "X" then "CANCELED" 
                                            WHEN "I" then "INVOICED"
                                        end purchase_return_status
                                    , t1.grand_total
                                    ,t3.name supplier_name
                                from purchase_return t1
                                inner join supplier t3 on t3.id = t1.supplier_id
                                left outer join purchase_invoice_purchase_return t4 on t4.purchase_return_id = t1.id
                                left outer join purchase_invoice t5 on t4.purchase_invoice_id = t5.id
                                
                                where t1.status>0
                        ';
                        $q_group = ' )tfinal
                            ';
                        $q_where=' 
                            and (t1.code like '.$lookup_str.'
                                or t1.purchase_return_date like '.$lookup_str.'
                                or t1.purchase_return_status like '.$lookup_str.'
                                or t3.name like '.$lookup_str.'
                            )
                            and '.$additional_filter.'
                        ';

                        $extra='';
                        if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                        else {$extra.=' order by purchase_return_code desc';}
                        $extra .= '  limit '.(($page-1)*$records_page).', '.($records_page);
                        $q_total_row = $q.$q_where.$q_group;
                        $q_data = $q.$q_where.$q_group.$extra;
                        $total_rows = $db->select_count($q_total_row,null,null);
                        $rs = $db->query_array($q_data);
                        if(is_null($rs)){
                            $success = 0;
                            $msg[] = $db->_error_message();
                        }else{
                            for($i = 0;$i<count($rs);$i++){
                                $rs[$i]['grand_total'] = Tools::thousand_separator($rs[$i]['grand_total'],2,true);
                                $rs[$i]['purchase_return_status_name'] = SI::get_status_attr($rs[$i]['purchase_return_status']);
                            }
                            $result = array("header"=>array("total_rows"=>$total_rows),"data"=>$rs);
                        }
                        break;
                }
                break;
            case 'purchase_return_transaction':
                switch($submethod){
                    case 'input_select_purchase_invoice_search':
                        $db = new DB();
                        $lookup_str = $db->escape('%'.$data['data'].'%');

                        $q = '
                            select distinct t1.id id
                                ,concat(t1.code,"  <span class=\"pull-right\">Outstanding Qty (units): <strong>"
                                    ,format(t3.total_qty-t2.sent_qty,2),"</strong>"
                                    ," Grand Total (Rp.): <strong>",format(t1.grand_total,2),"</strong></span>"
                                ) text
                            from purchase_invoice t1    
                            inner join
                            (
                                select tt1.id purchase_invoice_id, coalesce(sum(tt4.qty),0) sent_qty
                                from purchase_invoice tt1
                                    left outer join purchase_invoice_receive_product tt2 on tt1.id = tt2.purchase_invoice_id
                                    left outer join receive_product tt3 on tt3.id = tt2.receive_product_id and tt3.receive_product_status !="X"
                                    left outer join receive_product_product tt4 on tt4.receive_product_id = tt3.id                            
                                group by tt1.id
                            ) t2 on t2.purchase_invoice_id = t1.id
                            inner join(
                                select ttt1.purchase_invoice_id, sum(ttt1.qty) total_qty
                                from purchase_invoice_product ttt1
                                group by ttt1.purchase_invoice_id
                            ) t3 on t3.purchase_invoice_id = t1.id
                            
                            where t1.purchase_invoice_status = "I"
                                and t1.code like '.$lookup_str.'
                                and (t3.total_qty - t2.sent_qty) > 0

                        ';
                        $rs = $db->query_array($q);
                        if(is_null($rs)){
                            $success = 0;
                            $msg[] = $db->_error_message();                            
                        }
                        else{
                            $result['response'] = $rs;
                        }
                        break;
            
                    case 'input_select_purchase_invoice_get':
                        $db = new DB();

                        $q = '
                            select t1.id, t1.code
                                , t1.grand_total grand_total
                                , t3.total_qty - t2.sent_qty outstanding_qty
                                , concat(t4.name," ",t4.phone) supplier_name
                            from purchase_invoice t1    
                            inner join
                            (
                                select tt1.id purchase_invoice_id, coalesce(sum(tt4.qty),0) sent_qty
                                from purchase_invoice tt1
                                    left outer join purchase_invoice_receive_product tt2 on tt1.id = tt2.purchase_invoice_id
                                    left outer join receive_product tt3 on tt3.id = tt2.receive_product_id and tt3.receive_product_status !="X"
                                    left outer join receive_product_product tt4 on tt4.receive_product_id = tt3.id
                                group by tt1.id
                            ) t2 on t2.purchase_invoice_id = t1.id
                            inner join(
                                select ttt1.purchase_invoice_id, sum(ttt1.qty) total_qty
                                from purchase_invoice_product ttt1
                                group by ttt1.purchase_invoice_id
                            ) t3 on t3.purchase_invoice_id = t1.id
                            inner join supplier t4 on t1.supplier_id = t4.id
                            where t1.id  ='.$db->escape($data['data']).'
                        ';
                        $rs = $db->query_array($q);
                        
                        if(is_null($rs)){
                            $success = 0;
                            $msg[] = $db->_error_message();                            
                        }
                        else{
                            for($i = 0;$i<count($rs);$i++){
                                $rs[$i]['grand_total'] = Tools::thousand_separator($rs[$i]['grand_total'],2,true);
                                $rs[$i]['outstanding_qty'] = Tools::thousand_separator($rs[$i]['outstanding_qty'],2,true);
                            }
                            $result['response'] = $rs[0];
                        }
                        break;

                    case 'purchase_return_ajax_get':
                        $db = new DB();
                        $q = '
                            select distinct t1.code purchase_return_code
                                ,t1.purchase_return_date
                                ,t1.purchase_return_status
                                ,t3.id purchase_invoice_id
                                ,t3.code purchase_invoice_code
                            from purchase_return t1
                                inner join purchase_invoice_purchase_return t2 on t1.id = t2.purchase_return_id
                                inner join purchase_invoice t3 on t3.id = t2.purchase_invoice_id
                            where t1.id = '.$data['data'].'
                        ';
                        $rs = $db->query_array($q);
                        if(!is_null($rs)){
                            if(count($rs)>0){
                                $purchase_return_status_name = Purchase_Return_Engine::purchase_return_status_get($rs[0]['purchase_return_status'])['label'];
                                $rs[0]['purchase_return_status_name'] = SI::get_status_attr($purchase_return_status_name);
                                $result['response'] =$rs[0];
                            }
                            
                        }
                        else{
                            $success = 0;
                            $msg[] = $db->_error_message();
                        }
                        break;
                }
            break;
                
            case 'detail_purchase_invoice_get':
                $db = new DB();
                $q = '
                    select t1.id,t1.code,format(t1.grand_total,2) grand_total
                        ,format(purchase_invoice_outstanding_amount_get(t1.id),2) outstanding_amount
                    from purchase_invoice t1
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                $result = $rs;
                break;
                
            
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
    }
    
    public function data_support($method="", $submethod=""){
        //this function only used for urgently data retrieve
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>array(), 'response'=>null);
        $success = 1;
        $msg = array();
        switch($method){
            
            case 'purchase_return_transaction':
                switch($submethod){                    
                    case 'default_status_get':
                        get_instance()->load->helper($this->path->purchase_return_engine);
                        $default_status = Purchase_Return_Engine::purchase_return_status_default_status_get();
                        $result['response'] = $default_status;
                        if(isset($default_status['label'])){
                            $result['response']['label'] = SI::get_status_attr($default_status['label']);
                        }
                        break;
                    case 'next_allowed_status':
                        get_instance()->load->helper($this->path->purchase_return_engine);
                        $curr_status_val = isset($data['data'])?$data['data']:'';
                        $next_allowed_status = Purchase_Return_Engine::purchase_return_status_next_allowed_status_get($curr_status_val);
                        $num_of_result = count($next_allowed_status);
                        for($i = 0;$i<$num_of_result;$i++){
                            if(Security_Engine::get_controller_permission(
                                User_Info::get()['user_id']
                                    ,'purchase_return'
                                    ,strtolower($next_allowed_status[$i]['method']))){
                                    $next_allowed_status[$i]['label'] = SI::get_status_attr($next_allowed_status[$i]['label']);
                            }
                            else{
                                unset($next_allowed_status[$i]);
                            }
                        }
                        $result['response'] = $next_allowed_status;
                        break;
                    case 'purchase_return_current_status':
                        $db = new DB();
                        $q = 'select purchase_return_status from purchase_return where id = '.$db->escape($data['data']);
                        $rs = $db->query_array_obj($q);
                        if(is_null($rs)){
                            $success = 0;
                            $msg[] = $db->_error_message();
                        }
                        else{
                            if(count($rs>0)){
                                $result['response'] = $rs[0]->purchase_return_status;
                            }    
                        }

                        break;
                    case 'purchase_invoice_detail_get':
                        $db = new DB();
                        $cont = true;
                        $result['response']=array(
                            'purchase_invoice' => array()
                            ,'purchase_invoice_product' => array()
                        );
                        
                        $purchase_invoice_id = isset($data['data'])?$data['data']:'';
                        $q = '
                            select t1.purchase_invoice_date
                            from purchase_invoice t1
                            where t1.id = '.$db->escape($purchase_invoice_id).'
                        ';
                        $rs = $db->query_array($q);

                        if(is_null($rs)){
                            $success  = 0;
                            $msg[] = $db->_error_message();
                        }
                        else{
                            if( count($rs) > 0){
                                $result['response']['purchase_invoice'] = $rs[0];
                            }
                            else{
                                $cont = false;
                            }
                        }
                        
                        if($success == 1 && $cont ){
                            $q = '
                                select distinct t3.product_id, t4.name product_name
                                    , t5.id unit_id
                                    , t5.name unit_name
                                    , sum(t3.qty) received_qty
                                    , t7.price
                                from purchase_invoice_receive_product t1
                                    inner join receive_product t2 on t1.receive_product_id = t2.id
                                    inner join receive_product_product t3 on t2.id = t3.receive_product_id
                                    inner join product t4 on t3.product_id = t4.id
                                    inner join unit t5 on t3.unit_id = t5.id
                                    inner join purchase_invoice t6 on t6.id = t1.purchase_invoice_id
                                    inner join purchase_invoice_product t7 
                                        on t7.purchase_invoice_id = t6.id
                                        and t7.product_id = t3.product_id and t7.unit_id = t3.unit_id
                                where t1.purchase_invoice_id = '.$db->escape($purchase_invoice_id).'
                                    and t2.receive_product_status = "R"
                                group by t3.product_id, t4.name , t5.id , t5.name 
                                order by t4.name asc
                            ';
                            $rs = $db->query_array($q);
                            if(is_null($rs)){
                                $success = 0;
                                $msg[] = $db->_error_message();
                            }
                            else{
                                
                                for($i = 0;$i<count($rs);$i++){
                                    $rs[$i]['received_qty'] = Tools::thousand_separator($rs[$i]['received_qty'],2,true);
                                    $rs[$i]['price'] = Tools::thousand_separator($rs[$i]['price'],2,true);
                                    $filename = 'img/product/'.$rs[$i]['product_id'].'.jpg';
                                    $rs[$i]['product_img'] = '<img src = "'.Tools::img_load($filename,false).'"></img>';
                                }
                                $result['response']['purchase_invoice_product'] = $rs;
                            }
                        }

                        break;
                }
                break;
            

            
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
    }
    
    public function purchase_return_add(){
        $this->load->helper($this->path->purchase_return_engine);
        $post = $this->input->post();
        if($post!= null){
            Purchase_Return_Engine::purchase_return_submit('','purchase_return_add',$post);
        }
    }
    
    public function purchase_return_canceled($id){
        $this->load->helper($this->path->purchase_return_engine);
        $post = $this->input->post();
        if($post!= null){
            Purchase_Return_Engine::purchase_return_submit($id,'purchase_return_canceled',$post);
        }
    }
    
    
    
}

?>