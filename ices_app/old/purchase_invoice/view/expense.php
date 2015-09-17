<div class="form-group">
    <label>Expense</label>
    <table class="table table-fixed" id="purchase_invoice_expense_table">
        <thead >
            <tr>
                <th style="width:30px">#</th>
                <th style="text-align: center">Description</th>
                <th style="text-align: center;width:200px">Amount<br/>(<?php echo Tools::currency_get() ?>)</th>
                <th style="width:30px"></th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr><td colspan="1"/>
                <td style="text-align:right"><strong>TOTAL</strong></td>
                <td colspan="1" id="purchase_invoice_expense_total" style="padding-left:20px;text-align:right"><strong>0.00</strog></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
<div style="margin-bottom:30px"> </div>