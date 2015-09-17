<?php
class App{
    
    private $title="";
    private $title_icon="";
    private $root = array(
        "lib"=> ""
    );
    public $engine=null;
    
    private $body=null;
    private $js = "";
    private $html="";
    
    private $menu = array(
        'collapsed'=>true
    );
    
    private $path = array();
    
    function __construct() {
        
        $this->path = array(
            'app_base_dir'=>SI::type_get('ICES_Engine', 'ices', '$app_list')['app_base_dir'],
        );
        
        $root_lib = get_instance()->config->base_url().'libraries/'; 
        $this->root['lib'] = $root_lib;
        get_instance()->load->helper($this->path['app_base_dir'].'/app/app_message');
        
        get_instance()->load->helper($this->path['app_base_dir'].'/app/app_body_helper');
        $this->body = new App_Body($root_lib);
        
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/component_engine_helper');
        get_instance()->load->helper($this->path['app_base_dir'].'app/app_engine_helper');        
        $this->engine = new App_Engine();
        
        
    }
    
    
    
    public function set_title($title,$title_icon=""){
        $this->title = $title;
        $this->title_icon = $title_icon;
        $this->body->set_title($title,$title_icon);
    }
    
    public function set_menu($method,$data){
        switch($method){
            case 'collapsed':
                $this->menu['collapsed'] = $data;
                break;
                
        }
    }
    
    
    public function set_breadcrumb($name,$href){
        
        $this->body->breadcrumb_set($name,str_replace(' ','_',$href));
        
    }
    
    public function set_content_header($major,$major_icon="",$minor=""){
        $this->body->content_header_set($major,$major_icon,$minor);
    }
    
    
    public function render(){
        
        $output = "";
        $header = $this->header_get();
        
        $this->body->content_set($this->engine->render());
        $this->body->content_set($this->html);
        $body = $this->body->html_generate($this->menu);
        $footer = $this->footer_get();
        
        $output = "<!DOCTYPE html>
            <html>";        
        $output.= $header;
        $output.= $body;
        $output.= $footer;
        $output.= "</html>";
        
        echo $output;
    }
    
    function header_get(){
        $result = "";
        $param = array(
            'title'=>$this->title
            ,'lib_root'=>$this->root['lib']
        );
        $result = get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/header', $param, TRUE);
        return $result;        
    }
    
    function footer_get(){
        $result = "";
        $param = array(
            'title'=>$this->title
            ,'lib_root'=>$this->root['lib']
            
        );
        $result = get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/footer', $param, TRUE);
        $result.= get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/etc/modal_print/modal_print_view', array(), TRUE);
        $result.= get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/etc/modal_confirmation', $param, TRUE);
        $result.= get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/etc/modal_confirmation_submit/modal_confirmation_submit_view', $param, TRUE);
        $result.= get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/etc/modal_confirmation_cancel/modal_confirmation_cancel_view', $param, TRUE);
        $result.= get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/etc/modal_mail/modal_mail_view', $param, TRUE);
        $result.= get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/etc/scroll_up_down/scroll_up_down_view', $param, TRUE);
        $result.= get_instance()->load->view($this->path['app_base_dir'].'app_message/app_message_preview_modal', array(), TRUE);
        
        $scripts = '';
        //$scripts.= ' <script type="text/javascript">';
        $scripts.='
            
            $(document).ready(function(){
            $("[data-mask]").inputmask();
            $(function() {';
        
        $scripts.= str_replace(array("<script>","</script>"),'',get_instance()->load->view(ICES_Engine::$app['app_base_dir'].'notification/notification_js',array(),TRUE));
        $scripts.= str_replace(array("<script>","</script>"),'',get_instance()->load->view($this->path['app_base_dir'].'app_message/app_message_nav_js',array(),TRUE));

        $scripts.= $this->engine->scripts_get();
        $scripts.=$this->js;
        $scripts.= str_replace(array("<script>","</script>"),'',get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/etc/modal_confirmation_submit/modal_confirmation_submit_js', array(), TRUE));
        $scripts.= str_replace(array("<script>","</script>"),'',get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/etc/modal_print/modal_print_js', array(), TRUE));
        $scripts.= str_replace(array("<script>","</script>"),'',get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/etc/modal_confirmation_cancel/modal_confirmation_cancel_js', array(), TRUE));
        $scripts.= str_replace(array("<script>","</script>"),'',get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/etc/modal_mail/modal_mail_js', array(), TRUE));
        $scripts.= str_replace(array("<script>","</script>"),'',get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/etc/scroll_up_down/scroll_up_down_js', array(), TRUE));
        $scripts.=' $("#app_container").show(); ';
        
        if(get_instance()->config->config['MY_']['disable_console']){
            $scripts.='
                
            ';
        }
        
        $scripts.=' })});';
        
        if(get_instance()->config->config['MY_']['minify_js']){
            get_instance()->load->helper('minifier');
            $scripts=Minifier::minify($scripts);
        }
        
        $id = rand(1,10000).rand(1,10000).rand(1,10000);
        
        
        
        $pack_js = get_instance()->config->config['MY_']['pack_js'];
        $pack_js_media = get_instance()->config->config['MY_']['pack_js_media'];
        
        
        $js_dir = 'js_file/dynamic/';
        $js_file = glob($js_dir.'*.txt');
        foreach($js_file as $i=>$row){
            if(filemtime($row) < strtotime(Tools::_date('','Y-m-d H:i:s','-PT5M'))){
                unlink($row);
            }
        }
        
        if($pack_js){
            $js_url = ICES_Engine::$app['app_base_url'].'common_ajax_listener/load_js/'.$id;
            
            switch($pack_js_media){
                case 'file':
                    $filename = $js_dir.$id.'.txt';
                    $f = fopen($filename,'wb');
                    if(!$f) die('Unable to create new js file');
                    fwrite($f,$scripts,strlen($scripts));
                    fclose($f);
                    break;
            }
            
            
                
            $result.='<script src="'.$js_url.'" type="text/javascript"></script>';
        }
        else{
            $result.='<script type="text/javascript">'.$scripts.'</script>';
        }
        
        return $result; 
    }
    
    function js_set($js=""){
        $this->js .= str_replace(array("<script>","</script>"),'',$js);
    }
    
    function html_set($html=""){
        $this->html .= $html;
    }
    
    
}
?>
