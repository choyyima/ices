<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class refill_work_order_Renderer {
        
        public static function modal_refill_work_order_render($app,$modal){
            $modal->header_set(array('title'=>'Sales Receipt','icon'=>App_Icon::refill_work_order()));
            $components = self::refill_work_order_components_render($app, $modal,true);
        }
        
        public static function refill_work_order_render($app,$form,$data,$path,$method){
            //<editor-fold defaultstsate="collapsed">
            get_instance()->load->helper('refill_work_order/refill_work_order_Engine');
            $path = refill_work_order_Engine::path_get();
            $id = $data['id'];
            $components = self::refill_work_order_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            
            $modal_customer = $app->engine->modal_add()->id_set('modal_customer')
                ->width_set('75%')
                ->footer_attr_set(array('style'=>'display:none'));
            get_instance()->load->helper('customer/customer_renderer');
            Customer_Renderer::modal_customer_render($app, $modal_customer);
            $app->js_set('
                <script>
                    customer_init();            
                    customer_bind_event();
                </script>    
            ');
            
            $js = '
                <script>
                    $("#refill_work_order_method").val("'.$method.'");
                    $("#refill_work_order_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    refill_work_order_init();
                    refill_work_order_bind_event();
                    refill_work_order_components_prepare(); 
            ';
            $app->js_set($js);
            //</editor-fold>
        }
        
        public static function refill_work_order_components_render($app,$form,$is_modal){
            //<editor-fold defaultstate="collapsed">
            get_instance()->load->helper('refill_work_order/refill_work_order_Engine');
            $path = refill_work_order_Engine::path_get();            
            $components = array();
            $db = new DB();
            
            $id_prefix = 'refill_work_order';
            
            $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $disabled = array('disable'=>'');
                                    
            $form->input_add()->input_set('id',$id_prefix.'_method')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;            
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
                    ->input_select_set('hide_all',true)                                        
                ;
            
            $form->input_add()->input_set('label',Lang::get('Code'))
                    ->input_set('id',$id_prefix.'_code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->input_set('hide_all',true)
                ;
            
            $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('Work Order','Date')))
                    ->datetimepicker_set('id',$id_prefix.'_refill_work_order_date')
                    ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
                    ->datetimepicker_set('disable_all',true)
                    ->datetimepicker_set('hide_all',true)
                ;
            
            $form->input_select_detail_add()
                    ->input_select_set('icon',App_Icon::customer())
                    ->input_select_set('label',' Customer')
                    ->input_select_set('min_length','1')
                    ->input_select_set('id',$id_prefix.'_customer')
                    ->input_select_set('min_length','1')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url',$path->ajax_search.'/input_select_customer_search/')
                    ->input_select_set('disable_all',true)
                    ->detail_set('rows',array())
                    ->detail_set('id',$id_prefix."_customer_detail")
                    ->detail_set('ajax_url','')
                    ->detail_set('button_new',true)
                    ->detail_set('button_new_id',$id_prefix.'_btn_customer_new')
                    ->detail_set('button_new_class','btn btn-primary btn-sm')
                ;
            
            $form->input_add()->input_set('label',Lang::get('Creator'))
                    ->input_set('id',$id_prefix.'_creator')
                    ->input_set('icon','fa fa-user')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->input_set('value',User_Info::get()['name'])
                ;
            
            $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_refill_work_order_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('is_module_status',true)
                ->input_select_set('hide_all',true)                    
                ;
            
            $form->input_add()->input_set('label',Lang::get('Number of Product'))
                    ->input_set('id',$id_prefix.'_number_of_product')
                    ->input_set('icon',App_Icon::product())
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id',$id_prefix.'_product_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"product_marking_code","label"=>"Marking Code",'col_attrib'=>array('style'=>'text-align:left')));
            $table->table_set('columns',array("name"=>"product_category","label"=>"Category",'col_attrib'=>array('style'=>'text-align:left')));
            $table->table_set('columns',array("name"=>"product_medium","label"=>"Medium",'col_attrib'=>array('style'=>'text-align:left')));
            $table->table_set('columns',array("name"=>"capacity","label"=>"Capacity",'col_attrib'=>array('style'=>'text-align:right;')));
            $table->table_set('columns',array("name"=>"capacity_unit","label"=>"Cap. Unit",'col_attrib'=>array('style'=>'text-align:left;')));            
            $table->table_set('columns',array("name"=>"price","label"=>"Estimated Amount",'col_attrib'=>array('style'=>'text-align:right;')));
            $table->table_set('columns',array("name"=>"staff_checker","label"=>"ttd Staff",'col_attrib'=>array('style'=>'text-align:left;width:100px')));
            $table->table_set('hide_all',true);
            
            $form->input_add()->input_set('label','Total'.' '.Lang::get(array('Fee','Estimation')))
                    ->input_set('id',$id_prefix.'_total_estimated_amount')
                    ->input_set('icon',App_Icon::money())
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $form->input_add()->input_set('label',Lang::get('Total Deposit Amount'))
                    ->input_set('id',$id_prefix.'_total_deposit_amount')
                    ->input_set('icon',App_Icon::money())
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','refill_work_order_notes')
                    ->textarea_set('value','')
                    ->textarea_set('attrib',array())       
                    ->textarea_set('disable_all',true)
                    
                    ;
                        
            $form->hr_add()->hr_set('class','');
            
            $form->button_add()->button_set('value','Submit')
                            ->button_set('id',$id_prefix.'_submit')
                            ->button_set('icon',App_Icon::detail_btn_save())
                        ;
            $form->button_add()
                    ->button_set('class','btn btn-default pull-right')
                    ->button_set('icon',APP_ICON::printer())
                    ->button_set('value','PRINT')
                    ->button_set('id',$id_prefix.'_btn_print')
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
                $param['detail_tab'] = '#modal_'.$id_prefix.' .modal-body';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_'.$id_prefix;
            }
            
            $js = get_instance()->load->view('refill_work_order/'.$id_prefix.'_basic_function_js',$param,TRUE);
            $app->js_set($js);
            return $components;
            //</editor-fold>
        }
        
        public static function refill_work_order_status_log_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            $config=array(
                'module_name'=>'refill_work_order',
                'module_engine'=>'refill_work_order_Engine',
                'id'=>$data['id']
            );
            SI::form_renderer()->status_log_tab_render($form, $config);
            //</editor-fold>
        }
        
        public static function refill_work_order_customer_deposit_view_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            get_instance()->load->helper('customer_deposit/customer_deposit_engine');
            get_instance()->load->helper('customer_deposit/customer_deposit_renderer');
            $id = $data['id'];
            $db = new DB();
            $rs = $db->fast_get('refill_work_order',array('id'=>$id));
            if(count($rs)>0) {
                $refill_work_order = $rs[0];            
                $form->form_group_add();
                if($refill_work_order['refill_work_order_status'] != 'X'){
                    if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'customer_deposit','add')){
                    $form->button_add()->button_set('class','primary')
                            ->button_set('value',Lang::get(array(array('val'=>'New','grammar'=>'adj'),array('val'=>'Customer Deposit'))))
                            ->button_set('icon','fa fa-plus')
                            ->button_set('attrib',array(
                                'data-toggle'=>"modal" 
                                ,'data-target'=>"#modal_customer_deposit"
                            ))
                            ->button_set('disable_after_click',false)
                            ->button_set('id','customer_deposit_new')
                        ;
                    }
                }
                $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
                $tbl = $form->table_add();
                $tbl->table_set('class','table');
                $tbl->table_set('id','customer_deposit_view_table');
                $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
                $tbl->table_set('columns',array("name"=>"code","label"=>"Customer Deposit Code",'attribute'=>'style="text-align:left"','col_attrib'=>array('style'=>'text-align:left'),"is_key"=>true));
                $tbl->table_set('columns',array("name"=>"amount","label"=>"Amount (Rp.)",'attribute'=>'style="text-align:right"','col_attrib'=>array('style'=>'text-align:right')));
                $tbl->table_set('columns',array("name"=>"outstanding_amount","label"=>"Outstanding Amount (Rp.)",'attribute'=>'style="text-align:right"','col_attrib'=>array('style'=>'text-align:right')));
                $tbl->table_set('columns',array("name"=>"customer_deposit_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('data key','id');

                $q = '
                    select distinct NULL row_num
                        ,cd.*
                        
                    from customer_deposit cd
                        inner join rwo_cd on cd.id = rwo_cd.customer_deposit_id
                    where rwo_cd.refill_work_order_id = '.$id.' 
                    order by cd.id desc
                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['row_num'] = $i+1;
                    $rs[$i]['amount'] = Tools::thousand_separator($rs[$i]['amount'],2,true);
                    $rs[$i]['outstanding_amount'] = Tools::thousand_separator($rs[$i]['outstanding_amount']);
                    $rs[$i]['customer_deposit_status_text'] = SI::get_status_attr(
                        SI::status_get('customer_deposit_engine', $rs[$i]['customer_deposit_status'])['label']
                    );
                }
                $tbl->table_set('data',$rs);                
                
                $modal_customer_deposit = $app->engine->modal_add()->id_set('modal_customer_deposit')->width_set('75%')
                        ->footer_attr_set(array('style'=>'display:none'));

                $customer_deposit_data = array(
                    'refill_work_order'=>array(
                        'id'=>$refill_work_order['id']
                    )                
                );
                $customer_deposit_data = json_decode(json_encode($customer_deposit_data));

                Customer_Deposit_Renderer::modal_customer_deposit_render(
                        $app
                        ,$modal_customer_deposit
                    );


                $param = array(
                    'index_url'=>$path->index
                    ,'ajax_search'=>$path->ajax_search
                    ,'reference_id'=>$refill_work_order['id']
                    ,'reference_text'=>$refill_work_order['code']
                    ,'reference_type'=>'refill_work_order'
                );
                
                $js = get_instance()->load->view('refill_work_order/customer_deposit_js',$param,TRUE);
                $app->js_set($js);
                
            }
            //</editor-fold>
        }
    }
    
?>