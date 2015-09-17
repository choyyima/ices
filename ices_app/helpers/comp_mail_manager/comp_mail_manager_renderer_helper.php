<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Comp_Mail_Manager_Renderer {

    public static function modal_comp_mail_manager_render($app,$modal){
        $modal->header_set(array('title'=>Lang::get('Manufacturing Work Process'),'icon'=>App_Icon::comp_mail_manager()));
        $modal->width_set('95%');
        $components = self::comp_mail_manager_components_render($app, $modal,true);


    }

    public static function comp_mail_manager_render($app,$form,$data,$path,$method){
        get_instance()->load->helper('comp_mail_manager/comp_mail_manager_engine');
        $path = Comp_Mail_Manager_Engine::path_get();
        $id = $data['id'];
        $components = self::comp_mail_manager_components_render($app, $form,false);
        $back_href = $path->index;

        

        $js = '
            <script>
                $("#comp_mail_manager_method").val("'.$method.'");
                $("#comp_mail_manager_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                comp_mail_manager_init();
                comp_mail_manager_bind_event();
                comp_mail_manager_components_prepare(); 
        ';
        $app->js_set($js);

    }

    public static function comp_mail_manager_components_render($app,$form,$is_modal){
        get_instance()->load->helper('product/product_engine');
        get_instance()->load->helper('comp_mail_manager/comp_mail_manager_engine');
        $path = Comp_Mail_Manager_Engine::path_get();            
        $components = array();
        $db = new DB();

        $id_prefix = Comp_Mail_Manager_Engine::$prefix_id;

        $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                ->input_set('hide',true)
                ->input_set('value','')
                ;


        $form->input_add()->input_set('id',$id_prefix.'_method')
                ->input_set('hide',true)
                ->input_set('value','')
                ;            
        
        $tbl_res_product = $form->table_input_add();
        $tbl_res_product->table_input_set('id',$id_prefix.'_mail_list_table')

            ->label_set('value',Lang::get(array('Mail List')))
            ->table_input_set('columns',array(
                'col_name'=>'id'
                ,'th'=>array('val'=>'','class'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'','visible'=>false
                )
            ))
               
            ->table_input_set('columns',array(
                'col_name'=>'code'
                ,'th'=>array('val'=>'Code','col_style'=>'width:150px')
                ,'td'=>array('val'=>'','tag'=>'div','class'=>'','attr'=>array()
                )
            ))                    
            ->table_input_set('columns',array(
                'col_name'=>'name'
                ,'th'=>array('val'=>'Name','col_style'=>'width:150px')
                ,'td'=>array('val'=>'','tag'=>'div','class'=>'','attr'=>array())
            ))
            ->table_input_set('columns',array(
                'col_name'=>'username'
                ,'th'=>array('val'=>'Username','col_style'=>'')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array()
                )
            ))                    
            ->table_input_set('columns',array(
                'col_name'=>'password'
                ,'th'=>array('val'=>'Password','col_style'=>'')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array())
            ))
            
            ->table_input_set('new_row',false)
            ;
        

        $form->hr_add()->hr_set('class','');

        $form->button_add()->button_set('value','Submit')
                        ->button_set('id',$id_prefix.'_submit')
                        ->button_set('icon',App_Icon::detail_btn_save())
                    ;



        $param = array(
            'ajax_url'=>$path->index.'ajax_search/'
            ,'index_url'=>$path->index
            ,'detail_tab'=>'#detail_tab'
            ,'view_url'=>$path->index.'view/'
            ,'window_scroll'=>'body'
            ,'data_support_url'=>$path->index.'data_support/'
            ,'common_ajax_listener'=>get_instance()->config->base_url().'common_ajax_listener/'
            ,'component_prefix_id'=>$id_prefix
        );



        if($is_modal){
            $param['detail_tab'] = '#modal_comp_mail_manager .modal-body';
            $param['view_url'] = '';
            $param['window_scroll'] = '#modal_comp_mail_manager';
        }

        $js = get_instance()->load->view('comp_mail_manager/comp_mail_manager_mail_list_js',$param,TRUE);
        $app->js_set($js);
        
        $js = get_instance()->load->view('comp_mail_manager/comp_mail_manager_basic_function_js',$param,TRUE);
        $app->js_set($js);

        return $components;

    }

    public static function comp_mail_manager_status_log_render($app,$form,$data,$path){
        $config=array(
            'module_name'=>'comp_mail_manager',
            'module_engine'=>'comp_mail_manager_engine',
            'id'=>$data['id']
        );
        SI::form_renderer()->status_log_tab_render($form, $config);
    }

}
    
?>