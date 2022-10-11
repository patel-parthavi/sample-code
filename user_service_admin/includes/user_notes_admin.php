<?php
Class user_notes_admin{
	
	public function __construct() {

        $this->user_notes_filters();
        $this->user_notes_actions();
    }

    public function user_notes() {
        $wpdatatable_id = $this->get_wpdatatable_id();
        require_once(CSA_PLUGIN_PATH . 'views/user_notes_admin_page.php');
    }	

    public function get_wpdatatable_id(){

        global $wpdb;

        /* For Main Records */
        $commission_settings_result = $wpdb->get_row("SELECT cs.value as wpdatatable_id FROM  commission_settings cs WHERE cs.key ='user_notes_wpdataTable'");

        return $commission_settings_result->wpdatatable_id;
    }

    public function user_notes_actions() {

        add_action('wp_ajax_save_user_notes', array($this, 'save_user_notes'));
        add_action('wp_ajax_nopriv_save_user_notes', array($this, 'save_user_notes'));

        add_action('wp_ajax_view_notes', array($this, 'view_notes'));
        add_action('wp_ajax_nopriv_view_notes', array($this, 'view_notes'));

        add_action('wp_ajax_edit_notes', array($this, 'edit_notes'));
        add_action('wp_ajax_nopriv_edit_notes', array($this, 'edit_notes'));

        add_action('wp_ajax_delete_note', array($this, 'delete_note'));
        add_action('wp_ajax_nopriv_delete_note', array($this, 'delete_note'));

        add_action('wp_ajax_get_user_name_and_key', array($this, 'get_user_name_and_key'));
        add_action('wp_ajax_nopriv_get_user_name_and_key', array($this, 'get_user_name_and_key'));        

    }

    public function user_notes_filters() {
    }

    public function save_user_notes(){

        global $wpdb, $current_user;

        $current_user_id = $current_user->ID;

        // $form = $_POST['form_data'];
        // $form_data = explode("&",$form);

        // $notes_search_user = explode("=",$form_data[0]);

        $user = explode("-",$_POST['user']);
        $user_key = $user[0];
        $user_name = $user[1];
    
        // $notes_subject = explode("=",$form_data[1]);
        $subject = $_POST['subject'];

        // $notes = explode("=",$form_data[2]);
        $note = $_POST['note'];

        $type = $_POST['type'];

        if(trim($type) == 'save'){

            $user_id = $wpdb->get_row("SELECT user_id from wp_wpmlm_users where user_key ='".$user_key."'");
            $user_id = $user_id->user_id;

            $current_timestamp = date('Y-m-d h:i:s');
            $insert_new_note = array(
                        'user_id' => $user_id, 
                        'admin_id' => $current_user_id, 
                        'subject' => $subject, 
                        'text' => trim($note), 
                        'last_updated' => $current_timestamp,
                        'last_updated_by' =>  get_current_user_id()
                    );
            // var_dump($insert_new_note);
            $saved = $wpdb->insert('user_notes', $insert_new_note ); 

        }

        if(trim($type) == 'update'){

            $note_id = $_POST['note_id'];

            // $user_id = $wpdb->get_row("SELECT user_id from wp_wpmlm_users where user_key ='".$user_key."'");
            // $user_id = $user_id->user_id;

            $current_timestamp = date('Y-m-d h:i:s');

            $saved = $wpdb->update('user_notes', array(
                        // 'user_id' => $user_id, 
                        'admin_id' => $current_user_id, 
                        'subject' => $subject, 
                        'text' => $note, 
                        'last_updated' => $current_timestamp,
                        'last_updated_by' =>  get_current_user_id()
                    ), array('note_id'=>$note_id) ); 

        }



        

        // echo $wpdb->last_query;
        echo json_encode($saved);
        die();

    }

    public function view_notes(){

        global $wpdb;
        $note_id = $_POST['note_id'];
        $note_details = $wpdb->get_row("SELECT note_id,  CONCAT( UPPER(LEFT(wu.user_login,1)),SUBSTRING(wu.user_login,2) ) as `user`, CONCAT( UPPER(LEFT(wu_admin.user_login,1)),SUBSTRING(wu_admin.user_login,2) ) as admin_user, subject, `text`, created, CONCAT( UPPER(LEFT(wu_last.user_login,1)),SUBSTRING(wu_last.user_login,2) ) as `Updatedby` FROM `user_notes` un LEFT JOIN wp_users wu on wu.ID = un.admin_id LEFT JOIN wp_users wu_last on wu_last.ID = un.last_updated_by LEFT JOIN wp_users wu_admin on wu_admin.ID = un.last_updated_by where note_id ='".$note_id."'");

        echo json_encode($note_details);

        die();

    }

    public function edit_notes(){

        global $wpdb;
        $note_id = $_POST['note_id'];
        $note_details = $wpdb->get_row("SELECT subject, `text` FROM `user_notes` where note_id ='".$note_id."'");

        echo json_encode($note_details);

        die();    
    }

    public function delete_note(){
        global $wpdb;
        if(isset($_POST['note_id'])){

            $note_id = $_POST['note_id'];
 
            $delete_note_sql = "DELETE from user_notes WHERE note_id =  $note_id";
            $deleted = $wpdb->query($delete_note_sql);
        }

        echo json_encode( array('deleted' => $deleted ) );
        die();
    }

    public function get_user_name_and_key(){

        global $wpdb;
        $site_url = $_POST['site_url'];
        $arr_site_url = explode('wdt_column_filter%5B1%5D=', $site_url);
        // var_dump($arr_site_url);
        $uname = $arr_site_url[1];

        $uname = $wpdb->get_row("SELECT user_login, user_key FROM wp_users wu LEFT JOIN wp_wpmlm_users wwu on wu.ID = wwu.user_id where user_login ='".$uname."'");

        echo json_encode($uname);

        die();


    }
}
?>