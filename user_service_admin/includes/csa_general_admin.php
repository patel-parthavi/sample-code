<?php
Class csa_general {

    /* ----- to render General Page view ----- */
    public function csa_general_load_view() {
        $order_types = $this->get_all_custom_order_types();
        
        require_once(CSA_PLUGIN_PATH . 'views/csa_general_admin_page.php');
    }

    public function __construct() {

        $this->csa_general_actions();
    }

    public function csa_general_actions() {

        /*----- Search user on general page -----*/
        add_action('wp_ajax_search_users', array($this, 'search_users'));

        /* ----- Change Sponsor Section ----- */
        add_action('wp_ajax_change_sponsor', array($this, 'change_sponsor'));
        add_action('wp_ajax_display_sponsor_details', array($this, 'display_sponsor_details'));

        /* ----- Affiliate Termination Section ----- */
        // add_action('wp_ajax_affiliate_lookup_list', array($this, 'affiliate_lookup_list'));
        add_action('wp_ajax_get_banned_status', array($this, 'get_banned_status'));
        add_action('wp_ajax_terminate_affiliate', array($this, 'terminate_affiliate'));
        add_action('wp_ajax_activate_affiliate', array($this, 'activate_affiliate'));

        /* ----- Change Username Section ----- */
        add_action('wp_ajax_search_username_to_change', array($this, 'search_username_to_change'));
        add_action('wp_ajax_uname_availibility_status', array($this, 'uname_availibility_status'));
        add_action('wp_ajax_update_new_username', array($this , 'update_new_username' ));
        add_action('wp_ajax_save_country_option', array($this , 'save_country_option' ));
       

        /* ----- Auto Qualify Section ----- */ 
        add_action('wp_ajax_search_auto_qualified_users', array($this , 'search_auto_qualified_users' ));
        add_action('wp_ajax_search_reward_qualified_users_byrole', array($this , 'search_reward_qualified_users_byrole' ));
        add_action('wp_ajax_get_unredeemed_rewards', array($this , 'get_unredeemed_rewards' ));
        add_action('wp_ajax_delete_unredeemed_rewards', array($this , 'delete_unredeemed_rewards' ));
        add_action('wp_ajax_modify_auto_qualified_user', array($this , 'modify_auto_qualified_user' ));
        add_action('wp_ajax_add_reward', array($this , 'add_reward' ));
        /* ----- Change User Role Section ----- */ 
        add_action('wp_ajax_show_change_to_role_button', array($this , 'show_change_to_role_button' ));
        add_action('wp_ajax_modify_user_role', array($this , 'modify_user_role' ));       
        
        

    }

    /* ----- General Page ----- */
    public function save_country_option(){

        if($_POST['country'] ){
          
            update_option( 'country_filter', $_POST['country'] );
            echo "success";
        }
        die();
    }

    /* ----- Search Users ----- */
    public function search_users(){

        global $wpdb;
        $wp_wpmlm_users = array();
        $ajax_type = $_REQUEST['ajax_type'];

        $search_sql = "SELECT DISTINCT(wwu.user_key), wu.user_login FROM wp_wpmlm_users wwu LEFT JOIN wp_users wu on wwu.user_id = wu.ID LEFT JOIN wp_usermeta wum on wum.user_id = wwu.user_id";

        $where = "WHERE (wwu.user_key LIKE '%" . $_REQUEST['q'] . "%'  OR wu.user_login LIKE '%" . $_REQUEST['q'] . "%' OR (wum.meta_key = 'first_name' AND wum.meta_value LIKE '%" . $_REQUEST['q'] . "%' )) AND ";

        if(trim($ajax_type) == 'change_sponsor_search_user'){
            $where .= "(wwu.parent_key = '2' OR wwu.parent_key != '' OR wwu.parent_key = '0') AND (wwu.sponsor_key != '' OR wwu.sponsor_key != '0' AND wum.meta_key ='wp_capabilities' AND (wum.meta_value LIKE '%mlm_user%' OR wum.meta_value LIKE '%customer%'))";
        }

        if(trim($ajax_type) == 'change_sponsor_search_sponsor'){
            $where .= "(wwu.sponsor_key != '' OR wwu.sponsor_key != '0') AND (wum.meta_key ='wp_capabilities' AND wum.meta_value LIKE '%mlm_user%')";
        }

        if(trim($ajax_type) == 'termination_affiliate_list'){
            $where .= "(wwu.parent_key != '' OR wwu.parent_key != '0' OR wwu.parent_key != '1' OR wwu.parent_key != '2') AND wum.meta_key ='wp_capabilities' AND (wum.meta_value LIKE '%mlm_user%' OR wum.meta_value LIKE '%customer%' OR wum.meta_value='a:0:{}')";
        }

        if(trim($ajax_type) == 'order_assignment_new_user'){
            $where = "WHERE (user_login LIKE '%" . $_REQUEST['q'] . "%')";
        }

        if(trim($ajax_type) == 'auto_qualify_all_users'){
            $where .= "(wwu.user_key LIKE '%" . $_REQUEST['q'] . "%' OR wu.user_login LIKE '%" . $_REQUEST['q'] . "%')";
        }

        if(trim($ajax_type) == 'change_user_role_search_user'){
            $where .= "(wwu.parent_key != '' OR wwu.parent_key = '0') AND wum.meta_key ='wp_capabilities' AND (wum.meta_value LIKE '%mlm_user%' OR wum.meta_value LIKE '%customer%')";
        }
        if(trim($ajax_type) == 'rewards_all_users'){
        	/*$where .= "(wum.meta_key ='wp_capabilities' AND wum.meta_value LIKE '%representative%') OR (wum.meta_key ='wp_capabilities' AND wum.meta_value LIKE '%preferred_customer%')";*/
            $where .= "wum.meta_key ='wp_capabilities' AND (wum.meta_value LIKE '%preferred_customer%' OR wum.meta_value LIKE '%representative%')";
        }
        if(trim($ajax_type) == 'notes_search_user'){
            $where .= "1";
        }

        //echo $search_sql." ".$where;
        $wp_wpmlm_users = $wpdb->get_results($search_sql." ".$where);

        echo json_encode($wp_wpmlm_users);
        die();

    }

    /* ----- Change Sponsor Section ----- */
    
    /* ------ change Sponsor ------ */
    public function change_sponsor() {

        global $wpdb;

        $updated =false;        
        $form_data = explode('&', $_POST['data']);

        $user_key = explode('=', $form_data[0]);
        $user_key = explode('-', $user_key[1]);
        $user_key = $user_key[0];

        $existing_sponsor = $this->display_sponsor_details($user_key);

        $sponsor_key = explode('=', $form_data[1]);
        $sponsor_key = explode('-', $sponsor_key[1]);
        $sponsor_key = $sponsor_key[0];

        

        if(trim($sponsor_key) != ''){
            $updated = $wpdb->update('wp_wpmlm_users', array('sponsor_key' => $sponsor_key), array('user_key' => $user_key));
            // $updated =true;
        }
        
        if($updated){

            $current_user_id = get_current_user_id();
            $current_timestamp = date('Y-m-d h:i:s');

            $arr_user_key = $wpdb->get_row("SELECT user_key FROM wp_wpmlm_users wwu where wwu.user_id='".$current_user_id."'");
        
            $current_user_key = $arr_user_key->user_key;

            $inserted = $wpdb->insert('user_sponsor_changes', array(
                    'user' => $user_key, 
                    'current_sponsor' => $existing_sponsor['sponsor_key'], 
                    'new_sponsor' => $sponsor_key, 
                    'admin_user' => $current_user_key, 
                    'timestamp' => $current_timestamp 
                ) ); 

        }

        echo $updated;

        die();
    }

    /* ------ To display existing sponsor ------ */
    public function display_sponsor_details($affiliate) {
        
        global $wpdb;

        if(!isset($_POST['is_ajax'])){
            $user_key = $affiliate;
        }else{

            $data = explode('-', ($_POST['affiliate']));
            $user_key = $data[0];
            $sponsor_name = '';
        }        

        if ($user_key != '') {
            $sponsor = $wpdb->get_row("SELECT wwu.sponsor_key FROM wp_wpmlm_users wwu WHERE wwu.user_key = '" . $user_key . "'");

            $sponsor_key = $sponsor->sponsor_key;

            $sponsor_nm = $wpdb->get_row("SELECT wu.user_login FROM wp_wpmlm_users wwu LEFT JOIN wp_users wu on wu.ID = wwu.user_id WHERE wwu.user_key = '" . $sponsor_key . "'");

            $sponsor_name = $sponsor_nm->user_login;
        }

        if(!isset($_POST['is_ajax'])){

            $arr_sponsor = array( 
                                'sponsor_name' => ucfirst($sponsor_name),
                                'sponsor_key' => $sponsor_key,
                            );

            return $arr_sponsor;

        }else{

            echo ucfirst($sponsor_name);
            die();

        }
        
    }

    /* ----- Affiliate Termination Section ----- */

    /* ------ Get banned status ------ */

    public function get_banned_status() {

        global $wpdb;
        $banned_status = 'activate';
        $data = explode('-', ($_POST['affiliate']));
        $user_key = $data[0];

        if ($user_key != '') {
            $user_status = $wpdb->get_row("SELECT wwu.banned FROM wp_wpmlm_users wwu WHERE wwu.user_key = '" . $user_key . "'");

            $banned = $user_status->banned;

            if ($banned == '0') {
                $banned_status ='terminate';
            }
        }
        echo json_encode(array('msg'=>$banned_status));
        //echo $banned_status;
        die();
    }

    /* ----- Terminate affiliate ----- */
    public function terminate_affiliate() {
        
        global $wpdb;

        $pass_updated = false;
        $data = explode('-', ($_POST['affiliate']));
        $user_key = $data[0];

        //Remove user role
        $user = $wpdb->get_row("SELECT wwu.user_id FROM wp_wpmlm_users wwu WHERE wwu.user_key = '" . $user_key . "'");
        $user_id = (string)$user->user_id;

        if ($user_id != '') {

            //Set banned value and new current timestamp password

            $new_pass = md5(time());

            //Set password for terminated user

            $update_sql = "UPDATE wp_wpmlm_users a INNER JOIN wp_users b ON (a.user_id = b.ID )
                           SET 
                                banned = '1',
                                user_pass = '".$new_pass."'
                            where a.user_id = '".$user_id ."' AND a.user_key = '".$user_key."'";

            $pass_updated = $wpdb->query($update_sql);            
            


            //Remove user capablities
            $u = new WP_User($user_id);
            $user_meta = get_userdata($user_id);
            $user_roles = $user_meta->roles;

            foreach ($user_roles as $key => $role_value) {
                // Remove role
                $u->remove_role($role_value);
            }

            $user_roles = $user_meta->roles;             
           
        }

        echo $pass_updated;
        die();
    }

    /* ----- Activate affiliate ----- */
    public function activate_affiliate() {

        global $wpdb;

        $arr_activate = array();
        $update_flag = false;
        $reset_pass_url = '';

        $data = explode('-', ($_POST['affiliate']));
        $user_key = $data[0];

        if (trim($user_key) != '') {
            //Set banned value
            $updated = $wpdb->update('wp_wpmlm_users', array('banned' => '0'), array('user_key' => $user_key));

            //Remove user role
            $user = $wpdb->get_row("SELECT wwu.user_id FROM wp_wpmlm_users wwu WHERE wwu.user_key = '" . $user_key . "'");
            $user_id = $user->user_id;

            wp_update_user(array('ID' => $user_id, 'role' => "mlm_user"));
            $update_flag = true;
            // $reset_pass_url = wp_lostpassword_url();
        }
        $arr_activate = array(
            'update_flag' => $update_flag,
            'reset_pass_url' => $reset_pass_url
        );
        echo json_encode($arr_activate);
        die();
    }

    /* ------ Search user to change username ------ */

    public function search_username_to_change(){

        global $wpdb;
        $arr_commission_affiliates = array();

        if (isset($_REQUEST['q'])) {

            $usernames = $wpdb->get_results("SELECT wu.user_login FROM wp_users wu LEFT JOIN wp_usermeta wum on wum.user_id = wu.ID WHERE (user_login LIKE '%" . $_REQUEST['q'] . "%') AND wum.meta_key ='wp_capabilities' AND (wum.meta_value NOT LIKE '%administrator%') ");

            echo json_encode($usernames);
        }
        die();

    }

    /* ----- check username availibility ----- */
    public function uname_availibility_status(){

        global $wpdb;
        $availibility_status = false;
        if ( strpos($_REQUEST['username'], ' ') !== false) {
            echo json_encode(array("valid"=>false,"msg"=>'warning'));
            die;
        }
        if ( isset($_REQUEST['username']) && trim($_REQUEST['username']) != '') {

            $user_logins = $wpdb->get_results("SELECT wu.user_login FROM wp_users wu WHERE (user_login = '" . $_REQUEST['username'] . "')");
            
            if( empty($user_logins) ){
                $availibility_status = true;
            }           
        }
        echo json_encode(array("valid"=>$availibility_status));
       
        die();
    }

    /* ----- Update new username ----- */
    public function update_new_username(){

        global $wpdb;
        $update_uname = false;
        $uname = $_POST['current_uname'];
        $new_uname = $_POST['new_uname'];

        if( isset($uname) && isset($new_uname) ){
            $update_uname = $wpdb->update('wp_users', array('user_login' => $new_uname, 'user_nicename' => $new_uname), array('user_login' => $uname) );            
        }       
        echo $update_uname;
        die();
    }

    /* End on change username section */

    /* ----- Order Assignment Section ----- */

    /*public function get_order_nos(){

        global $wpdb;

        $order_ids = array();

        if (isset($_REQUEST['q'])) {

            $order_ids = $wpdb->get_results("SELECT wp.ID FROM wp_posts wp WHERE (post_type = 'shop_order' AND ID LIKE '%" . $_REQUEST['q'] . "%')");
            
        }

        echo json_encode($order_ids);
        die();

    }*/

    /* ----- Get all enum order types from order_commission_period_xref table ----- */
    public function get_all_custom_order_types(){

        global $wpdb;

        $column = $wpdb->get_var("SELECT DISTINCT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'order_commission_period_xref' AND COLUMN_NAME = 'order_type'");

        $enum = explode(",", $column);
        $enum1 = explode("'", $enum[0]);
        $val1 = $enum1[1];
        
        $order_types = array();
        array_push($order_types, $val1);
        foreach ($enum as $key => $value) {
            if($key > 0){
                $value = str_replace("'","",$value);
                $value = str_replace(")","",$value);
                $order_types[] = $value;
            }
        }

        return $order_types;
    }

    /* ----- Check for processing volume ----- */
    /*public function after_order_selection(){

        $process_volume = false;
        $order_no = $_POST['order_no'];
        $current_month = date('n');

        if(isset($order_no) && trim($order_no) != ''){                
           
            $paid_date = get_post_meta($order_no, '_paid_date', true); 
            $pd = date_parse_from_format("Y-m-d h:i:s", $paid_date);
            $paid_date_month = $pd["month"];

            if($paid_date_month == $current_month){
                $process_volume = true;
            }
            
            $cust_id = get_post_meta($order_no, '_customer_user', true);
            $cust_name = get_user_meta( $cust_id, 'nickname', true );
            $order_type = get_post_meta($_POST['order_no'], 'order_type', true ); 
            
        }

        echo json_encode( array(
                            'process_volume' => $process_volume,
                            'customer_id' => $cust_id,
                            'customer_name' => $cust_name,
                            'order_type' => ucfirst($order_type)
                            ) 
            );
        die();
    }*/

    /* ----- Save order modifications ------ */
    /*public function update_order_assignment(){

        
        $update_msg = false;
        global $wpdb;

        if( isset($_POST['order_no']) && trim($_POST['order_no']) != ''){

            $user_ids = $wpdb->get_row("SELECT wu.ID FROM wp_users wu WHERE (user_login = '" . trim($_POST['new_uname']) . "')");
            $user_id = $user_ids->ID;

            if(isset($_POST['new_uname']) && trim($_POST['new_uname']) != ''){ 

                $previous_customer = get_post_meta( $_POST['order_no'], '_customer_user', true );               
                
                update_post_meta($_POST['order_no'], '_customer_user', $user_id); 
                $update_msg = true;
                $user_updated = true;
            }

            if(isset($_POST['order_status']) && trim($_POST['order_status']) != '0'){

                $order_type = get_post_meta($_POST['order_no'], 'order_type', true ); 
                update_post_meta($_POST['order_no'], 'order_type', trim($_POST['order_status']) ); 
                $update_msg = true;
                $update_ostatus = $wpdb->update('order_commission_period_xref', array( 'order_type' => trim($_POST['order_status']) ), array('order_id' => $_POST['order_no'] ) ); 


                
            }

            if(isset($_POST['process_volume']) && trim($_POST['process_volume']) == 'checked'){


                $update_uname = $wpdb->update('order_commission_period_xref', array('processed' => 0), array('order_id' => $_POST['order_no']) );   
                $update_msg = true;

                $reprocessed = true;

            }

            if ( $update_msg ){
                $order_id = $_POST['order_no'];
                $previous_customer = ( isset( $user_updated ) && isset( $previous_customer ) ) ? $previous_customer : '';
                $previous_order_type = ( isset( $order_type ) ) ? $order_type : '';
                $reprocessed =  ( isset( $reprocessed ) && $reprocessed ) ? 'yes' : 'no';            
                $current_timestamp = date('Y-m-d h:i:s');

                $inserted = $wpdb->insert('order_assignment_changes', array(
                        'order_id' => $order_id,
                        'previous_customer' => $previous_customer, 
                        'previous_order_type' => $previous_order_type, 
                        'reprocessed' => $reprocessed, 
                        'timestamp' => $current_timestamp 
                    ) ); 
            }

        }

        echo json_encode( array('update_msg' => $update_msg ) );
        die();
        
    }
*/
    /* ----- Auto Qualify Section ----- */

    public function search_auto_qualified_users(){

        global $wpdb;

        $user_found = false;
        $non_qualified_users = array();
        $affiliate_id = $_POST['affiliate_id'];

        $arr_wpmlm_user_key = explode('-', $affiliate_id);
        $wpmlm_user_key = $arr_wpmlm_user_key[0];

        $auto_qualified_users = $wpdb->get_results("SELECT DISTINCT(caq.affiliate_id) FROM commission_auto_qualified caq WHERE 1");

        foreach ($auto_qualified_users as $key => $auto_qualified_users_val) {
            array_push($non_qualified_users, $auto_qualified_users_val->affiliate_id); 
        }

       
        if (in_array($wpmlm_user_key, $non_qualified_users)){

            $user_found = true;

        }
            
        echo json_encode( array('user_found' => $user_found ) );
        die();

    }



    public function search_reward_qualified_users_byrole(){

        global $wpdb;

        $user_found_pref = false;
        $non_qualified_users = array();
        $affiliate_id = $_POST['affiliate_id'];

        $arr_wpmlm_user_key = explode('-', $affiliate_id);
        $wpmlm_user_key = $arr_wpmlm_user_key[0];

        
        $get_u_id = $wpdb->get_var("SELECT `user_id` FROM wp_wpmlm_users WHERE user_key = '" . $wpmlm_user_key . "'");
        echo "---uid---" . $get_u_id;
        $user_rol = get_userdata( $get_u_id );
        $user_roles = $user_rol->roles;

       
        if ( in_array( 'preferred_customer', $user_roles ) ) {

            $user_found_pref = true;

        }
            
        echo json_encode( array('user_found_pref' => $user_found_pref ) );
        die();

    }


    public function get_unredeemed_rewards() {

        global $wpdb;

        $user_found_pref = false;

        $affiliate_id = $_POST['affiliate_id'];

        $arr_wpmlm_user_key = explode('-', $affiliate_id);
        $wpmlm_user_key = $arr_wpmlm_user_key[0];

        
        $rewards_remaining = $wpdb->get_results("SELECT * FROM `renova_rewards` WHERE `mlm_user_id` = '".$wpmlm_user_key."' AND (type='personal' OR  type='team') AND order_id ='' AND selected_product_id =''");

        //echo "<pre>"; print_r($rewards_remaining); die; 


        $get_u_id = $wpdb->get_var("SELECT `user_id` FROM wp_wpmlm_users WHERE user_key = '" . $wpmlm_user_key . "'");
        $user_rol = get_userdata( $get_u_id );
        $user_roles = $user_rol->roles;


        if ( in_array( 'preferred_customer', $user_roles ) ) {

            $user_found_pref = true;

        }



        $html = '';

            $html .= '<div class="notuse-rewards-section">';

                $html .= '<div class="notuse-rewards-header">';
                    $html .= '<h2>Available Rewards</h2>';
                $html .= '</div>';

                if ( $rewards_remaining ) {

                    $html .= '<table class="notuse-rewards-block wp-list-table widefat">';
                        $html .= '<thead>';
                            $html .= '<tr>';
                                $html .= '<td>User Key</td>';
                                $html .= '<td>Type</td>';
                                $html .= '<td>Commission Period ID</td>';
                                $html .= '<td colspan="3">Action</td>';
                            $html .= '</tr>';
                        $html .= '</thead>';

                        foreach ( $rewards_remaining as $remaining ) {
                            $html .= '<tr id="'.$remaining->id.'">';
                                $html .= '<td>' . $remaining->mlm_user_id . '</td>';
                                $html .= '<td>' . $remaining->type . '</td>';
                                $html .= '<td>' . $remaining->commission_period_id . '</td>';
                                $html .= '<td><a class="delete_rewards button button-primary" href="" data-deleteid="'.$remaining->id.'">Delete</a></td>';
                            $html .= '</tr>';
                        }
                    $html .= '</table>';

                } else {
                    $html .= '<h2>No Data Found.</h2>';
                }
            $html .= '</div>';
           
        //echo $html;
        echo json_encode( array('user_found_pref' => $user_found_pref,'html' => $html ) );
        die();
    }


    public function delete_unredeemed_rewards() {

        global $wpdb;

        $deleteid = $_POST['deleteid'];

        $wpdb->delete( 'renova_rewards', array('id' => $deleteid) );

        echo "1";
       
        die();

    }




    /* ----- Add/Remove qualified users ----- */
    public function modify_auto_qualified_user(){


        // ini_set("display_errors", "1");
        // error_reporting(E_ALL);

        global $wpdb;
        $str_affiliate_id = $_POST['affiliate_id'];
        $arr_affiliate_id = explode('-', $str_affiliate_id);
        $affiliate_id = $arr_affiliate_id[0];

        $added = false;
        $deleted = false;

        if(trim($_POST['user_action']) == 'add'){
     
            $auto_qualified_sql = "INSERT INTO commission_auto_qualified(affiliate_id,created) VALUES($affiliate_id, CURRENT_TIMESTAMP)";
            $added = $wpdb->query($auto_qualified_sql);
        }

        if(trim($_POST['user_action']) == 'delete'){
 
            $auto_qualified_sql = "DELETE from commission_auto_qualified WHERE affiliate_id =  $affiliate_id";
            $deleted = $wpdb->query($auto_qualified_sql);
        }

        echo json_encode( array('added' => $added, 'deleted' => $deleted ) );
        die();
    }

    /* ----- Add/Remove qualified users ----- */
    public function add_reward(){

//echo "<pre>"; print_r($_POST); die;
        global $wpdb;
        $str_affiliate_id = $_POST['affiliate_id'];
        $arr_affiliate_id = explode('-', $str_affiliate_id);
        $affiliate_id = $arr_affiliate_id[0];

        $added = false;
        $deleted = false;


        $commission_period_id = $wpdb->get_var("SELECT commission_period_id FROM `commission_periods` ORDER BY `commission_period_id` DESC LIMIT 1");


        if(trim($_POST['user_action']) == 'add_rewards_personal'){
            $type = "'" . "personal" . "'";
            $auto_qualified_sql = "INSERT INTO renova_rewards(mlm_user_id,type,commission_period_id,created) VALUES($affiliate_id,$type, $commission_period_id,CURRENT_TIMESTAMP)";
            $added = $wpdb->query($auto_qualified_sql);
        }
        if(trim($_POST['user_action']) == 'add_rewards_team'){
            $type = "'" . "team" . "'";
            $auto_qualified_sql = "INSERT INTO renova_rewards(mlm_user_id,type,commission_period_id,created) VALUES($affiliate_id,$type, $commission_period_id,CURRENT_TIMESTAMP)";
            $added = $wpdb->query($auto_qualified_sql);
        }

        if(trim($_POST['user_action']) == 'delete'){
 
            $auto_qualified_sql = "DELETE from commission_auto_qualified WHERE affiliate_id =  $affiliate_id";
            $deleted = $wpdb->query($auto_qualified_sql);
        }

        echo json_encode( array('added' => $added, 'deleted' => $deleted ) );
        die();
    }

    public function show_change_to_role_button(){

        global $wpdb;
        $str_affiliate = $_POST['affiliate'];
        $arr_affiliate_id = explode('-', $str_affiliate);
        $affiliate_id = $arr_affiliate_id[0];

        //Get user role
        $user = $wpdb->get_row("SELECT wwu.user_id FROM wp_wpmlm_users wwu LEFT JOIN wp_users wu on wwu.user_id = wu.ID WHERE (wwu.user_key =".$affiliate_id.")");
        $user_id = (string)$user->user_id;

        $user_meta=get_userdata($user_id);

        $user_roles=$user_meta->roles;
        $user_role = $user_roles[0];

        echo $user_role;
        die();

    }    

    public function modify_user_role(){

        global $wpdb;
        $str_affiliate = $_POST['affiliate'];
        $arr_affiliate_id = explode('-', $str_affiliate);
        $affiliate_id = $arr_affiliate_id[0];
        $user = $wpdb->get_row("SELECT wwu.user_id FROM wp_wpmlm_users wwu LEFT JOIN wp_users wu on wwu.user_id = wu.ID WHERE (wwu.user_key =".$affiliate_id.")");
        $user_id = (int)$user->user_id;
        $u = new WP_User($user_id);
        $role=get_user_meta($user_id,'wp_capabilities',true);
        
        foreach ($role as $key => $value) {
            $u->remove_role( $key );
        }
        $u->add_role($_POST['change_role']);
       
        $update_parent_key = true;
    }
    

}
