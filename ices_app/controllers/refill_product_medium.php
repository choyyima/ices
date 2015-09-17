<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Product_Medium extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get(array('Refill','Product Medium'));
        get_instance()->load->helper('Refill_Product_Medium/Refill_Product_Medium_Engine');
        $this->path = Refill_Product_Medium_Engine::path_get();
        $this->title_icon = App_Icon::refill_product_medium();
        
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
        $form = $row->form_add()->form_set('title',Lang::get(array('Refill','Product Medium','List')))->form_set('span','12');
        $form->form_group_add();
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Product Medium')))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"name","label"=>Lang::get("Name"),"data_type"=>"text"),
            array("name"=>"refill_product_medium_status","label"=>Lang::get("Status"),"data_type"=>"text"),
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/refill_product_medium')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'reference_type_filter','field'=>'reference_type')
                    ))
                ;        

        $app->render();
    }
    
    
    public function add(){        
        $this->load->helper($this->path->refill_product_medium_engine);
        
        $post = $this->input->post();      
        $default_status = SI::status_default_status_get('Refill_Product_Medium_Engine')['val'];
        $user_id = User_Info::get()['user_id'];
        $db = new DB();
        $q = '
            select t1.id
            from refill_product_medium t1
                inner join working_order_info t2 on t1.id = t2.refill_product_medium_id
            where t1.refill_product_medium_status === '.$db->escape($default_status).'
                and t2.creator_id = '.$db->escape($user_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            redirect(get_instance()->config->base_url().'refill_product_medium/view'.$rs[0]);
        }
        
        $this->view('','add');
        
    }
    
    
    public function view($id="",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->refill_product_medium_engine);
        $this->load->helper($this->path->refill_product_medium_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Refill_Product_Medium_Engine::refill_product_medium_exists($id)){
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
            $app->set_breadcrumb($this->title,'refill_product_medium');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','refill_product_medium');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Refill_Product_Medium_Renderer::Refill_Product_Medium_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            
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
            
            case 'refill_product_medium':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');                
                $config = array(
                    'additional_filter'=>array(

                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct t1.*
                                from refill_product_medium t1                    
                                where t1.status>0
                        ',
                        'where'=>'
                            and (t1.code like '.$lookup_str.'
                                or t1.name like '.$lookup_str.'
                            )
                        ',
                        'group'=>'
                            )tfinal
                        ',
                        'order'=>'order by code asc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for($i = 0;$i<count($temp_result['data']);$i++){
                    $temp_result['data'][$i]['refill_product_medium_status'] =
                        SI::get_status_attr(
                            SI::status_get('Refill_Product_Medium_Engine', 
                                $temp_result['data'][$i]['refill_product_medium_status']
                            )['label']
                        );
                    
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
        //<editor-fold defaultstate="collapsed">
        //this function only used for urgently data retrieve
        get_instance()->load->helper('refill_product_medium/refill_product_medium_engine');
        get_instance()->load->helper('refill_product_medium/refill_product_medium_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'refill_product_medium_get':
                $response =array();
                $db = new DB();
                $refill_product_medium_id = Tools::_str($data['data']);
                $q = '
                    select t1.*
                        
                    from refill_product_medium t1
                        
                    where t1.id = '.$db->escape($refill_product_medium_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $refill_product_medium = $rs[0];                                        
                    
                    $refill_product_medium['refill_product_medium_status_text'] = SI::get_status_attr(
                            SI::status_get('Refill_Product_Medium_Engine',$refill_product_medium['refill_product_medium_status'])['label']
                        );
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Refill_Product_Medium_Engine',
                            $refill_product_medium['refill_product_medium_status']
                        );
                    
                    $response['refill_product_medium'] = $refill_product_medium;
                    $response['refill_product_medium_status_list'] = $next_allowed_status_list;
                }
                
                break;
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function refill_product_medium_add(){
        
        $this->load->helper($this->path->refill_product_medium_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'refill_product_medium_add',
                'primary_data_key'=>'refill_product_medium','data_post'=>$post);            
            SI::data_submit()->submit('refill_product_medium_engine',$param);

        }
        
    }
    
    public function refill_product_medium_active($id){
        
        $this->load->helper($this->path->refill_product_medium_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'refill_product_medium_active',
                    'primary_data_key'=>'refill_product_medium','data_post'=>$post);            
            SI::data_submit()->submit('refill_product_medium_engine',$param);

        }
        
        
    }
    
    public function refill_product_medium_inactive($id){
        
        $this->load->helper($this->path->refill_product_medium_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'refill_product_medium_inactive',
                    'primary_data_key'=>'refill_product_medium','data_post'=>$post);            
            SI::data_submit()->submit('refill_product_medium_engine',$param);
        }
        
        
    }
    
    
}

?>