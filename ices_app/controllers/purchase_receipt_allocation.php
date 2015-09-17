<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_Receipt_Allocation extends MY_Controller {
    
    private $title='Purchase Receipt';
    private $title_icon = '';
    private $path = array(
        'index'=>''
        ,'purchase_receipt_allocation_engine'=>''
        ,'ajax_search'=>''
        
    );
    
    function __construct(){
        parent::__construct();
        $this->path = json_decode(json_encode($this->path));
        $this->path->index=  '';
        $this->path->purchase_receipt_allocation_engine=  'purchase_receipt_allocation/purchase_receipt_allocation_engine';
        $this->path->ajax_search=  $this->path->index.'ajax_search/';       
        $this->title_icon = App_Icon::purchase_receipt();
        
    }
    
    public function index()
    {           
        
        
    }
    
    public function add(){
        $this->load->helper($this->path->purchase_receipt_allocation_engine);
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
                $lookup_val = isset($data['data'])?Tools::_str($data['data']):'';
                $supplier_id = isset($data['extra_param']['supplier_id'])?
                    Tools::_str($data['extra_param']['supplier_id']):'';                
                $db = new DB();
                $search_param = array('supplier_id'=>$supplier_id,'lookup_val'=>$lookup_val);
                
                get_instance()->load->helper('purchase_invoice/purchase_invoice_data_support');                
                $purchase_invoice_raw = Purchase_Invoice_Data_Support::purchase_invoice_outstanding_amount_search($search_param);
                foreach($purchase_invoice_raw as $idx=>$pi){
                    $response[] = array(
                        'id'=>$pi['id'],
                        'text'=>$pi['code'],
                        'reference_type'=>'purchase_invoice'
                    );
                }
                
                //</editor-fold>
                break;
            case 'input_select_purchase_receipt_search':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_val = isset($data['data'])?Tools::_str($data['data']):'';
                $supplier_id = isset($data['extra_param']['supplier_id'])?
                    Tools::_str($data['extra_param']['supplier_id']):'';
                $search_param = array('supplier_id'=>$supplier_id,'lookup_val'=>$lookup_val);
                
                get_instance()->load->helper('purchase_receipt/purchase_receipt_data_support');                
                $purchase_receipt_raw = Purchase_Receipt_Data_Support::purchase_receipt_outstanding_amount_search($search_param);
                foreach($purchase_receipt_raw as $idx=>$pi){
                    $response[] = array(
                        'id'=>$pi['id'],
                        'text'=>$pi['code'],
                        'reference_type'=>'purchase_receipt'
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
        get_instance()->load->helper('purchase_receipt_allocation/purchase_receipt_allocation_engine');
        get_instance()->load->helper('purchase_receipt_allocation/purchase_receipt_allocation_data_support');
        get_instance()->load->helper('purchase_receipt/purchase_receipt_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'input_select_purchase_receipt_detail_get':
                $purchase_receipt_id = isset($data['data'])?Tools::_str($data['data']):'';
                $purchase_receipt_detail = Purchase_Receipt_Data_Support::purchase_receipt_get($purchase_receipt_id);
                $purchase_receipt_detail['amount'] = Tools::thousand_separator($purchase_receipt_detail['amount']);
                $purchase_receipt_detail['outstanding_amount'] = Tools::thousand_separator($purchase_receipt_detail['outstanding_amount']);
                $purchase_receipt_detail['purchase_receipt_date'] = Tools::_date($purchase_receipt_detail['purchase_receipt_date'],'F d, Y H:i:s');
                $response = $purchase_receipt_detail;
                break;
            case 'input_select_reference_detail_get':
                $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                $reference_type = isset($data['reference_type'])?Tools::_str($data['reference_type']):'';
                $reference_detail = Purchase_Receipt_Allocation_Data_Support::reference_detail_get($reference_type,$reference_id);
                $response['reference_detail'] = $reference_detail;
                break;
            case 'purchase_receipt_allocation_get':
                $response =array();
                $db = new DB();
                $pra_id = $data['data'];
                $q = '
                    select t1.*,
                        purchase_receipt_allocation_status pra_status,
                        t2.id store_id,
                        t2.code store_code,
                        t2.name store_name
                    from purchase_receipt_allocation t1
                        inner join store t2 on t1.store_id = t2.id
                    where t1.id = '.$db->escape($pra_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $pra = $rs[0];                    
                    $reference = array();
                    $purchase_receipt = array();
                    $customer = array();
                    
                    $purchase_receipt_id = $pra['purchase_receipt_id'];
                    $pra_type = $pra['purchase_receipt_allocation_type'];
                    
                    $pra['store_text'] = SI::html_tag('strong',$pra['store_code'])
                        .' '.$pra['store_name'];
                    $pra['pra_status_text'] = SI::get_status_attr(
                            SI::status_get('Purchase_Receipt_Allocation_Engine',$pra['pra_status'])['label']
                        );
                    $pra['allocated_amount'] = Tools::thousand_separator($pra['allocated_amount'],5);
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Purchase_Receipt_Allocation_Engine',
                            $pra['pra_status']
                        );
                    
                    $q = '
                        select t1.*,  outstanding_amount, t1.purchase_receipt_date
                        from purchase_receipt t1
                        where t1.id = '.$db->escape($purchase_receipt_id).'
                    ';
                    
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        $purchase_receipt = $rs[0];
                        $purchase_receipt['purchase_receipt_date'] = Tools::_date($purchase_receipt['purchase_receipt_date'],'F d, Y H:i:s');
                        $purchase_receipt['amount'] = Tools::thousand_separator($purchase_receipt['amount']);
                        $purchase_receipt['outstanding_amount'] = Tools::thousand_separator($purchase_receipt['outstanding_amount']);
                    }
                    
                    $q = '';
                    switch($pra_type){
                        case 'purchase_invoice':
                            get_instance()->load->helper('purchase_invoice/purchase_invoice_data_support');
                            $temp_data = Purchase_Invoice_Data_Support::purchase_invoice_get($pra['purchase_invoice_id']);
                            $reference['id'] = $temp_data['id'];
                            $reference['text'] = $temp_data['code'];
                            $reference['transactional_date'] = Tools::_date($temp_data['purchase_invoice_date'],'F d, Y H:i:s');
                            $reference['amount'] = Tools::thousand_separator($temp_data['grand_total']);
                            $reference['outstanding_amount'] = Tools::thousand_separator($temp_data['outstanding_amount']);
                            break;
                        case 'customer_bill':
                            get_instance()->load->helper('customer_bill/customer_bill_data_support');
                            $temp_data = Customer_Bill_Data_Support::customer_bill_get($pra['customer_bill_id']);
                            $reference['id'] = $temp_data['id'];
                            $reference['text'] = $temp_data['code'];
                            $reference['transactional_date'] = Tools::_date($temp_data['customer_bill_date'],'F d, Y H:i:s');
                            $reference['amount'] = Tools::thousand_separator($temp_data['amount']);
                            $reference['outstanding_amount'] = Tools::thousand_separator($temp_data['outstanding_amount']);
                            break;
                    }
                    
                    
                    $response['pra'] = $pra;
                    $response['reference'] = $reference;
                    $response['purchase_receipt'] = $purchase_receipt;
                    $response['pra_status_list'] = $next_allowed_status_list;
                    $response['customer'] = $customer;
                }
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
    }
    
    
    public function purchase_receipt_allocation_add(){
        $this->load->helper($this->path->purchase_receipt_allocation_engine);
        $post = $this->input->post();
        if($post!= null){
            Purchase_Receipt_Allocation_Engine::submit('','purchase_receipt_allocation_add',$post);
        }
    }
    
    public function purchase_receipt_allocation_canceled($id){
        $this->load->helper($this->path->purchase_receipt_allocation_engine);
        $post = $this->input->post();
        if($post!= null){
            Purchase_Receipt_Allocation_Engine::submit($id,'purchase_receipt_allocation_canceled',$post);
        }
    }
    
}

?>