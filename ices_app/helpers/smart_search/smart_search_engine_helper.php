<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Smart_Search_Engine {


    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'smart_search/',
            'smart_search_engine'=>'smart_search/smart_search_engine',
            'smart_search_data_support' => 'smart_search/smart_search_data_support',
            'smart_search_renderer' => 'smart_search/smart_search_renderer',
            'ajax_search'=>get_instance()->config->base_url().'smart_search/ajax_search/',
            'data_support'=>get_instance()->config->base_url().'smart_search/data_support/',
        );

        return json_decode(json_encode($path));
    }

}
?>