<div class="form-group">
    <label><?php echo $label ?></label>
    <div id ="<?php echo $id ?>" class="table-responsive" >
        <div class="row">
            <div class="col-sm-6">
                <div class="dataTables_length">
                    <label>
                        <select class="form-control"size="1" id="<?php echo $id ?>_records_page" style="text-align:left;border:1px solid #ccc;display:inline-block">
                            <option value="5"selected="selected">5</option>
                            <option value="10" >10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select> records per page
                    </label>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="dataTables_filter">
                    <div class="input-group" style="width:300px">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                     <input id="<?php echo $id ?>_filter" type="text" 
                                          placeholder="Press Enter to search" 
                                          style="border:1px solid #ccc"
                                          class="form-control"
                                          >
                    </div>
                </div>
            </div>
        </div>
        <div style="position:relative;min-height:150px">
        <table id="<?php echo $id ?>_tbl" class="<?php echo $class ?>"  
               style="font-size:14px">
            <thead id="<?php echo $id ?>_thead"></thead>
            <tbody id="<?php echo $id ?>_tbody" role="alert" aria-live="polite" aria-relevant="all"> </tbody>
        </table>
        <div id ="<?php echo $id ?>_overlay" class=""> </div>
        <div id ="<?php echo $id ?>_loading" class=""> </div>
        </div>
        </div>
        <div class="form-group">
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="dataTables_info" id="<?php echo $id ?>_info">Showing 1 to 2 of 2 entries</div>                
            </div>
            <div class="col-sm-6">
                <div class="dataTables_paginate paging_bootstrap" >
                    <ul id="<?php echo $id ?>_pagination" class="pagination lte-box-shadow" >
                        <li class="" id='<?php echo $id ?>_pagination_first'>
                            <a href="#">First</a>
                        </li>
                        <li class="prev" id='<?php echo $id ?>_pagination_prev'>
                            <a href="#">← Previous</a>
                        </li>
                        <li class="" id='<?php echo $id ?>_pagination_1'>
                            <a href="#"></a>
                        </li>
                        <li class="" id="<?php echo $id ?>_pagination_2">
                            <a href="#"></a>
                        </li>
                        <li class="" id="<?php echo $id ?>_pagination_3">
                            <a href="#"></a>
                        </li>
                        <li class="" id="<?php echo $id ?>_pagination_4">
                            <a href="#"></a>
                        </li>
                        <li class="" id="<?php echo $id ?>_pagination_5">
                            <a href="#"></a>
                        </li>
                        <li class="next" id='<?php echo $id ?>_pagination_next'>
                            <a href="#">Next → </a>
                        </li>
                        <li class="" id='<?php echo $id ?>_pagination_last'>
                            <a href="#">Last</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        

    </div>




