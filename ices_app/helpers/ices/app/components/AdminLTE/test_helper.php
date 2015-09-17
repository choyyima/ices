<?php
    
    class Test extends Component_Engine{
        
        private $test_properties=array(
            "id"=>""
            ,"span"=>""
            ,"class"=>""
            ,"lookup_url"=>''
            ,"columns"=>array()
            ,"controls"=>array()
        );
        
        public function __construct(){
            parent::__construct();
            $this->test_properties = json_decode(json_encode($this->test_properties));
            $this->test_properties->id = "tbl_ajax_".$this->generate_id();
        }
        
        public function test_set($method,$data){
            switch($method){
            }
            return $this;
        }
        
        public function html_render_first(){     
            $test_props = $this->test_properties;
            $param = array(
                "id"=>$test_props->id
                ,"lookup_url"=>$test_props->lookup_url
                ,"controls"=>$test_props->controls
                ,"columns"=>$test_props->columns
            );
            $output = get_instance()->load->view('templates/app/components/test',$param,TRUE);

            return $output;
        }
        
        public function html_render_second(){
            $output=""; 
            return $output;
             
        }
    }
?>
