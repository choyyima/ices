<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rpt_Refill_Renderer {

    public static function modal_rpt_refill_render($app,$modal){
        $modal->header_set(array('title'=>'System Investigation Report','icon'=>App_Icon::rpt_refill()));
        $components = self::rpt_refill_components_render($app, $modal,true);
    }

    public static function rpt_refill_render($app,$form,$data,$path,$method){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_refill/rpt_refill_engine');
        $id_prefix = Rpt_Refill_Engine::$prefix_id;
        $path = Rpt_Refill_Engine::path_get();
        $id = $data['id'];
        $components = self::rpt_refill_components_render($app, $form,false);
        $back_href = $path->index;

        $btn_group = $form->form_group_add()->attrib_set(array('style'=>'height:34px'))->button_group_add()
            ->button_group_set('icon',App_Icon::btn_save())
            ->button_group_set('value','Download')
            ->button_group_set('div_class','btn-group pull-right')
            ->button_group_set('item_list_add',array('id'=>$id_prefix.'_save_excel','label'=>'Excel','class'=>'fa fa-file-excel-o'))
            ;
        
        $js = '
            <script>
                $("#rpt_refill_method").val("'.$method.'");
                $("#rpt_refill_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                rpt_refill_init();
                rpt_refill_bind_event();
                rpt_refill_components_prepare(); 
        ';
        $app->js_set($js);
        //</editor-fold>
    }

    public static function rpt_refill_components_render($app,$form,$is_modal){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_refill/rpt_refill_engine');
        $path = Rpt_Refill_Engine::path_get();            
        $components = array();
        $db = new DB();

        $id_prefix = Rpt_Refill_Engine::$prefix_id;

        $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                ->input_set('hide',true)
                ->input_set('value','')
                ;

        $form->input_add()->input_set('id',$id_prefix.'_method')
                ->input_set('hide',true)
                ->input_set('value','')
                ;            
        $db = new DB();
        

        $form->input_select_add()
            ->input_select_set('label',Lang::get('Module Name'))
            ->input_select_set('icon',App_Icon::info())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_module_name')
            ->input_select_set('data_add',array())
            ->input_select_set('value',array())
            ->input_select_set('disable_all',true)
            ->input_select_set('hide_all',true)
            ->input_select_set('ajax_url',$path->data_support.'/input_select_module_name/')

        ;

        $form->div_add()
            ->div_set('id',$id_prefix.'_report_div')
            ->div_set('class','')
        ;
        
        
        $param = array(
            'ajax_url'=>$path->index.'ajax_search/'
            ,'index_url'=>$path->index
            ,'detail_tab'=>'#'.$id_prefix
            ,'view_url'=>$path->index.'view/'
            ,'window_scroll'=>'body'
            ,'form_render_url'=>$path->index.'form_render/'
            ,'data_support_url'=>$path->index.'data_support/'
            ,'common_ajax_listener'=>get_instance()->config->base_url().'common_ajax_listener/'
            ,'component_prefix_id'=>$id_prefix
        );
        


        if($is_modal){
            $param['detail_tab'] = '#modal_'.$id_prefix.' .modal-body';
            $param['view_url'] = '';
            $param['window_scroll'] = '#modal_'.$id_prefix;
        }

        $js = get_instance()->load->view('rpt_refill/'.$id_prefix.'_basic_function_js',$param,TRUE);
        $app->js_set($js);
        return $components;
        //</editor-fold>
    }

    public static function form_render($module_name){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_refill/rpt_refill_data_support');
        get_instance()->load->helper('rpt_refill/rpt_refill_engine');
        
        $result = array('html'=>'','script'=>'');        
        if(method_exists('Rpt_Refill_Renderer', $module_name.'_render')){
            $result = eval('return self::'.$module_name.'_render(false);');
        }
        return $result;
        //</editor-fold>
    }
    
    static function refill_invoice_render($is_modal){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_refill/rpt_refill_engine');
        $result = array('html'=>'','script'=>'');
        $path = Rpt_Refill_Engine::path_get();
        $id_prefix = Rpt_Refill_Engine::$prefix_id;
        
        $app = new App();        
        
        $main_div = $app->engine->div_add();        
        
        $main_div->datetimepicker_add()->datetimepicker_set('label',Lang::get('Start Date'))
            ->datetimepicker_set('id',$id_prefix.'_start_date')
            ->datetimepicker_set('value',Tools::_date(Date('Y-m-01'),'F d, Y H:i')) 
        ;
        
        $main_div->datetimepicker_add()->datetimepicker_set('label',Lang::get('End Date'))
            ->datetimepicker_set('id',$id_prefix.'_end_date')
            ->datetimepicker_set('value',Tools::_date(Date('Y-m-t 23:59:59'),'F d, Y H:i')) 
        ;
        
        $result['html'] = $main_div->render();
        $result['script'] = $main_div->scripts_get();
        
        $param = array(
            'ajax_url'=>$path->index.'ajax_search/'
            ,'index_url'=>$path->index
            ,'detail_tab'=>'#'.$id_prefix
            ,'view_url'=>$path->index.'view/'
            ,'window_scroll'=>'body'
            ,'form_render_url'=>$path->index.'form_render/'
            ,'data_support_url'=>$path->index.'data_support/'
            ,'common_ajax_listener'=>get_instance()->config->base_url().'common_ajax_listener/'
            ,'component_prefix_id'=>$id_prefix
        );
        
        
        $js = str_replace(array('<script>','</script>'),'',get_instance()->load->view('rpt_refill/refill_invoice_js',$param,true));
        $result['script'].=$js;
        
        
        
        return $result;
        //</editor-fold>
    }

}
    
?>