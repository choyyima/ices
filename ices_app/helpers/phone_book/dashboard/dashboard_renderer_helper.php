<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
$my_param = array(
    'file_path' => APPPATH . 'helpers/ices/dashboard/dashboard_renderer_helper.php',
    'src_class' => 'Dashboard_Renderer',
    'dst_class' => 'Dashboard_Renderer_Parent',
);
my_load_and_rename_class($my_param);

class Dashboard_Renderer extends Dashboard_Renderer_Parent {

    public static function dashboard_render($app, $panel) {
        $left = $panel->section_add()->section_set('class', 'col-lg-12 connectedSortable ui-sortable');
        $component = self::cobaa($app, $left, true);
    }

    public static function cobaa($app, $panel) {
        $buffer = $panel->dashboard_component_add();
        $buffer->properties_set('id', 'coba')->module_name_set('coba')->header_set('icon', "")->header_set('title', 'Cobaa');

        $table = $buffer->table_add();
        $table->table_set('id', 'coba');
        $table->table_set('class', 'table fixed-table');
        $table->table_set('columns', array("name" => "row_num", "label" => "#", 'col_attrib' => array('style' => 'width:30px')));
        $table->table_set('columns', array("name" => "id", "label" => "Cobaa", 'col_attrib' => array('style' => 'text-align:left')));
        $table->table_set('columns', array("name" => "app_name", "label" => "Test", 'col_attrib' => array('style' => 'text-align:right')));
        $table->table_set('columns', array("name" => "name", "label" => "Menjajal", 'col_attrib' => array('style' => 'text-align:right')));
        $table->table_set('columns', array("name" => "coba_status", "label" => "Menjajal", 'col_attrib' => array('style' => 'text-align:right')));
    }

}

?>