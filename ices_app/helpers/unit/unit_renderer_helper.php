<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Unit_Renderer {

    public static function modal_unit_render($app,$modal){
        $modal->header_set(array('title'=>'Unit','icon'=>App_Icon::unit()));
        $components = self::unit_components_render($app, $modal,true);


    }

    public static function unit_render($app,$form,$data,$path,$method){
        get_instance()->load->helper('unit/unit_engine');
        $path = Unit_Engine::path_get();
        $id = $data['id'];
        $components = self::unit_components_render($app, $form,false);
        $back_href = $path->index;

        $form->button_add()->button_set('value','BACK')
            ->button_set('icon',App_Icon::btn_back())
            ->button_set('href',$back_href)
            ->button_set('class','btn btn-default')
            ;

        $js = '
            <script>
                $("#unit_method").val("'.$method.'");
                $("#unit_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                unit_init();
                unit_bind_event();
                unit_components_prepare(); 
        ';
        $app->js_set($js);

    }

    public static function unit_components_render($app,$form,$is_modal){

        get_instance()->load->helper('unit/unit_engine');
        $path = Unit_Engine::path_get();            
        $components = array();
        $db = new DB();
        
        $id_prefix = Unit_Engine::$prefix_id;
        
        $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                ->input_set('hide',true)
                ->input_set('value','')
                ;


        $form->input_add()->input_set('id',$id_prefix.'_method')
                ->input_set('hide',true)
                ->input_set('value','')
                ;            
        
        $form->input_add()->input_set('label',Lang::get('Code'))->input_set('id',$id_prefix.'_code')
                ->input_set('icon','fa fa-info')
                ->input_set('value','');
        $form->input_add()->input_set('label',Lang::get('Name'))->input_set('id',$id_prefix.'_name')
                ->input_set('icon','fa fa-user')
                ->input_set('value','');         
        $form->textarea_add()->textarea_set('label','Notes')->textarea_set('id',$id_prefix.'_notes')
                ->textarea_set('value','')
                ;

        $form->hr_add()->hr_set('class','');

        $form->button_add()->button_set('value','Submit')
            ->button_set('id','unit_submit')
            ->button_set('icon',App_Icon::detail_btn_save())
        ;

        $form->button_add()->button_set('value','Delete')
            ->button_set('id','unit_delete')
            ->button_set('icon',App_Icon::detail_btn_delete())
            ->button_set('class','btn btn-danger hide_all')
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
            $param['detail_tab'] = '#modal_unit .modal-body';
            $param['view_url'] = '';
            $param['window_scroll'] = '#modal_unit';
        }

        $js = get_instance()->load->view('unit/unit_basic_function_js',$param,TRUE);
        $app->js_set($js);

        return $components;

    }

    public static function unit_status_log_render($app,$form,$data,$path){
        get_instance()->load->helper('unit/unit_engine');
        $path = Unit_Engine::path_get();
        get_instance()->load->helper($path->unit_engine);

        $id = $data['id'];
        $db = new DB();
        $q = '
            select null row_num
                ,t1.moddate
                ,t1.unit_status
                ,t2.name user_name

            from unit_status_log t1
                inner join user_login t2 on t1.modid = t2.id
            where t1.unit_id = '.$id.'
                order by moddate asc
        ';
        $rs = $db->query_array($q);
        for($i = 0;$i<count($rs);$i++){
            $rs[$i]['row_num'] = $i+1;
            $unit_status_name = '';
            $unit_status_name = SI::get_status_attr(
                Unit_Engine::unit_status_get(
                    $rs[$i]['unit_status']
                )['label']
            );
            $rs[$i]['unit_status_name'] = $unit_status_name;


        }
        $unit_status_log = $rs;

        $table = $form->form_group_add()->table_add();
        $table->table_set('id','unit_status_log_table');
        $table->table_set('class','table fixed-table');
        $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
        $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array('style'=>'')));
        $table->table_set('columns',array("name"=>"unit_status_name","label"=>"Status",'col_attrib'=>array('style'=>'')));
        $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array('style'=>'')));
        $table->table_set('data',$unit_status_log);
    }

    public static function unit_type_log_render($app,$form,$data,$path){
        get_instance()->load->helper('unit/unit_engine');
        $path = Unit_Engine::path_get();
        get_instance()->load->helper($path->unit_engine);

        $id = $data['id'];
        $db = new DB();
        $q = '
            select null row_num
                ,t1.moddate
                ,t3.code unit_type_code
                ,t2.name user_name

            from unit_unit_type_log t1
                inner join user_login t2 on t1.modid = t2.id
                inner join unit_type t3 on t1.unit_type_id = t3.id
            where t1.unit_id = '.$id.'
                order by moddate desc
        ';
        $rs = $db->query_array($q);
        for($i = 0;$i<count($rs);$i++){
            $rs[$i]['row_num'] = $i+1;


        }
        $unit_status_log = $rs;

        $table = $form->form_group_add()->table_add();
        $table->table_set('id','unit_status_log_table');
        $table->table_set('class','table fixed-table');
        $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
        $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array('style'=>'')));
        $table->table_set('columns',array("name"=>"unit_type_code","label"=>"Status",'col_attrib'=>array('style'=>'')));
        $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array('style'=>'')));
        $table->table_set('data',$unit_status_log);
    }

}
    
?>