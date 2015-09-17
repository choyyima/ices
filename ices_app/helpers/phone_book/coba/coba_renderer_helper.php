<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Coba_Renderer {

    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper(ICES_Engine::$app['app_base_dir'].'coba/coba_engine');
        //</editor-fold>
    }
    
    public static function modal_coba_render($app,$modal){
        //<editor-fold defaultstate="collapsed">
        $modal->header_set(array('title'=>Lang::get('Security Controller'),'icon'=>'fa fa-cogs'));
        $modal->width_set('95%');
        $components = self::coba_components_render($app, $modal,true);
        //</editor-fold>
    }

    public static function coba_render($app,$form,$data,$path,$method){
        //<editor-fold defaultstate="collapsed">
        $path = Coba_Engine::path_get();
        
        $id_prefix = Coba_Engine::$prefix_id;
        
        $id = $data['id'];
        $components = self::coba_components_render($app, $form,false);
        $back_href = $path->index;

        $form->button_add()->button_set('value','BACK')
            ->button_set('icon',App_Icon::btn_back())
            ->button_set('href',$back_href)
            ->button_set('class','btn btn-default')
        ;

        $js = '
            <script>
                $("#'.$id_prefix.'_method").val("'.$method.'");
                $("#'.$id_prefix.'_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                '.$id_prefix.'_init();
                '.$id_prefix.'_bind_event();
                '.$id_prefix.'_components_prepare(); 
        ';
        $app->js_set($js);
        //</editor-fold>
    }

    public static function coba_components_render($app,$form,$is_modal){
        $path = Coba_Engine::path_get();            
        $components = array();
        $db = new DB();

        $id_prefix = Coba_Engine::$prefix_id;

        $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
            ->input_set('hide',true)
            ->input_set('value','')
        ;

        $form->input_add()->input_set('id',$id_prefix.'_method')
            ->input_set('hide',true)
            ->input_set('value','')
        ;            

        $app_list = array();
        $t_app_list = SI::type_list_get('ICES_Engine','$app_list');
        foreach($t_app_list as $idx=>$row){
            $app_list[] = array('id'=>$row['val'],'text'=>$row['text'],'barcode'=>'1123');
        }
        
        $form->input_select_add()
            ->input_select_set('label',Lang::get('APP Name'))
            ->input_select_set('icon',App_Icon::info())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_app_name')
            ->input_select_set('data_add',$app_list)
            ->input_select_set('value',array())
            ->input_select_set('disable_all',true)
            ->input_select_set('hide_all',true)
            ->input_select_set('allow_empty',false)
        ;
        
        $form->input_add()->input_set('label',Lang::get('Name'))
            ->input_set('id',$id_prefix.'_controller_name')
            ->input_set('icon',APP_ICON::info())
            ->input_set('hide_all',true)
            ->input_set('disable_all',true)
        ;
        
        $form->input_add()->input_set('label',Lang::get('Method'))
            ->input_set('id',$id_prefix.'_method_name')
            ->input_set('icon',APP_ICON::info())
            ->input_set('hide_all',true)
            ->input_set('disable_all',true)
        ;
        
        $components[$id_prefix.'_status'] = $form->input_select_add()
            ->input_select_set('label','Status')
            ->input_select_set('icon','fa fa-info')
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_coba_status')
            ->input_select_set('data_add',array())
            ->input_select_set('value',array())
            ->input_select_set('hide_all',true)
            ->input_select_set('is_module_status',true)
            ;
        
        $form->hr_add()->hr_set('class','');

        $form->button_add()->button_set('value','Submit')
            ->button_set('id',$id_prefix.'_btn_submit')
            ->button_set('icon',App_Icon::detail_btn_save())
        ;
        
        $param = array(
            'ajax_url'=>$path->index.'ajax_search/'
            ,'index_url'=>$path->index
            ,'detail_tab'=>'#detail_tab'
            ,'view_url'=>$path->index.'view/'
            ,'window_scroll'=>'body'
            ,'data_support_url'=>$path->index.'data_support/'
            ,'common_ajax_listener'=>ICES_Engine::$app['app_base_url'].'common_ajax_listener/'
            ,'component_prefix_id'=>$id_prefix
        );

        if($is_modal){
            $param['detail_tab'] = '#modal_coba .modal-body';
            $param['view_url'] = '';
            $param['window_scroll'] = '#modal_coba';
        }

        $js = get_instance()->load->view(ICES_Engine::$app['app_base_dir'].'coba/coba_basic_function_js',$param,TRUE);
        $app->js_set($js);

        return $components;

    }

    public static function coba_status_log_render($app,$form,$data,$path){
        $config=array(
            'module_name'=>'coba',
            'module_engine'=>'coba_engine',
            'id'=>$data['id']
        );
        SI::form_renderer()->status_log_tab_render($form, $config);
    }
    
}
    
?>