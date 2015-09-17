<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Deposit_Allocation extends MY_Controller {
    
    private $title='Customer Deposit';
    private $title_icon = '';
    private $path = array(
        'index'=>''
        ,'customer_deposit_allocation_engine'=>''
        ,'ajax_search'=>''
        
    );
    
    function __construct(){
        parent::__construct();
        $this->path = json_decode(json_encode($this->path));
        $this->path->index=  '';
        $this->path->customer_deposit_allocation_engine=  'customer_deposit_allocation/customer_deposit_allocation_engine';
        $this->path->ajax_search=  $this->path->index.'ajax_search/';       
        $this->title_icon = App_Icon::info();
        
    }
    
    public function index()
    {           
        
        
    }
    
    public function add(){
        $this->load->helper($this->path->customer_deposit_allocation_engine);
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
                $lookup_val = isset($data['data'])?Tools::_str($data['data']):'';
                $customer_id = isset($data['extra_param']['customer_id'])?
                    Tools::_str($data['extra_param']['customer_id']):'';
                $cd_type = isset($data['extra_param']['customer_deposit_type'])?
                    Tools::_str($data['extra_param']['customer_deposit_type']):'';
                $search_param = array('customer_id'=>$customer_id,'lookup_val'=>$lookup_val);
                switch($cd_type){
                    case 'delivery_order_final_confirmation':
                        //<editor-fold defaultstate="collapsed">
                        get_instance()->load->helper('sales_pos/sales_pos_data_support');                        
                        $sales_invoice_raw = Sales_Pos_Data_Support::sales_invoice_outstanding_amount_search($search_param);
                        foreach($sales_invoice_raw as $idx=>$si){
                            $response[] = array(
                                'id'=>$si['id'],
                                'text'=>$si['code'],
                                'reference_type'=>'sales_invoice'
                            );
                        }
                        
                        get_instance()->load->helper('customer_bill/customer_bill_data_support');
                        $customer_bill_raw = Customer_Bill_Data_Support::customer_bill_outstanding_amount_search($search_param,array('customer_bill_type'=>$cd_type));
                        foreach($customer_bill_raw as $idx=>$cb){
                            $response[] = array(
                                'id'=>$cb['id'],
                                'text'=>$cb['code'],
                                'reference_type'=>'customer_bill'
                            );
                        }
                        //</editor-fold>
                        break;
                    case 'refill_work_order':
                        get_instance()->load->helper('refill_invoice/refill_invoice_data_support');
                        $refill_invoice_raw = Refill_Invoice_Data_Support::refill_invoice_outstanding_amount_search($search_param);
                        foreach($refill_invoice_raw as $idx=>$ri){
                            $response[] = array(
                                'id'=>$ri['id'],
                                'text'=>$ri['code'],
                                'reference_type'=>'refill_invoice'
                            );
                        }
                        break;
                }
                
                
                
                
                //</editor-fold>
                break;
            case 'input_select_customer_deposit_search':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_val = $data['data'];
                $customer_id = isset($data['extra_param']['customer_id'])?
                    Tools::_str($data['extra_param']['customer_id']):'';
                $reference_id = isset($data['extra_param']['reference_id'])?
                    Tools::_str($data['extra_param']['reference_id']):'';
                $reference_type = isset($data['extra_param']['reference_type'])?
                    Tools::_str($data['extra_param']['reference_type']):'';
                $search_param = array(
                    'customer_id'=>$customer_id,
                    'lookup_val'=>$lookup_val
                );
                $cd_type = '';
                switch($reference_type){
                    case 'sales_invoice':
                        $cd_type='delivery_order_final_confirmation';
                        break;
                    case 'refill_invoice':
                        $cd_type='refill_work_order';
                        break;
                    case 'customer_bill':
                        get_instance()->load->helper('customer_bill/customer_bill_data_support');
                        $cb = Customer_Bill_Data_Support::customer_bill_get($reference_id);
                        if(count($cb)>0){
                            switch($cb['customer_bill_type']){
                                case 'delivery_order_final_confirmation':
                                    $cd_type = 'delivery_order_final_confirmation';
                                    break;
                            }
                        }
                        break;
                }
                
                get_instance()->load->helper('customer_deposit/customer_deposit_data_support');
                $cd_raw = Customer_Deposit_Data_Support::customer_deposit_outstanding_amount_search($search_param,array('customer_deposit_type'=>$cd_type));
                foreach($cd_raw as $idx=>$cd){
                    $response[] = array(
                        'id'=>$cd['id'],
                        'text'=>SI::html_tag('strong',$cd['code']),
                        'reference_type'=>$cd['customer_deposit_type'],
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
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_engine');
        get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_data_support');
        get_instance()->load->helper('customer_deposit/customer_deposit_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'input_select_customer_deposit_detail_get':
                $customer_deposit_id = isset($data['data'])?Tools::_str($data['data']):'';
                $customer_deposit_detail = Customer_Deposit_Data_Support::customer_deposit_get($customer_deposit_id);
                $customer_deposit_detail['amount'] = Tools::thousand_separator($customer_deposit_detail['amount']);
                $customer_deposit_detail['outstanding_amount'] = Tools::thousand_separator($customer_deposit_detail['outstanding_amount']);
                $customer_deposit_detail['customer_deposit_date'] = Tools::_date($customer_deposit_detail['customer_deposit_date'],'F d, Y H:i:s');
                $response = $customer_deposit_detail;
                break;
            case 'input_select_reference_detail_get':
                $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                $reference_type = isset($data['reference_type'])?Tools::_str($data['reference_type']):'';
                $reference_detail = Customer_Deposit_Allocation_Data_Support::reference_detail_get($reference_type,$reference_id);
                $response['reference_detail'] = $reference_detail;
                break;
            case 'customer_deposit_allocation_get':
                $response =array();
                $db = new DB();
                $cda_id = $data['data'];
                $q = '
                    select t1.*,
                        customer_deposit_allocation_status cda_status,
                        t2.id store_id,
                        t2.code store_code,
                        t2.name store_name
                    from customer_deposit_allocation t1
                        inner join store t2 on t1.store_id = t2.id
                    where t1.id = '.$db->escape($cda_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $cda = $rs[0];                    
                    $reference = array();
                    $customer_deposit = array();
                    $customer = array();
                    
                    $customer_deposit_id = $cda['customer_deposit_id'];
                    $cda_type = $cda['customer_deposit_allocation_type'];
                    
                    $cda['store_text'] = SI::html_tag('strong',$cda['store_code'])
                        .' '.$cda['store_name'];
                    $cda['cda_status_text'] = SI::get_status_attr(
                            SI::status_get('Customer_Deposit_Allocation_Engine',$cda['cda_status'])['label']
                        );
                    $cda['allocated_amount'] = Tools::thousand_separator($cda['allocated_amount'],5);
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Customer_Deposit_Allocation_Engine',
                            $cda['cda_status']
                        );
                    
                    $q = '
                        select t1.*, t1.outstanding_amount, t1.customer_deposit_date
                        from customer_deposit t1
                        where t1.id = '.$db->escape($customer_deposit_id).'
                    ';
                    
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        $customer_deposit = $rs[0];
                        $customer_deposit['customer_deposit_date'] = Tools::_date($customer_deposit['customer_deposit_date'],'F d, Y H:i:s');
                        $customer_deposit['amount'] = Tools::thousand_separator($customer_deposit['amount']);
                        $customer_deposit['outstanding_amount'] = Tools::thousand_separator($customer_deposit['outstanding_amount']);
                    }
                    
                    $q = '';
                    switch($cda_type){
                        case 'sales_invoice':
                            get_instance()->load->helper('sales_pos/sales_pos_data_support');
                            $temp_data = Sales_Pos_Data_Support::sales_invoice_get($cda['sales_invoice_id']);
                            $reference['id'] = $temp_data['id'];
                            $reference['text'] = $temp_data['code'];
                            break;
                        case 'customer_bill':
                            get_instance()->load->helper('customer_bill/customer_bill_data_support');
                            $temp_data = Customer_Bill_Data_Support::customer_bill_get($cda['customer_bill_id']);
                            $reference['id'] = $temp_data['id'];
                            $reference['text'] = $temp_data['code'];
                            break;
                        case 'refill_invoice':
                            get_instance()->load->helper('refill_invoice/refill_invoice_data_support');
                            $temp_data = Refill_Invoice_Data_Support::refill_invoice_get($cda['refill_invoice_id']);
                            $reference['id'] = $temp_data['id'];
                            $reference['text'] = $temp_data['code'];
                            break;
                    }
                    
                    $reference_detail = Customer_Deposit_Allocation_Data_Support::reference_detail_get($cda_type,$reference['id']);
                    
                    $response['cda'] = $cda;
                    $response['reference'] = $reference;
                    $response['reference_detail'] = $reference_detail;
                    $response['customer_deposit'] = $customer_deposit;
                    $response['cda_status_list'] = $next_allowed_status_list;
                    $response['customer'] = $customer;
                }
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    
    public function customer_deposit_allocation_add(){
        $this->load->helper($this->path->customer_deposit_allocation_engine);
        $post = $this->input->post();
        if($post!= null){
            Customer_Deposit_Allocation_Engine::submit('','customer_deposit_allocation_add',$post);
        }
    }
    
    public function customer_deposit_allocation_canceled($id){
        $this->load->helper($this->path->customer_deposit_allocation_engine);
        $post = $this->input->post();
        if($post!= null){
            Customer_Deposit_Allocation_Engine::submit($id,'customer_deposit_allocation_canceled',$post);
        }
    }
    
}

?>