<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard_Engine {
    public static function path_get(){
        $app = ICES_Engine::$app;
        $path = array(
            'index'=>$app['app_base_url'].'dashboard/'
            ,'dashboard_engine'=>$app['app_base_dir'].'dashboard/dashboard_engine'
            ,'dashboard_data_support'=>$app['app_base_dir'].'dashboard/dashboard_data_support'
            ,'dashboard_renderer' => $app['app_base_dir'].'dashboard/dashboard_renderer'
            ,'ajax_search'=>$app['app_base_url'].'dashboard/ajax_search/'
            ,'data_support'=>$app['app_base_url'].'dashboard/data_support/'
        );

        return json_decode(json_encode($path));
    }
}
?>
