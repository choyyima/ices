<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard_Renderer {

    public static function dashboard_render($app, $pane) {
        $left_section = $pane->section_add()->section_set('class', 'col-lg-6 connectedSortable ui-sortable');
        $right_section = $pane->section_add()->section_set('class', 'col-lg-6 connectedSortable ui-sortable');

        $js = get_instance()->load->view(ICES_Engine::$app['app_base_dir'] . 'dashboard/dashboard_js', array(), TRUE);
        $app->js_set($js);
    }

}

?>