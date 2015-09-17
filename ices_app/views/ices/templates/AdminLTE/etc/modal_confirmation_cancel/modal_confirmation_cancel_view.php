
    <div class="modal fade" id="modal_confirmation_cancel" tabindex="-1" role="dialog" aria-hidden="true" parent_pane="">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="fa fa-question"></i> Confirmation</h4>
            </div>
            
            <div class="modal-body" >
                <div class="form-group">
                <p id ="modal_confirmation_cancel_msg">Are you sure want to cancel your transaction?</p>
                </div>
                <div class="form-group">
                    <label>Cancellation Reason</label>
                    <textarea id="modal_confirmation_cancel_cancellation_reason" class="form-control" rows="5"></textarea>
                </div>
            </div>
            <div class="modal-footer clearfix">
                <button id="modal_confirmation_cancel_btn_submit" type="button" class="btn btn-primary pull-left"><i class="fa fa-check"></i> Yes</button>
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-minus-circle"></i> No</button>
            </div>            
        </div>
    </div>
    </div>
    