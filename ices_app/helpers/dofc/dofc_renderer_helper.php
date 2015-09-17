<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class DOFC_Renderer {

    public static function modal_dofc_render($app,$modal){
        $modal->header_set(array('title'=>'Delivery Order Final','icon'=>App_Icon::info()));
        $components = self::dofc_components_render($app, $modal,true);
    }

    public static function dofc_render($app,$form,$data,$path,$method){
        get_instance()->load->helper('dofc/dofc_engine');
        $path = DOFC_Engine::path_get();
        $id = $data['id'];
        $components = self::dofc_components_render($app, $form,false);
        $back_href = $path->index;

        $form->button_add()->button_set('value','BACK')
            ->button_set('icon',App_Icon::btn_back())
            ->button_set('href',$back_href)
            ->button_set('class','btn btn-default')
            ;

        $js = '
            <script>
                $("#dofc_method").val("'.$method.'");
                $("#dofc_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                dofc_init();
                dofc_bind_event();
                dofc_components_prepare(); 
        ';
        $app->js_set($js);

    }

    public static function dofc_components_render($app,$form,$is_modal){

        get_instance()->load->helper('dofc/dofc_engine');
        $path = DOFC_Engine::path_get();            
        $components = array();
        $db = new DB();

        $id_prefix = 'dofc';

        $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                ->input_set('hide',true)
                ->input_set('value','')
                ;

        $form->input_add()->input_set('id',$id_prefix.'_type')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ;

        $disabled = array('disable'=>'');


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
                //->input_select_set('hide_all',true)                                        
            ;

        $form->input_add()->input_set('id',$id_prefix.'_method')
                ->input_set('hide',true)
                ->input_set('value','')
                ;            


        $form->input_add()->input_set('label',Lang::get('Code'))
                ->input_set('id',$id_prefix.'_code')
                ->input_set('icon','fa fa-info')
                ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                //->input_set('hide_all',true)
            ;

        $reference_detail = array(

        );

        $form->input_select_detail_add()
                ->input_select_set('label',Lang::get('Reference'))
                ->input_select_set('icon',App_Icon::info())
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_reference')
                ->input_select_set('min_length','1')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('ajax_url',$path->ajax_search.'input_select_reference_search')
                ->input_select_set('disable_all',true)
                ->input_select_set('hide_all',true)
                ->detail_set('rows',$reference_detail)
                ->detail_set('id',$id_prefix."_reference_detail")
                ->detail_set('ajax_url','')

            ;



        $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('Delivery Order Final Confirmation','Date')))
                ->datetimepicker_set('id',$id_prefix.'_delivery_order_final_confirmation_date')
                ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
                ->datetimepicker_set('disable_all',true)
                //->datetimepicker_set('hide_all',true)
            ;

        $form->input_add()->input_set('label',Lang::get('Receipt Number'))
                ->input_set('id',$id_prefix.'_receipt_number')
                ->input_set('icon','fa fa-info')
                //->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;

        $form->input_add()->input_set('label',Lang::get('Receiver Name'))
                ->input_set('id',$id_prefix.'_receiver_name')
                ->input_set('icon',APP_Icon::u_group())
                //->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;


        $form->input_add()->input_set('label',Lang::get('Expedition Name'))
                ->input_set('id',$id_prefix.'_expedition_name')
                ->input_set('icon','fa fa-info')
                //->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;

        $form->input_add()->input_set('label',Lang::get('Driver Name'))
                ->input_set('id',$id_prefix.'_driver_name')
                ->input_set('icon','fa fa-info')
                //->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;

        $form->input_add()->input_set('label',Lang::get('Driver Assistant Name'))
                ->input_set('id',$id_prefix.'_driver_assistant_name')
                ->input_set('icon','fa fa-info')
                //->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;

        $form->input_add()->input_set('label',Lang::get('Delivery Cost'))
                ->input_set('id',$id_prefix.'_delivery_cost')
                ->input_set('icon','fa fa-euro')
                //->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;

        $table = $form->form_group_add()->table_add();
        $table->div_set('label','Additional Cost');
        $table->table_set('id',$id_prefix.'_additional_cost_table');
        $table->table_set('class','table fixed-table');
        $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px;text-align:center'),'attribute'=>'style="text-align:center"'));
        $table->table_set('columns',array("name"=>"description","label"=>"Description",'col_attrib'=>array('style'=>'text-align:center;')));
        $table->table_set('columns',array("name"=>"amount","label"=>"Amount",'col_attrib'=>array('style'=>'text-align:center;')));
        $table->table_set('columns',array("name"=>"action","label"=>"",'col_attrib'=>array('style'=>'text-align:right','class'=>'table-action')));
        //$table->table_set('hide_all',true);




        $components['dofc_status'] = $form->input_select_add()
            ->input_select_set('label','Status')
            ->input_select_set('icon','fa fa-info')
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_delivery_order_final_confirmation_status')
            ->input_select_set('data_add',array())
            ->input_select_set('value',array())
            ->input_select_set('is_module_status',true)
            //->input_select_set('hide_all',true)    
            ->input_select_set('module_prefix_id','dofc')
            ->input_select_set('module_primary_data_key','dofc')
            ->input_select_set('module_status_field','delivery_order_final_confirmation_status')
            ;

        $form->textarea_add()->textarea_set('label','Notes')
            ->textarea_set('id','dofc_notes')
            ->textarea_set('value','')
            ->textarea_set('attrib',array())       
            ->textarea_set('disable_all',true)
            //->textarea_set('hide_all',true)

            ;


        $form->hr_add()->hr_set('class','');

        $form->button_add()->button_set('value','Submit')
                        ->button_set('id',$id_prefix.'_submit')
                        ->button_set('icon',App_Icon::detail_btn_save())
                    ;

        $form->button_add()->button_set('value','Print')
                ->button_set('id',$id_prefix.'_print')
                ->button_set('icon',App_Icon::printer())
                ->button_set('class','btn btn-default pull-right')
                ->button_set('disable_after_click',false)
        ;
        
        $form->button_add()
                ->button_set('class','btn btn-default pull-right')
                ->button_set('icon',APP_ICON::mail())
                ->button_set('value','Mail')
                ->button_set('id',$id_prefix.'_mail')
                ->button_set('style','margin-right:5px')
        ;
        
        $param = array(
            'ajax_url'=>$path->index.'ajax_search/'
            ,'index_url'=>$path->index
            ,'detail_tab'=>'#detail_tab'
            ,'view_url'=>$path->index.'view/'
            ,'window_scroll'=>'body'
            ,'data_support_url'=>$path->index.'data_support/'
            ,'common_ajax_listener'=>get_instance()->config->base_url().'common_ajax_listener/'
            ,'mail_message'=>Email_Message::template_get('attachment_find')
        );



        if($is_modal){
            $param['detail_tab'] = '#modal_'.$id_prefix.' .modal-body';
            $param['view_url'] = '';
            $param['window_scroll'] = '#modal_'.$id_prefix;
        }

        $js = get_instance()->load->view('dofc/'.$id_prefix.'_basic_function_js',$param,TRUE);
        $app->js_set($js);
        return $components;

    }

    public static function dofc_status_log_render($app,$form,$data,$path){
        //<editor-fold defaultstate="collapsed">
        $config=array(
            'module_name'=>'delivery_order_final_confirmation',
            'module_engine'=>'DOFC_Engine',
            'id'=>$data['id']
        );
        SI::form_renderer()->status_log_tab_render($form, $config);
        //</editor-fold>
    }
    
    public static function dofc_customer_bill_render($app,$form,$data,$path){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('customer_bill/customer_bill_engine');
        get_instance()->load->helper('customer_bill/customer_bill_renderer');
        $id = $data['id'];
        $db = new DB();
        $rs = $db->fast_get('delivery_order_final_confirmation',array('id'=>$id));
        
        if(count($rs)>0) {
            $dofc = $rs[0];            
            $form->form_group_add();
            
            $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
            $tbl = $form->table_add();
            $tbl->table_set('class','table');
            $tbl->table_set('id','customer_bill_table');
            $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
            $tbl->table_set('columns',array("name"=>"code","label"=>"Code",'attribute'=>'style="text-align:left"','col_attrib'=>array('style'=>'text-align:left'),"is_key"=>true));
            $tbl->table_set('columns',array("name"=>"customer_bill_type_text","label"=>"Reference Type",'attribute'=>'style="text-align:left"','col_attrib'=>array('style'=>'text-align:left')));            
            $tbl->table_set('columns',array("name"=>"amount","label"=>"Amount (Rp.)",'attribute'=>'style="text-align:right"','col_attrib'=>array('style'=>'text-align:right')));
            $tbl->table_set('columns',array("name"=>"outstanding_amount","label"=>"Outstanding Amount (Rp.)",'attribute'=>'style="text-align:right"','col_attrib'=>array('style'=>'text-align:right')));
            $tbl->table_set('columns',array("name"=>"customer_bill_status_text","label"=>"Status",'attribute'=>'style="text-align:left"','col_attrib'=>array('style'=>'text-align:left')));
            $tbl->table_set('data key','id');

            $q = '
                select distinct NULL row_num
                    ,cb.*
                from customer_bill cb
                    inner join dofc_cb on cb.id = dofc_cb.customer_bill_id
                where dofc_cb.delivery_order_final_confirmation_id = '.$id.' order by cb.id asc

            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['customer_bill_type_text'] = SI::get_status_attr(SI::type_get('Customer_Bill_Engine',
                    $rs[$i]['customer_bill_type'],'$module_type_list')['label']);
                $rs[$i]['amount'] = Tools::thousand_separator($rs[$i]['amount']);
                $rs[$i]['outstanding_amount'] = Tools::thousand_separator($rs[$i]['outstanding_amount']);
                $rs[$i]['customer_bill_status_text'] = SI::get_status_attr(SI::type_get('Customer_Bill_Engine',
                    $rs[$i]['customer_bill_status'],'$status_list')['label']);

            }
            $tbl->table_set('data',$rs);

            
            $modal_customer_bill = $app->engine->modal_add()->id_set('modal_customer_bill')->width_set('75%');

            $customer_bill_data = array(
                'dofc'=>array(
                    'id'=>$dofc['id']
                )
            );
            $customer_bill_data = json_decode(json_encode($customer_bill_data));

            Customer_Bill_Renderer::modal_customer_bill_render(
                    $app
                    ,$modal_customer_bill
                );


            $param = array(
                'index_url'=>$path->index
                ,'ajax_search'=>$path->ajax_search
                ,'reference_id'=>$dofc['id']
                ,'reference_text'=>SI::html_tag('strog',$dofc['code'])
            );

            $js = get_instance()->load->view('dofc/customer_bill_js',$param,TRUE);
            $app->js_set($js);
            
        }
        //</editor-fold>
    }

    public static function dofc_customer_deposit_render($app,$form,$data,$path){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('customer_deposit/customer_deposit_engine');
        get_instance()->load->helper('customer_deposit/customer_deposit_renderer');
        $id = $data['id'];
        $db = new DB();
        $rs = $db->fast_get('delivery_order_final_confirmation',array('id'=>$id));
        
        if(count($rs)>0) {
            $dofc = $rs[0];            
            $form->form_group_add();
            
            $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
            $tbl = $form->table_add();
            $tbl->table_set('class','table');
            $tbl->table_set('id','customer_deposit_table');
            $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
            $tbl->table_set('columns',array("name"=>"code","label"=>"Code",'attribute'=>'style="text-align:left"','col_attrib'=>array('style'=>'text-align:left'),"is_key"=>true));
            $tbl->table_set('columns',array("name"=>"customer_deposit_type_text","label"=>"Reference Type",'attribute'=>'style="text-align:left"','col_attrib'=>array('style'=>'text-align:left')));            
            $tbl->table_set('columns',array("name"=>"amount","label"=>"Amount (Rp.)",'attribute'=>'style="text-align:right"','col_attrib'=>array('style'=>'text-align:right')));
            $tbl->table_set('columns',array("name"=>"outstanding_amount","label"=>"Outstanding Amount (Rp.)",'attribute'=>'style="text-align:right"','col_attrib'=>array('style'=>'text-align:right')));
            $tbl->table_set('columns',array("name"=>"customer_deposit_status_text","label"=>"Status",'attribute'=>'style="text-align:left"','col_attrib'=>array('style'=>'text-align:left')));
            $tbl->table_set('data key','id');

            $q = '
                select distinct NULL row_num
                    ,cd.*
                from customer_deposit cd
                    inner join dofc_cd on cd.id = dofc_cd.customer_deposit_id
                where dofc_cd.delivery_order_final_confirmation_id = '.$id.' order by cd.id asc

            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['customer_deposit_type_text'] = SI::get_status_attr(SI::type_get('Customer_Deposit_Engine',
                    $rs[$i]['customer_deposit_type'],'$module_type_list')['label']);
                $rs[$i]['amount'] = Tools::thousand_separator($rs[$i]['amount']);
                $rs[$i]['outstanding_amount'] = Tools::thousand_separator($rs[$i]['outstanding_amount']);
                $rs[$i]['customer_deposit_status_text'] = SI::get_status_attr(SI::type_get('Customer_Deposit_Engine',
                    $rs[$i]['customer_deposit_status'],'$status_list')['label']);

            }
            $tbl->table_set('data',$rs);

            
            $modal_customer_deposit = $app->engine->modal_add()->id_set('modal_customer_deposit')->width_set('75%');

            $customer_deposit_data = array(
                'dofc'=>array(
                    'id'=>$dofc['id']
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
                ,'reference_id'=>$dofc['id']
                ,'reference_text'=>SI::html_tag('strog',$dofc['code'])
            );

            $js = get_instance()->load->view('dofc/customer_deposit_js',$param,TRUE);
            $app->js_set($js);
            
            
        }
        //</editor-fold>
    }
    
}
    
?>