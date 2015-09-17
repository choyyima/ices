<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Renderer {

    public static function modal_customer_render($app,$modal){
        $modal->header_set(array('title'=>'Customer','icon'=>App_Icon::customer()));
        $components = self::customer_components_render($app, $modal,true);


    }

    public static function customer_render($app,$form,$data,$path,$method){
        get_instance()->load->helper('customer/customer_engine');
        $path = Customer_Engine::path_get();
        $id = $data['id'];
        $components = self::customer_components_render($app, $form,false);
        $back_href = $path->index;

        $form->button_add()->button_set('value','BACK')
            ->button_set('icon',App_Icon::btn_back())
            ->button_set('href',$back_href)
            ->button_set('class','btn btn-default')
            ;

        $js = '
            <script>
                $("#customer_method").val("'.$method.'");
                $("#customer_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                customer_init();
                customer_bind_event();
                customer_components_prepare(); 
        ';
        $app->js_set($js);

    }

    public static function customer_components_render($app,$form,$is_modal){

        get_instance()->load->helper('customer/customer_engine');
        $path = Customer_Engine::path_get();            
        $components = array();
        $db = new DB();
        $components['id'] = $form->input_add()->input_set('id','customer_id')
                ->input_set('hide',true)
                ->input_set('value','')
                ;


        $form->input_add()->input_set('id','customer_method')
                ->input_set('hide',true)
                ->input_set('value','')
                ;            

        $form->input_add()->input_set('label',Lang::get('Code'))
                ->input_set('id','customer_code')
                ->input_set('icon','fa fa-info')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
                ->input_set('attrib',array('style'=>'font-weight:bold'))

            ;

        $form->input_add()->input_set('label',Lang::get('Name'))
                ->input_set('id','customer_name')
                ->input_set('icon','fa fa-info')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;

        $q ='
    select id id,name data
    from customer_type
    where status>0
    ';

    $customer_type = $db->query_array($q);

    $customer_type_columns = array(
        array(
            "name"=>"code"
            ,"label"=>"Code"
        )
        ,array(
            "name"=>"name"
            ,"label"=>"Name"
        )
    );

    $customer_type_ist = $form->input_select_table_add();
    $components['customer_type'] = $customer_type_ist->input_select_set('name','unit_id')
            ->input_select_set('id','customer_customer_type')
            ->input_select_set('label',Lang::get('Customer Type'))
            ->input_select_set('icon','fa fa-tag')
            ->input_select_set('min_length','0')
            ->input_select_set('data_add',$customer_type)
            ->input_select_set('value',array("id"=>"","data"=>""))
            ->input_select_set('hide_all',true)
            ->table_set('columns',$customer_type_columns)
            ->table_set('id',"customer_customer_type_table")
            ->table_set('ajax_url',get_instance()->config->base_url().'customer/ajax_search/customer_type')
            ->table_set('column_key','id')
            ->table_set('allow_duplicate_id',false)
            ->table_set('selected_value',array());
            ;

        $components['phone'] = $form->input_add()->input_set('label','Phone')
                ->input_set('id','customer_phone')
                ->input_set('icon','fa fa-phone')
                ->input_set('input_mask_type','phone-mobile')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;

        $components['phone2'] = $form->input_add()->input_set('label','Phone 2')
                ->input_set('id','customer_phone2')
                ->input_set('icon','fa fa-phone')
                ->input_set('input_mask_type','phone-mobile')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;

        $components['phone3'] = $form->input_add()->input_set('label','Phone 3')
                ->input_set('id','customer_phone3')
                ->input_set('icon','fa fa-phone')
                ->input_set('input_mask_type','phone-mobile')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;

        $components['bb_pin'] = $form->input_add()->input_set('label','BB Pin')
                ->input_set('id','customer_bb_pin')
                ->input_set('icon','fa fa-envelope')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;


        $components['email'] = $form->input_add()->input_set('label','Email')
                ->input_set('id','customer_email')
                ->input_set('icon','fa fa-envelope')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;

         $components['customer_status'] = $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-user')
                ->input_select_set('min_length','0')
                ->input_select_set('id','customer_customer_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                 ->input_select_set('hide_all',true)
                ;

        $true_false_arr = array(
            array('id'=>'1','data'=>'True')
            ,array('id'=>'0','data'=>'False')
        ) ;

        $components['customer_is_credit'] = $form->input_select_add()
                ->input_select_set('label','Is Credit')
                ->input_select_set('icon','fa fa-user')
                ->input_select_set('min_length','0')
                ->input_select_set('id','customer_is_credit')
                ->input_select_set('data_add',$true_false_arr)
                ->input_select_set('value',array())
                 ->input_select_set('hide_all',true)
                ;

        $form->input_select_add()
                ->input_select_set('label','Is Sales Receipt Outstanding')
                ->input_select_set('icon','fa fa-user')
                ->input_select_set('min_length','0')
                ->input_select_set('id','customer_is_sales_receipt_outstanding')
                ->input_select_set('data_add',$true_false_arr)
                ->input_select_set('value',array())
                 ->input_select_set('hide_all',true)
                ;

        $components['address']=$form->input_add()->input_set('label',Lang::get('Address'))
                ->input_set('id','customer_address')
                ->input_set('icon','fa fa-location-arrow')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
                ;

        $components['city'] = $form->input_add()->input_set('label',Lang::get('City'))
                ->input_set('id','customer_city')
                ->input_set('icon','fa fa-location-arrow')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;

        $components['country'] = $form->input_add()->input_set('label',Lang::get('Country'))
                ->input_set('id','customer_country')
                ->input_set('icon','fa fa-location-arrow')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;

        $components['notes'] = $form->textarea_add()->textarea_set('label','Notes')
                ->textarea_set('id','customer_notes')
                ->textarea_set('value','')
                ->textarea_set('hide_all',true)
                ->textarea_set('disable_all',true)
            ;

        $form->input_add()->input_set('label','Customer Credit')
                ->input_set('id','customer_credit')
                ->input_set('icon','fa fa-euro')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
                //->security_set('strict',true)
            ;

        $form->input_add()->input_set('label','Customer Debit')
                ->input_set('id','customer_debit')
                ->input_set('icon','fa fa-euro')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
                //->security_set('strict',true)
            ;

        $form->hr_add()->hr_set('class','');

        $form->button_add()->button_set('value','Submit')
                        ->button_set('id','customer_submit')
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
        );



        if($is_modal){
            $param['detail_tab'] = '#modal_customer .modal-body';
            $param['view_url'] = '';
            $param['window_scroll'] = '#modal_customer';
        }

        $js = get_instance()->load->view('customer/customer_basic_function_js',$param,TRUE);
        $app->js_set($js);

        return $components;

    }

    public static function customer_status_log_render($app,$form,$data,$path){
        $config=array(
            'module_name'=>'customer',
            'module_engine'=>'Customer_Engine',
            'id'=>$data['id']
        );
        SI::form_renderer()->status_log_tab_render($form, $config);
    }

    public static function customer_type_log_render($app,$form,$data,$path){
        get_instance()->load->helper('customer/customer_engine');
        $path = Customer_Engine::path_get();
        get_instance()->load->helper($path->customer_engine);

        $id = $data['id'];
        $db = new DB();
        $q = '
            select null row_num
                ,t1.moddate
                ,t3.code customer_type_code
                ,t2.name user_name

            from customer_customer_type_log t1
                inner join user_login t2 on t1.modid = t2.id
                inner join customer_type t3 on t1.customer_type_id = t3.id
            where t1.customer_id = '.$id.'
                order by moddate desc
        ';
        $rs = $db->query_array($q);
        for($i = 0;$i<count($rs);$i++){
            $rs[$i]['row_num'] = $i+1;


        }
        $customer_status_log = $rs;

        $table = $form->form_group_add()->table_add();
        $table->table_set('id','customer_status_log_table');
        $table->table_set('class','table fixed-table');
        $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
        $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array('style'=>'')));
        $table->table_set('columns',array("name"=>"customer_type_code","label"=>"Status",'col_attrib'=>array('style'=>'')));
        $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array('style'=>'')));
        $table->table_set('data',$customer_status_log);
    }

}
    
?>