<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_Receipt_Allocation extends MY_Controller {
    
    private $title='Sales Receipt';
    private $title_icon = '';
    private $path = array(
        'index'=>''
        ,'sales_receipt_allocation_engine'=>''
        ,'ajax_search'=>''
        
    );
    
    function __construct(){
        parent::__construct();
        $this->path = json_decode(json_encode($this->path));
        $this->path->index=  '';
        $this->path->sales_receipt_allocation_engine=  'sales_receipt_allocation/sales_receipt_allocation_engine';
        $this->path->ajax_search=  $this->path->index.'ajax_search/';       
        $this->title_icon = App_Icon::sales_receipt();
        
    }
    
    public function index()
    {           
        
        
    }
    
    public function add(){
        $this->load->helper($this->path->sales_receipt_allocation_engine);
        $post = $this->input->post();
        $this->edit('','Add');
        
    }
    
    public function view($id="",$method="view"){
        
    }
    
    
    public function ajax_search($method=""){
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;
        switch($method){
            case 'input_select_reference_search':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = '%'.$data['data'].'%';
                $customer_id = isset($data['extra_param']['customer_id'])?
                    Tools::_str($data['extra_param']['customer_id']):'';
                $q = '
                    select "sales_invoice" reference_type,
                        t1.id,
                        t1.code text                        
                    from sales_invoice t1
                    where t1.sales_invoice_status = "invoiced" and t1.outstanding_amount > 0
                        and t1.code like '.$db->escape($lookup_str).'
                        and t1.customer_id = '.$db->escape($customer_id).'
                    order by t1.sales_invoice_date desc
                    limit '.$limit.'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){                    
                    $response = array_merge($rs, $response);
                }
                
                $q = '
                    select "customer_bill" reference_type,
                        t1.id,
                        t1.code text
                    from customer_bill t1
                    where t1.customer_bill_status = "invoiced" and t1.outstanding_amount>0
                        and t1.code like '.$db->escape($lookup_str).'
                        and t1.customer_id = '.$db->escape($customer_id).'
                    order by t1.customer_bill_date desc
                    limit '.$limit.'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){                    
                    $response = array_merge($rs, $response);
                }
                //</editor-fold>
                break;
            case 'input_select_sales_receipt_search':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('sales_receipt/sales_receipt_data_support');
                $db = new DB();
                $lookup_val = $data['data'];
                $customer_id = isset($data['extra_param']['customer_id'])?
                    Tools::_str($data['extra_param']['customer_id']):'';
                $search_param = array('customer_id'=>$customer_id,'lookup_val'=>$lookup_val);
                $sales_receipt_raw = Sales_Receipt_Data_Support::sales_receipt_outstanding_amount_search($search_param);
                foreach($sales_receipt_raw as $idx=>$sr){
                    $response[] = array(
                        'id'=>$sr['id'],
                        'text'=>SI::html_tag('strong',$sr['code']),
                    );
                }
                
                //</editor-fold>
                break;
            
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
    }
    
    public function data_support($method=""){
        //this function only used for urgently data retrieve
        get_instance()->load->helper('sales_receipt_allocation/sales_receipt_allocation_engine');
        get_instance()->load->helper('sales_receipt_allocation/sales_receipt_allocation_data_support');
        get_instance()->load->helper('sales_receipt/sales_receipt_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'input_select_sales_receipt_detail_get':
                $sales_receipt_id = isset($data['data'])?Tools::_str($data['data']):'';
                $sales_receipt_detail = Sales_Receipt_Data_Support::sales_receipt_get($sales_receipt_id);
                $sales_receipt_detail['amount'] = Tools::thousand_separator($sales_receipt_detail['amount']);
                $sales_receipt_detail['outstanding_amount'] = Tools::thousand_separator($sales_receipt_detail['outstanding_amount']);
                $sales_receipt_detail['sales_receipt_date'] = Tools::_date($sales_receipt_detail['sales_receipt_date'],'F d, Y H:i:s');
                $response = $sales_receipt_detail;
                break;
            case 'input_select_reference_detail_get':
                $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                $reference_type = isset($data['reference_type'])?Tools::_str($data['reference_type']):'';
                $reference_detail = Sales_Receipt_Allocation_Data_Support::reference_detail_get($reference_type,$reference_id);
                $response['reference_detail'] = $reference_detail;
                break;
            case 'sales_receipt_allocation_get':
                $response =array();
                $db = new DB();
                $sra_id = $data['data'];
                $q = '
                    select t1.*,
                        sales_receipt_allocation_status sra_status,
                        t2.id store_id,
                        t2.code store_code,
                        t2.name store_name
                    from sales_receipt_allocation t1
                        inner join store t2 on t1.store_id = t2.id
                    where t1.id = '.$db->escape($sra_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $sra = $rs[0];                    
                    $reference = array();
                    $sales_receipt = array();
                    $customer = array();
                    
                    $sales_receipt_id = $sra['sales_receipt_id'];
                    $sra_type = $sra['sales_receipt_allocation_type'];
                    
                    $sra['store_text'] = SI::html_tag('strong',$sra['store_code'])
                        .' '.$sra['store_name'];
                    $sra['sra_status_text'] = SI::get_status_attr(
                            SI::status_get('Sales_Receipt_Allocation_Engine',$sra['sra_status'])['label']
                        );
                    $sra['allocated_amount'] = Tools::thousand_separator($sra['allocated_amount'],5);
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Sales_Receipt_Allocation_Engine',
                            $sra['sra_status']
                        );
                    
                    $sales_receipt_raw = Sales_Receipt_Data_Support::sales_receipt_get($sales_receipt_id);
                    
                    if(count($sales_receipt_raw)>0){
                        $sales_receipt = $sales_receipt_raw;
                        $sales_receipt['sales_receipt_date'] = Tools::_date($sales_receipt['sales_receipt_date'],'F d, Y H:i:s');
                        $sales_receipt['amount'] = Tools::thousand_separator($sales_receipt['amount']);
                        $sales_receipt['outstanding_amount'] = Tools::thousand_separator($sales_receipt['outstanding_amount']);
                    }
                    
                    $q = '';
                    switch($sra_type){
                        case 'sales_invoice':
                            get_instance()->load->helper('sales_pos/sales_pos_data_support');
                            $temp_data = Sales_Pos_Data_Support::sales_invoice_get($sra['sales_invoice_id']);
                            $reference['id'] = $temp_data['id'];
                            $reference['text'] = $temp_data['code'];
                            break;
                        case 'customer_bill':
                            get_instance()->load->helper('customer_bill/customer_bill_data_support');
                            $temp_data = Customer_Bill_Data_Support::customer_bill_get($sra['customer_bill_id']);
                            $reference['id'] = $temp_data['id'];
                            $reference['text'] = $temp_data['code'];
                            break;
                    }
                    
                    $reference_detail = Sales_Receipt_Allocation_Data_Support::reference_detail_get($sra_type,$reference['id']);
                    
                    $response['sra'] = $sra;
                    $response['reference'] = $reference;
                    $response['reference_detail'] = $reference_detail;
                    $response['sales_receipt'] = $sales_receipt;
                    $response['sra_status_list'] = $next_allowed_status_list;
                    $response['customer'] = $customer;
                }
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
    }
    
    
    public function sales_receipt_allocation_add(){
        $this->load->helper($this->path->sales_receipt_allocation_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'sales_receipt_allocation_add','primary_data_key'=>'sales_receipt_allocation','data_post'=>$post);
            SI::data_submit()->submit('sales_receipt_allocation_engine',$param);
        }
    }
    
    public function sales_receipt_allocation_canceled($id=''){
        $this->load->helper($this->path->sales_receipt_allocation_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'sales_receipt_allocation_canceled','primary_data_key'=>'sales_receipt_allocation','data_post'=>$post);
            SI::data_submit()->submit('sales_receipt_allocation_engine',$param);
        }
    }
    
}

?>