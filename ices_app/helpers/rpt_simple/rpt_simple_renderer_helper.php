<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rpt_Simple_Renderer {

    public static function modal_rpt_simple_render($app,$modal){
        $modal->header_set(array('title'=>'System Investigation Report','icon'=>App_Icon::rpt_simple()));
        $components = self::rpt_simple_components_render($app, $modal,true);
    }

    public static function rpt_simple_render($app,$form,$data,$path,$method){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_simple/rpt_simple_engine');
        $path = Rpt_Simple_Engine::path_get();
        $id = $data['id'];
        $components = self::rpt_simple_components_render($app, $form,false);
        $back_href = $path->index;

        $btn_group = $form->form_group_add()->attrib_set(array('style'=>'height:34px'))->button_group_add()
            ->button_group_set('icon',App_Icon::btn_save())
            ->button_group_set('value','Download')
            ->button_group_set('div_class','btn-group pull-right')
            ->button_group_set('item_list_add',array('id'=>'save_excel','label'=>'Excel','class'=>'fa fa-file-excel-o'))
            ;
        
        

        
        
        $js = '
            <script>
                $("#rpt_simple_method").val("'.$method.'");
                $("#rpt_simple_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                rpt_simple_init();
                rpt_simple_bind_event();
                rpt_simple_components_prepare(); 
        ';
        $app->js_set($js);
        //</editor-fold>
    }

    public static function rpt_simple_components_render($app,$form,$is_modal){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_simple/rpt_simple_engine');
        $path = Rpt_Simple_Engine::path_get();            
        $components = array();
        $db = new DB();

        $id_prefix = 'rpt_simple';

        $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                ->input_set('hide',true)
                ->input_set('value','')
                ;

        $reference_detail = array(

        );

        $form->input_add()->input_set('id',$id_prefix.'_method')
                ->input_set('hide',true)
                ->input_set('value','')
                ;            
        $db = new DB();
        $store_list = array();
        $q = 'select id id, name data from store where status>0';            
        $store_list = $db->query_array($q);

        $form->input_select_add()
            ->input_select_set('label',Lang::get('Module Name'))
            ->input_select_set('icon',App_Icon::info())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_module_name')
            ->input_select_set('data_add',array())
            ->input_select_set('value',array())
            ->input_select_set('ajax_url',$path->data_support.'input_select_module_name_get/')
            ->input_select_set('disable_all',true)
             ->input_select_set('hide_all',true)

        ;

        $form->input_select_add()
            ->input_select_set('label',Lang::get('Module Condition'))
            ->input_select_set('icon',App_Icon::info())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_module_condition')
            ->input_select_set('data_add',array())
            ->input_select_set('value',array())
            ->input_select_set('ajax_url',$path->data_support.'input_select_module_action_get/')
            ->input_select_set('disable_all',true)
             ->input_select_set('hide_all',true)

        ;

        $form->div_add()
            ->div_set('id',$id_prefix.'_report_table')
            ->div_set('class','form-group')
        ;
        
        
        $param = array(
            'ajax_url'=>$path->index.'ajax_search/'
            ,'index_url'=>$path->index
            ,'detail_tab'=>'#'.$id_prefix
            ,'view_url'=>$path->index.'view/'
            ,'window_scroll'=>'body'
            ,'data_support_url'=>$path->index.'data_support/'
            ,'common_ajax_listener'=>get_instance()->config->base_url().'common_ajax_listener/'
            ,'component_prefix_id'=>$id_prefix
        );
        


        if($is_modal){
            $param['detail_tab'] = '#modal_'.$id_prefix.' .modal-body';
            $param['view_url'] = '';
            $param['window_scroll'] = '#modal_'.$id_prefix;
        }

        $js = get_instance()->load->view('rpt_simple/'.$id_prefix.'_basic_function_js',$param,TRUE);
        $app->js_set($js);
        return $components;
        //</editor-fold>

    }

    public static function report_table_render($module_name, $module_condition){
        get_instance()->load->helper('rpt_simple/rpt_simple_data_support');
        $result = '';
        
        $rpt_data = eval('return Rpt_Simple_Data_Support::report_table_'.$module_name.'_'.$module_condition.'();');
        
        $app = new App();
        
        $table = $app->engine->table_add();
        $table->table_set('class','table fixed-table')
            ->table_set('id','report_table')
            ->table_set('base href',isset($rpt_data['info']['base_href'])?$rpt_data['info']['base_href']:'')
            ->table_set('base href attr',array('target'=>'_blank'))
            ->table_set('data key','id')
            ->table_set('is_data_table',true)
        ;
        foreach($rpt_data['column'] as $column_idx=>$column){
            $table->table_set('columns',$column);
        }
        
        $limit = 100;
        if(count($rpt_data['data'])>$limit) array_splice($rpt_data['data'],$limit);
        
        $table->table_set('data',$rpt_data['data']);
        $result .=  $table->html_render_first();
        
        return $result;
    }
    

}
    
?>