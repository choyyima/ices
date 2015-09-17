<?php
    
    class Table_Ajax extends Component_Engine{
        
        private $table_ajax_properties=array(
            "id"=>""
            ,"span"=>""
            ,"class"=>"table table-bordered dataTable table-hover lte-box-shadow"
            ,"lookup_url"=>''
            ,"columns"=>array()
            ,"controls"=>array()
            ,"base_href"=>''
            ,"base_href2"=>''
            ,'filters'=>array()
            ,'key_column'=>'id'
            ,'key_column2'=>''
            ,'label'=>''
            ,'hide_all'=>false
            ,'screen_refresh'=>true
        );
        
        public function __construct(){
            parent::__construct();
            $this->table_ajax_properties = json_decode(json_encode($this->table_ajax_properties));
            $this->table_ajax_properties->id = "tbl_ajax_".$this->generate_id();
        }
        
        public function filter_set($data){
            foreach($data as $row){
                $tmpt_row = array(
                    'field'=>''
                    ,'id'=>''
                    ,'type'=>''
                );
                $tmplt_row['field'] = $row['field'];
                $tmplt_row['id'] = $row['id'];
                $tmplt_row['type'] = isset($row['type'])?$row['type']:'input';
                $tmpt_row = json_decode(json_encode($row));
                $this->table_ajax_properties->filters[] = $tmplt_row;
            }
            return $this;
        }
        
        public function table_ajax_set($method,$data){
            switch($method){
                case 'id':
                    $this->table_ajax_properties->id = $data;
                    break;
                case 'lookup_url':
                    $this->table_ajax_properties->lookup_url = $data;
                    break;
                case 'key_column':
                    $this->table_ajax_properties->key_column = $data;
                    break;
                case 'key_column2':
                    $this->table_ajax_properties->key_column2 = $data;
                    break;
                case 'base_href':
                    $this->table_ajax_properties->base_href = $data;
                    break;
                case 'base_href2':
                    $this->table_ajax_properties->base_href2 = $data;
                    break;
                case 'controls':
                    $this->table_ajax_properties->controls = array();
                    foreach($data as $row){
                        $control_temp = array(
                            "label"=>""
                            ,"base_url"=>""
                            ,"confirmation"=>true
                        );
                        $control_temp= json_decode(json_encode($control_temp));
                        $control_temp->label = $row['label'];
                        $control_temp->base_url = $row['base_url'];
                        if(isset($row['confirmation'])) $control_temp->confirmation = $row['confirmation'];
                        $this->table_ajax_properties->controls[]=$control_temp;
                    }
                    break;
                case 'columns':
                    $this->table_ajax_properties->columns = array();
                    foreach($data as $row){
                        $col_temp = array(
                            "name"=>""
                            ,"label"=>""
                            ,"data_type"=>"text"
                            ,"is_key"=>false
                            ,"is_key2"=>false
                            ,'row_attrib'=>array()
                            ,'attribute'=>array()
                            ,'data_format'=>array()
                        );
                        $col_temp= json_decode(json_encode($col_temp));
                        $col_temp->name = $row['name'];
                        $col_temp->label = $row['label'];
                        if(isset($row['data_type'])) $col_temp->confirmation = $row['data_type'];
                        if(isset($row['is_key'])) $col_temp->is_key = $row['is_key'];
                        if(isset($row['is_key2'])) $col_temp->is_key2 = $row['is_key2'];
                        if(isset($row['row_attrib'])) $col_temp->row_attrib = $row['row_attrib'];
                        if(isset($row['attribute'])) $col_temp->attribute = $row['attribute'];
                        if(isset($row['data_format'])) $col_temp->data_format = $row['data_format'];
                        $this->table_ajax_properties->columns[]=$col_temp;
                    }
                    break;
                case 'label':
                    $this->table_ajax_properties->label = $data;
                    break;
                case 'class':
                    $this->table_ajax_properties->class = $data;
                    break;
                case 'hide_all':
                    $this->table_ajax_properties->hide_all = $data;
                    break;
                case 'screen_refresh':
                    $this->table_ajax_properties->screen_refresh = $data;
                    break;
            }
            return $this;
        }
        
        public function html_render_first(){
            $table_ajax_props = $this->table_ajax_properties;
            $param = array(
                "id"=>$table_ajax_props->id
                ,"lookup_url"=>$table_ajax_props->lookup_url
                ,"controls"=>$table_ajax_props->controls
                ,"columns"=>$table_ajax_props->columns
                ,"base_href"=>$table_ajax_props->base_href
                ,"base_href2"=>$table_ajax_props->base_href2
                ,'filters'=>$table_ajax_props->filters
                ,'key_column'=>$table_ajax_props->key_column
                ,'key_column2'=>$table_ajax_props->key_column2
                ,'label'=>$table_ajax_props->label 
                ,'class'=>$table_ajax_props->class
                ,'screen_refresh'=>$table_ajax_props->screen_refresh
            );
            $output = get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/components/table_ajax',$param,TRUE);
            $this->additional_script = str_replace(array("<script>","</script>"),''
                    ,get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/components/table_ajax_js',$param,TRUE));
            
            if($table_ajax_props->hide_all){
                $this->additional_script.=''
                    .'$("#'.$this->table_ajax_properties->id.'").closest(".form-group").addClass("hide_all");'
                .'';
            }
            
            return $output;
        }
        
        public function html_render_second(){
            $output=""; 
            return $output;
             
        }
    }
?>
