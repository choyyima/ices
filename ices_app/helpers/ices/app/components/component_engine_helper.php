<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Component_Engine {
    public $child=array();
    public $path = array();
    protected $properties = array(
        "id"=>""
        ,"name"=>""
        ,"title"=>""
        ,"span"=>"12"
    );

    protected $security_properties = array(
        'strict'=>false,
        'component'=>array('module'=>'','id'=>'')
    );
    protected $additional_script = "";

    function __construct(){
        $this->path = array(
            'app_base_dir'=>SI::type_get('ICES_Engine', 'ices','$app_list')['app_base_dir'],
        );
        $this->properties = json_decode(json_encode($this->properties));    
        $this->security_properties = json_decode(json_encode($this->security_properties));    
        $this->security_properties->component->module = get_instance()->uri->segment(1);
    }

    public function security_get($method){   
        $result = null;
        switch($method){
            case 'component':
                $result = $this->security_properties->component;
                break;
        }            
        return $result;
    }

    public function security_set($method,$data){
        switch($method){
            case 'strict':
                $this->security_properties->strict = $data;
                break;
        }            
        return $this;
    }

    public function component_set($method="",$data){
        switch($method){
            case 'name':
                $this->properties->name = $data;
                break;
            case 'id':
                $this->properties->id = $data;
                break;
            case  'span':
                $this->properties->span = $data;
                break;
            case 'title':
                $this->properties->title = $data;
                break;
        }
        return $this;
    }

    public function render(){            
        $result = "";
        if(isset($this->child)){
            $result = $this->render_child($this);                
            return $result;                
        }            
    }

    public function render_child($data)
    {
        //<editor-fold defaultstate="collapsed">
        $result = "";
        $permission_granted = true;
        if (isset($data->security_properties->strict)){
            if($data->security_properties->strict){
                $permission_granted = false;
                $lcomponent = $data->security_get('component');
                foreach(User_Info::component_security_get() as $comp_idx=>$comp){
                    if($comp['comp_id'] === $lcomponent->id){
                        $permission_granted = true;
                    }
                }

            }
        }

        if($permission_granted){
            if(method_exists($data,"html_render_first")){                
                $result= $data->html_render_first();
            }

            if(isset($data->child)){

                foreach($data->child as $key=>$val){
                    $result.=$this->render_child($val);
                }
            }
            if(method_exists($data,"html_render_second")){                
                $result.= $data->html_render_second();
            }

            if(method_exists($data,"additional_script_render")){                
                $data->additional_script_render();                   
            }
        }
        return $result;
        //</editor-fold>
    }

    function scripts_get(){
        //<editor-fold defaultstate="collapsed">
        $result = '';            
        $result = $this->get_additional_scripts($this);
        return $result;

        //</editor-fold>
    }

    function get_additional_scripts($data){
        $result = "";

        $result= $data->additional_script;

        if(isset($data->child)){
            foreach($data->child as $key=>$val){
                $result.=$data->get_additional_scripts($val);
            }

        }

        return $result;
    }

    public function dashboard_component_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/dashboard_component_helper');
        $obj = new Dashboard_Component();
        $this->child[]=$obj;
        return $obj;
    }

    public function button_group_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/button_group_helper');
        $obj = new Button_Group();
        $this->child[]=$obj;
        return $obj;
    }

    public function button_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/button_helper');
        $button = new Button();
        $this->child[]=$button;
        return $button;
    }

    public function form_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/form_helper');
        $form = new Form();
        $this->child[]=$form;
        return $form;
    }

    public function form_group_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/form_group_helper');
        $fgroup = new Form_Group();
        $this->child[]=$fgroup;
        return $fgroup;
    }

    public function input_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/input_helper');
        $input = new Input();
        $this->child[]=$input;
        return $input;
    }

    public function datetimepicker_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/datetimepicker_helper');
        $obj = new Datetimepicker();
        $this->child[]=$obj;
        return $obj;
    }

    public function input_raw_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/input_raw_helper');
        $input = new Input_Raw();
        $this->child[]=$input;
        return $input;
    }

    public function div_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/div_helper');
        $div = new Div();
        $this->child[]=$div;
        return $div;
    }

    public function section_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/section_helper');
        $section = new Section();
        $this->child[]=$section;
        return $section;
    }

    public function span_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/span_helper');
        $span = new Span();
        $this->child[]=$span;
        return $span;
    }

    public function hr_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/hr_helper');
        $hr = new Hr();
        $this->child[]=$hr;
        return $hr;
    }

    public function label_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/label_helper');
        $label = new Label();
        $this->child[]=$label;
        return $label;
    }

    public function label_span_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/label_span_helper');
        $label_span = new Label_Span();
        $this->child[]=$label_span;
        return $label_span;
    }

    public function img_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/img_helper');
        $img = new Img();
        $this->child[]=$img;
        return $img;
    }

    public function nav_tab_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/nav_tab_helper');
        $nav_tab = new Nav_Tab();
        $this->child[]=$nav_tab;
        return $nav_tab;
    }

    public function input_select_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/input_select_helper');
        $input_select = new Input_Select();
        $this->child[]=$input_select;
        return $input_select;
    }

    public function input_select_table_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/input_select_table_helper');
        $obj = new Input_Select_Table();
        $this->child[]=$obj;
        return $obj;
    }

    public function input_select_detail_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/input_select_detail_helper');
        $obj = new Input_Select_Detail();
        $this->child[]=$obj;
        return $obj;
    }

    public function input_select_detail_editable_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/input_select_detail_editable_helper');
        $obj = new Input_Select_Detail_Editable();
        $this->child[]=$obj;
        return $obj;
    }

    public function textarea_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/textarea_helper');
        $textarea = new TextArea();
        $this->child[]=$textarea;
        return $textarea;
    }

    public function select_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/select_helper');
        $select = new Select();
        $this->child[]=$select;
        return $select;
    }

    public function table_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/table_helper');
        $input = new Table();
        $this->child[]=$input;
        return $input;
    }

    public function table_input_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/table_input_helper');
        $input = new Table_Input();
        $this->child[]=$input;
        return $input;
    }

    public function generate_id(){
        return str_replace(".","",uniqid('',TRUE));
    }

    public function test_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/test_helper');
        $test = new Test();
        $this->child[]=$test;
        return $test;
    }

    public function accordion_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/accordion_helper');
        $obj = new Accordion();
        $this->child[]=$obj;
        return $obj;
    }

    public function button_toggle_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/button_toggle_helper');
        $button_toggle = new Button_Toggle();
        $this->child[]=$button_toggle;
        return $button_toggle;
    }

    public function timeline_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/timeline_helper');
        $obj = new Timeline();
        $this->child[]=$obj;
        return $obj;
    }

    public function table_ajax_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/table_ajax_helper');
        $obj = new Table_Ajax();
        $this->child[]=$obj;
        return $obj;
    }

    public function custom_component_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/custom_component_helper');
        $obj = new Custom_Component();
        $this->child[]=$obj;
        return $obj;
    }

    public function ul_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/ul_helper');
        $obj = new Ul();
        $this->child[]=$obj;
        return $obj;
    }

    public function li_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/li_helper');
        $obj = new Li();
        $this->child[]=$obj;
        return $obj;
    }

    public function modal_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/modal_helper');
        $obj = new Modal();
        $this->child[]=$obj;
        return $obj;
    }

    public function form_select_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/form_select_helper');
        $obj = new Form_Select();
        $this->child[]=$obj;
        return $obj;
    }

    public function i_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/i_helper');
        $obj = new I();
        $this->child[]=$obj;
        return $obj;
    }

    public function comp_sir_add(){
        get_instance()->load->helper($this->path['app_base_dir'].'app/components/'.ICES_Engine::$app['app_theme'].'/comp_sir_helper');
        $obj = new Comp_Sir();
        $this->child[]=$obj;
        return $obj;
    }

    protected function generate_additional_script(){}


}
?>
