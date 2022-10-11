<html>
	<head></head>
	<body>
		<div class="wrap">
			 <h1>User notes </h1>

			 <div class="user-notes-container">
			 		<form id="save-user-notes">	                    

	                    <div class="notes-user-search-wrap">
		                    <label for="find-user">Search User </label>
		                    
		                    <input type="text" name="notes_find_user" class="notes-find-user" />
		                    <p>Type first 3 letters of Username or Affiliate ID</p>
		                    <!-- <textarea name="notes_find_user" cols="5" rows="5" class="form-control notes-find-user"></textarea> -->
	                    </div>

	                    <div class="user-notes-wpdatatable-wrap" style="display: none">	                    	
	                    	
	                    	<h3 class="notes-for" style="display: inline-block;"></h3>
	                    	<button class="btn-small btn-add-new-note" style="display: none; margin-left: 10px;"> Add new note </button>
						 	<?php echo do_shortcode("[wpdatatable id=$wpdatatable_id]");?>
				            <span class="wpdatatable-notes-msg"></span>  
						 </div>
						 
                    </form>
			 </div> 

			<!-- Wp datatable modals -->

			 <!-- The Modal -->
			<div id="view-modal" class="view-modal">

			  <!-- Modal content -->
			  <div class="view-modal-content">
			    <span class="view-close">&times;</span>
			    <h4>User Note details</h4>
			    <p class="notes-details"></p>
			  </div>

			</div>

			 <!-- The Modal -->
			<div id="edit-modal" class="edit-modal">

			  <!-- Modal content -->
			  <div class="edit-modal-content">
			    <span class="edit-close">&times;</span>
			    <h4>Edit Note </h4>
			    <p class="edit-details">
			    	
			    	<form id="save-user-notes">
	                    <!-- <h3 for="add-new-note">Edit Note </h3> -->

	                    <!-- <div class="notes-user-search-wrap">
		                    <label for="find-user">Search User </label>
		                    <input type="text" name="notes_find_user" class="notes-find-user" />
		                    <!-- <textarea name="notes_find_user" cols="5" rows="5" class="form-control notes-find-user"></textarea> -->
	                    <!-- </div> --> 

	                    <div class="subject-wrap">
		                    <label for="subject">Subject </label>
		                    <!-- <textarea name="subject" class="edit-subject" cols="20" rows="5" style="margin-left: 16px;"></textarea> -->
		                    <input type="text" name="subject" class="edit-subject" style="margin-left: 12px;     width: 90%; margin-bottom: 10px">
	                    </div>

	                    <div class="note-wrap">
		                    <label for="search-user">Note </label>
		                    <textarea name="note" class="edit-note" cols="20" rows="5" style="margin-left: 12px;"></textarea>
	                    </div>

	                    <!-- <div class="note-wrap"> -->
		                    <button class="btn-small save-notes-details" data-type="update" name="save_user_notes">
		                        Save
		                    </button>
	                    <!-- </div> -->

	                    <div class="note-msg-wrap">
	                    	<span class="mofified-user-notes-msg"></span>  
	                    </div>
                    </form>

			    </p>
			  </div>

			</div>

			 <!-- The Modal -->
			<div id="delete-modal" class="delete-modal">

			  <!-- Modal content -->
			  <div class="delete-modal-content">
			    <span class="delete-close">&times;</span>
			    <h4>Are you sure you want to delete the user note?</h4>
			    <p class="delete-options">
			    	<button class="btn btn-small delete-yes"> Yes </button>
		            <button class="btn btn-small delete-no delete-close"> No </button>
			    </p>
			  </div>

			</div>

			 <!-- The Modal -->
			<div id="add-modal" class="add-modal">

			  <!-- Modal content -->
			  <div class="add-modal-content">
			    <span class="add-close">&times;</span>
			    <h4>Add New Note </h4>
			    <p class="add-details">
			    	
			    	<form id="save-user-notes">
	                    <div class="add-new-note-wrap" style="/*display: none*/">
		                    <!-- <h3 for="add-new-note">Add New Note </h3> -->
		                    <div class="subject-wrap">
			                    <label for="subject">Subject </label>
			                    <!-- <textarea name="subject" class="subject" cols="20" rows="5" style="margin-left: 16px;"></textarea> -->
			                    <input type="text" name="subject" class="subject" style="margin-left: 12px;     width: 90%; margin-bottom: 10px">
		                    </div>

		                    <div class="note-wrap">
			                    <label for="search-user">Note </label>
			                    <textarea name="note" class="note" cols="20" rows="5" style="margin-left: 12px;"></textarea>
		                    </div>

		                    <!-- <div class="note-wrap"> -->
			                    <button class="btn-small save-notes-details" data-type="save" name="save_user_notes"> Save
			                    </button>
		                    <!-- </div> -->

		                    <div class="note-msg-wrap">
		                    	<span class="user-notes-msg"></span>  
		                    </div>
	                    </div>
                    </form>

			    </p>
			  </div>

			</div>

			<!-- End of wpdataTables modals -->
		</div>
	</body>
</html>