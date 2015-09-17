<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class U_Profile_Renderer {

    public static function modal_u_profile_render($app,$modal){
        $modal->header_set(array('title'=>'System Investigation Report','icon'=>App_Icon::u_profile()));
        $components = self::u_profile_components_render($app, $modal,true);
    }

    public static function u_profile_render($app,$form,$data,$path,$method){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('u_profile/u_profile_engine');
        $path = U_Profile_Engine::path_get();
        $id = $data['id'];
        $components = self::u_profile_components_render($app, $form,false);
        $back_href = $path->index;

        $js = '
            <script>
                $("#u_profile_method").val("'.$method.'");
                $("#u_profile_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                u_profile_init();
                u_profile_bind_event();
                u_profile_components_prepare(); 
        ';
        $app->js_set($js);
        //</editor-fold>
    }

    public static function u_profile_components_render($app,$form,$is_modal){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('u_profile/u_profile_engine');
        get_instance()->load->helper('security/user_login_engine');
        $path = U_Profile_Engine::path_get();            
        $components = array();
        $db = new DB();

        $id_prefix = 'u_profile';

        $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                ->input_set('hide',true)
                ->input_set('value','')
                ;

        $form->input_add()->input_set('id',$id_prefix.'_method')
                ->input_set('hide',true)
                ->input_set('value','')
                ;            
        $db = new DB();
        
        $user_login = User_Login_Engine::get(User_Info::get()['user_id']);
        
        $form->input_add()->input_set('label',Lang::get('User Name'))
            ->input_set('id',$id_prefix.'_name')
            ->input_set('icon','fa fa-info')
            ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
            ->input_set('value',$user_login['name'])
            ->input_set('hide_all',true)
            ->input_set('disable_all',true)
        ;
        
        $form->input_add()->input_set('label',Lang::get('First Name'))
            ->input_set('id',$id_prefix.'_first_name')
            ->input_set('icon','fa fa-info')
            ->input_set('attrib',array('disabled'=>'','style'=>''))
            ->input_set('value',$user_login['first_name'])
            ->input_set('hide_all',true)
            ->input_set('disable_all',true)
        ;
        
        $form->input_add()->input_set('label',Lang::get('Last Name'))
            ->input_set('id',$id_prefix.'_last_name')
            ->input_set('icon','fa fa-info')
            ->input_set('attrib',array('disabled'=>'','style'=>''))
            ->input_set('value',$user_login['last_name'])
            ->input_set('hide_all',true)
            ->input_set('disable_all',true)
        ;
        
        $form->input_add()->input_set('label',Lang::get('Password'))
            ->input_set('id',$id_prefix.'_password')
            ->input_set('icon','fa fa-info')
            ->input_set('attrib',array('disabled'=>'','style'=>''))
            ->input_set('value',$user_login['password'])
            ->input_set('hide_all',true)
            ->input_set('disable_all',true)
        ;
        
        $form->hr_add()->hr_set('class','');
            
        $form->button_add()->button_set('value','Submit')
                        ->button_set('id',$id_prefix.'_submit')
                        ->button_set('icon',App_Icon::detail_btn_save())
                    ;
        
        $param = array(
            'ajax_url'=>$path->index.'ajax_search/'
            ,'index_url'=>$path->index
            ,'detail_tab'=>'#'.$id_prefix
            ,'view_url'=>$path->index.''
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

        $js = get_instance()->load->view('u_profile/'.$id_prefix.'_basic_function_js',$param,TRUE);
        $app->js_set($js);
        return $components;
        //</editor-fold>

    }

}
    
?>