<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Sales_Prospect_Renderer {
        
        public static function modal_sales_prospect_render($app,$modal){
            $modal->header_set(array('title'=>'Receive Product','icon'=>App_Icon::info()));
            $components = self::sales_prospect_components_render($app, $modal,true);
            
            
        }
        
        public static function sales_prospect_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('sales_prospect/sales_prospect_engine');
            $path = Sales_Prospect_Engine::path_get();
            $id = $data['id'];
            
            $modal_customer = $app->engine->modal_add()->id_set('modal_customer')
                ->width_set('75%')
                ->footer_attr_set(array('style'=>'display:none'));
            get_instance()->load->helper('customer/customer_renderer');
            Customer_Renderer::modal_customer_render($app, $modal_customer);
            
            $components = self::sales_prospect_components_render($app, $form,false);
            $back_href = $path->index;
            /*
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;
            */
            $js = '
                <script>
                    $("#sales_prospect_method").val("'.$method.'");
                    $("#sales_prospect_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    sales_prospect_init();
                    sales_prospect_bind_event();
                    sales_prospect_components_prepare(); 
            ';
            $app->js_set($js);
            
            $js = get_instance()->load->view('sales_prospect/sales_prospect_js',array(),TRUE);
            $app->js_set($js);
            
        }
        
        public static function sales_prospect_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('sales_prospect/sales_prospect_engine');
            $path = Sales_Prospect_Engine::path_get();            
            $components = array();
            $db = new DB();            
            
            $div_pos = $form->div_add()->div_set('class','pos');
            
            
            $div_pos->input_add()->input_set('id','sales_prospect_id')
                    ->input_set('hide','true');
            $div_pos->input_add()->input_set('id','sales_prospect_id')
                    ->input_set('hide','true');
            $div_pos->input_add()->input_set('id','sales_prospect_method')
                    ->input_set('hide','true');
            
            $div_content = $div_pos->div_add()->div_set('class','form-group');
            
            $right_div = $div_content->div_add()
                    ->div_set('class','col-md-3')
                    ->div_set('id','sales_prospect_div_right')
                    ->div_add()
                    ->div_set('class','box')
                    ->div_set('attrib',array('style'=>''))
                    ;
            
            $left_div = $div_content
                    ->div_add()
                    ->div_set('id','sales_prospect_div_left')
                    ->div_set('class','col-md-9')
                    ;
            
            $controller_div = $div_pos->div_add()->div_set('class','col-md-9');
            $controller_div->button_add()
                    ->button_set('class','btn btn-default')
                    ->button_set('icon',APP_ICON::btn_back())
                    ->button_set('value','BACK')
                    ->button_set('id','sales_prospect_btn_back')
                    ->button_set('href',Sales_Prospect_Engine::path_get()->index)
            ;
            
            $controller_div->button_add()
                    ->button_set('class','btn btn-default')
                    ->button_set('icon','fa fa-arrow-left')
                    ->button_set('value','PREV')
                    ->button_set('id','sales_prospect_btn_prev')
            ;
            
            $controller_div->button_add()
                    ->button_set('class','btn btn-default')
                    ->button_set('icon','fa fa-arrow-right')
                    ->button_set('value','NEXT')
                    ->button_set('id','sales_prospect_btn_next')
            ;
            
            $controller_div->button_add()
                    ->button_set('class','btn btn-primary')
                    ->button_set('icon',APP_ICON::btn_save())
                    ->button_set('value','SUBMIT')
                    ->button_set('id','sales_prospect_submit')
            ;
            
            $controller_div->button_add()
                    ->button_set('class','btn btn-danger')
                    ->button_set('icon',APP_ICON::btn_cancel())
                    ->button_set('value','CANCEL')
                    ->button_set('id','sales_prospect_cancel')
            ;
            
            $controller_div->button_add()
                    ->button_set('class','btn btn-default pull-right')
                    ->button_set('icon',APP_ICON::sales_pos())
                    ->button_set('value','Point of Sale')
                    ->button_set('id','sales_prospect_sales_pos')
                    ->button_set('style','margin-right:5px')
            ;
            
            $controller_div->button_add()
                    ->button_set('class','btn btn-default pull-right')
                    ->button_set('icon',APP_ICON::mail())
                    ->button_set('value','Mail')
                    ->button_set('id','sales_prospect_mail')
                    ->button_set('style','margin-right:5px')
            ;
            
            $controller_div->button_add()
                    ->button_set('class','btn btn-default pull-right')
                    ->button_set('icon',APP_ICON::printer())
                    ->button_set('value','PRINT')
                    ->button_set('id','sales_prospect_btn_print')
                    ->button_set('style','margin-right:5px')
            ;
            
            
            $db = new DB();
            $store_list = array();
            $q = 'select id id, name data from store where status>0';            
            $store_list = $db->query_array($q);
            
            $init_div = $left_div->div_add()
                ->div_set('attrib',array('routing_section'=>'init'))
                ->div_set('class','pos-section')
            ;
            
            $init_div->input_add()->input_set('label',Lang::get('Code'))
                    ->input_set('id','sales_prospect_code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $init_div->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-info')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','sales_prospect_sales_prospect_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('hide_all',true)
                    ->input_select_set('disable_all',true)
                    ->input_select_set('is_module_status',true)
                    ;
            
            $init_div->textarea_add()->textarea_set('label',Lang::get('Cencellation Reason'))
                    ->textarea_set('id','sales_prospect_cancellation_reason')
                    ->textarea_set('value','')
                    ->textarea_set('hide_all',true)
                    ->textarea_set('disable_all',true)
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
                    ->input_select_set('id','sales_prospect_sales_inquiry_by')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url','')
                    ->input_select_set('disable_all',true)
                    
                ;
            
            $init_div->input_select_detail_add()
                    ->input_select_set('icon',App_Icon::customer())
                    ->input_select_set('label',' Customer')
                    ->input_select_set('min_length','1')
                    ->input_select_set('id','sales_prospect_customer')
                    ->input_select_set('min_length','1')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url',$path->ajax_search.'/input_select_customer_search/')
                    ->input_select_set('disable_all',true)
                    ->detail_set('rows',$customer_detail)
                    ->detail_set('id',"sales_prospect_customer_detail")
                    ->detail_set('ajax_url','')
                    ->detail_set('button_new',true)
                    ->detail_set('button_new_id','sales_prospect_btn_customer_new')
                    ->detail_set('button_new_class','btn btn-primary btn-sm')
                ;
            
            $init_div->input_select_add()
                    ->input_select_set('label','Price List')
                    ->input_select_set('icon',App_Icon::product_price_list())
                    ->input_select_set('min_length','1')
                    ->input_select_set('id','sales_prospect_price_list')
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
                    ->input_select_set('label',Lang::get('Expedition'))
                    ->input_select_set('icon',App_Icon::expedition())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','sales_prospect_expedition')
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
            
            $product_div->custom_component_add()->src_set('sales_prospect/view/sales_prospect_product_view');
            
            $summary_body  = $right_div->div_add()
                    ->div_set('class','box-body')
                    ;
            
            $div_summary_datetime = $summary_body->div_add()->div_set('class','text-right form-group');
            //$div_summary_datetime->label_add()->label_set('value',Date('F d, Y'));
            $div_summary_datetime->label_add()->label_set('value',Date('F d, Y H:i:s'))->label_set('id','sales_prospect_datetime');
            
            $div_code = $summary_body->div_add()->div_set('attrib',array('style'=>'font-size:12px'))->div_set('class','form-group');;
            $div_code->label_add()->label_set('value','Code ');
            $div_code->label_add()->label_set('value','[ AUTO GENERATE ]')
                    ->label_set('id','sales_prospect_summary_code')
                    ->label_set('class','pull-right');
            
            $div_customer = $summary_body->div_add()->div_set('attrib',array('style'=>'font-size:12px'))->div_set('class','form-group');;
            $div_customer->label_add()->label_set('value','Customer ');
            $div_customer->label_add()->label_set('value','')
                    ->label_set('id','sales_prospect_summary_customer')
                    ->label_set('class','pull-right');
            
            
            $div_price_list = $summary_body->div_add()->div_set('attrib',array('style'=>'font-size:12px'))->div_set('class','form-group');;;
            $div_price_list->label_add()->label_set('value','Price List ');
            $div_price_list->label_add()->label_set('value','')
                    ->label_set('id','sales_prospect_summary_price_list')
                    ->label_set('class','pull-right');
            
            $div_grand_total = $summary_body->div_add()->div_set('attrib',array('style'=>'font-size:12px'))->div_set('class','form-group');;;
            $div_grand_total->label_add()->label_set('value','Product Grand Total ('.Tools::currency_get().')');
            $div_grand_total->label_add()->label_set('value','0.00')->label_set('id','sales_prospect_summary_product_grand_total')->label_set('class','pull-right');;
            
            $param = array(
                'ajax_url'=>$path->index.'ajax_search/'
                ,'index_url'=>$path->index
                ,'detail_tab'=>'#sales_prospect'
                ,'view_url'=>$path->index.'view/'
                ,'window_scroll'=>'body'
                ,'data_support_url'=>$path->index.'data_support/'
                ,'common_ajax_listener'=>get_instance()->config->base_url().'common_ajax_listener/'
                ,'mail_message'=>Email_Message::template_get('attachment_find')
            );
            
            
            
            if($is_modal){
                $param['detail_tab'] = '#modal_sales_prospect .modal-body';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_sales_prospect';
            }
            
            $form->custom_component_add()
                    ->src_set('sales_prospect/view/modal_extra_charge_view')
                ;
            
            
            $js = get_instance()->load->view('sales_prospect/sales_prospect_basic_function_js',$param,TRUE);
            $app->js_set($js);
            
            $js = get_instance()->load->view('sales_prospect/sales_prospect_summary_js',$param,TRUE);
            $app->js_set($js);
            
            $js = get_instance()->load->view('sales_prospect/sales_prospect_init_section_js',$param,TRUE);
            $app->js_set($js);
            
            $js = get_instance()->load->view('sales_prospect/sales_prospect_product_section_js',$param,TRUE);
            $app->js_set($js);
            

            

            $app->js_set($js);
            
            return $components;
            
        }
        
        public static function sales_prospect_status_log_render($app,$form,$data,$path){
            get_instance()->load->helper('sales_prospect/sales_prospect_engine');
            $path = Receive_Product_Engine::path_get();
            get_instance()->load->helper($path->sales_prospect_purchase_invoice_engine);
            get_instance()->load->helper($path->sales_prospect_rma_engine);
            
            $id = $data['id'];
            $db = new DB();
            $q = '
                select null row_num
                    ,t1.moddate
                    ,t1.sales_prospect_status
                    ,t2.name user_name
                    ,case when t3.id is null then 0 else 1 end is_purchase_invoice
                    ,case when t4.id is null then 0 else 1 end is_rma
                from sales_prospect_status_log t1
                    inner join user_login t2 on t1.modid = t2.id
                    left outer join purchase_invoice_sales_prospect t3 
                        on t3.sales_prospect_id = t1.sales_prospect_id
                    left outer join rma_sales_prospect t4
                        on t4.sales_prospect_id = t1.sales_prospect_id
                where t1.sales_prospect_id = '.$id.'
                    order by moddate asc
            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $sales_prospect_status_name = '';
                if($rs[$i]['is_purchase_invoice'] === '1'){
                    $sales_prospect_status_name = SI::get_status_attr(
                        Receive_Product_Purchase_Invoice_Engine::sales_prospect_purchase_invoice_status_get(
                            $rs[$i]['sales_prospect_status']
                        )['label']
                    );
                }
                if($rs[$i]['is_rma'] === '1'){
                    $sales_prospect_status_name = SI::get_status_attr(
                        Receive_Product_RMA_Engine::sales_prospect_rma_status_get(
                            $rs[$i]['sales_prospect_status']
                        )['label']
                    );
                }
                
                $rs[$i]['sales_prospect_status_name'] = $sales_prospect_status_name;
                        
                
            }
            $sales_prospect_status_log = $rs;
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','sales_prospect_sales_prospect_status_log_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('columns',array("name"=>"sales_prospect_status_name","label"=>"Status",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('data',$sales_prospect_status_log);
        }
        
    }
    
?>