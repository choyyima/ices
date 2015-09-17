<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Deposit_Renderer {

    public static function modal_customer_deposit_render($app,$modal){
        $modal->header_set(array('title'=>'Customer Deposit','icon'=>App_Icon::money()));
        $components = self::customer_deposit_components_render($app, $modal,true);
    }

    public static function customer_deposit_render($app,$form,$data,$path,$method){
        get_instance()->load->helper('customer_deposit/customer_deposit_engine');
        $path = Customer_Deposit_Engine::path_get();
        $id = $data['id'];
        $components = self::customer_deposit_components_render($app, $form,false);
        $back_href = $path->index;

        $form->button_add()->button_set('value','BACK')
            ->button_set('icon',App_Icon::btn_back())
            ->button_set('href',$back_href)
            ->button_set('class','btn btn-default')
            ;

        $js = '
            <script>
                $("#customer_deposit_method").val("'.$method.'");
                $("#customer_deposit_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                customer_deposit_init();
                customer_deposit_bind_event();
                customer_deposit_components_prepare(); 
        ';
        $app->js_set($js);

    }

    public static function customer_deposit_components_render($app,$form,$is_modal){
        
        get_instance()->load->helper('customer_deposit/customer_deposit_engine');
        get_instance()->load->helper('bos_bank_account/bos_bank_account_data_support');
        
        $path = Customer_Deposit_Engine::path_get();            
        $components = array();
        $db = new DB();

        $id_prefix = 'customer_deposit';

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
            array('name'=>'type','label'=>Lang::get('Type'))
        );

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

        $form->input_select_detail_add()
            ->input_select_set('label',Lang::get('Reference'))
            ->input_select_set('icon',App_Icon::info())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_reference')
            ->input_select_set('min_length','1')
            ->input_select_set('data_add',array())
            ->input_select_set('value',array())
            ->input_select_set('ajax_url',$path->ajax_search.'input_select_reference_search/')
            ->input_select_set('disable_all',true)
             ->input_select_set('hide_all',true)
            ->detail_set('id',$id_prefix."_reference_detail")
            ->detail_set('ajax_url','')                    
        ;

        $form->datetimepicker_add()->datetimepicker_set('label',Lang::get('Customer Deposit Date'))
                ->datetimepicker_set('id',$id_prefix.'_customer_deposit_date')
                ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
                ->datetimepicker_set('disable_all',true)
                ->datetimepicker_set('hide_all',true)
            ;

        $form->input_select_add()
            ->input_select_set('label','Customer')
            ->input_select_set('icon',APP_Icon::user())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_customer')
            ->input_select_set('data_add',array())
            ->input_select_set('value',array())
            ->input_select_set('hide_all',true)                    
            ->input_select_set('disable_all',true)           
            ->input_select_set('ajax_url',$path->ajax_search.'input_select_customer_search/')
            ;

        $form->input_select_add()
            ->input_select_set('label',Lang::get('Payment Type'))
            ->input_select_set('icon',App_Icon::info())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_payment_type')
            ->input_select_set('value',array())
            ->input_select_set('disable_all',true)
            ->input_select_set('hide_all',true)    
            ->input_select_set('ajax_url','')
        ;
        
        $form->input_add()->input_set('label',Lang::get('Customer Bank Acc.'))
                    ->input_set('id',$id_prefix.'_customer_bank_acc')
                    ->input_set('icon',App_Icon::money())
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
        $bba_list = array();
        foreach(Bos_Bank_Account_Data_Support::bos_bank_account_list_get(array('bos_bank_account_status'=>'active')) 
            as $idx=>$row){
            $bba_list[] = array(
                'id'=>$row['id'],
                'text'=>$row['code'],
            );
        }

        $form->input_select_add()
                ->input_select_set('label',Lang::get('BOS Bank Account'))
                ->input_select_set('icon',App_Icon::info())
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_bos_bank_account')
                ->input_select_set('value',array())
                ->input_select_set('disable_all',true)
                ->input_select_set('hide_all',true)    
                ->input_select_set('data_add',$bba_list)
            ;
        
        $form->input_add()->input_set('label',Lang::get('Amount '))
                ->input_set('id',$id_prefix.'_amount')
                ->input_set('icon',App_Icon::money())
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;

        $form->input_add()->input_set('label',Lang::get('Outstanding Amount '))
                ->input_set('id',$id_prefix.'_outstanding_amount')
                ->input_set('icon',App_Icon::money())
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;

        $form->datetimepicker_add()->datetimepicker_set('label',Lang::get('Deposit Date'))
            ->datetimepicker_set('id',$id_prefix.'_deposit_date')
            ->datetimepicker_set('value','') 
            ->datetimepicker_set('disable_all',true)
            ->datetimepicker_set('hide_all',true)
        ;
        
        $components['customer_deposit_status'] = $form->input_select_add()
            ->input_select_set('label','Status')
            ->input_select_set('icon','fa fa-info')
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_customer_deposit_status')
            ->input_select_set('data_add',array())
            ->input_select_set('value',array())
            ->input_select_set('is_module_status',true)
            ->input_select_set('hide_all',true)                    
            ;

        $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','customer_deposit_notes')
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
            $param['detail_tab'] = '#modal_'.$id_prefix.' .modal-body';
            $param['view_url'] = '';
            $param['window_scroll'] = '#modal_'.$id_prefix;
        }

        $js = get_instance()->load->view('customer_deposit/'.$id_prefix.'_basic_function_js',$param,TRUE);
        $app->js_set($js);
        return $components;

    }

    public static function customer_deposit_status_log_render($app,$form,$data,$path){
        $config=array(
            'module_name'=>'customer_deposit',
            'module_engine'=>'Customer_Deposit_Engine',
            'id'=>$data['id']
        );
        SI::form_renderer()->status_log_tab_render($form, $config);
    }

    public static function customer_deposit_allocation_view_render($app,$form,$data,$path){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_engine');
        get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_renderer');
        $id = $data['id'];
        $db = new DB();
        $rs = $db->fast_get('customer_deposit',array('id'=>$id));
        if(count($rs)>0) {
            $customer_deposit = $rs[0];
            $form->form_group_add();
            if($customer_deposit['customer_deposit_status'] != 'X'){
                if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'customer_deposit_allocation','add')){
                $form->button_add()->button_set('class','primary')
                        ->button_set('value',Lang::get(array('New','Customer Deposit Allocation')))
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
            $tbl->table_set('columns',array("name"=>"customer_deposit_allocation_type_text","label"=>"Reference Type",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"reference_code","label"=>"Reference Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"code","label"=>"Allocation Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
            $tbl->table_set('columns',array("name"=>"allocated_amount","label"=>"Allocated Amount (Rp.)",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"customer_deposit_allocation_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('data key','id');

            $q = '
                select distinct NULL row_num
                    ,t1.*
                    ,t2.code sales_invoice_code
                    ,t3.code customer_bill_code
                    ,ri.code refill_invoice_code
                from customer_deposit_allocation t1
                    left outer join sales_invoice t2 on t1.sales_invoice_id = t2.id
                    left outer join customer_bill t3 on t1.customer_bill_id = t3.id
                    left outer join refill_invoice ri on t1.refill_invoice_id = ri.id
                    inner join customer_deposit t4 on t4.id = t1.customer_deposit_id
                where t4.id = '.$id.' order by t1.moddate desc

            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['reference_code'] = $rs[$i][$rs[$i]['customer_deposit_allocation_type'].'_code'];
                $rs[$i]['customer_deposit_allocation_type_text'] = SI::type_get('Customer_Deposit_Allocation_Engine',
                    $rs[$i]['customer_deposit_allocation_type'])['label'];
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['allocated_amount'] = Tools::thousand_separator($rs[$i]['allocated_amount'],2,true);
                $rs[$i]['customer_deposit_allocation_status_text'] = SI::get_status_attr(
                    SI::status_get('Customer_Deposit_Allocation_Engine', $rs[$i]['customer_deposit_allocation_status'])['label']
                );
            }
            $tbl->table_set('data',$rs);


            $modal_customer_deposit_allocation = $app->engine->modal_add()->id_set('modal_customer_deposit_allocation')->width_set('75%');

            $customer_deposit_allocation_data = array(
                'customer_deposit'=>array(
                    'id'=>$customer_deposit['id']
                )                
            );
            $customer_deposit_allocation_data = json_decode(json_encode($customer_deposit_allocation_data));

            Customer_Deposit_Allocation_Renderer::modal_customer_deposit_allocation_render(
                    $app
                    ,$modal_customer_deposit_allocation
                    ,$customer_deposit_allocation_data
                );


            $param = array(
                'index_url'=>$path->index
                ,'ajax_search'=>$path->ajax_search
                ,'customer_deposit_id'=>$customer_deposit['id']
                ,'customer_deposit_text'=>$customer_deposit['code']
                ,'customer_deposit_type'=>$customer_deposit['customer_deposit_type']
                ,'customer_id'=>$customer_deposit['customer_id']
                    
            );

            $js = get_instance()->load->view('customer_deposit/customer_deposit_allocation_js',$param,TRUE);
            $app->js_set($js);

        }
        //</editor-fold>
    }
    
    public static function customer_refund_view_render($app,$form,$data,$path){
        //<editor-fold defaultstate="collapsed">
            
            get_instance()->load->helper('customer_refund/customer_refund_engine');
            get_instance()->load->helper('customer_refund/customer_refund_renderer');
            $id = $data['id'];
            $db = new DB();
            $rs = $db->fast_get('customer_deposit',array('id'=>$id));
            if(count($rs)>0) {
                $customer_deposit = $rs[0];            
                $form->form_group_add();
                if($customer_deposit['customer_deposit_status'] != 'X'){
                    if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'customer_refund','add')){
                    $form->button_add()->button_set('class','primary')
                            ->button_set('value',Lang::get(array('New','Customer Refund')))
                            ->button_set('icon','fa fa-plus')
                            ->button_set('attrib',array(
                                'data-toggle'=>"modal" 
                                ,'data-target'=>"#modal_customer_refund"
                            ))
                            ->button_set('disable_after_click',false)
                            ->button_set('id','customer_refund_new')
                        ;
                    }
                }
                $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
                $tbl = $form->table_add();
                $tbl->table_set('class','table');
                $tbl->table_set('id','customer_refund_table');
                $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
                $tbl->table_set('columns',array("name"=>"code","label"=>"Customer Refund Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),'is_key'=>true));
                $tbl->table_set('columns',array("name"=>"amount","label"=>"Amount (Rp.)",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"customer_refund_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('data key','id');

                $q = '
                    select distinct NULL row_num
                        ,t1.*
                        ,t3.code customer_deposit_code
                    from customer_refund t1
                        inner join customer_deposit_customer_refund t2 on t2.customer_refund_id = t1.id
                        inner join customer_deposit t3 on t3.id = t2.customer_deposit_id
                    where t3.id = '.$id.' order by t1.moddate desc

                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['row_num'] = $i+1;
                    $rs[$i]['amount'] = Tools::thousand_separator($rs[$i]['amount'],2,true);
                    $rs[$i]['customer_refund_status_text'] = SI::get_status_attr(
                        SI::status_get('Customer_Refund_Engine', $rs[$i]['customer_refund_status'])['label']
                    );
                }
                $tbl->table_set('data',$rs);
                

                $modal_customer_refund = $app->engine->modal_add()->id_set('modal_customer_refund')->width_set('75%');

                Customer_Refund_Renderer::modal_customer_refund_render(
                        $app
                        ,$modal_customer_refund
                    );


                $param = array(
                    'index_url'=>$path->index
                    ,'ajax_search'=>$path->ajax_search
                    ,'reference_id'=>$customer_deposit['id']
                    ,'reference_text'=>$customer_deposit['code']
                    ,'reference_type'=>'customer_deposit'
                );

                $js = get_instance()->load->view('customer_deposit/customer_refund_js',$param,TRUE);
                $app->js_set($js);
                
            }
            //</editor-fold>
    }
}
    
?>