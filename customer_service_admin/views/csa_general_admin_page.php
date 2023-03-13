<div class="wrap">

    <!-- <script src="https://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" /> -->
    <h1>General </h1>
    
    <div class="change-sponsor-wrap">   
        <h3>Change Sponsor </h3>
            <p>Enter the Name, ID, Username of the member to change the sponsor for, and the Name,ID,Username of the new MLM sponsor. Click the Change Sponsor button to complete.</p>          
        <form id="form_change_sponsor" action="#">
            <div class="sponsor-change-container">
                <div class="list sc-container-left search-wrap">
                    <label>Search Member: </label>
                    <input type="text" placeholder="Search Member ..." name="search_user" class="form-control search-user" />
                    <!-- button class="btn btn-small get-sponsor-details" disabled="true">
                        <i class="fa fa-search"></i> Get details
                    </button> -->
                    <span class="existing-sponsor"></span>        
                </div>
                <div class="list sc-container-right sponsor-search-wrap">
                    <label>Search new sponsor: </label>
                    <input type="text" placeholder="Search Sponsor to be assigned..." name="search_sponsor" class="form-control search-sponsor" />
                    <button class="btn btn-small modify-new-sponsor" disabled="true">
                        <i class="fa fa-edit"></i>Change Sponsor
                    </button>
                </div>
                <div class="sc-clear"></div>
            </div>
        </form>	
        <div class="message-wrap" style="margin-top: 25px;" >
            <span class="sponsor-change-message" style=" font-size: 20px; font-weight: bold; color:  green"></span>
            <span class="error-message" style=" font-size: 20px; font-weight: bold; color:  red"></span>
        </div>

    </div>


    <div class="wrap-border change-username-wrap">
        <div>
            <h3>Change Username </h3>
            <p>Search the member username to change.</p>
            <div class="change-username-container username-list-wrap">
                <div class="list cu-container-left change-username-list">
                    <label>Change username: </label>
                    <input type="text" placeholder="Change Username ..." name="change_username" class="form-control change-username" />
                    <span class="change-username-validation" style="display:none; color: red"></span>
                    
                </div>

                <div class="row list cu-container-right new-username-list" style="display:none;">
                    <div style="float:left;">
                        <label>Enter new username: </label>
                        <input type="text" placeholder="New Username ..." name="new_username" class="form-control new-username" />        

                        <div class="uname-availabilty-msg">

                            <span class="available" style="display:none; color: green"><i class="fa fa-check"></i>Username Available</span>
                            <span class="unavailable" style="display:none; color: red"><i class="fa fa-close"></i>Username Unavailable</span>
                             
                        </div> 
                    </div>

                    <div style="float:left; transform: translateY(75%);">
                      <button class="btn btn-small submit-new-sponsor btn-modify-username" disabled="true">
                            <i class="fa fa-edit"></i>Change Username
                    </button>
                    </div>
                </div>

            </div>

            

            <!-- <div class="username-actions">
                <button type="btn" class="btn btn-small btn-change-username" style="display: none" disabled="true"><i class="fa fa-user-edit"></i> Change Username</button>
            </div> -->
            <div class="msg change-username-msg" style="display:none;"></div>
        </div>
    </div>


    <div class="affiliate-termination-wrap">
        <h3>Terminations </h3>
        <p>Enter the Name,ID,Username of the Member to terminate. Click the Activate or Terminate button to complete.</p>
        <div class="list lookup-affiliate-list">     

            <label>Search Member: </label>
            <input type="text" placeholder="Search Member to Activate/Terminate..." name="search_valid_affiliate" class="form-control search-valid-affiliate" />

        </div>

        <div class="affiliate-actions">
            <button type="btn" class="btn btn-small btn-activate" style="display: none" disabled="true"><i class="fa fa-user-plus"></i> Activate</button>

            <button type="btn" class="btn btn-small btn-terminate" style="display: none" disabled="true"><i class="fa fa-user-times"></i> Terminate</button>
        </div>
        <div class="status-msg"></div>
    </div>

    <!-- The Modal -->
    <div id="terminate-modal" class="terminate-modal">

      <!-- Modal content -->
      <div class="terminate-modal-content">
        <!-- <span class="terminate-close">&times;</span> -->
        <p>Are you sure you want to terminate the Member?</p>
        <ul class="terminate-confirm-buttons" data-commission-id="1">
          <li><a class="terminate-yes" href="javascript:void(0)">Yes</a></li>
          <li><a class="terminate-no" href="javascript:void(0)">No</a></li>
        </ul>
      </div>

    </div>

    <!-- The Modal -->
    <div id="activate-modal" class="activate-modal">

      <!-- Modal content -->
      <div class="activate-modal-content">
        <!-- <span class="activate-close">&times;</span> -->
        <p>Are you sure you want to activate the Member?</p>
        <ul class="activate-confirm-buttons">
          <li><a class="activate-yes" href="javascript:void(0)">Yes</a></li>
          <li><a class="activate-no" href="javascript:void(0)">No</a></li>
        </ul>
      </div>

    </div>  

   


    <div class="wrap-border change-role-wrap">
        <div>
            <h3>Change User Type </h3>
            <p>Search the Member to change their roles as Representative/Retail customer/Preferred Customer.</p>
            <div class="change-role-container user-list-wrap">
                <div class="list cu-container-left change-role-list">
                    <label>Search Name,Id,Username: </label>
                    <input type="text" placeholder="Change to Representative/Customer" name="change_role" class="form-control change-role" />
                    <span class="change-role-validation" style="display:none; color: red"></span>               
                </div>

                <div class="role-update" style="float:left; transform: translateY(-108%);margin-left: 258px;">
                    <button type="btn" class="btn btn-small btn-role-update update-as-customer" style="display:none;" disabled="true"><i class="fa fa-edit"></i> Change to Retail Customer</button>
                     <button type="btn" class="btn btn-small btn-role-update update-as-preferredcustomer" style="display:none;" disabled="true"><i class="fa fa-edit"></i> Change to Preferred Customer</button>
                    <button type="btn" class="btn btn-small btn-role-update update-as-affiliate" style="display:none;" disabled="true"><i class="fa fa-edit"></i> Change to Representative</button>
                </div>      

            </div>
            <div class="msg role-update-msg" style="display:none;"></div>
        </div>
    </div>


    <div class="wrap-border auto-qualify-wrap">
        <div>
            <h3>Auto Qualify </h3>
            <p>Add or Delete username to auto qualify</p>
            <div class="row list auto-qualify-wrap">
                <div class="username-list" style="float:left">
                    <label>Search Name,Id,Username: </label>
                    <input type="text" placeholder="Enter Username ..." name="username_input" class="form-control username-input" />
                    <span class="username-input-validation" style="display:none; color: red"></span>
                </div>

               <div class="qualify-update" style="float:left">
                    <button type="btn" class="btn btn-small btn-qualify-update auto-qualify-add" style="display:none;" disabled="true"><i class="fa fa-user-plus"></i> Qualify</button>
                     <button type="btn" class="btn btn-small btn-qualify-update auto-qualify-delete" style="display:none;" disabled="true"><i class="fa fa-trash"></i> Delete</button>
                </div>
            </div>

                  

            
            <div class="msg auto-qualify-msg" style="display:none;"></div>
        </div>
    </div> 
    <div class="wrap-border" style="margin-bottom: 15px"> 
        <h3> Product Listing Setting</h3>
        <fieldset>
        <legend class="screen-reader-text"><span>Enable Country Filter</span></legend>
        <label for="woocommerce_enable_country">
        <?php $c_val=get_option('country_filter'); ?>
        <?php if($c_val == "on"):?>
        <input name="woocommerce_enable_country" id="woocommerce_enable_country" type="checkbox" class="" value="1" checked="checked"> Enable country filter in products admin area
        <?php else:?>
        <input name="woocommerce_enable_country" id="woocommerce_enable_country" type="checkbox" class="" value="1"> Enable country filter 
        <?php endif;?>
        </label></fieldset>
        <br><span class="success-message" style=" font-size: 14px; font-weight: bold; color:  green"></span>
    </div>
    <div class="wrap-border reward-qualify-wrap" style="margin-bottom: 15px">
        <div>
            <h3>Rewards </h3>
            <p>Add or Delete username to Reward qualify</p>
            <div class="row list reward-qualify-wrap" style="display:flex;" >
                <div class="username-list" style="float:left">
                    <label>Search Name,Id,Username: </label>
                    <input type="text" placeholder="Enter Username ..." name="username_rewards" class="form-control username-rewards" />
                    <input type="hidden" name="username_rewards_hidden" class="form-control username-rewards-hidden" value="" />
                    <span class="username-input-validation" style="display:none; color: red"></span>
                </div>

               <div class="qualify-update" style="float:left">
                    <button type="btn" class="btn btn-small reward-qualify-add-personal" style="display:none;" disabled="true"><i class="fa fa-user-plus"></i> Add Personal</button><span class="button_or" style="display: none;">OR</span>
                    <button type="btn" class="btn btn-small reward-qualify-add-team" style="display:none;" disabled="true"><i class="fa fa-user-plus"></i> Add Team</button>
                     
                </div>
            </div>
            <div class="msg auto-qualify-msg" style="display:none;"></div>
        </div>

        <div class="unused-rewards-list" id="unused-rewards-list"></div>
    </div> 
    


</div>
