<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class U_Group_Renderer {

    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper(ICES_Engine::$app['app_base_dir'].'u_group/u_group_engine');
        //</editor-fold>
    }
    
    public static function modal_u_group_render($app,$modal){
        //<editor-fold defaultstate="collapsed">
        $modal->header_set(array('title'=>Lang::get('User Group'),'icon'=>'fa fa-cogs'));
        $modal->width_set('95%');
        self::u_group_components_render($app, $modal,true);
        //</editor-fold>
    }

    public static function u_group_render($app,$form,$data,$path,$method){
        //<editor-fold defaultstate="collapsed">
        $path = U_Group_Engine::path_get();
        
        $id_prefix = U_Group_Engine::$prefix_id;
        
        $id = $data['id'];
        self::u_group_components_render($app, $form,false);
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

    public static function u_group_components_render($app,$form,$is_modal){
        // <editor-fold defaultstate="collapsed" desc="">
        $path = U_Group_Engine::path_get();

        $db = new DB();

        $id_prefix = U_Group_Engine::$prefix_id;

        $form->input_add()->input_set('id', $id_prefix . '_id')
                ->input_set('hide', true)
                ->input_set('value', '')
        ;

        $form->input_add()->input_set('id', $id_prefix . '_method')
                ->input_set('hide', true)
                ->input_set('value', '')
        ;


        $app_list = array();
        $t_app_list = SI::type_list_get('ICES_Engine', '$app_list');
        foreach ($t_app_list as $idx => $row) {
            $app_list[] = array('id' => $row['val'], 'text' => $row['text'], 'barcode' => '1123');
        }


        $form->input_select_add()
                ->input_select_set('label', Lang::get('APP Name'))
                ->input_select_set('icon', App_Icon::info())
                ->input_select_set('min_length', '0')
                ->input_select_set('id', $id_prefix . '_app_name')
                ->input_select_set('data_add', $app_list)
                ->input_select_set('value', array())
                ->input_select_set('disable_all', true)
                ->input_select_set('hide_all', true)
                ->input_select_set('allow_empty', false)
        ;


        $form->input_add()->input_set('label', Lang::get('Name'))
                ->input_set('id', $id_prefix . '_name')
                ->input_set('icon', APP_ICON::info())
                ->input_set('hide_all', true)
                ->input_set('disable_all', true)
        ;


        $form->input_select_add()
                ->input_select_set('label', 'Status')
                ->input_select_set('icon', 'fa fa-info')
                ->input_select_set('min_length', '0')
                ->input_select_set('id', $id_prefix . '_u_group_status')
                ->input_select_set('data_add', array())
                ->input_select_set('value', array())
                ->input_select_set('hide_all', true)
                ->input_select_set('is_module_status', true)
        ;


        $form->hr_add()->hr_set('class', '');

        $form->button_add()->button_set('value', 'Submit')
                ->button_set('id', $id_prefix . '_btn_submit')
                ->button_set('icon', App_Icon::detail_btn_save())
        ;


        $param = array(
            'ajax_url' => $path->index . 'ajax_search/'
            , 'index_url' => $path->index
            , 'detail_tab' => '#detail_tab'
            , 'view_url' => $path->index . 'view/'
            , 'window_scroll' => 'body'
            , 'data_support_url' => $path->index . 'data_support/'
            , 'common_ajax_listener' => ICES_Engine::$app['app_base_url'] . 'common_ajax_listener/'
            , 'component_prefix_id' => $id_prefix
        );
        
        if ($is_modal) {
            $param['detail_tab'] = '#modal_u_group .modal-body';
            $param['view_url'] = '';
            $param['window_scroll'] = '#modal_u_group';
        }

        $js = get_instance()->load->view(ICES_Engine::$app['app_base_dir'] . 'u_group/u_group_basic_function_js', $param, TRUE);
        $app->js_set($js);

        // </editor-fold>
    
    }
    
    public static function u_group_status_log_render($app,$form,$data,$path){
        // <editor-fold defaultstate="collapsed" desc="">
        $config = array(
            'module_name' => 'u_group',
            'module_engine' => 'u_group_engine',
            'id' => $data['id']
        );
        SI::form_renderer()->status_log_tab_render($form, $config); // </editor-fold>
    }

    public static function security_menu_render($app,$form,$data,$path){
        // <editor-fold defaultstate="collapsed" desc="">
        $path = U_Group_Engine::path_get();

        $db = new DB();

        $id_prefix = 'security_menu';
        
        $form->input_add()->input_set('id', $id_prefix . '_app_name')
                ->input_set('hide', true)
                ->input_set('value', '')
        ;

        $form->input_add()->input_set('id', $id_prefix . '_method')
                ->input_set('hide', true)
                ->input_set('value', '')
        ;
        
        $form->table_add()
                ->table_set('id', $id_prefix.'_table')
                ->table_set('data', array())
                ->table_set('columns', array("name" => "selected",'col_attrib' => array('style' => 'text-align:center;width:50px'), "attribute" => 'style="text-align:center;"',  "label" => '<input type="checkbox" id="check_all">', 'element_tag' => 'input', 'element_attribute' => 'type="checkbox"'))
                ->table_set('columns', array("name" => "id", "label" => "Menu ID", 'attribute' => 'style="display:none"', 'col_attrib' => array('style' => "display:none")))
                ->table_set('columns', array("name" => "menu", "label" => "Menu", 'col_attrib' => array('style' => 'text-align:left'), 'attribute' => 'style="text-align:left"'))
        ;


        $form->hr_add()->hr_set('class', '');

        $form->button_add()->button_set('value', 'Submit')
                ->button_set('id', $id_prefix . '_btn_submit')
                ->button_set('icon', App_Icon::detail_btn_save())
        ;
                
        $param = array(
            'ajax_url' => $path->index . 'ajax_search/'
            , 'index_url' => $path->index
            , 'detail_tab' => '#'.$id_prefix.'_tab'
            , 'view_url' => $path->index . 'view/'
            , 'window_scroll' => 'body'
            , 'data_support_url' => $path->index . 'data_support/'
            , 'common_ajax_listener' => ICES_Engine::$app['app_base_url'] . 'common_ajax_listener/'
            , 'component_prefix_id' => $id_prefix
        );

        $js = get_instance()->load->view(ICES_Engine::$app['app_base_dir'] . 'u_group/u_group_security_menu_js', $param, TRUE);
        $app->js_set($js);
        
        $js = '
            <script>
                $("#'.$id_prefix.'_tab").find("#'.$id_prefix.'_method").val("view");
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
    
    public static function security_controller_render($app, $form, $data, $path){
        //<editor-fold defaultstate="collapsed">
        $ices = SI::type_get('ICES_Engine','ices','$app_list');
        get_instance()->load->helper($ices['app_base_dir'].'u_group/u_group_engine');
        
        $u_group_path = U_Group_Engine::path_get();
        get_instance()->load->helper($u_group_path->u_group_data_support);
        get_instance()->load->helper($ices['app_base_dir'].'security_controller/security_controller_engine');
        
        $security_controller_path = Security_Controller_Engine::path_get();
        get_instance()->load->helper($security_controller_path->security_controller_data_support);
        
        $u_group_id = $data['id'];
        $u_group = U_Group_Data_Support::u_group_get($u_group_id);
        $u_group_security_controller = U_Group_Data_Support::u_group_security_controller_get($u_group_id);
        $security_controller_list = Security_Controller_Data_Support::security_controller_by_app_name_get($u_group['app_name']);
        $db = new DB();
        
        
        $id_prefix = 'security_controller';
        $name='';
        $accordion = null;
        $form->form_group_add();
        
        $form->input_add()->input_set('id', $id_prefix . '_method')
                ->input_set('hide', true)
                ->input_set('value', '')
        ;
        
        $form_group = $form->form_group_add();
        $form_group->label_add()->input_raw_add()->input_raw_set('type','checkbox')
                ->input_raw_set('id',$id_prefix.'_check_all');
        $form_group->label_add()->label_set('value','Check All');
        foreach($security_controller_list as $controller){
            if($name != $controller['name']){
                $name = $controller['name'];
                $accordion = $form->accordion_add()->accordion_set('header',array('id'=>$name,'text'=>$name));
            }
    
            $attrib = array();
            foreach($u_group_security_controller as $item){
                if($item['id'] == $controller['id'])
                    $attrib=array(
                        "checked"=>''
                    );
            }             
            $form_group=$accordion->form_group_add();
            $form_group->label_add()
                    ->input_raw_add()->input_raw_set('type','checkbox')
                    ->input_raw_set('attrib',$attrib)
                    ->input_raw_set('id',$id_prefix.$controller['id']);
            $form_group->label_add()->label_set('value',$controller['method']);
        }

        $form->hr_add()->button_add()->button_set('value','Submit')
            ->button_set('icon','fa fa-save')
            ->button_set('id',$id_prefix.'_btn_submit');
        
        $param = array(
            'ajax_url' => $u_group_path->index . 'ajax_search/'
            , 'index_url' => $u_group_path->index
            , 'detail_tab' => '#'.$id_prefix.'_tab'
            , 'view_url' => $u_group_path->index . 'view/'
            , 'window_scroll' => 'body'
            , 'data_support_url' => $u_group_path->index . 'data_support/'
            , 'common_ajax_listener' => ICES_Engine::$app['app_base_url'] . 'common_ajax_listener/'
            , 'component_prefix_id' => $id_prefix
        );
        
        $js = get_instance()->load->view(ICES_Engine::$app['app_base_dir'] . 'u_group/u_group_security_controller_js', $param, TRUE);
        $app->js_set($js);
        
        $js = '
            <script>
                $("#'.$id_prefix.'_tab").find("#'.$id_prefix.'_method").val("view");
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
    
}
    
?>