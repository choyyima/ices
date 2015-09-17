<?php
    $data = array(
        "table"=>array(
            "columns"=>$table_columns
            ,"id"=>$table_id
        )
    );
    
    $data = json_decode(json_encode($data));
?>

<table id ="<?php echo $data->table->id ?>"  class="table table-bordered dataTable lte-box-shadow"
        style = "font-size:14px;table-layout:fixed">
    <thead>
        <tr>
        <th style="width:30px">#</th>
        <?php foreach($data->table->columns as $col){
                echo '<th name="">'.$col->label.'</th>';
            }
        ?>
        <th style="width:40px">Action</th>
        </tr>
    </thead>
    <tbody id="<?php echo 'tbody_'.$data->table->id?>">
    </tbody>
</table>
