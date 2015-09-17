<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

get_instance()->load->helper('ices/handy/fpdf/fpdf.php');

class extended_fpdf extends FPDF{
    public $data_header = array();
    public $data_footer = array();
    public $LineHeight = 1;
    public $FooterY = 10;
    public function page_width_get(){
        $orientation = strtolower($this->CurOrientation);
        
        if($orientation === 'l') return ($this->CurPageSize[1] - ($this->rMargin + $this->lMargin));
        else return ($this->CurPageSize[0] - ($this->rMargin + $this->lMargin));
    }
    
    public function page_height_get(){
        $orientation = strtolower($this->CurOrientation);
        if($orientation === 'l') return $this->CurPageSize[0] - $this->tMargin - $this->bMargin;// - $this->FooterY;
        else return $this->CurPageSize[1] - $this->tMargin - $this->bMargin;// - $this->FooterY;
    }
    
    public function page_height_available_get(){
        return Tools::_int($this->page_height_get() - ($this->GetY() - $this->tMargin)
            - $this->FooterY);
    }
    
    function header(){
        
    }
    
    function footer(){
        //$this->SetFont('Times','',10);
        $this->SetY($this->FooterY*-2);
        $this->Cell(0,10,''.Tools::_date('','F d, Y H:i:s'), 0, 0);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}', 0, 0, 'R');
        
        
    }
    
    function NbLines($w,$txt)
    {
        //<editor-fold defaultstate="collapsed">
        //Computes the number of lines a MultiCell of width w will take
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb)
        {
            $c=$s[$i];
            if($c=="\n")
            {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
        //</editor-fold>
    }
}

class Printer {
    public $fpdf = null;
    private $fpdf_class_name = 'extended_fpdf';
    private $setting = array(
        'paper'=>array('orientation'=>'P','size'=>array(),'line_height'=>1),
        'margin'=>array('left'=>0,'right'=>0,'top'=>0),
        'font'=>array('size'=>12,'name'=>'Times','style'=>''),

    );
    
    private $data_header = array();
    private $data_footer = array();

    function __construct($fpdf_class_name=""){
        if($fpdf_class_name!==''){
            $this->fpdf_class_name=$fpdf_class_name;
        }
        
        $this->setting = json_decode(json_encode($this->setting));
    }
    
    
    function paper_set($paper_name){
        switch($paper_name){
            case 'thin-man':
                $this->setting->paper->orientation= 'P';
                $this->setting->paper->size = array(148,105);
                $this->setting->paper->line_height=4;
                $this->setting->margin->left=5;
                $this->setting->margin->top=3;
                $this->setting->margin->right=5;
                $this->font_set('Times',7,'');
                break;
            case 'A4':
                $this->setting->paper->orientation= 'P';
                $this->setting->paper->size = array(297 ,210);
                $this->setting->paper->line_height=4;
                $this->setting->margin->left=5;
                $this->setting->margin->top=3;
                $this->setting->margin->right=5;
                $this->font_set('Times',10,'');
                break;

        }
        
    }
    
    function set_orientation($orientation){
        $this->setting->paper->orientation = $orientation;
    }
    
    function normal(){
        $this->fpdf->SetFont($this->fpdf->FontFamily,'',$this->fpdf->FontSizePt);
    }
    
    function bold(){
        $this->fpdf->SetFont($this->fpdf->FontFamily,'B',$this->fpdf->FontSizePt);
    }
    
    function Ln(){
        $this->fpdf->Ln();
    }
    
    function font_set($name,$size,$style=''){
        $this->setting->font->name = $name;
        $this->setting->font->size = $size;
        $this->setting->font->style = $style;
        if($this->fpdf !== null){
            $this->fpdf->SetFont($this->setting->font->name,$this->setting->font->style,$this->setting->font->size);
        }
    }
    function data_header_set($data){
        $this->data_header = $data;
    }
    function data_footer_set($data){
        $this->data_footer = $data;
    }
    function start(){
        $this->fpdf = eval('return new '.$this->fpdf_class_name.'(
                $this->setting->paper->orientation,
                "mm",
                $this->setting->paper->size);');
        
        $this->fpdf->data_header = $this->data_header;
        $this->fpdf->data_footer = $this->data_footer;
        $this->fpdf->LineHeight = $this->setting->paper->line_height;
        
        $this->fpdf->SetMargins(
            $this->setting->margin->left,
            $this->setting->margin->top,
            $this->setting->margin->right
        );
        $this->fpdf->SetFont($this->setting->font->name,$this->setting->font->style,$this->setting->font->size);
        $this->fpdf->SetAutoPageBreak(true, 0);
        $this->fpdf->AddPage();
        $this->fpdf->AliasNbPages();
        
        
    }
    
    function size_get($size){
        $result = 0;
        $pWidth = $this->fpdf->page_width_get();
        if(strpos(Tools::_str($size),'%') ===false){
            $result = Tools::_float($size);
        }
        else{
            $result = (Tools::_float($size) / Tools::_float('100') * Tools::_float($pWidth));
        }
        return $result;
    }
    
    function print_table($col_config, $header,$data){
        //<editor-fold defaultstate="collapsed">
        $fName = $this->fpdf->FontFamily;
        $fSize = $this->fpdf->FontSizePt;
        $lHeight = $this->fpdf->LineHeight;
        $success = 1;
        if(count($header)>0){
            $this->fpdf->SetFont($fName,'B',$fSize+1);
            for($i = 0;$i<count($header);$i++){
                $x = $this->fpdf->GetX();
                $y = $this->fpdf->GetY();
                $size = $this->size_get($col_config[$i]['width']);
                $align = $col_config[$i]['align'];
                $this->fpdf->Cell($size,$lHeight,$header[$i]['data'],0,0,$align);  
                $border = isset($header[$i]['border'])?Tools::_bool($header[$i]['border']):false;
                if($border) $this->fpdf->Rect($x,$y,$size,$lHeight);
            }
            $this->fpdf->Ln();
        }
        $this->fpdf->SetFont($fName,'',$fSize);
        for($i = 0;$i<count($data);$i++){
            $nb = 0;
            for($j=0;$j<count($data[$i]);$j++){
                $size = $this->size_get($col_config[$j]['width']);
                $rdata = isset($data[$i][$j]['data'])?Tools::_str($data[$i][$j]['data']):'';
                $text = $rdata;
                $nb=max($nb,$this->fpdf->NbLines($size,$text));
            }
            $h=$lHeight*$nb;
            $y = $this->fpdf->GetY();
            if(Tools::_float($y)+Tools::_float($h)> Tools::_float($this->fpdf->page_height_get())){
                array_splice($data,0,$i);
                $this->fpdf->AddPage();
                $this->fpdf->AliasNbPages();
                $this->print_table($col_config,$header,$data);
                break;
            }
            for($j = 0;$j<count($data[$i]);$j++){
                $x = $this->fpdf->GetX();                
                $align = $col_config[$j]['align'];
                $size = $this->size_get($col_config[$j]['width']);
                $data_col_config = isset($data[$i][$j]['config'])?Tools::_arr($data[$i][$j]['config']):array();
                $border = isset($data_col_config['border'])?Tools::_bool($data_col_config['border']):false;
                $bold = isset($data_col_config['bold'])?Tools::_bool($data_col_config['bold']):false;
                $font_increment = isset($data_col_config['font_increment'])?Tools::_int($data_col_config['font_increment']):0;
                $rdata = isset($data[$i][$j]['data'])?Tools::_str($data[$i][$j]['data']):'';                
                $text = $rdata;
                //$this->fpdf->Cell($size,$lHeight,$text,1,0,$align);
                $font_weight = '';
                if($bold) $font_weight = 'B';
                $this->fpdf->SetFont($fName,$font_weight,$fSize+$font_increment);
                $this->fpdf->MultiCell($size,$lHeight,$text,0,$align);
                $this->fpdf->SetFont($fName,$font_weight,$fSize);
                $this->fpdf->SetXY($x+$size,$y);
                if($border) {
                    $this->fpdf->Rect($x,$y,$size,$h);
                }
                
            }            
            $this->fpdf->Ln($h);
        }
        //</editor-fold>
    }
    
    function subtitle($w, $h=null, $txt='', $border=0, $ln=0, $align='', $fill=false, $link=''){
        if(is_null($h)) $h = $this->fpdf->LineHeight;
        $lHeight = $this->size_get($h);
        $fName = $this->fpdf->FontFamily;
        $fSize = $this->fpdf->FontSizePt;
        
        $this->fpdf->SetFont($fName,'B',$fSize+1);
        $this->fpdf->Cell($w,$lHeight,$txt,$border,$ln,$align,$fill,$link);
        $this->fpdf->Ln();
        $this->fpdf->SetFont($fName,'',$fSize);
        
    }
    
    function Cell($w, $h=null, $txt='', $border=0, $ln=0, $align='', $fill=false, $link=''){
        if(is_null($h)) $h = $this->fpdf->LineHeight;
        $lWidth = $this->size_get($w);
        $this->fpdf->Cell($lWidth,$h,$txt,$border,$ln,$align,$fill,$link);
    }
    
    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false){
        if(is_null($h)) $h = $this->fpdf->LineHeight;
        $this->fpdf->MultiCell($w,$h,$txt,$border,$align,$fill);
    }
    
    function CellFit($w,$h=null,$txt,$border=0,$ln=0,$align='',$fill=false,$link=''){
        if(is_null($h)) $h = $this->fpdf->LineHeight;
        $size = $this->size_get($w);
        
        if(Tools::_float($this->fpdf->GetStringWidth($txt))>Tools::_float($size)){
            while(Tools::_float($this->fpdf->GetStringWidth($txt))>Tools::_float($size)){
                $txt = substr($txt,0,strlen($txt)-1);
            }
        }
        else{
            $idx = 0;
            while(Tools::_float($this->fpdf->GetStringWidth($txt))<Tools::_float($size)){
                $txt = $txt.substr($txt,$idx,1);
                if($idx<strlen($txt)-1) $idx++;
                else $idx = 0;
            }
        }
        
        $this->fpdf->Cell($size,$h,$txt,$border,$ln,$align,$fill,$link);
    }
    
    function output($filename="",$dest=""){
        $result = true;
        $this->fpdf->Output($filename,$dest);
        if($dest === 'F'){
            if(!file_exists($filename)) $result = false;
        }
        return $result;
    }    
    
    
    
    function print_table_smart($page_header,$page_table, $max_row_per_page=null){
        
        
        $row_counter = 0;
        
        $temp_data = array();
        $h_col_setting = isset($page_header['col_setting'])?$page_header['col_setting']:array();
        $h_tbl_data = isset($page_header['table_data'])?$page_header['table_data']:array();
        $t_col_setting = isset($page_table['col_setting'])?$page_table['col_setting']:array();
        $t_header_data = isset($page_table['header_data'])?$page_table['header_data']:array();
        $t_tbl_data = isset($page_table['table_data'])?$page_table['table_data']:array();
        
        if(is_null($max_row_per_page)){
            $max_row_per_page = (($this->fpdf->page_height_available_get() 
                - (count($h_tbl_data) * $this->fpdf->LineHeight))/$this->fpdf->LineHeight)-2;
        }
        
        
        
        for($i=0;$i<count($t_tbl_data);$i++){
            if($row_counter==$max_row_per_page){
                $this->print_table($h_col_setting,array(),$h_tbl_data);
                $this->print_table($t_col_setting,$t_header_data,$temp_data);
                $this->fpdf->AddPage();
                $temp_data = array();
                $row_counter = 0;
                $max_row_per_page = (($this->fpdf->page_height_available_get() 
                - (count($h_tbl_data) * $this->fpdf->LineHeight))/$this->fpdf->LineHeight)-2;
            }
            $temp_data[] = $t_tbl_data[$i];            
            $row_counter+=1;
        }
        $this->print_table($h_col_setting,array(),$h_tbl_data);
        $this->print_table($t_col_setting,$t_header_data,$temp_data);
    }
    
    function set_xy($x=null,$y=null){
        //<editor-fold defaultstate="collapsed">
        $this->fpdf->SetXY($x,$y);
        //</editor-fold>
    }
}

?>