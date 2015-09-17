<div class="form-group" style="">
<div class=""><label>Expense</label></div>
<div class="table-responsive" id="">
    <table id="purchase_invoice_expense_table" class="table fixed-table" style="font-size:12px">
        <thead>
            <tr><th col_name="row_num" class="table-row-num">#</th>
                <th col_name="description" class="">Description</th>
                <th col_name="amount" style="text-align:right;width:200px" class="">Amount</th>
                <th col_name="action" class="table-action"></th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
            <?php $f_colspan=1; ?>
            <tr><td colspan="<?php echo $f_colspan; ?>" style="text-align:right;border-top:2px solid #ddd">
                </td>
                <td style="text-align:right;border-top:2px solid #ddd">
                    <strong >Total <?php echo '('.Tools::currency_get().')'?></strong></td>
                <td style="text-align:right;border-top:2px solid #ddd"><span><strong id="purchase_invoice_expense_total" ></strong></span>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
</div>