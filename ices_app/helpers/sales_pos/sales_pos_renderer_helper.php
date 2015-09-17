<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Sales_Pos_Renderer {
        
        public static function modal_sales_pos_render($app,$modal){
            $modal->header_set(array('title'=>'Receive Product','icon'=>App_Icon::info()));
            $components = self::sales_pos_components_render($app, $modal,true);
            
            
        }
        
        public static function sales_pos_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('sales_pos/sales_pos_engine');
            get_instance()->load->helper('delivery_order_final/delivery_order_final_engine');
            $path = Sales_Pos_Engine::path_get();
            $id = $data['id'];
            
            $modal_customer = $app->engine->modal_add()->id_set('modal_customer')
                ->width_set('75%')
                ->footer_attr_set(array('style'=>'display:none'));
            get_instance()->load->helper('customer/customer_renderer');
            Customer_Renderer::modal_customer_render($app, $modal_customer);
            
            $modal_delivery_order_final = $app->engine->modal_add()->id_set('modal_dof')
                ->width_set('95%')
                ->footer_attr_set(array('style'=>'display:none'));
            get_instance()->load->helper('delivery_order_final/delivery_order_final_renderer');
            Delivery_Order_Final_Renderer::modal_delivery_order_final_render($app, $modal_delivery_order_final);
            
            $dof_param = array();
            $js = get_instance()->load->view('sales_pos/dof_js',$dof_param,TRUE);
            $app->js_set($js);
            
            $modal_intake_final = $app->engine->modal_add()->id_set('modal_intake_final')
                ->width_set('95%')
                ->footer_attr_set(array('style'=>'display:none'));
            get_instance()->load->helper('intake_final/intake_final_renderer');
            Intake_Final_Renderer::modal_intake_final_render($app, $modal_intake_final);
            
            $dof_param = array();
            $js = get_instance()->load->view('sales_pos/intake_final_js',$dof_param,TRUE);
            $app->js_set($js);
            
            
            $components = self::sales_pos_components_render($app, $form,false);
            $back_href = $path->index;
            
            $js = '
                <script>
                    $("#sales_pos_method").val("'.$method.'");
                    $("#sales_pos_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    sales_pos_init();
                    sales_pos_bind_event();
                    sales_pos_components_prepare(); 
            ';
            $app->js_set($js);
            
            $js = get_instance()->load->view('sales_pos/sales_pos_js',array(),TRUE);
            $app->js_set($js);
            
            
            
        }
        
        public static function sales_pos_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('sales_pos/sales_pos_engine');
            get_instance()->load->helper('bos_bank_account/bos_bank_account_data_support');
            $path = Sales_Pos_Engine::path_get();            
            $components = array();
            $db = new DB();            
            
            $div_pos = $form->div_add()->div_set('class','pos');

            $div_pos->input_add()->input_set('id','sales_pos_id')
                    ->input_set('hide','true');
            $div_pos->input_add()->input_set('id','sales_pos_method')
                    ->input_set('hide','true');
            
            $div_content = $div_pos->div_add()->div_set('class','form-group');
            
            $right_div = $div_content->div_add()
                    ->div_set('class','col-md-3')
                    ->div_set('id','sales_pos_div_right')
                    ->div_add()
                    ->div_set('class','box')
                    ->div_set('attrib',array('style'=>''))
                    ;
            
            $left_div = $div_content
                    ->div_add()
                    ->div_set('id','sales_pos_div_left')
                    ->div_set('class','col-md-9')
                    ;
            
            $controller_div = $div_pos->div_add()->div_set('class','col-md-9');
            $controller_div->button_add()
                    ->button_set('class','btn btn-default')
                    ->button_set('icon',APP_ICON::btn_back())
                    ->button_set('value','BACK')
                    ->button_set('id','sales_pos_btn_back')
                    ->button_set('href',Sales_Pos_Engine::path_get()->index)
            ;
            $controller_div->button_add()
                    ->button_set('class','btn btn-default')
                    ->button_set('icon','fa fa-arrow-left')
                    ->button_set('value','PREV')
                    ->button_set('id','sales_pos_btn_prev')
            ;
            
            $controller_div->button_add()
                    ->button_set('class','btn btn-default')
                    ->button_set('icon','fa fa-arrow-right')
                    ->button_set('value','NEXT')
                    ->button_set('id','sales_pos_btn_next')
            ;
            $controller_div->button_add()
                    ->button_set('class','btn btn-primary')
                    ->button_set('icon',APP_ICON::btn_save())
                    ->button_set('value','SUBMIT')
                    ->button_set('id','sales_pos_submit')
            ;
           
            
            $controller_div->button_add()
                    ->button_set('class','btn btn-default pull-right')
                    ->button_set('icon',APP_ICON::printer())
                    ->button_set('value','PRINT')
                    ->button_set('id','sales_pos_btn_print')
                    ->button_set('style','margin-left:5px')
            ;
            
            $controller_div->button_add()
                    ->button_set('class','btn btn-default pull-right')
                    ->button_set('icon',APP_ICON::mail())
                    ->button_set('value','Mail')
                    ->button_set('id','sales_pos_mail')
                    ->button_set('style','margin-left:5px')
            ;
            
            $controller_div->button_add()
                    ->button_set('class','btn btn-default pull-right')
                    ->button_set('icon',APP_ICON::btn_add())
                    ->button_set('value',Lang::get(array('New','Point of Sale')))
                    ->button_set('id','sales_pos_btn_sales_pos_add')
                    ->button_set('href',$path->index.'add');
            ;
            
            $db = new DB();
            $store_list = array();
            $q = 'select id id, name data from store where status>0';            
            $store_list = $db->query_array($q);
            
            $init_div = $left_div->div_add()
                ->div_set('attrib',array('routing_section'=>'init'))
                ->div_set('class','pos-section')
            ;
            
            $init_div->input_select_add()
                ->input_select_set('label','Store')
                ->input_select_set('icon',App_Icon::store())                    
                ->input_select_set('min_length','0')
                ->input_select_set('id','sales_pos_store')
                ->input_select_set('data_add',$store_list)
                ->input_select_set('value',array())
                ->input_select_set('disable_all',true)
            ;
            
            $init_div->input_add()->input_set('label',Lang::get('Code'))
                    ->input_set('id','sales_pos_code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $init_div->input_add()->input_set('label',Lang::get('Reference Type'))
                    ->input_set('id','sales_pos_reference_type')
                    ->input_set('icon','fa fa-info')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $init_div->input_select_detail_add()
                    ->input_select_set('label','Reference')
                    ->input_select_set('icon','fa fa-info')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','sales_pos_reference_id')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('hide_all',true)
                    ->input_select_set('disable_all',true)
                    ->detail_set('id',"sales_pos_reference_id_detail")
                    ;
            
            $init_div->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-info')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','sales_pos_sales_pos_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('hide_all',true)
                    ->input_select_set('disable_all',true)
                    ;
            
            $init_div->textarea_add()->textarea_set('label',Lang::get('Cencellation Reason'))
                    ->textarea_set('id','sales_pos_cancellation_reason')
                    ->textarea_set('value','')
                    ->div_set('id','sales_pos_div_cancellation_reason')  
                    ->textarea_set('hide_all',true)
                    ->textarea_set('disable',true)
                    ;
            
            $customer_detail = array(
                array('name'=>'code','label'=>'Code')
                ,array('name'=>'name','label'=>'Name')
                ,array('name'=>'phone','label'=>'Phone')
                ,array('name'=>'bb_pin','label'=>'BB Pin')
                ,array('name'=>'email','label'=>'Email')
               
                ,array('name'=>'is_sales_receipt_outstanding','label'=>'Sales Receipt Outstanding')
            );
            
            $init_div->input_select_add()
                    ->input_select_set('label','Sales Inquiry By')
                    ->input_select_set('icon',App_Icon::product_price_list())
                    ->input_select_set('min_length','1')
                    ->input_select_set('id','sales_pos_sales_inquiry_by')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url','')
                    ->input_select_set('disable_all',true)
                    
                ;
            
            $init_div->input_select_detail_add()
                    ->input_select_set('icon',App_Icon::customer())
                    ->input_select_set('label',' Customer')
                    ->input_select_set('min_length','1')
                    ->input_select_set('id','sales_pos_customer')
                    ->input_select_set('min_length','1')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url',$path->ajax_search.'/input_select_customer_search/')
                    ->input_select_set('disable_all',true)
                    ->detail_set('rows',$customer_detail)
                    ->detail_set('id',"sales_pos_customer_detail")
                    ->detail_set('ajax_url','')
                    ->detail_set('button_new',true)
                    ->detail_set('button_new_id','sales_pos_btn_customer_new')
                    ->detail_set('button_new_class','btn btn-primary btn-sm')
                    //->detail_set('button_edit',true)
                    //->detail_set('button_edit_id','sales_pos_btn_customer_edit')
                    //->detail_set('button_edit_class','btn btn-primary btn-sm')
                ;
            
            $init_div->input_select_add()
                    ->input_select_set('label','Price List')
                    ->input_select_set('icon',App_Icon::product_price_list())
                    ->input_select_set('min_length','1')
                    ->input_select_set('id','sales_pos_price_list')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url',$path->ajax_search.'/input_select_price_list_search/')
                    ->input_select_set('disable_all',true)
                ;

            $product_div = $left_div->div_add()
                ->div_set('attrib',array('routing_section'=>'product'))
                ->div_set('class','pos-section')
            ;
            
            $product_div->input_select_add()
                    ->input_select_set('label','Approval')
                    ->input_select_set('icon',App_Icon::product_price_list())
                    ->input_select_set('min_length','1')
                    ->input_select_set('id','sales_pos_approval')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url',$path->ajax_search.'/input_select_approval_search/') 
                    ->input_select_set('disable_all',true)
                ;
            
            $product_div->input_select_add()
                    ->input_select_set('label','Expedition')
                    ->input_select_set('icon',App_Icon::expedition())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','sales_pos_expedition')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url',$path->ajax_search.'/input_select_expedition_search/')                    
                    ->input_select_set('disable_all',true)
                ;
            
            $right_div->div_add()
                    ->div_set('class','box-header text-center')
                    ->label_add()
                    ->label_set('value','Summary')
                    ->label_set('attrib',array('style'=>'font-size:200%'))
                    ;
            
            $product_div->custom_component_add()->src_set('sales_pos/view/sales_pos_product_view');
            
            
            
            //$extra_charge_section_div->custom_component_add()->src_set('sales_pos/view/sales_pos_extra_charge_view');
            
            $payment_div = $left_div->div_add()
                ->div_set('attrib',array('routing_section'=>'payment'))
                ->div_set('class','pos-section')
            ;
            
            
            
            $payment_div->custom_component_add()->src_set('sales_pos/view/sales_pos_payment_view');
            
            
            $movement_div = $left_div->div_add()
                ->div_set('attrib',array('routing_section'=>'movement'))
                ->div_set('class','pos-section')
            ;
            $movement_div->custom_component_add()->src_set('sales_pos/view/sales_pos_movement_view')
                    
                    ;
            
            $cd_cb_div = $left_div->div_add()
                ->div_set('attrib',array('routing_section'=>'cd_cb'))
                ->div_set('class','pos-section')
            ;
            
            $cd_cb_div->custom_component_add()->src_set('sales_pos/view/sales_pos_cd_cb_view')
                    
                    ;
            
            $summary_body  = $right_div->div_add()
                    ->div_set('class','box-body')
                    ;
            
            $div_summary_datetime = $summary_body->div_add()->div_set('class','text-right form-group');
            $div_summary_datetime->label_add()->label_set('value',Date('F d, Y'));
            $div_summary_datetime->label_add()->label_set('value',Date('H:i:s'))->label_set('id','sales_pos_time');
            
            $div_code = $summary_body->div_add()->div_set('attrib',array('style'=>'font-size:12px'))->div_set('class','form-group');;
            $div_code->label_add()->label_set('value','Code ');
            $div_code->label_add()->label_set('value','[ AUTO GENERATE ]')
                    ->label_set('id','sales_pos_summary_code')
                    ->label_set('class','pull-right');
            
            $div_customer = $summary_body->div_add()->div_set('attrib',array('style'=>'font-size:12px'))->div_set('class','form-group');;
            $div_customer->label_add()->label_set('value','Customer ');
            $div_customer->label_add()->label_set('value','')
                    ->label_set('id','sales_pos_summary_customer')
                    ->label_set('class','pull-right');
            
            
            $div_price_list = $summary_body->div_add()->div_set('attrib',array('style'=>'font-size:12px'))->div_set('class','form-group');;;
            $div_price_list->label_add()->label_set('value','Price List ');
            $div_price_list->label_add()->label_set('value','')
                    ->label_set('id','sales_pos_summary_price_list')
                    ->label_set('class','pull-right');
            
            $div_grand_total = $summary_body->div_add()->div_set('attrib',array('style'=>'font-size:12px'))->div_set('class','form-group');;;
            $div_grand_total->label_add()->label_set('value','Product Grand Total ('.Tools::currency_get().')');
            $div_grand_total->label_add()->label_set('value','0.00')->label_set('id','sales_pos_summary_product_grand_total')->label_set('class','pull-right');;
            
            
            $div_payment_total = $summary_body->div_add()->div_set('attrib',array('style'=>'font-size:12px'))->div_set('class','form-group');
            $div_payment_total->label_add()->label_set('value','Payment Grand Total ('.Tools::currency_get().')');
            $div_payment_total->label_add()->label_set('value','0.00')->label_set('id','sales_pos_summary_payment_grand_total')->label_set('class','pull-right');;
            
            $div_change_amount = $summary_body->div_add()->div_set('attrib',array('style'=>'font-size:12px'))->div_set('class','form-group');
            $div_change_amount->label_add()->label_set('value','Change Amount ('.Tools::currency_get().')');
            $div_change_amount->label_add()->label_set('value','0.00')->label_set('id','sales_pos_summary_change_amount')->label_set('class','pull-right');;
            
            $div_outstanding_amount = $summary_body->div_add()->div_set('attrib',array('style'=>'font-size:12px'))->div_set('class','form-group');
            $div_outstanding_amount->label_add()->label_set('value','Outstanding Amount ('.Tools::currency_get().')');
            $div_outstanding_amount->label_add()->label_set('value','0.00')->label_set('id','sales_pos_summary_outstanding_amount')->label_set('class','pull-right');;
            
            $bos_bank_account_list = array();
            $bos_bank_account_db = Bos_Bank_Account_Data_Support::bos_bank_account_list_get(array('bos_bank_account_status'=>'active'));
            foreach($bos_bank_account_db as $idx=>$row){
                $bos_bank_account_list[] = array(
                    'id'=>$row['id'],
                    'text'=>$row['code']
                );
            }
            
            $param = array(
                'ajax_url'=>$path->index.'ajax_search/'
                ,'index_url'=>$path->index
                ,'detail_tab'=>'#sales_pos'
                ,'view_url'=>$path->index.'view/'
                ,'window_scroll'=>'body'
                ,'data_support_url'=>$path->index.'data_support/'
                ,'common_ajax_listener'=>get_instance()->config->base_url().'common_ajax_listener/'
                ,'bos_bank_account_list'=>$bos_bank_account_list
                ,'mail_message'=>Email_Message::template_get('attachment_find')
            );
            
            if($is_modal){
                $param['detail_tab'] = '#modal_sales_pos .modal-body';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_sales_pos';
                
            }
            
            $form->custom_component_add()
                    ->src_set('sales_pos/view/modal_extra_charge_view')
                ;
            
            
            $js = get_instance()->load->view('sales_pos/sales_pos_basic_function_js',$param,TRUE);
            $app->js_set($js);
            
            $js = get_instance()->load->view('sales_pos/sales_pos_summary_js',$param,TRUE);
            $app->js_set($js);
            
            $js = get_instance()->load->view('sales_pos/sales_pos_init_section_js',$param,TRUE);
            $app->js_set($js);
            
            $js = get_instance()->load->view('sales_pos/sales_pos_product_section_js',$param,TRUE);
            $app->js_set($js);
            
            $js = get_instance()->load->view('sales_pos/sales_pos_payment_section_js',$param,TRUE);
            $app->js_set($js);
            
            $js = get_instance()->load->view('sales_pos/sales_pos_cd_cb_section_js',$param,TRUE);
            $app->js_set($js);
            
            $js = get_instance()->load->view('sales_pos/sales_pos_movement_section_js',array(),TRUE);
            $app->js_set($js);
            
            return $components;
            
        }
        
        public static function sales_pos_status_log_render($app,$form,$data,$path){
            get_instance()->load->helper('sales_pos/sales_pos_engine');
            $path = Receive_Product_Engine::path_get();
            get_instance()->load->helper($path->sales_pos_purchase_invoice_engine);
            get_instance()->load->helper($path->sales_pos_rma_engine);
            
            $id = $data['id'];
            $db = new DB();
            $q = '
                select null row_num
                    ,t1.moddate
                    ,t1.sales_pos_status
                    ,t2.name user_name
                    ,case when t3.id is null then 0 else 1 end is_purchase_invoice
                    ,case when t4.id is null then 0 else 1 end is_rma
                from sales_pos_status_log t1
                    inner join user_login t2 on t1.modid = t2.id
                    left outer join purchase_invoice_sales_pos t3 
                        on t3.sales_pos_id = t1.sales_pos_id
                    left outer join rma_sales_pos t4
                        on t4.sales_pos_id = t1.sales_pos_id
                where t1.sales_pos_id = '.$id.'
                    order by moddate asc
            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $sales_pos_status_name = '';
                if($rs[$i]['is_purchase_invoice'] === '1'){
                    $sales_pos_status_name = SI::get_status_attr(
                        Receive_Product_Purchase_Invoice_Engine::sales_pos_purchase_invoice_status_get(
                            $rs[$i]['sales_pos_status']
                        )['label']
                    );
                }
                if($rs[$i]['is_rma'] === '1'){
                    $sales_pos_status_name = SI::get_status_attr(
                        Receive_Product_RMA_Engine::sales_pos_rma_status_get(
                            $rs[$i]['sales_pos_status']
                        )['label']
                    );
                }
                
                $rs[$i]['sales_pos_status_name'] = $sales_pos_status_name;
                        
                
            }
            $sales_pos_status_log = $rs;
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','sales_pos_sales_pos_status_log_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('columns',array("name"=>"sales_pos_status_name","label"=>"Status",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('data',$sales_pos_status_log);
        }
        
    }
    
?>