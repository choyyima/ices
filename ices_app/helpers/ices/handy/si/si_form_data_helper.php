<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SI_Form_Data{
    function __construct(){
        
    }
    function status_next_allowed_status_list_get($module_engine, $curr_status_val){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        
        $curr_status = SI::status_get($module_engine, $curr_status_val);
        
        $result = array(
            array('id'=>$curr_status['val'],
                'text'=>SI::get_status_attr($curr_status['text']),
                'method'=>$curr_status['method'],
                
            ),
        );
        
        $next_allowed_status_arr = SI::status_next_allowed_status_get($module_engine, 
            $curr_status_val
        );
        
        foreach($next_allowed_status_arr as $nas_idx=>$nas){
            $result[] = array(
                'id'=>$nas['val'],
                'text'=>SI::get_status_attr($nas['text']),
                'method'=>$nas['method'],
            );
        }
        
        return $result;
        //</editor-fold>
    }
    
    function ajax_table_search($config, $data, $opt=array('output_type'=>'array')){
        //<editor-fold defaultstate="collapsed">
        $opt = Tools::_arr($opt);
        
        $output_type = isset($opt['output_type'])?Tools::_str($opt['output_type']):'array';
        if(!in_array($output_type,array('object','array','query'))) $output_type = 'array';
        
        
        
        $result = array('header'=>array('total_rows'=>0),'data'=>array());
        $total_rows = 0;
        $final_data = array();
        
        $db = new DB();
        $records_page = isset($data['records_page'])?$data['records_page']:'5';
        $page = isset($data['page'])?$data['page']:'1';
        $sort_by = isset($data['sort_by'])?$data['sort_by']:'';
        
        
        $q_additional_filter='';
        $iadditional_filter = isset($data['additional_filter'])?
                Tools::_arr($data['additional_filter']):array();                
        $additional_filter_arr = isset($config['additional_filter'])?
            Tools::_arr($config['additional_filter']):array();
        for($i = 0;$i<count($additional_filter_arr);$i++){
            $ref_type_key = isset($additional_filter_arr[$i]['key'])?
                Tools::_str($additional_filter_arr[$i]['key']):'';
            if(isset($iadditional_filter[$ref_type_key])){
                if(Tools::_str($iadditional_filter[$ref_type_key])!==''){
                    $q_additional_filter .= $additional_filter_arr[$i]['query']
                        .$db->escape(Tools::_str($iadditional_filter[$ref_type_key]));
                }
            }
        }
        
        $q = isset($config['query']['basic'])?Tools::_str($config['query']['basic']):'';
        $q_group = isset($config['query']['group'])?Tools::_str($config['query']['group']):'';
        $q_where=(isset($config['query']['where'])?Tools::_str($config['query']['where']):' ')
            .$q_additional_filter
        ;

        $extra='';
        $order_by = '';
        if(strlen($sort_by)>0) {$order_by.=' order by '.$sort_by;}
        else {$order_by = isset($config['query']['order'])?Tools::_str($config['query']['order']):'';}
        $extra .= $order_by.'  limit '.(($page-1)*$records_page).', '.($records_page);
        $q_total_row = $q.$q_where.$q_group;
        $q_data = $q.$q_where.$q_group.$extra;
        $total_rows = $db->select_count($q_total_row,null,null);
        $rs = $db->query_array($q_data,99999999);


        $total_rs = count($rs);
        
        $final_data = $rs;
        $result['header']['total_rows'] = $total_rows;
        $result['data'] = $final_data;
        //$result = array("header"=>array("total_rows"=>$total_rows),"data"=>$final_rs);
        switch($output_type){
            case 'object':
                $result = json_decode(json_encode($result));
                break;
            case 'query':
                $result = $q_data;
                echo $q_data;
                die();
                break;
        }
        return $result;
        //</editor-fold>
    }
    
    
}

?>