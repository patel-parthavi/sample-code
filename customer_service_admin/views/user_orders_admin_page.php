<div class="wrap">
    <h1>Order Admin </h1>
    <!-- start of order assignment -->
    <div class="wrap-border order-assignment-wrap">
        <div>
            <h3>Order Assignment </h3>
            <p>Enter Order ID to modify assignments.</p>
            <div class="change-username-container order-list-wrap">
                <div class="list cu-container-left order-list">
                    <label>Enter Order No: </label>
                    <input type="text" placeholder="Enter Order No ..." name="order_no" class="form-control order-no" />
                    <span class="order-list-validation" style="display:none; color: red"></span>
                </div>

                <div class="list cu-container-right new-cust-username-list" style="display:none;"">
                    <label>Enter new customer's username: </label>
                    <input type="text" placeholder="New customer username ..." name="new_cust_username" class="form-control new-cust-username" /> 
                    <span class="order-customer" style="display:none; color: green"></span>              
                </div>
            </div>

            <div class="change-username-container order-options-wrap" style="display:none;">
                <div class="list cu-container-left  order-type-wrap">
                    <label>Select Order type: </label>

                    <select class="order-types">
                        <option value="0"><--------- Select ---------></option>
                        <?php foreach ($order_types as $key => $value) { ?>
                            <option value="<?php echo $value ?>"><?php echo ucfirst($value); ?></option>
                        <?php } ?>


                    </select>
                    <span class="existing-order-type" style="display:none; color: green"></span>
                </div>

                <div class="list cu-container-right process-volume-wrap">
                    <label for="process_volume">Check if volume update required: </label>
                    <input type="checkbox" id="process_volume" name="process_volume">
                    <b><i>Reprocess</i></b>
                </div> 
            </div>           

            <div class="order-update" style="display:none;">
                <button type="btn" class="btn btn-small btn-order-update" ><i class="fa fa-user-edit"></i> Update</button>
            </div>
            <div class="msg order-update-msg" style="display:none;"></div>
        </div>
    </div> 
    <!-- End of order assignment -->
    <!-- Start of order backdates -->
    <div class="wrap-border order-backdates-wrap">
        <div>
            <h3>Order Backdates </h3>

            <form id="upload_csv" action="#" method="post" enctype="multipart/form-data">
                <div class="col-md-3">
                    <br />
                    <label>Select CSV File</label>
                </div>  
                <div class="col-md-4">  
                    <input type="file" name="csv_file" id="csv_file" accept=".csv" style="margin-top:15px;" />
                </div>  
                <div class="col-md-5">  
                    <input type="submit" name="upload" id="upload" value="Upload" style="margin-top:10px;" class="button button-primary" />
                </div>  
                <div style="clear:both"></div>
            </form>
            <br />
            <br />
            <div id="csv_file_data">
               
            </div>
        </div>
        <input type="hidden" name="run_orderback" id="run_orderback" value="<?php echo site_url(); ?>/wp-content/plugins/commissions/cron/commissions/process_order_backdates.php">
    <div class="run_script_wrapper"><button class="button button-primary run_script">Run Process</button></div>
    </div>
    <!-- Popup box -->
    <div class="order_run_logs_popup">
        <span class="helper"></span>
            <div class="popup_inner_wrap">
                <div class="popup_title">Please do not refresh page until Order run is completed </div>
                <span class="popup_notice">Note*: Popup will auto close once run process is completed</span>
                <p>Processing...Please Wait!</p>
                <div id="popup-footer"></div>
                
            </div>      
    </div>
    <div class="cd-popup" role="alert">
      <div class="cd-popup-container cd-popup-run-process">
        <p>Are you sure you want to Run this process ?</p>
        <ul class="cd-buttons">
          <li><a href="javascript:void(0)">Yes</a></li>
          <li><a href="javascript:void(0)">No</a></li>
        </ul>
        <a href="javascript:void(0)" class="cd-popup-close img-replace">×</a>
      </div> <!-- cd-popup-container -->
    </div> <!-- cd-popup -->
    <!-- End of pop ups -->
	<!-- Begin Order by Period Report -->
	<div class="wrap-border order-backdates-wrap>
		<div><?php echo do_shortcode("[wpdatatable id=70]"); ?></div>
	</div>
	<!-- End Order by Period Report -->
</div>