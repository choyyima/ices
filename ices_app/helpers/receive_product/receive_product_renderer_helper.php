<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Receive_Product_Renderer {

    public static function modal_receive_product_render($app,$modal){
        $modal->footer_attr_set(array('style'=>'display:none'));
        $modal->header_set(array('title'=>'Receive Product','icon'=>  APP_ICON::receive_product()));
        $components = self::receive_product_components_render($app, $modal,true);
        
    }

    public static function receive_product_render($app,$form,$data,$path,$method){
        get_instance()->load->helper('receive_product/receive_product_engine');
        $path = Receive_Product_Engine::path_get();
        $id = $data['id'];
        $components = self::receive_product_components_render($app, $form,false);
        $back_href = $path->index;

        $form->button_add()->button_set('value','BACK')
            ->button_set('icon',App_Icon::btn_back())
            ->button_set('href',$back_href)
            ->button_set('class','btn btn-default')
            ;

        $js = '
            <script>
                $("#receive_product_method").val("'.$method.'");
                $("#receive_product_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                receive_product_init();
                receive_product_bind_event();
                receive_product_components_prepare(); 
        ';
        $app->js_set($js);

    }

    public static function receive_product_components_render($app,$form,$is_modal){

    get_instance()->load->helper('receive_product/receive_product_engine');
    $path = Receive_Product_Engine::path_get();            
    $components = array();
    $db = new DB();
    $id_prefix = 'receive_product';

    $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
            ->input_set('hide',true)
            ->input_set('value','')
            ;

    $form->input_add()->input_set('id',$id_prefix.'_type')
            ->input_set('value','')
            ->input_set('hide_all',true)
            ;

    $disabled = array('disable'=>'');


    $reference_detail = array(
    );

    $db = new DB();
    $store_list = array();
    $q = 'select id id, name data from store where status>0';            
    $store_list = $db->query_array($q);
    
    $form->input_select_add()
            ->input_select_set('label',Lang::get('Store'))
            ->input_select_set('icon',App_Icon::store())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_store')
            ->input_select_set('data_add',$store_list)
            ->input_select_set('value',array())
            ->input_select_set('disable_all',true)

        ;

    $form->input_add()->input_set('label',Lang::get('Code'))
            ->input_set('id',$id_prefix.'_code')
            ->input_set('icon','fa fa-info')
            ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
        ;
    
    $form->input_select_detail_add()
            ->input_select_set('label',Lang::get('Reference'))
            ->input_select_set('icon',App_Icon::info())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_reference')
            ->input_select_set('min_length','1')
            ->input_select_set('data_add',array())
            ->input_select_set('value',array())
            ->input_select_set('ajax_url',$path->ajax_search.'input_select_reference_search')
            ->input_select_set('disable_all',true)
            ->detail_set('rows',$reference_detail)
            ->detail_set('id',"receive_product_reference_detail")
            ->detail_set('ajax_url','')

        ;

    $form->input_add()->input_set('id',$id_prefix.'_method')
            ->input_set('hide',true)
            ->input_set('value','')
            ;            
    

    

    $form->datetimepicker_add()->datetimepicker_set('label',Lang::get('Receive Product Date'))
            ->datetimepicker_set('id',$id_prefix.'_receive_product_date')
            ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
            ->datetimepicker_set('disable_all',true)

        ;

    $warehouse_list = array();
    $q = '
        select t1.id id, t1.name data 
        from warehouse t1
            inner join warehouse_type t2 on t1.warehouse_type_id = t2.id
        where t1.status>0 and t2.code = "BOS"
        ';            
    $warehouse_list = $db->query_array($q);

    $form->input_select_detail_add()
            ->input_select_set('label',Lang::get('From Warehouse'))
            ->input_select_set('icon',App_Icon::warehouse())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_warehouse_from')                
            ->input_select_set('value',array())
            ->input_select_set('disable_all',true)
            ->detail_set('rows',array())
            ->detail_set('id',$id_prefix."_warehouse_from_detail")
            ->detail_set('ajax_url','')
        ;

    $form->input_select_detail_editable_add()
            ->input_select_set('label',Lang::get('To Warehouse'))
            ->input_select_set('icon',App_Icon::warehouse())
            ->input_select_set('min_length','0')
            ->input_select_set('id','receive_product_warehouse_to')
            ->input_select_set('data_add',$warehouse_list)
            ->input_select_set('value',array())
            ->input_select_set('disable_all',true)
            ->detail_editable_set('rows',array())
            ->detail_editable_set('id',$id_prefix."_warehouse_to_detail")
            ->detail_editable_set('ajax_url','')

        ;

    $form->input_select_add()
            ->input_select_set('label','Status')
            ->input_select_set('icon','fa fa-info')
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_receive_product_status')
            ->input_select_set('data_add',array())
            ->input_select_set('value',array())
            ->div_set('id','receive_product_div_receive_product_status')
            ->input_select_set('is_module_status',true)
            ;


    $table = $form->form_group_add()->table_add();
    $table->table_set('id',$id_prefix.'_product_table');
    $table->div_set('label','Product');
    $table->table_set('class','table fixed-table');

    $form->textarea_add()->textarea_set('label','Notes')
        ->textarea_set('id',$id_prefix.'_notes')
        ->textarea_set('value','')
        ->textarea_set('attrib',array())       
        ->textarea_set('disable_all',true)
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
        $param['detail_tab'] = '#modal_receive_product .modal-body';
        $param['view_url'] = '';
        $param['window_scroll'] = '#modal_receive_product';
    }

    $js = get_instance()->load->view('receive_product/receive_product_basic_function_js',$param,TRUE);
    $app->js_set($js);

    return $components;

    }

    public static function receive_product_status_log_render($app,$form,$data,$path){
        $config=array(
            'module_name'=>'receive_product',
            'module_engine'=>'Receive_Product_Engine',
            'id'=>$data['id']
        );
        SI::form_renderer()->status_log_tab_render($form, $config);
    }

}
    
?>