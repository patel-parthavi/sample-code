<html>

<head>
</head>

<body>
    <h1> Placement Wizard Admin Settings </h1>
    <div class="placement-wizard-block">
	    <ul id="progressbar" class="progressbar">
	        <li class="active">Select member</li>
	        <li>Select position</li>
	        <li>Assign member</li>
	    </ul>
	    <div class="placement-wizard-wrap">
	        <!-- Step 1 screen -->
	        <div class="steps step-1">
	            <h2>Please select member to be placed</h2>
	            <p>Type member name or ID in below box and click on search, it will display you the current member postion. </p>	
	            <div class="affiliate-search-wrap">
	                <input type="text" placeholder="Search Member to move/assign..." class="form-control search-affiliate" />
	                <button type="btn" class="btn btn-small btn-search-affiliate">Search<!-- <i class="fa fa-search"></i> --></button>
	            </div>
	            <div class="affiliate-list-wrap">
	            	<div class="affiliate-list">	
	            		<h4 class="extratext">OR</h4>
	            		<p>Select from the list of unplaced members
	            			<br><i>(Double click on unplaced member to view its details)</i>
	            		</p>
	            		    
	            		<div class="un-placed-list">		            
		            		<select size="2">
			            		<?php foreach($unplaced_affiliates as $key => $unplaced_affiliate){?>
								 <option value="<?php echo $unplaced_affiliate->user_key." - ".$unplaced_affiliate->user_login; ?>"><?php echo $unplaced_affiliate->user_login." - ".$unplaced_affiliate->user_key; ?></option>
								 <?php } ?>							 
							</select>
						</div>
	            	</div>
	            	<div class="hv-wrapper hv-wrapper1">
		                <div class="selected-affiliate-wrap"></div>
		            </div>
	            </div>
	            <p>Once confirmend click on next button to proceed with step 2.</p>	            
	        </div>
	        <!-- Step 2 screen -->
	        <div class="steps step-2" style="display: none;">
	            <div>
	            	<div class="affiliatetext">
	            		<h2>Select parent under which you need to place member</h2>
	                	<h3>Member to be place: <b class="screen-1-affiliate"></b> </h3>
	            	</div>	    
	            	<div class="sponsor-tree-view-wrap">
	            		<a href="#" class="btn btn-small btn-sponsor-treeview" target="_blank" ><i class="fa fa-eye"></i> Sponsor's Tree View</a>
	            	</div>            
	                <p>Type user name or ID of parment and click on search button </p>
	                <p>It will display you the current position of parent along with its child node, Click on respetive node where you wish to make placement</p>
	            </div>
	            <div class="parent-search-wrap">
	                <input type="text" placeholder="Search Parent to place under...." class="form-control search-parent" />
	                <button type="btn" class="btn btn-small btn-search-parent">Search<!-- <i class="fa fa-search"></i> --></button>
	                
	            </div>
	            
	            <div class="hv-wrapper">
	                <div class="selected-parent-wrap">
	                </div>
	            </div>
	            <div class="msg-wrap">
	                <center>
	                    <span class="validation-message"></span>
	                </center>
	            </div>	           
	        </div>
	        <!-- Step 3 screen -->
	        <div class="steps step-3" style="display: none;">
	            <div class="temporary-unplaced-wrap">
	                <div class="hv-wrapper">
	                    <div class="tree-structure-wrap">
	                    </div>
	                </div>
	                <div class="check-mark"></div>
	                <div class="msg-wrap">
	                    <center>
	                        <span class="update-message"></span>
	                    </center>
	                </div>
	            </div>
	        </div>
	        <div class="btn-box">
	        	<a href="#" class="btn btn-small btn-parent-treeview disabled" target="_blank" style="display: none;"><i class="fa fa-eye"></i> Tree View</a> 
	        	<button type="btn" class="btn btn-small btn-next-placement btn-step-1" data-step='step-1' disabled="true">Next</button>
	        </div>	        
	    </div>
	    <div class="loader-wrap">
	        <div class="loader"></div>
	    </div>
    </div>
</body>

</html>
<?php


?>