<script>
    var purchase_invoice_expense_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    
    var purchase_invoice_expense_tbl ={
        
        tbl:null,
        init:function(tbl){
            this.tbl = tbl;
            $(this.tbl).find('tbody').empty();
        },
        row_tmplt :function(){
            var lrow = document.createElement('tr');
            var ltd_row_num = document.createElement('td');
            ltd_row_num.setAttribute('col_name','row_num');
            ltd_row_num.setAttribute('style','padding-top:16px');

            var ltd_desc = document.createElement('td');
            var linpt_desc = document.createElement('input');
            linpt_desc.setAttribute('col_name','description');
            linpt_desc.setAttribute('class','form-control');
            ltd_desc.appendChild(linpt_desc);
            
            var ltd_amount = document.createElement('td');            
            var linpt_amount = document.createElement('input');
            linpt_amount.setAttribute('col_name','amount');
            linpt_amount.setAttribute('class','form-control');
            $(linpt_amount).attr('style','text-align:right');
            ltd_amount.appendChild(linpt_amount);
            
            var ltd_action = document.createElement('td');
            ltd_action.setAttribute('col_name','action');
            
            lrow.appendChild(ltd_row_num);
            lrow.appendChild(ltd_desc);
            lrow.appendChild(ltd_amount);
            lrow.appendChild(ltd_action);
            
            return lrow;
        },
        calculate_total:function(){
            var ltotal_expense  = 0;
            $.each($(purchase_invoice_expense_tbl.tbl).find('tbody').children(),function(key, val){

                ltotal_expense+=parseFloat($(val).find('[col_name="amount"]').val().replace(/[,]/g,''));
            });
            $(purchase_invoice_expense_tbl.tbl).find('#purchase_invoice_expense_total').find('strong')[0].innerHTML = APP_CONVERTER.thousand_separator(ltotal_expense);
        },        
        row_delete:function(me){
            var ltbody = $(me).parents('tbody')[0];
            $(me).parent().parent().remove();
            for (var i = 0;i<$(ltbody).children().length;i++){
                var lrow = $($(ltbody).children()[i]).find('[col_name="row_num"]')[0];
                lrow.innerHTML = i+1;
            }
            
                
            
        },        
        row_append_new:function(){
            var lparent_pane = purchase_invoice_expense_parent_pane;
            var lrow_count = $(this.tbl).find('tbody')[0].children.length;
            lrow = this.row_tmplt();
            $(lrow).find('[col_name="row_num"]')[0].innerHTML = lrow_count+1;
            var lamount = $(lrow).find('[col_name="amount"]');
            APP_EVENT.init().component_set(lamount).type_set('input').numeric_set().render();
            lamount.val('0').blur();
            $(this.tbl).find('tbody').append(lrow);
            $(lrow).find('[col_name="amount"]').on('blur',function(){purchase_invoice_expense_tbl.calculate_total();});
            for(var i = 0;i<$(this.tbl).find('tbody').children().length;i++){
                var lmax_row = $(this.tbl).find('tbody').children().length-1;
                var lrow = $(this.tbl).find('tbody').children()[i];
                $(lrow).find('[col_name="action"]').empty();
                if(i === lmax_row){
                    var li_action = document.createElement('i');
                    li_action.setAttribute('col_name','action');
                    li_action.setAttribute('style','padding-top:10px;cursor:pointer;color:blue');
                    li_action.setAttribute('class','fa fa-plus');
                    $(li_action).on('click',function(){purchase_invoice_expense_tbl.row_append_new();});
                    $(lrow).find('[col_name="action"]')[0].appendChild(li_action);
                }
                else{
                    var li_action = document.createElement('i');
                    li_action.setAttribute('col_name','action');
                    li_action.setAttribute('style','padding-top:10px;cursor:pointer;color:red');
                    li_action.setAttribute('class','fa fa-trash-o');
                    $(li_action).on('click',function(){purchase_invoice_expense_tbl.row_delete($(this)[0]);});
                    $(lrow).find('[col_name="action"]')[0].appendChild(li_action);
                }
            }
        }
    }
    
    var purchase_invoice_expense_init = function(){
        var lparent_pane = purchase_invoice_expense_parent_pane;
        var ltbl = $(lparent_pane).find('#purchase_invoice_expense_table')[0];
        purchase_invoice_expense_tbl.init(ltbl);
        purchase_invoice_expense_tbl.row_append_new();
    }
    
    purchase_invoice_expense_init();
</script>