<?php 
    $data['input_selector_id']=$input_selector_id;
    $data['raw_data_id'] = 'raw_data_'.$id;
    $data['format_id'] = 'format_'.$id;
    $data['ajax_url'] = $ajax_url;
    
    $data['raw_data'] = $raw_data;
    $data['min_length'] = $min_length;
    $data['value']=$value;
    $data['detail'] = array(
        "rows"=>$detail_rows
        ,"id"=>$detail_id
        ,'ajax_url'=>$detail_ajax_url
        ,'selected_value'=>''
    );
    
    
    $data = json_decode(json_encode($data));

?>
<script>
    
    $("#<?php echo $data->input_selector_id; ?>").on("change",function(e){
        var cont = true;
        <?php foreach($data->detail->rows as $row){?>                
            $("#<?php echo $data->detail->id.'_'.$row->name ?>")[0].innerHTML = "";
        <?php } ?>
        
        var json_data = {data:$(this).val()};
        var url = "<?php echo $data->detail->ajax_url ?>";
        if(url === '') cont = false;
        
        if(cont){
            var result = APP_AJAX.ajaxPOST(url,json_data);
            var response = null;
            if(typeof result.response !== 'undefined'){
                response = result.response;
            }
            else{
                response = result;
            }
            //$.each(response,function(key,val){            
                <?php foreach($data->detail->rows as $row){?>
                    if(typeof response.<?php echo $row->name ?> !== 'undefined')
                    $("#<?php echo $data->detail->id.'_'.$row->name ?>")[0].innerHTML = response.<?php echo $row->name ?>;
                <?php } ?>
            //});

        }
        
        
    ;});



</script>