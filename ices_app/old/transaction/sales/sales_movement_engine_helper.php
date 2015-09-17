<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Purchase_Movement_Engine {
        
        public static function get($id=""){
            $db = new DB();
            $q = "select * 
                from purchase_order 
                where status>0 and id = ".$db->escape($id);
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $rs = $rs[0];
            else $rs = null;
            return $rs;
        }
        
        public static function warehouse_get(){
            $result = null;
            $db = new DB();
            $q = '
                select * from warehouse where status>0
            ';                    
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $result = $rs;
            return $result;
        }
        
        public static function movement_type_get($name){
            $result = null;
            $db = new DB();
            $q = '
                select id, code, name from movement_type where status>0 and code = '.$db->escape($name).'
            ';                    
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $result = $rs[0];
            return $result;
            
        }
        
        public static function movement_render($app,$pane,$data,$path,$method){
            $id = $data['id'];
            $pane->form_group_add();
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'movement','add')){
            $pane->button_add()->button_set('class','primary')
                    ->button_set('value','New Movement')
                    ->button_set('icon','fa fa-plus')
                    ->button_set('attrib',array(
                        'data-toggle'=>"modal" 
                        ,'data-target'=>"#modal_movement"
                    ))
                    ->button_set('disable_after_click',false)
                    ->button_set('script','$("#movement_modal_method").val("add");')
                    ->button_set('id','new_movement')
                ;
            }
            $pane->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
            $tbl = $pane->table_add();
            $tbl->table_set('class','table');
            $tbl->table_set('id','movement_table');
            $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
            $tbl->table_set('columns',array("name"=>"code","label"=>"Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
            $tbl->table_set('columns',array("name"=>"date","label"=>"Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"movement_type","label"=>"Type",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"movement_status","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('data key','id');
            
            $db = new DB();
            $q = '
                select distinct NULL row_num, t1.id, t1.code, t4.name movement_type, t1.date
                    ,case t1.movement_status 
                        when "O" then "OPENED" 
                        when "X" then "CANCELLED" 
                        when "D" then "DELIVERED" 
                    end movement_status
                from movement t1
                inner join purchase_order_movement t2 on t1.id = t2.movement_id
                inner join purchase_order t3 on t3.id = t2.purchase_order_id
                inner join movement_type t4 on t4.id = t1.movement_type_id
                where t3.id = '.$id.'
                order by  t1.code desc
            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                
                $rs[$i]['row_num'] = $i+1;
            }
            $tbl->table_set('data',$rs);
            
            $po = self::get($id);
            $mov_type = self::movement_type_get('SUPTOWARE');
            $warehouse = self::warehouse_get();
                    
            $movement_data = array(
                'po'=>array(
                    'id'=>$po->id
                    ,'code'=>$po->code
                )
                ,'movement_detail'=>array()
                
            );
            
            $movement_list = array(
                'warehouse'=>array()                
            );
            
            $movement_data = json_decode(json_encode($movement_data));
            $movement_list = json_decode(json_encode($movement_list));
            
            foreach($warehouse as $key=>$val){
                $temp = array(
                    'id'=> $val->id
                    ,'data'=> $val->code.' - '.$val->name
                );
                $temp = json_decode(json_encode($temp));
                $movement_list->warehouse[]= $temp;
            }
            
            $movement_modal = $app->engine->modal_add()->id_set('modal_movement')->width_set('75%');
                                
            self::movement_modal_render($app,$movement_modal,$path,$movement_data,$movement_list,$method);
            
            
        }
        
        public static function movement_modal_render($app,$modal,$path,$data,$list,$method){
            

            $modal->header_set(array('title'=>'Movement - Supplier to Warehouse','icon'=>'fa fa-taxi'));            
            
            $modal->input_add()->input_set('id','movement_modal_method')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;

            
            $modal->input_add()->input_set('id','movement_modal_po_id')
                    ->input_set('hide',true)
                    ->input_set('value',$data->po->id)
                    ;
            
            $modal->input_add()->input_set('id','movement_modal_movement_code')
                    ->input_set('label','Movement Code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->input_set('value','')
                    ;
            
            $modal->input_add()->input_set('id','movement_modal_po_code')
                    ->input_set('label','Purchase Order Code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->input_set('value','')
                    ;
            
            $modal->input_add()->input_set('label','Date')
                    ->input_set('id','movement_modal_date')
                    ->input_set('is_date_picker',true)
                    ->input_set('icon','fa fa-calendar')
                    ->input_set('value','')                    
                    ;
            
            $modal->input_select_add()
                    ->input_select_set('label','Warehouse Destination')
                    ->input_select_set('icon','fa fa-user')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','movement_modal_movement_to')
                    ->input_select_set('data_add',Tools::object_to_array($list->warehouse))
                    ->input_select_set('value',array())
                    ;
            
            $modal->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-user')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','movement_modal_movement_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ;
            
            $modal->textarea_add()->textarea_set('label','Cencellation Reason')
                    ->textarea_set('id','movement_modal_cancellation_reason')
                    ->textarea_set('value','')
                    ->div_set('id','movement_modal_div_cancellation_reason')                    
                    ;
            
            $modal->input_add()->input_set('id','movement_modal_delivery_note')
                    ->input_set('label','Delivery Note Number')
                    ->input_set('icon','fa fa-info')
                    //->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->input_set('value','')
                    ;  
            
            $modal->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','movement_modal_notes')
                    ->textarea_set('value','')
                    ->textarea_set('attrib',array())                    
                    ;
            
            
            $tbl = $modal->table_add();
            $tbl->table_set('class','table');
            $tbl->table_set('id','movement_modal_table_item');
            
            $modal->modal_button_footer_add("movement_modal_button_save",'button','',  App_Icon::detail_btn_save(),'Submit');
            
            $param = array(
                'movement_index_url'=>get_instance()->config->base_url().'movement/'
                ,'index_url'=>$path->index
                ,'ajax_search'=>$path->ajax_search
                ,'po_id'=>$data->po->id
                
            );
            
            $js = get_instance()->load->view('transaction/purchase/movement_js',$param,TRUE);
            $app->js_set($js);
        }
        
        
        
        
    }
?>
