<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_Invoice extends MY_Controller {
    
    private $title='Purchase Invoice';
    private $title_icon = '';
    private $path = array(
        'index'=>''
        ,'purchase_invoice_engine'=>''
        ,'purchase_receipt_allocation_engine'=>''
        ,'receive_product_renderer'=>''
        ,'supplier_engine'=>''
        ,'ajax_search'=>''
        
    );
    
    function __construct(){
        parent::__construct();
        $this->path = json_decode(json_encode($this->path));
        $this->path->index=  get_instance()->config->base_url().'purchase_invoice/';
        $this->path->purchase_invoice_engine=  'purchase_invoice/purchase_invoice_engine';
        $this->path->purchase_receipt_allocation_engine=  'purchase_receipt_allocation/purchase_receipt_allocation_engine';
        $this->path->receive_product_renderer=  'receive_product/receive_product_renderer';
        $this->path->supplier_engine=  'master/supplier_engine';
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
        $form = $row->form_add()->form_set('title','Purchase Invoice List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value','New Purchase Invoice')
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $status_filter_opts = array(
            array('value'=>'','label'=>'ALL')
            ,array('value'=>'I','label'=>'INVOICED')
            ,array('value'=>'X','label'=>'CANCELED')
        );
        
        $form->select_add()
                ->select_set('id','purchase_invoice_status_filter')
                ->select_set('options_add',$status_filter_opts)
                ;
        
        $cols = array(
            //array("name"=>"purchase_order_code","label"=>"Purchase Order <br/> Code","data_type"=>"text",'is_key2'=>true)
            //,
            array("name"=>"code","label"=>"Purchase Invoice <br/> Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"supplier_name","label"=>"Supplier","data_type"=>"text")
            ,array("name"=>"purchase_invoice_date","label"=>"Purchase Invoice <br/> Date","data_type"=>"text")
            ,array("name"=>"purchase_invoice_status","label"=>"Status","data_type"=>"text")
            ,array("name"=>"grand_total","label"=>"Grand Total<br/>(".Tools::currency_get().')',"data_type"=>"text",'row_attrib'=>array('style'=>'text-align:right'))           
            ,array("name"=>"outstanding_amount","label"=>"Outstanding Amount<br/>(".Tools::currency_get().')',"data_type"=>"text",'row_attrib'=>array('style'=>'text-align:right'))           

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('base_href2',get_instance()->config->base_url().'purchase_order/view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/purchase_invoice')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->table_ajax_set('key_column2','purchase_order_id')
                ->filter_set(array(array('id'=>'purchase_invoice_status_filter','field'=>'purchase_invoice_status')))
                ;        
        $js = ' $("#purchase_invoice_status_filter").on("change",function(){
                    ajax_table.methods.data_show(1);
                }) 
                
            ';
        $app->js_set($js);
        $app->render();
        
    }
    
    public function add(){
        $this->load->helper($this->path->purchase_invoice_engine);
        $post = $this->input->post();
        if($post){
            $this->edit('','Add');
        }
        else{
            $this->view('','Add');
        }
        
    }
    
    public function edit($id="",$method="Edit"){
        $this->load->helper($this->path->purchase_invoice_engine);
        $post = $this->input->post();
        if($post != null){
            $post = json_decode($post,TRUE);
            $data = $post;
            $ajax_post = false;                  
            $result = null;
            $cont = true;
            if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
            
            if($method == 'Add') $data['purchase_invoice']['id'] = "";
            else if ($method == 'Edit') $data['purchase_invoice']['id'] = $id;
            else $cont = false;
            
            if(strlen($id)=== 0 && $method === 'Edit') $cont = false;
            
            if($cont){
                $result = Purchase_Invoice_Engine::save($data);
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


        $this->load->helper($this->path->purchase_invoice_engine);
        $this->load->helper($this->path->purchase_receipt_allocation_engine);
        $this->load->helper($this->path->receive_product_renderer);
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('Add','Edit','View'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('Edit','View'))){
                if(Purchase_Invoice_Engine::get($id) == null){
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
            Purchase_Invoice_Engine::purchase_invoice_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            
            if($method == 'View'){
                $receipt_allocation_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#receipt_allocation_view_tab',"value"=>"Payment"));
                $receipt_allocation_view_pane = $receipt_allocation_tab->div_add()->div_set('id','receipt_allocation_view_tab')->div_set('class','tab-pane');
                Purchase_Invoice_Engine::receipt_allocation_view_render($app,$receipt_allocation_view_pane,array("id"=>$id),$this->path);
                
                $receive_product_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#receive_product_view_tab',"value"=>"Receive Product"));
                $receive_product_view_pane = $receive_product_tab->div_add()->div_set('id','receive_product_view_tab')->div_set('class','tab-pane');
                Purchase_Invoice_Engine::receive_product_view_render($app,$receive_product_view_pane,array("id"=>$id),$this->path);
                
                $rma_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#rma_view_tab',"value"=>"RMA"));
                $rma_view_pane = $rma_tab->div_add()->div_set('id','rma_view_tab')->div_set('class','tab-pane');
                Purchase_Invoice_Engine::rma_view_render($app,$rma_view_pane,array("id"=>$id),$this->path);
                
            }
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
    }
    
    
    public function ajax_search($method=""){
        $data = json_decode(file_get_contents('php://input'), true);
        $result =array();
        $row_limit = 15;
        switch($method){
            case 'purchase_invoice':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $additional_filter = '1=1';
                if($data['additional_filter']['purchase_invoice_status'] != '')
                    $additional_filter = 'purchase_invoice_status = '.$db->escape($data['additional_filter']['purchase_invoice_status']);
                $q = '
                    select * from (
                    select t1.id
                    , t1.code,cast(t1.purchase_invoice_date as date) purchase_invoice_date
                    ,case purchase_invoice_status 
                        when "X" then "CANCELED" 
                            WHEN "I" then "INVOICED"
                        end purchase_invoice_status
                    , t1.grand_total
                    ,t3.name supplier_name
                    ,t1.grand_total - t6.paid_amount outstanding_amount
                    from purchase_invoice t1
                    inner join supplier t3 on t3.id = t1.supplier_id
                    left outer join (
                        select tt1.id purchase_invoice_id
                            , coalesce(sum(tt2.allocated_amount),0) paid_amount
                        from purchase_invoice tt1
                        left outer join purchase_receipt_allocation tt2 on tt2.purchase_invoice_id = tt1.id 
                            and tt2.purchase_receipt_allocation_status = "I"
                        group by tt1.id
                    ) t6 on t6.purchase_invoice_id = t1.id
                    where t1.status>0
                ';
                $q_group = ' )tfinal
                    ';
                $q_where=' 
                    and (t1.code like '.$lookup_str.'
                        or t1.purchase_invoice_date like '.$lookup_str.'
                        or t1.purchase_invoice_status like '.$lookup_str.'
                        or t3.name like '.$lookup_str.'
                    )
                    and '.$additional_filter.'
                ';
                
                $extra='';
                if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                else {$extra.=' order by code desc';}
                $extra .= '  limit '.(($page-1)*$records_page).', '.($records_page);
                $q_total_row = $q.$q_where.$q_group;
                $q_data = $q.$q_where.$q_group.$extra;
                $total_rows = $db->select_count($q_total_row,null,null);
                $rs = $db->query_array($q_data);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['grand_total'] = Tools::thousand_separator($rs[$i]['grand_total'],2,true);
                    $rs[$i]['outstanding_amount'] = Tools::thousand_separator($rs[$i]['outstanding_amount'],2,true);
                    $rs[$i]['purchase_invoice_status'] = SI::get_status_attr($rs[$i]['purchase_invoice_status']);
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
                            or replace(phone,"-","") like '.$lookup_str.'
                        )  
                        and supplier_status ="A"
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
            case 'purchase_invoice_product':
                $db= new DB();
                $exceptions = '';
                $q = '
                    select distinct t1.id id, t1.code,t1.name 
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
                $result = $rs;
                break;                
            case 'purchase_invoice_product_unit':
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
                for($i = 0;$i<count($result);$i++){
                    $filename = 'img/product/'.$data['data'].'.jpg';                    
                    $result[$i]['product_img']='<img class = "product-img" src = "'.Tools::img_load($filename,false).'"></img>';
                    
                }
                break;
            
        }
        
        echo json_encode($result);
    }
    
}

?>