<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_Receipt_old extends MY_Controller {
    
    private $title='Purchase Receipt';
    private $title_icon = '';
    private $path = array(
        'index'=>''
        ,'purchase_receipt_engine'=>''
        ,'purchase_receipt_allocation_engine'=>''
        ,'ajax_search'=>''
        
    );
    
    function __construct(){
        parent::__construct();
        $this->path = json_decode(json_encode($this->path));
        $this->path->index=  get_instance()->config->base_url().'purchase_receipt/';
        $this->path->purchase_receipt_engine=  'purchase_receipt/purchase_receipt_engine';
        $this->path->purchase_receipt_allocation_engine=  'purchase_receipt_allocation/purchase_receipt_allocation_engine';
        $this->path->ajax_search=  $this->path->index.'ajax_search/';       
        $this->title_icon = App_Icon::info();
        
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
        $form = $row->form_add()->form_set('title','Purchase Receipt List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value','New Purchase Receipt')
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $status_filter_opts = array(
            array('value'=>'','label'=>'ALL')
            ,array('value'=>'I','label'=>'INVOICED')
            ,array('value'=>'X','label'=>'CANCELED')
        );
        
        $form->select_add()
                ->select_set('id','purchase_receipt_status_filter')
                ->select_set('options_add',$status_filter_opts)
                ;
        
        $cols = array(
            array("name"=>"code","label"=>"Purchase Receipt <br/> Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"supplier_name","label"=>"Supplier","data_type"=>"text")
            ,array("name"=>"payment_type_name","label"=>"Type","data_type"=>"text")
            ,array("name"=>"purchase_receipt_date","label"=>"Purchase Receipt <br/> Date","data_type"=>"text")
            ,array("name"=>"amount","label"=>"Amount","data_type"=>"text",'row_attrib'=>array('style'=>'text-align:right'))           
            ,array("name"=>"allocated_amount","label"=>"Allocated <br/> Amount","data_type"=>"text",'row_attrib'=>array('style'=>'text-align:right'))           
            ,array("name"=>"outstanding_amount","label"=>"Outstanding <br/> Amount","data_type"=>"text",'row_attrib'=>array('style'=>'text-align:right'))           
            
            ,array("name"=>"purchase_receipt_status_name","label"=>"Status","data_type"=>"text")
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('base_href2',get_instance()->config->base_url().'purchase_order/view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/purchase_receipt')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->table_ajax_set('key_column2','purchase_order_id')
                ->filter_set(array(array('id'=>'purchase_receipt_status_filter','field'=>'purchase_receipt_status')))
                ;        
        $js = ' $("#purchase_receipt_status_filter").on("change",function(){
                    ajax_table.methods.data_show(1);
                }) 
                
            ';
        $app->js_set($js);
        $app->render();
        
    }
    
    public function add(){
        $this->load->helper($this->path->purchase_receipt_engine);
        $post = $this->input->post();
        if($post){
            $this->edit('','Add');
        }
        else{
            $this->view('','Add');
        }
        
    }
    
    public function edit($id="",$method="Edit"){
        $this->load->helper($this->path->purchase_receipt_engine);
        $post = $this->input->post();
        if($post != null){
            $post = json_decode($post,TRUE);
            $data = $post;
            $ajax_post = false;                  
            $result = null;
            $cont = true;
            if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
            
            if($method == 'Add') $data['purchase_receipt']['id'] = "";
            else if ($method == 'Edit') $data['purchase_receipt']['id'] = $id;
            else $cont = false;
            
            if(strlen($id)=== 0 && $method === 'Edit') $cont = false;
            
            if($cont){
                $result = Purchase_Receipt_Engine::save($data);
            }
            
            if(!$ajax_post){
                echo json_encode($result);
                die();
            }            
            else{
                echo json_encode($result);
                die();
            }
        }
        //$this->view($id,$method);
    }
    
    
    
    public function view($id="",$method="View"){

        $this->load->helper($this->path->purchase_receipt_engine);
        $this->load->helper($this->path->purchase_receipt_allocation_engine);
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('Add','View'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('View'))){
                if(Purchase_Receipt_Engine::get($id) == null){
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
            $db = $this->db;

            $app->set_title($this->title);
            $app->set_breadcrumb($this->title,strtolower($this->title));
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row');            
            $init_state = true;

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Purchase_Receipt_Engine::purchase_receipt_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method ==='View'){
                $receipt_allocation_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#receipt_allocation_view_tab',"value"=>"Receipt Allocation"));
                $receipt_allocation_view_pane = $receipt_allocation_tab->div_add()->div_set('id','receipt_allocation_view_tab')->div_set('class','tab-pane');
                Purchase_Receipt_Engine::receipt_allocation_view_render($app,$receipt_allocation_view_pane,array("id"=>$id),$this->path);
            }
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
    }
    
    
    public function ajax_search($method=""){
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'purchase_receipt':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $additional_filter = '1=1';
                if($data['additional_filter']['purchase_receipt_status'] != '')
                    $additional_filter = 'purchase_receipt_status = '.$db->escape($data['additional_filter']['purchase_receipt_status']);
                $q = 'select * from ( ';
                $q .= '
                    
                    select t1.id, t1.code
                        , t1.allocated_amount, t1.amount, t1.amount-t1.allocated_amount outstanding_amount
                        ,t1.purchase_receipt_status
                        ,t1.purchase_receipt_date
                        ,t2.name supplier_name
                        ,case t1.purchase_receipt_status
                            when "I" then "INVOICED"
                            when "X" then "CANCELED"
                            end purchase_receipt_status_name
                        ,t3.code payment_type_name
                    from purchase_receipt t1
                        inner join supplier t2 on t1.supplier_id = t2.id
                        inner join payment_type t3 on t1.payment_type_id = t3.id
                    where t1.status >0
                ';
                
                $q_where=' 
                    and (t1.code like '.$lookup_str.'
                        or t1.purchase_receipt_date like '.$lookup_str.'
                        or t1.purchase_receipt_status like '.$lookup_str.'
                    )
                    and '.$additional_filter.'
                ';
                
                $q_group = ' ) tfinal
                    ';
                
                $extra='';
                if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                else {$extra.=' order by code desc';}
                $extra .= '  limit '.(($page-1)*$records_page).', '.($records_page);
                $q_total_row = $q.$q_where.$q_group;//die($q_total_row);
                $q_data = $q.$q_where.$q_group.$extra;// die($q_data);
                $total_rows = $db->select_count($q_total_row,null,null);
                $rs = $db->query_array($q_data);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['purchase_receipt_date'] = str_replace(' ','&nbsp',date('Y M d   H:i:s',strtotime($rs[$i]['purchase_receipt_date'])));
                    $rs[$i]['amount'] = Tools::thousand_separator($rs[$i]['amount'],2,true);
                    $rs[$i]['allocated_amount'] = Tools::thousand_separator($rs[$i]['allocated_amount'],2,true);
                    $rs[$i]['outstanding_amount'] = Tools::thousand_separator($rs[$i]['outstanding_amount'],2,true);
                    $rs[$i]['purchase_receipt_status_name'] = SI::get_status_attr($rs[$i]['purchase_receipt_status_name'],2,true);
                }
                $result = array("header"=>array("total_rows"=>$total_rows),"data"=>$rs);
                
                break;
                
            case 'detail_po_code_search':
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $q = '
                    select id id, code text
                    from purchase_order
                    where status >0 and purchase_order_status != "X" and code like '.$lookup_str.'
                    limit 0,20    
                ';
                $result = $db->query_array($q);
                break;
            case 'detail_supplier_search':
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $q = '
                    select id id, name text
                    from supplier
                    where status >0 
                        and (
                            code like '.$lookup_str.'
                            or name like '.$lookup_str.'
                        )
                        and supplier_status = "A"
                    limit 0,20    
                ';
                $result = $db->query_array($q);
                break;
            case 'detail_supplier_get':
                $db = new DB();
                
                $q = '
                    select *
                    from supplier
                    where id = '.$data['data'].'
                               
                ';
                
                $result = $db->query_array($q);
                if(count($result)>0) $result = $result[0];
                break;
            case 'purchase_receipt_product_unit':
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
            case 'purchase_receipt_product':
                $db= new DB();
                $exceptions = '';
                $q = '
                    select distinct t1.id id, t1.name text 
                    from product t1
                    inner join product_unit t2 on t1.id = t2.product_id
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
        }
        
        echo json_encode($result);
    }
    
}

?>