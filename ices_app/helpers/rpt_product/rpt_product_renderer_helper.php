<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rpt_Product_Renderer {

    public static function modal_rpt_product_render($app,$modal){
        $modal->header_set(array('title'=>'System Investigation Report','icon'=>App_Icon::rpt_product()));
        $components = self::rpt_product_components_render($app, $modal,true);
    }

    public static function rpt_product_render($app,$form,$data,$path,$method){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_product/rpt_product_engine');
        $path = Rpt_Product_Engine::path_get();
        $id = $data['id'];
        $components = self::rpt_product_components_render($app, $form,false);
        $back_href = $path->index;

        $btn_group = $form->form_group_add()->attrib_set(array('style'=>'height:34px'))->button_group_add()
            ->button_group_set('icon',App_Icon::btn_save())
            ->button_group_set('value','Download')
            ->button_group_set('div_class','btn-group pull-right')
            ->button_group_set('item_list_add',array('id'=>'save_excel','label'=>'Excel','class'=>'fa fa-file-excel-o'))
            ;
        
        $js = '
            <script>
                $("#rpt_product_method").val("'.$method.'");
                $("#rpt_product_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                rpt_product_init();
                rpt_product_bind_event();
                rpt_product_components_prepare(); 
        ';
        $app->js_set($js);
        //</editor-fold>
    }

    public static function rpt_product_components_render($app,$form,$is_modal){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_product/rpt_product_engine');
        $path = Rpt_Product_Engine::path_get();            
        $components = array();
        $db = new DB();

        $id_prefix = 'rpt_product';

        $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                ->input_set('hide',true)
                ->input_set('value','')
                ;

        $form->input_add()->input_set('id',$id_prefix.'_method')
                ->input_set('hide',true)
                ->input_set('value','')
                ;            
        $db = new DB();
        
        $module_list = Rpt_Product_Engine::$module_type_list;
        for($i=0;$i<count($module_list);$i++){
            $module_list[$i]['id'] = $module_list[$i]['val'];
            $module_list[$i]['data'] = $module_list[$i]['label'];
        }

        $form->input_select_add()
            ->input_select_set('label',Lang::get('Module Name'))
            ->input_select_set('icon',App_Icon::info())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_module_name')
            ->input_select_set('data_add',$module_list)
            ->input_select_set('value',array())
            ->input_select_set('disable_all',true)
             ->input_select_set('hide_all',true)

        ;

        $form->div_add()
            ->div_set('id',$id_prefix.'_report_div')
            ->div_set('class','')
        ;
        
        
        $param = array(
            'ajax_url'=>$path->index.'ajax_search/'
            ,'index_url'=>$path->index
            ,'detail_tab'=>'#'.$id_prefix
            ,'view_url'=>$path->index.'view/'
            ,'window_scroll'=>'body'
            ,'form_render_url'=>$path->index.'form_render/'
            ,'data_support_url'=>$path->index.'data_support/'
            ,'common_ajax_listener'=>get_instance()->config->base_url().'common_ajax_listener/'
            ,'component_prefix_id'=>$id_prefix
        );
        


        if($is_modal){
            $param['detail_tab'] = '#modal_'.$id_prefix.' .modal-body';
            $param['view_url'] = '';
            $param['window_scroll'] = '#modal_'.$id_prefix;
        }

        $js = get_instance()->load->view('rpt_product/'.$id_prefix.'_basic_function_js',$param,TRUE);
        $app->js_set($js);
        return $components;
        //</editor-fold>
    }

    public static function report_render($module_name){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_product/rpt_product_data_support');
        get_instance()->load->helper('rpt_product/rpt_product_engine');
        
        $result = array('html'=>'','script'=>'');        
        if(method_exists('Rpt_Product_Renderer', $module_name.'_render')){
            $result = eval('return self::'.$module_name.'_render();');
        }
        return $result;
        //</editor-fold>
    }
    
    static function product_stock_render(){
        //<editor-fold defaultstate="collapsed">
        $result = array('html'=>'','script'=>'');
        $path = Rpt_Product_Engine::path_get();
        
        $app = new App();
        $id_prefix = Rpt_Product_Engine::$prefix_id;
        
        $cols = SI::type_get('rpt_product_engine', 'product_stock')['tbl_col'];
        $main_div = $app->engine->div_add();
        
        $rs = Warehouse_Engine::BOS_get();
        $warehouse_list = array(array('id'=>'','data'=>'ALL'));
        foreach($rs as $i=>$row){
            $warehouse_list[] = array('id'=>$row['id'], 'data'=> $row['name']);
        }
        $main_div->input_select_add()
            ->input_select_set('label',Lang::get('Warehouse'))
            ->input_select_set('icon',App_Icon::info())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_product_stock_warehouse')
            ->input_select_set('data_add',$warehouse_list)
            ->input_select_set('allow_empty',false)
        ;
        
        $tbl = $main_div->form_group_add()->table_ajax_add();
        $tbl->table_ajax_set('id',$id_prefix.'_product_stock_ajax_table')
            ->table_ajax_set('base_href',get_instance()->config->base_url().'product/view')
            ->table_ajax_set('lookup_url',$path->index.'ajax_search/product_stock_search')
            ->table_ajax_set('columns',$cols)
            ->table_ajax_set('key_column','product_id')
            ->filter_set(array(array('id'=>$id_prefix.'_product_stock_warehouse','field'=>'warehouse_id')))            
            
        ;
        
        $result['html'] = $main_div->render();
        $result['script'] = $main_div->scripts_get();
        $js = '
            $("#'.$id_prefix.'_product_stock_warehouse").on("change",function(){
                '.$id_prefix.'_product_stock_ajax_table.methods.data_show(1);
            });
            $("#'.$id_prefix.'_product_stock_warehouse").select2("data",{id:"",text:"All"}).change();
        ';
        $result['script'].=$js;
        //$result =  $main_div->render();
        
        
        return $result;
        //</editor-fold>
    }

}
    
?>