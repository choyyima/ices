<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Print_Form_Renderer {
        
        public static function modal_print_form_render($app,$modal){
            $modal->header_set(array('title'=>Lang::get(array('Refill - ','Checking Result Form')),'icon'=>App_Icon::print_form()));
            $components = self::print_form_components_render($app, $modal,true);
        }
        
        public static function print_form_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('print_form/print_form_engine');
            $path = Print_Form_Engine::path_get();
            $id = $data['id'];
            $components = self::print_form_components_render($app, $form,false);
            $back_href = $path->index;
            
            $js = '
                <script>
                    $("#print_form_method").val("'.$method.'");
                    $("#print_form_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    print_form_init();
                    print_form_bind_event();
                    print_form_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function print_form_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('print_form/print_form_engine');
            $path = Print_Form_Engine::path_get();            
            $components = array();
            $db = new DB();
            
            $id_prefix = Print_Form_Engine::$prefix_id;
            
            $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $disabled = array('disable'=>'');
                     
            $db = new DB();
                         
            $form_type_list = array();
            $form_type_list_raw = SI::type_list_get('Print_Form_Engine','$form_type');
            foreach($form_type_list_raw as $idx=>$row){
                $form_type_list[] = array('id'=>$row['val'],'data'=>$row['label']);
            }
            
            
            $form->input_select_add()
                    ->input_select_set('label',Lang::get('Type'))
                    ->input_select_set('icon',App_Icon::store())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id',$id_prefix.'_form_type')
                    ->input_select_set('data_add',$form_type_list)
                    ->input_select_set('value',array())
                ;
            
            $param = array(
                'ajax_url'=>$path->index.'ajax_search/'
                ,'index_url'=>$path->index
                ,'detail_tab'=>'#print_form'
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
            
            $js = get_instance()->load->view('print_form/'.$id_prefix.'_basic_function_js',$param,TRUE);
            $app->js_set($js);
            return $components;
            
        }
        
    }
    
?>