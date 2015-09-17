
    <div class="col-md-3 col-sm-4">
        <div class="box-header">
            <i class="fa fa-inbox"></i>
            <h3 class="box-title">INBOX</h3>

        </div>
        <?php /*
        <a class="btn btn-block btn-primary" data-toggle="modal" data-target="#compose-modal"><i class="fa fa-pencil"></i> Compose Message</a>
         */ ?>
        <div style="margin-top: 15px;">
            <ul class="nav nav-pills nav-stacked" >
                <li class="header">Folders</li>
                <li class=""><a href="#" id="app_message_folder_inbox"><i class="fa fa-inbox"></i> Inbox </a></li>
                <?php /*
                <li><a href="#"><i class="fa fa-pencil-square-o"></i> Drafts</a></li>
                <li><a href="#"><i class="fa fa-mail-forward"></i> Sent</a></li>
                <li><a href="#"><i class="fa fa-star"></i> Starred</a></li>
                <li><a href="#"><i class="fa fa-folder"></i> Junk</a></li>
                 */ ?>
            </ul>
        </div>
    </div>
    <div class="col-md-9 col-sm-8">
        <div class="row pad">
            <div class="col-sm-6">
                <label style="margin-right: 10px;">
                    <input type="checkbox" id="app_message_check_all"/>
                </label>
                <div class="btn-group">
                    <button type="button" class="btn btn-default btn-sm btn-flat dropdown-toggle" data-toggle="dropdown">
                        Action <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="#" id="app_message_mark_read">Mark as read</a></li>
                        <li><a href="#" id="app_message_mark_unread"><strong>Mark as unread</strong></a></li>
                        <li class="divider"></li>
                        <li><a href="#" id="app_message_delete">Delete</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-sm-6 search-form">
                <form action="#" class="text-right">
                    <div class="input-group">                                                            
                        <input id="app_message_search" type="text" class="form-control input-sm" placeholder="Search" >
                        <div class="input-group-btn">
                            <button id="app_message_btn_search" type="submit" name="q" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></button>
                        </div>
                    </div>                                                     
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <!-- THE MESSAGES -->
            <table class="table table-mailbox" id="app_message_table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th style="text-align:center">Time</th>
                    </tr>
                </thead>
                <tbody>
                
                
                </tbody>
            </table>
            
            <div id ="app_message_overlay" class=""> </div>
            <div id ="app_message_loading" class=""> </div>
        </div><!-- /.table-responsive -->
        <div class="box-footer clearfix">
            <div class="pull-right">
                <small id="app_message_num_of_rows"></small>
                <button id="app_message_btn_prev_page" class="btn btn-xs btn-primary"><i class="fa fa-caret-left"></i></button>
                <button id="app_message_btn_next_page" class="btn btn-xs btn-primary"><i class="fa fa-caret-right"></i></button>
            </div>
        </div>
    </div><!-- /.col (RIGHT) -->

