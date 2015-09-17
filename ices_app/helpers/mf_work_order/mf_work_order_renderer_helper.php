<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mf_Work_Order_Renderer {

    public static function modal_mf_work_order_render($app,$modal){
        $modal->header_set(array('title'=>'Expedition','icon'=>App_Icon::mf_work_order()));
        $components = self::mf_work_order_components_render($app, $modal,true);


    }

    public static function mf_work_order_render($app,$form,$data,$path,$method){
        get_instance()->load->helper('mf_work_order/mf_work_order_engine');
        $path = Mf_Work_Order_Engine::path_get();
        $id = $data['id'];
        $components = self::mf_work_order_components_render($app, $form,false);
        $back_href = $path->index;

        $form->button_add()->button_set('value','BACK')
            ->button_set('icon',App_Icon::btn_back())
            ->button_set('href',$back_href)
            ->button_set('class','btn btn-default')
            ;

        $js = '
            <script>
                $("#mf_work_order_method").val("'.$method.'");
                $("#mf_work_order_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                mf_work_order_init();
                mf_work_order_bind_event();
                mf_work_order_components_prepare(); 
        ';
        $app->js_set($js);

    }

    public static function mf_work_order_components_render($app,$form,$is_modal){
        get_instance()->load->helper('product/product_engine');
        get_instance()->load->helper('mf_work_order/mf_work_order_engine');
        $path = Mf_Work_Order_Engine::path_get();            
        $components = array();
        $db = new DB();

        $id_prefix = Mf_Work_Order_Engine::$prefix_id;

        $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                ->input_set('hide',true)
                ->input_set('value','')
                ;


        $form->input_add()->input_set('id',$id_prefix.'_method')
                ->input_set('hide',true)
                ->input_set('value','')
                ;            

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
                    ->input_select_set('hide_all',true)
                    ->input_select_set('allow_empty',false)
                ;
        
        $form->input_add()->input_set('label',Lang::get('Code'))
                ->input_set('id',$id_prefix.'_code')
                ->input_set('icon','fa fa-info')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
                ->input_set('attrib',array('style'=>'font-weight:bold'))

            ;


        $mf_work_order_type_list = array();
        $raw_list = SI::type_list_get('Mf_Work_Order_Engine');
        foreach($raw_list as $idx=>$row){
            $mf_work_order_type_list[] = array('id'=>$row['val'],'data'=>SI::html_tag('strong',$row['label']));
        }

        $form->input_select_add()
            ->input_select_set('label',Lang::get('Type'))
            ->input_select_set('icon',App_Icon::info())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_type')
            ->input_select_set('data_add',$mf_work_order_type_list)
            ->input_select_set('allow_empty',false)
            ->input_select_set('value',array())
            ->input_select_set('disable_all',true)
        ;
        
        $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('Manufacturing Work Order','Date')))
                    ->datetimepicker_set('id',$id_prefix.'_mf_work_order_date')
                    ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
                    ->datetimepicker_set('disable_all',true)
                    ->datetimepicker_set('hide_all',true)
                ;
        
        $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('Start','Date','Plan')))
            ->datetimepicker_set('id',$id_prefix.'_start_date_plan')
            ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
            ->datetimepicker_set('disable_all',true)
            ->datetimepicker_set('hide_all',true)
        ;
        
        $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('End','Date','Plan')))
                    ->datetimepicker_set('id',$id_prefix.'_end_date_plan')
                    ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
                    ->datetimepicker_set('disable_all',true)
                    ->datetimepicker_set('hide_all',true)
                ;
        
        $components[$id_prefix.'_status'] = $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-user')
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_mf_work_order_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                 ->input_select_set('hide_all',true)
                ->input_select_set('is_module_status',true)
                ;

        $form->input_add()->input_set('label',Lang::get('Approver'))
            ->input_set('id',$id_prefix.'_approver')
            ->input_set('icon','fa fa-user')
            ->input_set('hide_all',true)
            ->input_set('disable_all',true)
        ;
        
        $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('Approved','Date')))
            ->datetimepicker_set('id',$id_prefix.'_approved_date')
            ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
            ->datetimepicker_set('disable_all',true)
            ->datetimepicker_set('hide_all',true)
        ;
        
        $form->input_add()->input_set('label',Lang::get('Rejector'))
            ->input_set('id',$id_prefix.'_rejector')
            ->input_set('icon','fa fa-user')
            ->input_set('hide_all',true)
            ->input_set('disable_all',true)
        ;
        
        $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('Rejected','Date')))
            ->datetimepicker_set('id',$id_prefix.'_rejected_date')
            ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
            ->datetimepicker_set('disable_all',true)
            ->datetimepicker_set('hide_all',true)
        ;
        
        
        $tbl_res_product = $form->table_input_add();
        $tbl_res_product->table_input_set('id',$id_prefix.'_ordered_product_table')
            ->label_set('value',Lang::get(array('Ordered','Product')))
            ->table_input_set('columns',array(
                'col_name'=>'product_img'
                ,'th'=>array('val'=>'','class'=>'product-img')
                ,'td'=>array('val'=>Product_Engine::img_get(null),'tag'=>'','class'=>'','attr'=>array('original'=>'')
                )
            )) 
            ->table_input_set('columns',array(
                'col_name'=>'product_type'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                )
            ))   
            ->table_input_set('columns',array(
                'col_name'=>'product_id'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','attr'=>array(),'visible'=>false
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'product'
                ,'th'=>array('val'=>'Product')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'','attr'=>array('original'=>'')
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'unit_id'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','attr'=>array(),'visible'=>false
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'unit'
                ,'th'=>array('val'=>'Unit','col_style'=>'width:200px')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'','attr'=>array('original'=>''))
            ))
            ->table_input_set('columns',array(
                'col_name'=>'qty'
                ,'th'=>array('val'=>'Qty','col_style'=>'width:200px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))
            ->table_input_set('columns',array(
                'col_name'=>'outstanding_qty'
                ,'th'=>array('val'=>Lang::get('Manufacturing Outstanding Qty'),'col_style'=>'width:200px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'form-control','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))
             ->table_input_set('columns',array(
                'col_name'=>'bom_id'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'bom'
                ,'th'=>array('val'=>'Bill of Material','col_style'=>'width:200px')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'','attr'=>array('original'=>''))
            ))
            ->main_div_set('hide_all',true)
            ;

        $components['notes'] = $form->textarea_add()->textarea_set('label','Notes')
                ->textarea_set('id',$id_prefix.'_notes')
                ->textarea_set('value','')
                ->textarea_set('hide_all',true)
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
            $param['detail_tab'] = '#modal_mf_work_order .modal-body';
            $param['view_url'] = '';
            $param['window_scroll'] = '#modal_mf_work_order';
        }



        $js = get_instance()->load->view('mf_work_order/mf_work_order_ordered_product_js',$param,TRUE);
        $app->js_set($js);

        $js = get_instance()->load->view('mf_work_order/mf_work_order_basic_function_js',$param,TRUE);
        $app->js_set($js);

        return $components;

    }

    public static function mf_work_order_status_log_render($app,$form,$data,$path){
        //<editor-fold defaultstate="collapsed">
        $config=array(
            'module_name'=>'mf_work_order',
            'module_engine'=>'mf_work_order_engine',
            'id'=>$data['id']
        );
        SI::form_renderer()->status_log_tab_render($form, $config);
        //</editor-fold>
    }

    public static function mf_work_process_view_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            get_instance()->load->helper('mf_work_process/mf_work_process_engine');
            get_instance()->load->helper('mf_work_process/mf_work_process_renderer');
            $id = $data['id'];
            $db = new DB();
            $rs = $db->fast_get('mf_work_order',array('id'=>$id));
            if(count($rs)>0) {
                $mf_work_order = $rs[0];            
                $form->form_group_add();
                if($mf_work_order['mf_work_order_status'] != 'X' && $mf_work_order['mf_work_order_status'] === 'approved'){
                    if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'mf_work_process','add')){
                    $form->button_add()->button_set('class','primary')
                            ->button_set('value',Lang::get(array(array('val'=>'New','grammar'=>'adj'),array('val'=>'Manufacturing Work Process'))))
                            ->button_set('icon','fa fa-plus')
                            ->button_set('attrib',array(
                                'data-toggle'=>"modal" 
                                ,'data-target'=>"#modal_mf_work_process"
                            ))
                            ->button_set('disable_after_click',false)
                            ->button_set('id','mf_work_process_new')
                        ;
                    }
                }
                $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
                $tbl = $form->table_add();
                $tbl->table_set('class','table');
                $tbl->table_set('id','mf_work_process_view_table');
                $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
                $tbl->table_set('columns',array("name"=>"code","label"=>Lang::get(array("Manufacturing Work Process",'Code')),'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
                $tbl->table_set('columns',array("name"=>"mf_work_process_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('data key','id');

                $q = '
                    select distinct NULL row_num
                        ,mfwp.*                        
                    from mf_work_process mfwp
                    where mfwp.reference_id = '.$id.' 
                    order by mfwp.id desc
                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['row_num'] = $i+1;
                    $rs[$i]['mf_work_process_status_text'] = SI::get_status_attr(
                        SI::status_get('mf_work_process_engine', $rs[$i]['mf_work_process_status'])['label']
                    );
                }
                
                $tbl->table_set('data',$rs);
                
                $modal_mf_work_process = $app->engine->modal_add()->id_set('modal_mf_work_process')->width_set('75%')
                        ->footer_attr_set(array('style'=>'display:none'));

                $mf_work_process_data = array(
                    'mf_work_order'=>array(
                        'id'=>$mf_work_order['id']
                    )                
                );
                $mf_work_process_data = json_decode(json_encode($mf_work_process_data));

                Mf_Work_Process_Renderer::modal_mf_work_process_render(
                        $app
                        ,$modal_mf_work_process
                    );


                $param = array(
                    'index_url'=>$path->index
                    ,'ajax_search'=>$path->ajax_search
                    ,'reference_id'=>$mf_work_order['id']
                    ,'reference_text'=>$mf_work_order['code']
                    ,'reference_type'=>$mf_work_order['mf_work_order_type']
                );
                
                $js = get_instance()->load->view('mf_work_order/mf_work_process_js',$param,TRUE);
                $app->js_set($js);
                
            }
            //</editor-fold>
        }
    
}
    
?>