<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_Receipt_Allocation_old extends MY_Controller {
    
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
        $this->title_icon = App_Icon::info();
        
    }
    
    public function index()
    {           
        
        
    }
    
    public function add(){
        $this->load->helper($this->path->purchase_receipt_allocation_engine);
        $post = $this->input->post();
        $this->edit('','Add');
        
    }
    
    public function edit($id="",$method="Edit"){
        $this->load->helper($this->path->purchase_receipt_allocation_engine);
        $post = $this->input->post();
        if($post != null){
            $post = json_decode($post,TRUE);
            $data = $post;
            $ajax_post = false;                  
            $result = null;
            $cont = true;
            if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
            
            if($method == 'Add') $data['purchase_receipt_allocation']['id'] = "";
            else if ($method == 'Edit') $data['purchase_receipt_allocation']['id'] = $id;
            else $cont = false;
            
            if(strlen($id)=== 0 && $method === 'Edit') $cont = false;
            
            if($cont){
                $result = Purchase_Receipt_Allocation_Engine::save($data);
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
        $this->view($id,$method);
    }
    
    public function view($id="",$method="View"){

        
        
        
    }
    
    
    public function ajax_search($method=""){
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'purchase_receipt_get':
                $db = new DB();
                $q = '
                    select *
                    from purchase_receipt
                    where id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                $result = $rs;
                break;
            case 'detail_supplier_search':
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $supplier_id = '';
                $q = '
                    select t1.id id
                    , t1.name text
                    from supplier t1
                    where t1.status > 0
                        and t1.code like '.$lookup_str.'
                        and t1.supplier_status = "A"
                ';
                $rs = $db->query_array($q);
                $result = $rs;
                break;
            case 'detail_supplier_get':
                $db = new DB();
                $q = '
                    select * from supplier
                    where id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0) $rs = $rs[0];
                $result = $rs;
                break;
            
            case 'detail_purchase_receipt_search':
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $supplier_id = '';
                if(isset($data['extra_param']['supplier_id'])){
                    $supplier_id = $data['extra_param']['supplier_id'];
                }
                $q = '
                    select t1.id id
                    , concat(t1.code," Total: <strong>",format(t1.amount,2),"</strong> Outstanding Amount: <strong>",format(t1.amount - t1.allocated_amount,2),"</strong>" ) text
                    from purchase_receipt t1
                    where t1.purchase_receipt_status = "I"
                        and t1.code like '.$lookup_str.'
                        and t1.amount - t1.allocated_amount >0
                        and t1.supplier_id = '.$db->escape($supplier_id).'
                ';
                $rs = $db->query_array($q);
                $result = $rs;
                break;
            case 'detail_purchase_receipt_get':
                $db = new DB();
                $q = '
                    select t1.id, t1.code, format(t1.amount ,2) amount
                        , format((t1.amount-t1.allocated_amount),2) outstanding_amount
                        , case when t1.payment_type_id
                                in(2,3) then concat(t2.code," <strong>My Bank Acc:</strong> ",coalesce(t1.bank_acc,"")," <strong>Supplier\'s Bank Acc: </strong>",coalesce(t1.bank_acc_supplier,"")) 
                            else concat(t2.code) end payment_type_name
                    from purchase_receipt t1
                        inner join payment_type t2 on t1.payment_type_id = t2.id
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0) $rs = $rs[0];
                $result = $rs;
                break;
            
            case 'detail_purchase_invoice_search':
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $supplier_id = '';
                if(isset($data['extra_param']['supplier_id'])){
                    $supplier_id = $data['extra_param']['supplier_id'];
                }
                $q = '
                    select t1.id id
                    , concat(t1.code," <span class=\"pull-right\">Grand Total('.Tools::currency_get().'): <strong>",format(t1.grand_total,2),"</strong> Outstanding('.Tools::currency_get().'): <strong>",format(purchase_invoice_outstanding_amount_get(t1.id),2),"</strong></span>" ) text
                    from purchase_invoice t1
                    where t1.purchase_invoice_status = "I"
                        and t1.code like '.$lookup_str.'
                        and purchase_invoice_outstanding_amount_get(t1.id)>0
                        and t1.supplier_id = '.$db->escape($supplier_id).'
                ';
                $rs = $db->query_array($q);
                $result = $rs;
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
                if(count($rs)>0) $rs = $rs[0];
                $result = $rs;
                break;
            case 'purchase_receipt_allocation_ajax_get':
                $db = new DB();
                $result = null;
                $q = '
                    select t1.id, t1.purchase_receipt_id
                        , t1.purchase_invoice_id, t3.code purchase_invoice_code, t3.grand_total purchase_invoice_amount
                        , t1.allocated_amount
                        , t1.purchase_receipt_id, t2.code purchase_receipt_code, t2.amount purchase_receipt_amount
                        , t1.purchase_receipt_allocation_status
                        , t1.cancellation_reason
                        , t1.notes
                        ,case t1.purchase_receipt_allocation_status
                            when "I" then "INVOICED"
                            when "X" then "CANCELED"
                        end purchase_receipt_allocation_status_name
                    from purchase_receipt_allocation t1
                        inner join purchase_receipt t2 on t1.purchase_receipt_id = t2.id
                        inner join purchase_invoice t3 on t1.purchase_invoice_id = t3.id
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $rs[0]['allocated_amount']  = Tools::thousand_separator($rs[0]['allocated_amount'],2,true);
                    $rs[0]['purchase_receipt_amount']  = Tools::thousand_separator($rs[0]['purchase_receipt_amount'],2,true);
                    $rs[0]['purchase_invoice_amount']  = Tools::thousand_separator($rs[0]['purchase_invoice_amount'],2,true);
                    $result = $rs[0];
                }
                break;
            
        }
        
        echo json_encode($result);
    }
    
    public function data_support($method=""){
        //this function only used for urgently data retrieve
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'purchase_receipt_allocation_outstanding_amount_get':
                get_instance()->load->helper('purchase_receipt_allocation/purchase_receipt_allocation_engine');
                $result = Purchase_Receipt_Allocation_Engine::purchase_receipt_allocation_outstanding_amount_get($data);
                break;
            
        }
        
        echo json_encode($result);
    }
    
}

?>