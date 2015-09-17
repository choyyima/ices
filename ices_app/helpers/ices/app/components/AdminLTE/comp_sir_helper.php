<?php
    
    class Comp_Sir extends Component_Engine{
        
        private $path = array(
            "view"=>'templates/app/components/comp_sir/'
        );
        
        private $div_properties = array(
            'id'  =>''
            ,'hide'=>false
        );
        
        private $input_select_properties=array(
            "id"=>""
            ,"class"=>""
            ,'label'=>''
            ,'disable_all'=>false
            ,'disable_all_type'=>'select2'
            ,'hide_all'=>false
            ,'ajax_url'=>''
            ,'data_support_url'=>''
        );
        
        private $detail_properties = array(
            'module_action'=>array('val'=>'','label'=>''),
            'module_name'=>array('val'=>'','label'=>'')
        );
        
        
        public function __construct(){
            parent::__construct();
            $this->input_select_properties = json_decode(json_encode($this->input_select_properties));
            $this->div_properties = json_decode(json_encode($this->div_properties));
            $this->detail_properties = json_decode(json_encode($this->detail_properties));
            $this->path = json_decode(json_encode($this->path));
            
            $this->input_select_properties->id = $this->generate_id();
            $this->div_properties->id = $this->generate_id();
            $this->input_select_properties->ajax_url = get_instance()->config->base_url().'sir/ajax_search/';
            $this->input_select_properties->data_support_url = get_instance()->config->base_url().'sir/data_support/sir_get';
            
        }
        
        public function div_set($method,$data){
            switch($method){
                case 'id':
                    $this->div_properties->id = $data;
                    break;
                case'hide':
                    $this->div_properties->hide = $data;
                    break;
            }
            return $this;
        }
        
        public function input_select_set($method,$data){
            $props = $this->input_select_properties;
            
            switch($method){
                case 'class':
                    $props->class=$data;
                    break;
                case 'id':
                    if(strlen($data)>0) $props->id=$data;
                    break;
                case 'hide_all':
                    $this->input_select_properties->hide_all = $data;
                    break;
                case 'module':
                    $this->input_select_properties->module = $data;
                    break;
                    
            }
            return $this;
        }
        
        public function detail_set($method,$data){
            $props = $this->detail_properties;
            
            switch($method){
                case 'module_action':
                    $props->module_action = $data;
                    break;
                case 'module_name':
                    $props->module_name = $data;
                    break;
                    
            }
            return $this;
        }
        
        public function additional_script_render(){
            
            $param = array(
                'properties'=>array(
                    'input_select_properties'=>json_decode(json_encode($this->input_select_properties),true)
                    ,'div_properties' => json_decode(json_encode($this->div_properties))
                    ,'detail_properties' => json_decode(json_encode($this->detail_properties))
                )
            );
            
            $js = str_replace(array("<script>","</script>"),""
                    ,get_instance()->load->view($this->path->view.'comp_sir_js'
                        ,$param 
                        ,TRUE)
                    ); 
            $this->additional_script.=$js;
            
            
            
        }       

        
        public function html_render_first(){     
            $param = array(
                'properties'=>array(
                    'input_select_properties'=>json_decode(json_encode($this->input_select_properties),true)
                    ,'div_properties' => json_decode(json_encode($this->div_properties))
                    ,'detail_properties' => json_decode(json_encode($this->detail_properties))
                )
            );

            
            $output = get_instance()->load->view($this->path->view.'comp_sir',$param,TRUE);
            return $output;
        }
        
        public function html_render_second(){
            $output=""; 
            
            return $output;
             
        }
    }
?>
