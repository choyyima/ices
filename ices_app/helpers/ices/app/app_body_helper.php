<?php
    class App_Body{
        
        private $root = array(
            "lib"=> ""
        );
        private $title = "";
        private $title_icon = "";
        private $path = array();
        
        private $menu_item=null;
        private $user_info=null;
        private $content = null;
        
        function __construct($root_lib){
            $this->root['lib'] = $root_lib;
            
            $t_app_base_dir = SI::type_get('ICES_Engine', 'ices','$app_list')['app_base_dir'];
            $this->path = array(
                'app_base_dir'=>$t_app_base_dir,
                "top_nav"=>$t_app_base_dir.'templates/'.ICES_Engine::$app['app_theme'].'/body/top_nav'
                ,"left_nav"=>$t_app_base_dir.'templates/'.ICES_Engine::$app['app_theme'].'/body/left_nav'
            );
            
            $this->menu_set();            
            $this->user_info = User_Info::get();
            
            get_instance()->load->helper($this->path['app_base_dir'].'app/app_content_helper');
            $this->content = new App_Content();
            
            
        }
        
        private function menu_set(){
            $app_base_dir = SI::type_get('ICES_Engine','ices','$app_list')['app_base_dir'];
            
            get_instance()->load->helper($app_base_dir.'security_menu/security_menu_engine');
            $menu  = Security_Menu_Engine::current_user_menu_get();
            $this->menu_item = $menu;
        }
        
        public function set_title($title,$title_icon){
            $this->title = $title;
            $this->title_icon = $title_icon;
        }
        
        public function content_header_set($major="",$major_icon="",$minor=""){
            $this->content->content_header_set($major,$major_icon,$minor);
        }
        
        public function breadcrumb_set($name,$href){
            $this->content->breadcrumb_set($name,$href);        
        }
        
        public function content_set($content){
            $this->content->content_set($content);
        }
        public function top_nav_generate(){
            $result = "";
            $param = array(
                'title'=>$this->title
                ,'title_icon'=>$this->title_icon
                ,'base_url'=>ICES_Engine::$app['app_base_url']
                ,'lib_root'=>$this->root['lib']
                ,'user_info'=>$this->user_info
                
            );
            $result = get_instance()->load->view($this->path['top_nav'], $param, TRUE);
            return $result;
        }
        
        public function left_nav_generate(){
            $result = "";
            $param = array(
                'title'=>$this->title
                ,'title_icon'=>$this->title_icon
                ,'base_url'=>ICES_Engine::$app['app_base_url']
                ,'lib_root'=>$this->root['lib']
                ,'user_info'=>$this->user_info
                ,'menu_item'=> $this->menu_item
            );
            $result = get_instance()->load->view($this->path['left_nav'], $param, TRUE);
            return $result;
        }
        
        public function html_generate($menu_properties){
            $result = "";
            $top_nav = $this->top_nav_generate();
            $left_nav = $this->left_nav_generate();
            $content = $this->content->html_generate();
            $param = array(
                'title'=>$this->title
                ,'title_icon'=>$this->title_icon
                ,'lib_root'=>$this->root['lib']
                ,'user_info'=>$this->user_info
                ,'top_nav'=>$top_nav
                ,'left_nav'=>$left_nav
                ,'content'=>$content    
                ,'menu_properties'=>$menu_properties
            );
            $result = get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/body', $param, TRUE);
            
            return $result;
        }
        
    }
?>
