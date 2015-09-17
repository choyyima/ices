<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SI_Form_Renderer{
    function __construct(){
        
    }
    
    function status_log_tab_render($status_log_tab, $config){
        //<editor-fold defaultstate="collapsed">
        $id = Tools::_str($config['id']);
        $module_name = Tools::_str($config['module_name']);
        $field_name = Tools::_str($module_name.'_status');
        $table_log_name = Tools::_str($module_name.'_status_log');
        $module_engine = Tools::_str($config['module_engine']);
        $limit = 20;
        $db = new DB();
        $q = '
            select null row_num
                ,t1.moddate
                ,t1.'.$field_name.'
                ,t2.username user_name
            from '.$table_log_name.' t1
                inner join ices_db.employee t2 on t1.modid = t2.id
            where t1.'.$module_name.'_id = '.$db->escape($id).'
                order by t1.moddate desc
            limit '.$limit.'
        ';
        $rs = $db->query_array($q);
        for($i = 0;$i<count($rs);$i++){
            $rs[$i]['row_num'] = $i+1;
            $rs[$i][$field_name.'_name']  = SI::get_status_attr(                    
                SI::status_get($module_engine,
                    $rs[$i][$field_name]
                )['text']
            );
            $rs[$i]['moddate'] = Tools::_date($rs[$i]['moddate'],'F d, Y H:i:s');

        }
        $status_log = $rs;

        $table = $status_log_tab->form_group_add()->table_add();
        $table->table_set('id',$module_name.$module_name.'_status_log_table');
        $table->table_set('class','table fixed-table');
        $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
        $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array()));
        $table->table_set('columns',array("name"=>$field_name."_name","label"=>"Status",'col_attrib'=>array()));
        $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array()));
        $table->table_set('data',$status_log);
        //</editor-fold>
    }
    
}

?>