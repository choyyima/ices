<?php
class App_Content{
    
    private $path = array('content'=>'','app_base_dir'=>'');
    private $content_header_major="";
    private $content_header_major_icon="";
    private $content_header_minor="";
    private $breadcrumb=array("name"=>"","href"=>"");
    private $content = "";

    
    function __construct(){
        $this->path['app_base_dir'] = SI::type_get('ICES_Engine', 'ices', '$app_list')['app_base_dir'];
        $this->path['content'] = $this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/body/content';
    }
    
    public function content_header_set($major,$major_icon="",$minor){
        $this->content_header_major = $major;
        $this->content_header_major_icon = $major_icon;
        $this->content_header_minor = $minor;
    }
    
    public function breadcrumb_set($name="",$href=""){
        
        $this->breadcrumb['name']=$name;
        $this->breadcrumb['href']=ICES_Engine::$app['app_base_url'].$href;
    }
    
    public function content_set($content){
        $this->content .= $content;
    }
    
    public function html_generate(){
        $result = "";
        $param = array(
            "content_header_major"=>$this->content_header_major
            ,"content_header_major_icon"=>$this->content_header_major_icon
            ,"content_header_minor"=>$this->content_header_minor
            ,"breadcrumb"=>$this->breadcrumb
            ,"content"=>$this->content
            ,"msg"=>Message::get()
            ,'base_url'=>ICES_Engine::$app['app_base_url'],
        );
        $result = get_instance()->load->view($this->path['content'],$param,TRUE);
        return $result;              
    }
}

?>
