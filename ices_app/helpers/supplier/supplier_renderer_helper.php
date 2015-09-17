<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Supplier_Renderer {

    public static function modal_supplier_render($app,$modal){
        $modal->header_set(array('title'=>'Supplier','icon'=>App_Icon::supplier()));
        $components = self::supplier_components_render($app, $modal,true);


    }

    public static function supplier_render($app,$form,$data,$path,$method){
        get_instance()->load->helper('supplier/supplier_engine');
        $path = Supplier_Engine::path_get();
        $id = $data['id'];
        $components = self::supplier_components_render($app, $form,false);
        $back_href = $path->index;

        $form->button_add()->button_set('value','BACK')
            ->button_set('icon',App_Icon::btn_back())
            ->button_set('href',$back_href)
            ->button_set('class','btn btn-default')
            ;

        $js = '
            <script>
                $("#supplier_method").val("'.$method.'");
                $("#supplier_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                supplier_init();
                supplier_bind_event();
                supplier_components_prepare(); 
        ';
        $app->js_set($js);

    }

    public static function supplier_components_render($app,$form,$is_modal){

        get_instance()->load->helper('supplier/supplier_engine');
        $path = Supplier_Engine::path_get();            
        $components = array();
        $db = new DB();
        
        $id_prefix = Supplier_Engine::$prefix_id;
        
        $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                ->input_set('hide',true)
                ->input_set('value','')
                ;


        $form->input_add()->input_set('id',$id_prefix.'_method')
                ->input_set('hide',true)
                ->input_set('value','')
                ;            

        $components['code'] = $form->input_add()->input_set('label','Code')
                ->input_set('id',$id_prefix.'_code')
                ->input_set('icon','fa fa-info')
                ->input_set('value','')
                
            ;
        $components['name'] = $form->input_add()->input_set('label','Name')
                ->input_set('id',$id_prefix.'_name')
                ->input_set('icon','fa fa-user')
                ->input_set('value','')
            ;

        $components['phone'] = $form->input_add()->input_set('label','Phone')
                ->input_set('id',$id_prefix.'_phone')
                ->input_set('icon','fa fa-phone')
                ->input_set('input_mask_type','phone-mobile')
                ->input_set('value','')
            ;

        $components['phone2'] = $form->input_add()->input_set('label','Phone 2')
                ->input_set('id',$id_prefix.'_phone2')
                ->input_set('icon','fa fa-phone')
                ->input_set('input_mask_type','phone-mobile')
                ->input_set('value','')
            ;

        $components['phone3'] = $form->input_add()->input_set('label','Phone 3')
                ->input_set('id',$id_prefix.'_phone3')
                ->input_set('icon','fa fa-phone')
                ->input_set('input_mask_type','phone-mobile')
                ->input_set('value','')
            ;

        $components['bb_pin'] = $form->input_add()->input_set('label','BB Pin')
                ->input_set('id',$id_prefix.'_bb_pin')
                ->input_set('icon','fa fa-envelope')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;
        
        $components['supplier_status'] = $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-user')
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_supplier_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                 ->input_select_set('is_module_status',true)
                ;

        $components['address']=$form->input_add()->input_set('label',Lang::get('Address'))
                ->input_set('id',$id_prefix.'_address')
                ->input_set('icon','fa fa-location-arrow')
                ->input_set('value','')
                ;

        $components['city'] = $form->input_add()->input_set('label','City')
                ->input_set('id',$id_prefix.'_city')
                ->input_set('icon','fa fa-location-arrow')
                ->input_set('value','')
            ;

        $components['country'] = $form->input_add()->input_set('label',Lang::get('Country'))
                ->input_set('id',$id_prefix.'_country')
                ->input_set('icon','fa fa-location-arrow')
                ->input_set('value','')
            ;

        $components['email'] = $form->input_add()->input_set('label','Email')
                ->input_set('id',$id_prefix.'_email')
                ->input_set('icon','fa fa-envelope')
                ->input_set('value','')
            ;

        $components['notes'] = $form->textarea_add()->textarea_set('label','Notes')
                ->textarea_set('id',$id_prefix.'_notes')
                ->textarea_set('value','')
            ;

        $form->hr_add()->hr_set('class','');

        $form->button_add()->button_set('value','Submit')
                        ->button_set('id','supplier_submit')
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
            $param['detail_tab'] = '#modal_supplier .modal-body';
            $param['view_url'] = '';
            $param['window_scroll'] = '#modal_supplier';
        }

        $js = get_instance()->load->view('supplier/supplier_basic_function_js',$param,TRUE);
        $app->js_set($js);

        return $components;

    }

    public static function supplier_status_log_render($app,$form,$data,$path){
        get_instance()->load->helper('supplier/supplier_engine');
        $path = Supplier_Engine::path_get();
        get_instance()->load->helper($path->supplier_engine);

        $id = $data['id'];
        $db = new DB();
        $q = '
            select null row_num
                ,t1.moddate
                ,t1.supplier_status
                ,t2.name user_name

            from supplier_status_log t1
                inner join user_login t2 on t1.modid = t2.id
            where t1.supplier_id = '.$id.'
                order by moddate asc
        ';
        $rs = $db->query_array($q);
        for($i = 0;$i<count($rs);$i++){
            $rs[$i]['row_num'] = $i+1;
            $supplier_status_name = '';
            $supplier_status_name = SI::get_status_attr(
                Supplier_Engine::supplier_status_get(
                    $rs[$i]['supplier_status']
                )['label']
            );
            $rs[$i]['supplier_status_name'] = $supplier_status_name;


        }
        $supplier_status_log = $rs;

        $table = $form->form_group_add()->table_add();
        $table->table_set('id','supplier_status_log_table');
        $table->table_set('class','table fixed-table');
        $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
        $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array('style'=>'')));
        $table->table_set('columns',array("name"=>"supplier_status_name","label"=>"Status",'col_attrib'=>array('style'=>'')));
        $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array('style'=>'')));
        $table->table_set('data',$supplier_status_log);
    }

    public static function supplier_type_log_render($app,$form,$data,$path){
        get_instance()->load->helper('supplier/supplier_engine');
        $path = Supplier_Engine::path_get();
        get_instance()->load->helper($path->supplier_engine);

        $id = $data['id'];
        $db = new DB();
        $q = '
            select null row_num
                ,t1.moddate
                ,t3.code supplier_type_code
                ,t2.name user_name

            from supplier_supplier_type_log t1
                inner join user_login t2 on t1.modid = t2.id
                inner join supplier_type t3 on t1.supplier_type_id = t3.id
            where t1.supplier_id = '.$id.'
                order by moddate desc
        ';
        $rs = $db->query_array($q);
        for($i = 0;$i<count($rs);$i++){
            $rs[$i]['row_num'] = $i+1;


        }
        $supplier_status_log = $rs;

        $table = $form->form_group_add()->table_add();
        $table->table_set('id','supplier_status_log_table');
        $table->table_set('class','table fixed-table');
        $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
        $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array('style'=>'')));
        $table->table_set('columns',array("name"=>"supplier_type_code","label"=>"Status",'col_attrib'=>array('style'=>'')));
        $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array('style'=>'')));
        $table->table_set('data',$supplier_status_log);
    }

}
    
?>