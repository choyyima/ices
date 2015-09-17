<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Timeline extends Component_Engine{
    private $timeline_properties=array(
        "id"=>""
        ,"data"=>array()
    );

    function __construct(){
        parent::__construct();
        $this->timeline_properties = json_decode(json_encode($this->timeline_properties));
        $this->timeline_properties->id = $this->generate_id();
    }
    
    public function data_set($data){
        // $data must be a 2 dimensional array of data
        foreach($data as $row){
            $data_temp = array(
                "date"=>$row['date']
                ,"icon"=>$row['icon']
                ,"time"=>$row['time']
                ,"header"=>array(
                    "label"=>$row['header_label']
                    ,"link"=>$row['header_link']
                    ,"link_label"=>$row['header_link_label']                    
                )
                ,"content"=>$row['content']
            );

            $this->timeline_properties->data[]=$data_temp;
        }
        return $this;
    }
    
    public function html_render_first(){     
            $timeline_props = $this->timeline_properties;
            $output='<ul class="timeline">';
            $curr_date = '';
            foreach($timeline_props->data as $row){
                
                $date = $row['date'];
                $icon = '<i class="'.$row['icon'].'"></i>';
                $time = $row['time'] == ''?'':'<span class="time"><i class="fa fa-clock-o"></i>'.$time.'</span>';
                $header_label = $row['header']['label'];
                $header_link = $row['header']['link'];
                $header_link_label = $row['header']['link_label'];
                $header = '<h3 class="timeline-header"><a href="'.$header_link.'">'.$header_link_label.'</a>'.$header_label.'</h3>';
                $content = $row['content'];
                if($curr_date != $date){
                   $output.= '
                        <li class="time-label">
                            <span class="bg-red">
                                '.$date.'
                            </span>
                        </li> 
                    ';
                   $curr_date = $date;
                }
                $output.='<li>';
                $output.='
                    '.$icon.'
                    <div class="timeline-item">
                        '.$time.'
                            
                        '.$header.'
                        <div class="timeline-body">
                            '.$content.'
                        </div>
                        <div class="timeline-footer">                            
                        </div>
                    </div>
                ';
                $output.='</li>';
                
            }
            $output.='
                    <li>
                        <i class="fa fa-clock-o"></i>
                    </li>
                </ul>';
            return $output;
        }

        public function html_render_second(){
            $output = "";
            
            return $output;
        }
    
}
?>