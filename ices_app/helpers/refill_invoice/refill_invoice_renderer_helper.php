<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Invoice_Renderer {

    public static function modal_refill_invoice_render($app,$modal){
        $modal->header_set(array('title'=>Lang::get('Manufacturing Work Process'),'icon'=>App_Icon::refill_invoice()));
        $modal->width_set('95%');
        $components = self::refill_invoice_components_render($app, $modal,true);


    }

    public static function refill_invoice_render($app,$form,$data,$path,$method){
        get_instance()->load->helper('refill_invoice/refill_invoice_engine');
        $path = Refill_Invoice_Engine::path_get();
        $id = $data['id'];
        $components = self::refill_invoice_components_render($app, $form,false);
        $back_href = $path->index;

        $form->button_add()->button_set('value','BACK')
            ->button_set('icon',App_Icon::btn_back())
            ->button_set('href',$back_href)
            ->button_set('class','btn btn-default')
            ;

        $js = '
            <script>
                $("#refill_invoice_method").val("'.$method.'");
                $("#refill_invoice_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                refill_invoice_init();
                refill_invoice_bind_event();
                refill_invoice_components_prepare(); 
        ';
        $app->js_set($js);

    }

    public static function refill_invoice_components_render($app,$form,$is_modal){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product/product_engine');
        get_instance()->load->helper('refill_invoice/refill_invoice_engine');
        $path = Refill_Invoice_Engine::path_get();            
        $components = array();
        $db = new DB();

        $id_prefix = Refill_Invoice_Engine::$prefix_id;

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

        $form->input_add()->input_set('id',$id_prefix.'_type')
                ->input_set('hide',true)
                ->input_set('value','')
                ;
        
        $form->input_select_detail_add()
            ->input_select_set('label',Lang::get('Reference'))
            ->input_select_set('icon',App_Icon::info())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_reference')
            ->input_select_set('data_add',array())
            ->input_select_set('ajax_url',$path->ajax_search.'input_select_reference_search')
            ->input_select_set('allow_empty',false)
            ->input_select_set('value',array())
            ->input_select_set('disable_all',true)
            ->detail_set('id',$id_prefix.'_reference_detail')
        ;
        
        $form->input_select_add()
            ->input_select_set('label',Lang::get('Customer'))
            ->input_select_set('icon',App_Icon::customer())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_customer')
            ->input_select_set('data_add',array())
            ->input_select_set('allow_empty',false)
            ->input_select_set('value',array())
            ->input_select_set('disable_all',true)
                
        ;
        
        $form->datetimepicker_add()->datetimepicker_set('label','Refill - '.Lang::get(array('Invoice','Date')))
            ->datetimepicker_set('id',$id_prefix.'_refill_invoice_date')
            ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
            ->datetimepicker_set('disable_all',true)
            ->datetimepicker_set('hide_all',true)
        ;
                
        $components[$id_prefix.'_status'] = $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_refill_invoice_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('hide_all',true)
                ->input_select_set('is_module_status',true)
                ;
        
        $form->input_add()->input_set('label',Lang::get('Grand Total Amount'))
                ->input_set('id',$id_prefix.'_grand_total_amount')
                ->input_set('icon','fa fa-info')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
                ->input_set('attrib',array())
                ->input_set('is_numeric',true)

            ;
        
        $form->input_add()->input_set('label',Lang::get('Outstanding Amount'))
                ->input_set('id',$id_prefix.'_outstanding_amount')
                ->input_set('icon','fa fa-info')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
                ->input_set('attrib',array())
                ->input_set('is_numeric',true)

            ;
        
        $form->table_input_add()->table_input_set('id',$id_prefix.'_product_table')
            ->main_div_set('class','form-group hide_all')
            ->label_set('value','Result Product')
            ->table_input_set('columns',array(
                'col_name'=>'product_type'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                )
            ))   
            ->table_input_set('columns',array(
                'col_name'=>'product','col_id_exists'=>true
                ,'th'=>array('val'=>'Product','col_style'=>'text-align:left;')
                ,'td'=>array('val'=>'','tag'=>'div','class'=>'','attr'=>array()
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'unit','col_id_exists'=>true
                ,'th'=>array('val'=>'Unit','col_style'=>'width:50px')
                ,'td'=>array('val'=>'','tag'=>'div','class'=>'','attr'=>array('original'=>''))
            ))
            ->table_input_set('columns',array(
                'col_name'=>'qty'
                ,'th'=>array('val'=>Lang::get(array('Qty')),'col_style'=>'width:50px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'div','class'=>'','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))
            ->table_input_set('columns',array(
                'col_name'=>'amount'
                ,'th'=>array('val'=>Lang::get(array('Amount')),'col_style'=>'width:200px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'div','class'=>'','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))
            ->table_input_set('columns',array(
                'col_name'=>'product_recondition'
                ,'th'=>array('val'=>Lang::get(array('Recondition')),'col_style'=>'width:300px;')
                ,'td'=>array('val'=>'','tag'=>'div','class'=>'','attr'=>array())
            ))
                ->table_input_set('columns',array(
                'col_name'=>'product_sparepart'
                ,'th'=>array('val'=>Lang::get(array('Sparepart')),'col_style'=>'width:300px;')
                ,'td'=>array('val'=>'','tag'=>'div','class'=>'','attr'=>array())
            ))
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

        $form->button_add()
                    ->button_set('class','btn btn-default pull-right hide_all')
                    ->button_set('icon',APP_ICON::printer())
                    ->button_set('value','PRINT')
                    ->button_set('id',$id_prefix.'_btn_print')
                    ->button_set('style','margin-left:5px')
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
            $param['detail_tab'] = '#modal_refill_invoice .modal-body';
            $param['view_url'] = '';
            $param['window_scroll'] = '#modal_refill_invoice';
        }

        $js = get_instance()->load->view('refill_invoice/refill_invoice_product_js',array(),TRUE);
        $app->js_set($js);
        
        $js = get_instance()->load->view('refill_invoice/refill_invoice_basic_function_js',$param,TRUE);
        $app->js_set($js);

        return $components;
        //</editor-fold>
    }

    public static function refill_invoice_status_log_render($app,$form,$data,$path){
        $config=array(
            'module_name'=>'refill_invoice',
            'module_engine'=>'refill_invoice_engine',
            'id'=>$data['id']
        );
        SI::form_renderer()->status_log_tab_render($form, $config);
    }
    
    public static function cda_view_render($app,$form,$data,$path){
        //<editor-fold defaultstate="collapsed">

        get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_engine');
        get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_renderer');
        $id = $data['id'];
        $db = new DB();
        $rs = $db->fast_get('refill_invoice',array('id'=>$id));
        if(count($rs)>0) {
            $refill_invoice = $rs[0];            
            $form->form_group_add();
            if($refill_invoice['refill_invoice_status'] != 'X'){
                if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'customer_deposit_allocation','add')){
                $form->button_add()->button_set('class','primary')
                        ->button_set('value',Lang::get(array('New','Customer Deposit Allocation'),true,true,false,false))
                        ->button_set('icon','fa fa-plus')
                        ->button_set('attrib',array(
                            'data-toggle'=>"modal" 
                            ,'data-target'=>"#modal_customer_deposit_allocation"
                        ))
                        ->button_set('disable_after_click',false)
                        ->button_set('id','customer_deposit_allocation_new')
                    ;
                }
            }
            $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
            $tbl = $form->table_add();
            $tbl->table_set('class','table');
            $tbl->table_set('id','customer_deposit_allocation_table');
            $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
            $tbl->table_set('columns',array("name"=>"customer_deposit_code","label"=>"Purchase Receipt Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"code","label"=>"Allocation Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
            $tbl->table_set('columns',array("name"=>"allocated_amount","label"=>"Allocated Amount (Rp.)",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"customer_deposit_allocation_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('data key','id');

            $q = '
                select distinct NULL row_num
                    ,t1.*
                    ,t2.code refill_invoice_code
                    ,t4.code customer_deposit_code
                from customer_deposit_allocation t1
                    inner join refill_invoice t2 on t1.refill_invoice_id = t2.id
                    inner join customer_deposit t4 on t4.id = t1.customer_deposit_id
                where t2.id = '.$id.' order by t1.moddate desc

            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['customer_deposit_allocation_type_text'] = SI::type_get('Purchase_Receipt_Allocation_Engine',
                    $rs[$i]['customer_deposit_allocation_type'])['label'];
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['allocated_amount'] = Tools::thousand_separator($rs[$i]['allocated_amount'],2,true);
                $rs[$i]['customer_deposit_allocation_status_text'] = SI::get_status_attr(
                    SI::status_get('Customer_Deposit_Allocation_Engine', $rs[$i]['customer_deposit_allocation_status'])['label']
                );
            }
            $tbl->table_set('data',$rs);


            $modal_customer_deposit_allocation = $app->engine->modal_add()->id_set('modal_customer_deposit_allocation')->width_set('75%');

            Customer_Deposit_Allocation_Renderer::modal_customer_deposit_allocation_render(
                    $app
                    ,$modal_customer_deposit_allocation
                );


            $param = array(
                'index_url'=>$path->index
                ,'ajax_search'=>$path->ajax_search
                ,'reference_id'=>$refill_invoice['id']
                ,'reference_text'=>$refill_invoice['code']
                ,'reference_type'=>'refill_invoice'
                ,'customer_id'=>$refill_invoice['customer_id']

            );

            $js = get_instance()->load->view('refill_invoice/customer_deposit_allocation_js',$param,TRUE);
            $app->js_set($js);

        }
        //</editor-fold>
    }
    
    public static function refill_receipt_allocation_view_render($app,$form,$data,$path){
        //<editor-fold defaultstate="collapsed">

        get_instance()->load->helper('refill_receipt_allocation/refill_receipt_allocation_engine');
        get_instance()->load->helper('refill_receipt_allocation/refill_receipt_allocation_renderer');
        $id = $data['id'];
        $db = new DB();
        $rs = $db->fast_get('refill_invoice',array('id'=>$id));
        if(count($rs)>0) {
            $refill_invoice = $rs[0];            
            $form->form_group_add();
            if($refill_invoice['refill_invoice_status'] != 'X'){
                if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'refill_receipt_allocation','add')){
                $form->button_add()->button_set('class','primary')
                        ->button_set('value',Lang::get(array('New','Refill Receipt Allocation'),true,true,false,false))
                        ->button_set('icon','fa fa-plus')
                        ->button_set('attrib',array(
                            'data-toggle'=>"modal" 
                            ,'data-target'=>"#modal_refill_receipt_allocation"
                        ))
                        ->button_set('disable_after_click',false)
                        ->button_set('id','refill_receipt_allocation_new')
                    ;
                }
            }
            $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
            $tbl = $form->table_add();
            $tbl->table_set('class','table');
            $tbl->table_set('id','refill_receipt_allocation_table');
            $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
            $tbl->table_set('columns',array("name"=>"refill_receipt_code","label"=>"Purchase Receipt Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"code","label"=>"Allocation Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
            $tbl->table_set('columns',array("name"=>"allocated_amount","label"=>"Allocated Amount (Rp.)",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"refill_receipt_allocation_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('data key','id');

            $q = '
                select distinct NULL row_num
                    ,t1.*
                    ,t2.code refill_invoice_code
                    ,t4.code refill_receipt_code
                from refill_receipt_allocation t1
                    inner join refill_invoice t2 on t1.refill_invoice_id = t2.id
                    inner join refill_receipt t4 on t4.id = t1.refill_receipt_id
                where t2.id = '.$id.' order by t1.moddate desc

            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['refill_receipt_allocation_type_text'] = SI::type_get('Purchase_Receipt_Allocation_Engine',
                    $rs[$i]['refill_receipt_allocation_type'])['label'];
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['allocated_amount'] = Tools::thousand_separator($rs[$i]['allocated_amount'],2,true);
                $rs[$i]['refill_receipt_allocation_status_text'] = SI::get_status_attr(
                    SI::status_get('Refill_Receipt_Allocation_Engine', $rs[$i]['refill_receipt_allocation_status'])['label']
                );
            }
            $tbl->table_set('data',$rs);


            $modal_refill_receipt_allocation = $app->engine->modal_add()->id_set('modal_refill_receipt_allocation')->width_set('75%');

            Refill_Receipt_Allocation_Renderer::modal_refill_receipt_allocation_render(
                    $app
                    ,$modal_refill_receipt_allocation
            );


            $param = array(
                'index_url'=>$path->index
                ,'ajax_search'=>$path->ajax_search
                ,'refill_invoice_id'=>$refill_invoice['id']
                ,'refill_invoice_text'=>$refill_invoice['code']
                ,'reference_type'=>'refill_invoice'
                ,'customer_id'=>$refill_invoice['customer_id']

            );

            $js = get_instance()->load->view('refill_invoice/refill_receipt_allocation_js',$param,TRUE);
            $app->js_set($js);

        }
        //</editor-fold>

        
    }

}
    
?>