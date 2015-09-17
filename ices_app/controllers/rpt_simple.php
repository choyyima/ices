<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rpt_Simple extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Simple Report');
        get_instance()->load->helper('rpt_simple/rpt_simple_engine');
        $this->path = Rpt_Simple_Engine::path_get();
        $this->title_icon = App_Icon::report();
        
    }
    
    public function index($module_name='',$module_condition=''){
        
        $this->load->helper($this->path->rpt_simple_engine);
        $this->load->helper($this->path->rpt_simple_renderer);
        $this->load->helper($this->path->rpt_simple_data_support);
        
        $app = new App();    
        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,'rpt_simple');
        $app->set_content_header($this->title,$this->title_icon,'');
        $row = $app->engine->div_add()->div_set('class','row')->div_set('id','rpt_simple');            
        $form = $row->form_add()->form_set('title',Lang::get('Report Simple'))->form_set('span','12');
        Rpt_Simple_Renderer::rpt_simple_render($app,$form,array("id"=>''),$this->path,'view');
        
        if(Rpt_Simple_Data_Support::module_condition_exists($module_name, $module_condition)){
            //notification use this section to generate report
            $module = Rpt_Simple_Data_Support::module_get($module_name);
            $module_name_text = $module['name']['label'];
            $module_condition_text = Rpt_Simple_Data_Support::module_condition_get($module_name,$module_condition)['label'];
            $module_condition_list = array();
            foreach($module['condition'] as $condition_idx=>$condition){
                $module_condition_list[] = array('id'=>$condition['val'],'text'=>$condition['label']);            
            }
            $js=''
                .'$("#rpt_simple_module_name").select2("data",{id:"'.$module_name.'",text:"'.$module_name_text.'"});'
                .'$("#rpt_simple_module_condition").select2({allowClear:true,data:'.  json_encode($module_condition_list).'});'
                .'$("#rpt_simple_module_condition").select2("data",{id:"'.$module_condition.'",text:"'.$module_condition_text.'"}).change();'
            .'';
            $app->js_set($js);
        }

        $app->render();
        
    }
    
    public function ajax_search($method){
        
    }
    
    public function data_support($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_simple/rpt_simple_engine');
        get_instance()->load->helper('rpt_simple/rpt_simple_data_support');
        get_instance()->load->helper('rpt_simple/rpt_simple_renderer');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        
        switch($method){
            case 'input_select_module_name_get':
                $response = array();
                $module_list = Rpt_Simple_Data_Support::module_list_get();
                for($i = 0;$i<count($module_list);$i++){
                    $temp_response = array(
                        'id'=>$module_list[$i]['name']['val'],
                        'text'=>$module_list[$i]['name']['label'],
                        'condition'=>array()
                    );
                    for($j = 0;$j<count($module_list[$i]['condition']);$j++){
                        if( 
                            Security_Engine::get_controller_permission(
                                User_Info::get()['user_id'], 
                                'rpt_simple', 
                                $module_list[$i]['name']['val'].'_'.$module_list[$i]['condition'][$j]['val']
                            )
                        ){
                            $temp_response['condition'][] = array(
                                'id'=>$module_list[$i]['condition'][$j]['val'],
                                'text'=>$module_list[$i]['condition'][$j]['label'],
                            );
                        }
                    }
                    if(count($temp_response['condition'])>0){
                        $response[] = $temp_response;
                    }
                }
                break;
            case 'report_table_get':
                $response = array();
                $module_name = isset($data['module_name'])?Tools::_str($data['module_name']):'';
                $module_condition = isset($data['module_condition'])?Tools::_str($data['module_condition']):'';
                if(Rpt_Simple_Data_Support::module_condition_exists($module_name, $module_condition) 
                    && Security_Engine::get_controller_permission(User_Info::get()['user_id'], 'rpt_simple', $module_name.'_'.$module_condition)
                ){                    
                    $response = Rpt_Simple_Renderer::report_table_render($module_name, $module_condition);
                }
                break;
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function download_excel($module_name='',$module_condition=''){
        get_instance()->load->helper('rpt_simple/rpt_simple_data_support');
        get_instance()->load->helper('rpt_simple/rpt_simple_engine');
        if(Rpt_Simple_Data_Support::module_condition_exists($module_name, $module_condition)
            && Security_Engine::get_controller_permission(
                User_Info::get()['user_id'], 
                'rpt_simple', 
                Tools::_str($module_name).'_'.Tools::_str($module_condition)
            )
        ){
            $cfg = array('thousand_separator'=>false);
            $rpt_data = eval('return Rpt_Simple_Data_Support::report_table_'.$module_name.'_'.$module_condition.'($cfg);');
            $column = $rpt_data['column'];
            $temp_data = array();
            for($i = 0;$i<count($rpt_data['data']);$i++){
                $temp_row = array();
                for($j = 0;$j<count($column);$j++){
                    if(isset($rpt_data['data'][$i][$column[$j]['name']])){
                        
                        $temp_row[] = SI::html_untag($rpt_data['data'][$i][$column[$j]['name']]);
                    }
                }           
                $temp_data[] = $temp_row;
            }

            $rpt_data['data'] = $temp_data;
            
            $excel = new Excel();
            
            $title = Rpt_Simple_Data_Support::module_get($module_name)['name']['label'].
                ' - '.Rpt_Simple_Data_Support::module_condition_get($module_name,$module_condition)['label'];
            $excel::file_info_set('title',$title);
            
            $data_arr= array();
            $data_arr[] = array($title);
            $excel::array_to_text($data_arr,'A1',0);
            
            $col_header = array();
            foreach($column as $idx=>$col){
                $col_header[] = array(
                    'val'=>$col['label'],
                    'style'=>array(
                        'font'=>array(),
                        'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)),
                );
            }
            $data_arr['column_header'] = $col_header;
            $data_arr['data'] = $rpt_data['data'];
            $excel::array_to_text_smart($data_arr,'A3',0);
            
            foreach($column as $idx=>$col){
                if($idx === 0){
                    $excel->column_width_set(0,5);
                }
                else{
                    $excel::$objPHPExcel->getActiveSheet()
                        ->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($idx))
                        ->setAutoSize(true);
                }
            }
            
            $excel::save($title.' '.(string)Date('Ymd His'));
            
        }
    }
    
}

?>