<div class="form-group">
    <button id = 'sales_pos_intake_btn_new' class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> New</button>
</div>
<div>
    <table id="sales_pos_intake_table" class="table fixed-table pos-table">
        <thead>
            <tr>
            <th style="width:30px">#</th>
            <th style="width:200px"><?php echo Lang::get(array('Intake','Date')); ?> </th>
            <th style="">Warehouse</th>
            <th style='width:50px'></th>
            </tr>
        </thhead>
        <tbody>                    
        </tbody>
    </table>
</div>

<div class="modal fade" id="sales_pos_modal_intake" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog"  style="width:75%">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><i class="fa fa-info"></i> Intake </h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label> Warehouse From </label>
                <div class="input-group"> 
                    <span class="input-group-addon" style='min-width:0px'><i class='<?php echo App_Icon::warehouse() ?>'></i></span>                    
                    <select id = 'select_pos_modal_intake_warehouse_from' class='form-control'>
                    <?php 
                        for($i = 0;$i<count($warehouse_list);$i++){
                    ?>
                        <option val='<?php echo $warehouse_list[$i]['id'] ?>'><?php echo $warehouse_list[$i]['name'] ?></option>
                    <?php 
                        } 
                    ?>
                    </select>
                </div>                
            </div>
            <div class="form-group">
                <label> Intake Date </label>
                <div class="input-group"> 
                    <span class="input-group-addon" style='min-width:0px'><i class='<?php echo App_Icon::info() ?>'></i></span>                    
                    <input type="text" id="seles_pos_modal_intake_intake_date" class="form-control">
                </div>                
            </div>
            <div class="form-group">
            <table id = 'sales_pos_modal_intake_table' class='table' style = "font-size:14px;table-layout:fixed">
                <thead>
                    <th style="width:30px">#</th>
                    <th style='width:150px'></th>
                    <th>Product </th>
                    <th>Max Qty </th>
                    <th>Qty</th>
                    <th>Unit</th>
                </thead>
                <tbody>
                </tbody>
            </table>
            </div>
            <div class="form-group">
                <label> Notes </label>
                <textarea type="text" id="seles_pos_modal_intake_notes" class="form-control" rows='5'></textarea>
            </div>
        </div>
        <div class="modal-footer clearfix">
            <button id = "sales_pos_modal_intake_btn_ok" type="button" class="btn btn-primary pull-left"><i class="fa fa-check"></i> OK</button>
            <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-minus-circle"></i> Cancel</button>
        </div>
    </div>
</div>
</div>




