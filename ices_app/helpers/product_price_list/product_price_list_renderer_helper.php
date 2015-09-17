<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_Price_List_Renderer {

    public static function modal_product_price_list_render($app,$modal){
        $modal->header_set(array('title'=>'Product Price List','icon'=>App_Icon::info()));
        $components = self::product_price_list_components_render($app, $modal,true);


    }

    public static function product_price_list_render($app,$form,$data,$method){
        get_instance()->load->helper('product_price_list/product_price_list_engine');
        $path = Product_Price_List_Engine::path_get();
        $id = $data['id'];
        $components = self::product_price_list_components_render($app, $form,false);
        $back_href = $path->index;

        $form->button_add()->button_set('value','BACK')
            ->button_set('icon',App_Icon::btn_back())
            ->button_set('href',$back_href)
            ->button_set('class','btn btn-default')
            ;

        $js = '
            <script>
                $("#product_price_list_method").val("'.$method.'");
                $("#product_price_list_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                product_price_list_init();
                product_price_list_bind_event();
                product_price_list_components_prepare(); 
        ';
        $app->js_set($js);

    }

    public static function product_price_list_components_render($app,$form,$is_modal){

        get_instance()->load->helper('product_price_list/product_price_list_engine');
        $path = Product_Price_List_Engine::path_get();            
        $components = array();
        $db = new DB();
        $components['id'] = $form->input_add()->input_set('id','product_price_list_id')
                ->input_set('hide',true)
                ->input_set('value','')
                ;


        $form->input_add()->input_set('id','product_price_list_method')
                ->input_set('hide',true)
                ->input_set('value','')
                ;            

        $form->input_add()->input_set('label',Lang::get('Code'))
                ->input_set('id','product_price_list_code')
                ->input_set('icon','fa fa-info')

            ;

        $form->input_add()->input_set('label',Lang::get('Name'))
                ->input_set('id','product_price_list_name')
                ->input_set('icon','fa fa-info')

            ;

        $components['product_price_list_status'] = $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id','product_price_list_product_price_list_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->div_set('id','product_price_list_div_product_price_list_status')
                ;

        $true_false = [];
        $true_false[] = array('id'=>'1','data'=>'True');
        $true_false[] = array('id'=>'0','data'=>'False');

        $form->input_select_add()
                ->input_select_set('label','Is Delivery')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id','product_price_list_is_delivery')
                ->input_select_set('data_add',$true_false)
                ->input_select_set('value',array())
                ->input_select_set('allow_empty',false)
                ;

        $form->input_select_add()
            ->input_select_set('label','Is Refill Sparepart Price List')
            ->input_select_set('icon','fa fa-info')
            ->input_select_set('min_length','0')
            ->input_select_set('id','product_price_list_is_refill_sparepart_price_list')
            ->input_select_set('data_add',$true_false)
            ->input_select_set('value',$true_false[1])
            ->input_select_set('allow_empty',false)
            ;
        
        $form->input_add()->input_set('label',Lang::get('Delivery Extra Charge').' - non MOQ & MOP')
                ->input_set('id','product_price_list_delivery_extra_charge')
                ->input_set('icon',  App_Icon::money())
                ->input_set('is_numeric',true)
            ;

        
        $form->input_select_add()
                ->input_select_set('label','Is Discount')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id','product_price_list_is_discount')
                ->input_select_set('data_add',$true_false)
                ->input_select_set('value',array())
                ->input_select_set('allow_empty',false)
                ;
        

        $form->input_select_add()
                ->input_select_set('label','Product')
                ->input_select_set('icon',App_Icon::product())
                ->input_select_set('min_length','1')
                ->input_select_set('id','product_price_list_product')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('ajax_url',$path->ajax_search.'input_select_product_search')
                ;

        $table = $form->form_group_add()->table_add();
        $table->table_set('id','product_price_list_table');
        $table->table_set('class','table fixed-table table-hover');
        $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:50px')));
        $table->table_set('columns',array("name"=>"product_id","label"=>"",'col_attrib'=>array('class'=>'hidden')));
        $table->table_set('columns',array("name"=>"product_img","label"=>"",'col_attrib'=>array('style'=>'text-align:center;width:100px')));
        $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'text-align:left')));
        $table->table_set('columns',array("name"=>"unit_id","label"=>"",'col_attrib'=>array('style'=>'text-align:center;display:none')));            
        $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'text-align:left')));
        $table->table_set('columns',array("name"=>"price","label"=>"",'col_attrib'=>array('style'=>'display:none')));
        $table->table_set('columns',array("name"=>"","label"=>"",'col_attrib'=>array('style'=>'text-align:left;width:30px')));


        $form->textarea_add()->textarea_set('label','Notes')
                ->textarea_set('id','product_price_list_notes')
                ->textarea_set('value','')
                ->textarea_set('attrib',array())       
                ->div_set('id','product_price_list_div_notes')
                ;

        $form->custom_component_add()
                ->src_set('product_price_list/modal_price_list')
            ;


        $form->hr_add()->hr_set('class','');

        $form->button_add()->button_set('value','Submit')
                        ->button_set('id','product_price_list_submit')
                        ->button_set('icon',App_Icon::detail_btn_save())
                    ;

        $form->button_add()->button_set('value','Excel')
                        ->button_set('id','product_price_list_download_excel')
                        ->button_set('class','btn btn-default pull-right')
                        ->button_set('icon',App_Icon::detail_btn_download())
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
            $param['detail_tab'] = '#modal_product_price_list .modal-body';
            $param['view_url'] = '';
            $param['window_scroll'] = '#modal_product_price_list';
        }

        $js = get_instance()->load->view('product_price_list/product_price_list_basic_function_js',$param,TRUE);
        $app->js_set($js);

        return $components;            
    }

    public static function delivery_moq_view_render($app,$pane,$data){
        $my_path = Product_Price_List_Engine::path_get();
        get_instance()->load->helper($my_path->delivery_moq_engine);
        $path = Delivery_MOQ_Engine::path_get();
        get_instance()->load->helper($path->delivery_moq_renderer);

        $id = $data['id'];
        $product_price_list = Product_Price_List_Engine::get($id);
        $pane->form_group_add();
        if($product_price_list->product_price_list_status != 'X'){
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id']
                    ,'product_price_list','delivery_moq_add')){
            $pane->button_add()->button_set('class','primary')
                    ->button_set('value',Lang::get(array('New','Delivery MOQ')))
                    ->button_set('icon','fa fa-plus')
                    ->button_set('attrib',array(
                        'data-toggle'=>"modal" 
                        ,'data-target'=>"#modal_delivery_moq"
                    ))
                    ->button_set('disable_after_click',false)
                    ->button_set('id','delivery_moq_btn_new')
                ;
            }
        }

        $pane->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
        $tbl = $pane->table_add();
        $tbl->table_set('class','table');
        $tbl->table_set('id','delivery_moq_table');
        $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
        $tbl->table_set('columns',array("name"=>"delivery_moq_code","label"=>Lang::get("Code"),'attribute'=>'','col_attrib'=>array(),"is_key"=>true));
        $tbl->table_set('columns',array("name"=>"calculation_type_name","label"=>Lang::get("Calculation Type"),'attribute'=>'','col_attrib'=>array()));
        $tbl->table_set('columns',array("name"=>"total_products","label"=>Lang::get("Total Products"),'attribute'=>'','col_attrib'=>array()));
        $tbl->table_set('columns',array("name"=>"action","label"=>'','attribute'=>'style="width:50px"','col_attrib'=>array()));
        $tbl->table_set('data key','id');

        $db = new DB();
        $q = '
            select distinct NULL row_num
                ,t1.id                    
                ,t1.code delivery_moq_code
                ,0 total_products
                ,t1.calculation_type
            from product_price_list_delivery_moq t1
                inner join product_price_list t2 on t1.product_price_list_id = t2.id                    
            where t2.id = '.$id.' and t1.status>0
            order by t1.calculation_type, t1.code
        ';

        $rs = $db->query_array($q);
        $delete_url = get_instance()->config->base_url().'product_price_list/delivery_moq_delete/';
        for($i = 0;$i<count($rs);$i++){
            $rs[$i]['row_num'] = $i+1;
            $rs[$i]['action'] = '<button class="fa fa-trash-o text-red no-border background-transparent" delete_url="'.$delete_url.$rs[$i]['id'].'/'.$id.'"></button>';
            $calculation_type = $rs[$i]['calculation_type'];

            switch($calculation_type){
                case 'mixed':
                    $rs[$i]['calculation_type_name'] = 'Mixed';
                    $q = '
                        select count(1) total_products
                        from product_price_list_delivery_moq_mixed t1 
                            inner join product_price_list_delivery_moq_mixed_product t2
                                on t1.id = t2.product_price_list_delivery_moq_mixed_id                                
                        where t1.product_price_list_delivery_moq_id = '.$rs[$i]['id'].'
                    ';
                    $rs_total_products = $db->query_array_obj($q);
                    if(count($rs_total_products)>0){
                        $rs[$i]['total_products'] = $rs_total_products[0]->total_products;
                    }
                    break;
                case 'separated':
                    $rs[$i]['calculation_type_name'] = 'Separated';
                    $q = '
                        select count(1) total_products
                        from product_price_list_delivery_moq_separated t1 
                        where t1.product_price_list_delivery_moq_id = '.$rs[$i]['id'].'
                    ';
                    $rs_total_products = $db->query_array_obj($q);
                    if(count($rs_total_products)>0){
                        $rs[$i]['total_products'] = $rs_total_products[0]->total_products;
                    }
                    break;
            }
        }

        $tbl->table_set('data',$rs);

        $modal_delivery_moq = $app->engine->modal_add()
                ->id_set('modal_delivery_moq')
                ->width_set('90%')
                ->header_set(array('title'=>'Delivery Min. Order Qty'))
                ->footer_attr_set(array('style'=>'display:none'))
                ;

        Delivery_MOQ_Renderer::delivery_moq_components_render(
                $app
                ,$modal_delivery_moq
                ,true
            );            

        $param = array('product_price_list_id'=>$product_price_list->id);

        $js = get_instance()->load->view('product_price_list/delivery_moq_view_js',$param,TRUE);
        $app->js_set($js);
    }

    public static function delivery_mop_view_render($app,$pane,$data){
        $my_path = Product_Price_List_Engine::path_get();
        get_instance()->load->helper($my_path->delivery_mop_engine);
        $path = Delivery_MOP_Engine::path_get();
        get_instance()->load->helper($path->delivery_mop_renderer);


        $id = $data['id'];
        $product_price_list = Product_Price_List_Engine::get($id);
        $pane->form_group_add();
        if($product_price_list->product_price_list_status != 'X'){
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id']
                    ,'product_price_list','delivery_mop_add')){
            $pane->button_add()->button_set('class','primary')
                    ->button_set('value',Lang::get(array('New','Delivery MOP')))
                    ->button_set('icon','fa fa-plus')
                    ->button_set('attrib',array(
                        'data-toggle'=>"modal" 
                        ,'data-target'=>"#modal_delivery_mop"
                    ))
                    ->button_set('disable_after_click',false)
                    ->button_set('id','delivery_mop_btn_new')
                ;
            }
        }

        $pane->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
        $tbl = $pane->table_add();
        $tbl->table_set('class','table');
        $tbl->table_set('id','delivery_mop_table');
        $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
        $tbl->table_set('columns',array("name"=>"delivery_mop_code","label"=>Lang::get("Code"),'attribute'=>'','col_attrib'=>array(),"is_key"=>true));
        $tbl->table_set('columns',array("name"=>"calculation_type_name","label"=>Lang::get("Calculation Type"),'attribute'=>'','col_attrib'=>array()));
        $tbl->table_set('columns',array("name"=>"total_products","label"=>Lang::get("Total Products"),'attribute'=>'','col_attrib'=>array()));
        $tbl->table_set('columns',array("name"=>"action","label"=>'','attribute'=>'style="width:50px"','col_attrib'=>array()));
        $tbl->table_set('data key','id');

        $db = new DB();
        $q = '
            select distinct NULL row_num
                ,t1.id                    
                ,t1.code delivery_mop_code
                ,0 total_products
                ,t1.calculation_type
            from product_price_list_delivery_mop t1
                inner join product_price_list t2 on t1.product_price_list_id = t2.id                    
            where t2.id = '.$id.' and t1.status>0
            order by t1.calculation_type, t1.code
        ';

        $rs = $db->query_array($q);
        $delete_url = get_instance()->config->base_url().'product_price_list/delivery_mop_delete/';
        for($i = 0;$i<count($rs);$i++){
            $rs[$i]['row_num'] = $i+1;
            $rs[$i]['action'] = '<button class="fa fa-trash-o text-red no-border background-transparent" delete_url="'.$delete_url.$rs[$i]['id'].'/'.$id.'"></button>';
            $calculation_type = $rs[$i]['calculation_type'];

            switch($calculation_type){
                case 'mixed':
                    $rs[$i]['calculation_type_name'] = 'Mixed';
                    $q = '
                        select count(1) total_products
                        from product_price_list_delivery_mop_mixed t1 
                            inner join product_price_list_delivery_mop_mixed_product t2
                                on t1.id = t2.product_price_list_delivery_mop_mixed_id                                
                        where t1.product_price_list_delivery_mop_id = '.$rs[$i]['id'].'
                    ';
                    $rs_total_products = $db->query_array_obj($q);
                    if(count($rs_total_products)>0){
                        $rs[$i]['total_products'] = $rs_total_products[0]->total_products;
                    }
                    break;
                case 'separated':
                    $rs[$i]['calculation_type_name'] = 'Separated';
                    $q = '
                        select count(1) total_products
                        from product_price_list_delivery_mop_separated t1 
                        where t1.product_price_list_delivery_mop_id = '.$rs[$i]['id'].'
                    ';
                    $rs_total_products = $db->query_array_obj($q);
                    if(count($rs_total_products)>0){
                        $rs[$i]['total_products'] = $rs_total_products[0]->total_products;
                    }
                    break;
            }
        }

        $tbl->table_set('data',$rs);

        $modal_delivery_mop = $app->engine->modal_add()
                ->id_set('modal_delivery_mop')
                ->width_set('75%')
                ->header_set(array('title'=>'Delivery Min. Order Price'))
                ->footer_attr_set(array('style'=>'display:none'))
                ;

        Delivery_MOP_Renderer::delivery_mop_components_render(
                $app
                ,$modal_delivery_mop
                ,true
            );            

        $param = array('product_price_list_id'=>$product_price_list->id);

        $js = get_instance()->load->view('product_price_list/delivery_mop_view_js',$param,TRUE);
        $app->js_set($js);
    }

    public static function delivery_extra_charge_view_render($app,$pane,$data){
        //<editor-fold defaultstate="collapsed">
        $my_path = Product_Price_List_Engine::path_get();
        get_instance()->load->helper($my_path->delivery_extra_charge_engine);
        $path = Delivery_Extra_Charge_Engine::path_get();
        get_instance()->load->helper($path->delivery_extra_charge_renderer);

        $id = $data['id'];
        $product_price_list = Product_Price_List_Engine::get($id);
        $pane->form_group_add();
        if($product_price_list->product_price_list_status != 'X'){
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id']
                    ,'product_price_list','delivery_extra_charge_add')){
            $pane->button_add()->button_set('class','primary')
                    ->button_set('value',Lang::get(array('New','Delivery Extra Charge')))
                    ->button_set('icon','fa fa-plus')
                    ->button_set('attrib',array(
                        'data-toggle'=>"modal" 
                        ,'data-target'=>"#modal_delivery_extra_charge"
                    ))
                    ->button_set('disable_after_click',false)
                    ->button_set('id','delivery_extra_charge_btn_new')
                ;
            }
        }

        $pane->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
        $tbl = $pane->table_add();
        $tbl->table_set('class','table');
        $tbl->table_set('id','delivery_extra_charge_table');
        $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
        $tbl->table_set('columns',array("name"=>"description","label"=>'Description','attribute'=>'style=""','col_attrib'=>array(),'is_key'=>true));
        $tbl->table_set('columns',array("name"=>"min_qty","label"=>"Min. Qty",'attribute'=>'style="text-align:right"','col_attrib'=>array('style'=>'text-align:right')));
        $tbl->table_set('columns',array("name"=>"unit_name","label"=>'Unit','attribute'=>'style=""','col_attrib'=>array()));
        $tbl->table_set('columns',array("name"=>"amount","label"=>'Amount','attribute'=>'style="text-align:right"','col_attrib'=>array('style'=>'text-align:right')));
        $tbl->table_set('columns',array("name"=>"action","label"=>'','attribute'=>'style="width:50px"','col_attrib'=>array()));
        $tbl->table_set('data key','id');

        $db = new DB();
        $q = '
            select t1.*, t3.name unit_name
            from product_price_list_delivery_extra_charge t1
                inner join unit t3 on t3.id = t1.unit_id
            where t1.product_price_list_id = '.$id.' 
                and t1.status>0
            order by t1.min_qty, t1.amount
        ';

        $rs = $db->query_array($q);
        $delete_url = get_instance()->config->base_url().'product_price_list/delivery_extra_charge_delete/';
        for($i = 0;$i<count($rs);$i++){
            $rs[$i]['row_num'] = $i+1;
            $rs[$i]['action'] = '<button class="fa fa-trash-o text-red no-border background-transparent" delete_url="'.$delete_url.$rs[$i]['id'].'/'.$id.'"></button>';
            $rs[$i]['amount'] = Tools::thousand_separator($rs[$i]['amount'],2);
            $rs[$i]['min_qty'] = Tools::thousand_separator($rs[$i]['min_qty'],2);

        }

        $tbl->table_set('data',$rs);

        $modal_delivery_extra_charge = $app->engine->modal_add()
                ->id_set('modal_delivery_extra_charge')
                ->width_set('50%')
                ->header_set(array('title'=>'Delivery Extra Charge'))
                ->footer_attr_set(array('style'=>'display:none'))
                ;

        Delivery_Extra_Charge_Renderer::delivery_extra_charge_components_render(
                $app
                ,$modal_delivery_extra_charge
                ,true
            );            

        $param = array('product_price_list_id'=>$product_price_list->id);

        $js = get_instance()->load->view('product_price_list/delivery_extra_charge_view_js',$param,TRUE);
        $app->js_set($js);
        //</editor-fold>
    }
    
    public static function ppl_extra_charge_view_render($app,$pane,$data){
        //<editor-fold defaultstate="collapsed">
        $my_path = Product_Price_List_Engine::path_get();
        get_instance()->load->helper($my_path->ppl_extra_charge_engine);
        $path = PPL_Extra_Charge_Engine::path_get();
        get_instance()->load->helper($path->ppl_extra_charge_renderer);

        $id = $data['id'];
        $product_price_list = Product_Price_List_Engine::get($id);
        $pane->form_group_add();
        if($product_price_list->product_price_list_status != 'X'){
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id']
                    ,'product_price_list','ppl_extra_charge_add')){
            $pane->button_add()->button_set('class','primary')
                    ->button_set('value',Lang::get('New Extra Charge'))
                    ->button_set('icon','fa fa-plus')
                    ->button_set('attrib',array(
                        'data-toggle'=>"modal" 
                        ,'data-target'=>"#modal_ppl_extra_charge"
                    ))
                    ->button_set('disable_after_click',false)
                    ->button_set('id','ppl_extra_charge_btn_new')
                ;
            }
        }

        $pane->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
        $tbl = $pane->table_add();
        $tbl->table_set('class','table');
        $tbl->table_set('id','ppl_extra_charge_table');
        $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
        $tbl->table_set('columns',array("name"=>"name","label"=>'Name','attribute'=>'style=""','col_attrib'=>array(),'is_key'=>true));
        $tbl->table_set('columns',array("name"=>"group","label"=>'Group','attribute'=>'style="text-align:right"'));
        $tbl->table_set('columns',array("name"=>"amount","label"=>'Amount','attribute'=>'style="text-align:right"','col_attrib'=>array('style'=>'text-align:right')));
        $tbl->table_set('columns',array("name"=>"ppl_extra_charge_status_text","label"=>'Status','attribute'=>'style="text-align:right"'));
        $tbl->table_set('columns',array("name"=>"action","label"=>'','attribute'=>'style="width:50px"','col_attrib'=>array()));
        $tbl->table_set('data key','id');

        $db = new DB();
        $q = '
            select t1.*
            from ppl_extra_charge t1
            where t1.product_price_list_id = '.$id.' 
                and t1.status>0
            order by t1.id asc
        ';

        $rs = $db->query_array($q);
        $delete_url = get_instance()->config->base_url().'product_price_list/ppl_extra_charge_delete/';
        for($i = 0;$i<count($rs);$i++){
            $rs[$i]['row_num'] = $i+1;
            $rs[$i]['action'] = '<button class="fa fa-trash-o text-red no-border background-transparent" delete_url="'.$delete_url.$rs[$i]['id'].'/'.$id.'"></button>';
            $rs[$i]['amount'] = Tools::thousand_separator($rs[$i]['amount'],2);
            $rs[$i]['ppl_extra_charge_status_text'] = SI::status_get('PPL_Extra_Charge_Engine', $rs[$i]['ppl_extra_charge_status']);

        }

        $tbl->table_set('data',$rs);
        
        $modal_extra_charge = $app->engine->modal_add()
                ->id_set('modal_ppl_extra_charge')
                ->width_set('50%')
                ->header_set(array('title'=>'Extra Charge'))
                ->footer_attr_set(array('style'=>'display:none'))
                ;

        PPL_Extra_Charge_Renderer::ppl_extra_charge_components_render(
                $app
                ,$modal_extra_charge
                ,true
            );            

        $param = array('product_price_list_id'=>$product_price_list->id);

        $js = get_instance()->load->view('product_price_list/ppl_extra_charge_view_js',$param,TRUE);
        $app->js_set($js);
        
        
        //</editor-fold>
    }

    public static function product_price_list_status_log_render($app,$form,$data){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product_price_list/product_price_list_engine');
        $config=array(
            'module_name'=>'product_price_list',
            'module_engine'=>'product_price_list_engine',
            'id'=>$data['id']
        );
        SI::form_renderer()->status_log_tab_render($form, $config);
        //</editor-fold>
    }

}
    
?>