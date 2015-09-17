<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    
    class Dashboard_Component extends Component_Engine{
        
        public $props=array(
            'id'=>''
        );
        
        public $div_props = array(
            'id'=>''
            ,'class'=>'box box-primary'
            ,'module_name'=>''
        );
        
        public $header_props = array(
            'class'=>'box box-primary'
            ,'icon'=>''
            ,'title'=>''
            
        );
        
        public $component_props = array(
           
        );
        
        function __construct(){
            parent::__construct();
            $this->component_props = json_decode(json_encode($this->component_props));
            $this->div_props = json_decode(json_encode($this->div_props));
            $this->header_props = json_decode(json_encode($this->header_props));
            $this->props = json_decode(json_encode($this->props));
            
            $this->props->id = $this->generate_id();
            $this->div_props->id = $this->props->id.'_div';
            
        }
        
        public function properties_set($method = "", $data=""){
           $props = $this->props;
            switch($method){
                case 'id':
                    $props->id = $data;
                    $this->div_props->id = $this->props->id.'_div';
                    break;
            }
           return $this;
        }
        
        public function div_set($method = "", $data=""){
            
            return $this;
        }
        
        public function header_set($method = "", $data=""){
            $header_props = $this->header_props;
            switch($method){
                case 'icon':
                    $header_props->icon = $data;
                    break;
                case 'title':
                    $header_props->title = $data;
                    break;
                
            }
            return $this;
        }
        
        public function component_set($method = "", $data=""){
            
            return $this;
        }
        
        public function module_name_set($data){
            $this->div_props->module_name = $data;
            return $this;
        }
        
        public function html_render_first(){            
            
            $output = '';            
            $output .= '
                <div class="'.$this->div_props->class.'" id="'.$this->div_props->id.'" module_name="'.$this->div_props->module_name.'" dashboard_component>
                    <div class="box-header">
                        <div class="pull-right box-tools">
                            <button id = "'.$this->props->id.'_refresh" class="btn btn-primary btn-sm refresh-btn" data-toggle="tooltip" title="Reload"><i class="fa fa-refresh"></i></button>
                            <button id = "'.$this->props->id.'_minus" class="btn btn-primary btn-sm" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                            <button id = "'.$this->props->id.'_remove" class="btn btn-primary btn-sm" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
                        </div>
                        '.APP_Icon::html_get($this->header_props->icon).'
                        <h3 class="box-title">'.$this->header_props->title.'</h3>
                    </div>
                    <div class="box-body">'.
                '';
            
            
            $this->generate_additional_script();
            
            return $output;
        }
        
        protected function generate_additional_script(){
           $this->additional_script='
               
            ';
        }
        
        public function html_render_second(){
            $output=''
                . '</div>'
                    . '<div id = "'.$this->props->id.'_overlay" class=""></div>'
                    . '<div id = "'.$this->props->id.'_loading" class=""></div>'
                . '</div>'; 
            return $output;             
        }
        
        
        
    }
?>