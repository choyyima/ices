<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rpt_Product extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Report Product');
        get_instance()->load->helper('rpt_product/rpt_product_engine');
        $this->path = Rpt_Product_Engine::path_get();
        $this->title_icon = App_Icon::report();
        
    }
    
    public function index(){
        
        $this->load->helper($this->path->rpt_product_engine);
        $this->load->helper($this->path->rpt_product_renderer);
        $this->load->helper($this->path->rpt_product_data_support);
        
        $app = new App();    
        $app->set_title($this->title);
        $app->set_menu('collapsed',false);
        $app->set_breadcrumb($this->title,'rpt_product');
        $app->set_content_header($this->title,$this->title_icon,'');
        $row = $app->engine->div_add()->div_set('class','row')->div_set('id','rpt_product');            
        $form = $row->form_add()->form_set('title',Lang::get('Report Product'))->form_set('span','12');
        Rpt_Product_Renderer::rpt_product_render($app,$form,array("id"=>''),$this->path,'view');
        
        $app->render();
        
    }
    
    public function form_render($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_product/rpt_product_engine');
        get_instance()->load->helper('rpt_product/rpt_product_renderer');
        $data = json_decode($this->input->post(), true);
        $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;
        $submethod = Tools::_str($submethod);
        $method = Tools::_str($method);
        switch($method){
             case 'report_get':
                $module_name = isset($data['module_name'])?Tools::_str($data['module_name']):'';
                if(SI::type_match('rpt_product_engine',$module_name)){
                    $response = Rpt_Product_Renderer::report_render($module_name);
                }
                break;
                
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        
        //</editor-fold>
    }
    
    public function ajax_search($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_product/rpt_product_engine');
        $data = json_decode($this->input->post(), true);
        $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;
        $submethod = Tools::_str($submethod);
        $method = Tools::_str($method);
        switch($method){            
            case 'rpt_product':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$lookup_data.'%');                
                $config = array(
                    'additional_filter'=>array(
                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select cn.*
                                from rpt_product cn   
                                where cn.status>0
                        ',
                        'where'=>'
                            and (cn.code like '.$lookup_str.'
                            )
                        ',
                        'group'=>'
                            )tfinal
                        ',
                        'order'=>'order by code desc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for($i = 0;$i<count($temp_result['data']);$i++){
                    $temp_result['data'][$i]['rpt_product_status_text'] =
                        SI::get_status_attr(
                            SI::status_get('Credit_Note_Engine', 
                                $temp_result['data'][$i]['rpt_product_status']
                            )['label']
                        );
                    $temp_result['data'][$i]['total_claimed_amount'] = Tools::thousand_separator(
                        $temp_result['data'][$i]['total_claimed_amount']
                    );
                    $temp_result['data'][$i]['total_paid_amount'] = Tools::thousand_separator(
                        $temp_result['data'][$i]['total_paid_amount']
                    );
                }
                $result = $temp_result;
                //</editor-fold>
                break;
            
            case 'product_stock_search':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('rpt_product/rpt_product_data_support');
                $temp_result = Rpt_Product_Data_Support::product_stock_search($data);
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
        get_instance()->load->helper('rpt_product/rpt_product_engine');
        get_instance()->load->helper('rpt_product/rpt_product_data_support');
        get_instance()->load->helper('rpt_product/rpt_product_renderer');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        
        switch($method){
           
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function download_excel($module_name=''){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_product/rpt_product_data_support');
        get_instance()->load->helper('rpt_product/rpt_product_engine');
        if(SI::type_match('rpt_product_engine',$module_name)){
            
            $excel = new Excel();
            
            $title = SI::type_get('rpt_product_engine',$module_name)['label'];
            $excel::file_info_set('title',$title);
            $excel::array_to_text(array($title),'A1',0);
            
            $module = SI::type_get('rpt_product_engine', $module_name);
            if($module['type'] === 'table'){
                $col_header = array();
                foreach($module['tbl_col'] as $i => $col){
                    $col_header[] = array(
                        'val'=>SI::html_untag($col['label']),
                        'style'=>array(
                            'font'=>array(),
                            'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                        ),
                    );
                                
                }
                
                $search_param = array('data'=>'','records_page'=>'99999999');
                $ajax_tbl_data = eval('return Rpt_Product_Data_Support::'.$module_name.'_search($search_param);');

                $data_arr= array();
                $t_data = $ajax_tbl_data['data'];
                unset($ajax_tbl_data);
                
                if(count($t_data)>0){
                    $f_data = array();
                    for($i = 0;$i<count($t_data);$i++){
                        $t_row = array();
                        foreach($module['tbl_col'] as $col_i=>$col){
                            $t_row[] = isset($t_data[$i][$col['name']])?SI::html_untag($t_data[$i][$col['name']]):'';
                        }
                        $f_data[] = $t_row;
                    }
                    $excel::array_to_text_smart(array('column_header'=>$col_header,'data'=>$f_data),'A4',0);
                }
            }
            $excel::save($title.' '.(string)Date('Ymd His'));
            
        }
        //</editor-fold>
    }
    
}

?>