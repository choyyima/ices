<div class="form-group" id="sales_pos_intake">
    <label style="margin-right:5px"> <?php echo Lang::get(array('Product Intake')); ?></label>
    <button class="btn btn-default hide_all" id="sales_pos_new_intake_final"><i class="fa fa-plus"></i> <?php echo Lang::get(array('Intake')).' Final '.Lang::get('New'); ?></button>
    <div class='table-responsive' style='overflow-x:auto'>
    <table id="sales_pos_movement_intake_table" class="table fixed-table pos-table">
        <thead>
            <tr>
            <th class="pos-table-row-num">#</th>
            <th class="pos-movement-table-code">Code</th>
            <th class="pos-movement-table-date"><?php echo Lang::get(array('Product Intake','Date')); ?></th>
            <th class="pos-movement-table-product">Product/s</th>
            <th class="pos-table-status">Status</th>
            <th style='width:50px'></th>
            </tr>
        </thhead>
        <tbody>                    
        </tbody>
    </table>
    </div>
</div>

<div class="form-group" id="sales_pos_delivery">
    <label style="margin-right:5px"> <?php echo Lang::get(array('Delivery Order')); ?></label>
    <button class="btn btn-default hide_all" id="sales_pos_new_dof"><i class="fa fa-plus"></i> <?php echo Lang::get(array('Delivery Order Final')).' '.Lang::get('New'); ?></button>
    <div class='table-responsive' style='overflow-x:auto'>
    <table id="sales_pos_movement_delivery_table" class="table fixed-table pos-table">
        <thead>
            <tr>
            <th class="pos-table-row-num">#</th>
            <th class="pos-movement-table-code">Code</th>
            <th class="pos-movement-table-date"><?php echo Lang::get(array('Delivery Order','Date')); ?></th>
            <th class="pos-movement-table-product">Product/s</th>
            <th class="pos-table-status">Status</th>
            <th style='width:50px'></th>
            </tr>
        </thhead>
        <tbody>                    
        </tbody>
    </table>
    </div>
</div>

<div class="modal fade" id="sales_pos_movement_modal_product" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog"  style="width:95%">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><?php echo APP_Icon::html_get(App_Icon::delivery_order()); ?> <span id="sales_pos_movement_modal_product_title"></span> </h4>
        </div>
        <div class="modal-body">            
            <div class="row">
            <div class="final_movement_product" style="padding-right:0px;overflow-x:auto">
            <table id = 'sales_pos_movement_modal_product_table' class='table' style = "font-size:12px;table-layout:fixed;">
                <thead>
                    <th class='table-row-num'><br/>#</th>
                    <th class='pos-table-product-img'></th>
                    <th style="width:120px;max-width:120px"><br/>Product </th>
                    <th style="width:100px;text-align:right">Ordered<br/>Qty</th>
                    <th style='width:35px;'><br/>Unit</th>                    
                </thead>
                <tbody>
                </tbody>
            </table>
            </div>
            <div class="final_movement_qty" style="padding-left:0px;overflow-x:auto">
            <table id = 'sales_pos_movement_modal_qty_table' class='table ' 
                   style = "font-size:12px;">
                <thead><tr></tr>
                </thead>
                <tbody>
                </tbody>
            </table>    
            </div>
            </div>
        </div>
        <div class="modal-footer clearfix">
            <button id = "sales_pos_movement_modal_product_btn_ok" type="button" class="btn btn-primary pull-left"><i class="fa fa-check"></i> OK</button>
            <button id = "sales_pos_movement_modal_product_btn_cancel" type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-minus-circle"></i> Cancel</button>
        </div>
    </div>
</div>
</div>





