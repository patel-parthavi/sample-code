<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.customer-service-admin.co.in
 * @since             1.0.0
 * @package           Customer Service Admin
 *
 * @wordpress-plugin
 * Plugin Name:       Customer Service Admin
 * Plugin URI:        #
 * Description:       Custom develop plugin to to add administrative features to the WP Admin area/menu. The scope includes 
 * all specific interface(s) to facilitate Customer Service tasks.
 * Version:           1.5
 * Author:            #
 * Author URI:        #
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       customer-service-admin-settings
 * Domain Path:       /languages
 *
 */


/***** ------ ***** ----- ***** ----- ***** ------ ***** ----/
/***** ------ ***** ----- MAIN CLASS ----- ***** ----- *****/
/***** ------ ***** ----- ***** ----- ***** ------ ***** ---*/

// require_once('wpdatatable_styles.php');
// require_once('wpdatatable_customizations.php');

Class Customer_Service_Admin {

    var $placement_wizard_admin, $csa_general, $user_notes_admin, $user_order_admin;

    public function __construct() {

         /*ini_set("display_errors", "1");
         error_reporting(E_ALL & ~E_NOTICE);*/

        $this->csa_init_constants();

        /* ----- Adding scripts ----- */
        add_action('admin_enqueue_scripts', array($this, 'nimbus_admin_csa_scripts'), 999);

        /* ----- Plugin activation create db tables ----- */
        register_activation_hook(__FILE__, array($this, 'on_csa_plugin_activation'));

        /* ----- User table settings ----- */

        add_action( 'restrict_manage_users', array($this , 'add_country_section_filter') );
        add_filter('pre_get_users',  array($this , 'filter_users_by_country_section') );
        add_filter( 'manage_users_sortable_columns', array($this , 'filter_manage_users_sortable_columns'), 10, 1 );
        add_action( 'pre_get_posts',array($this , 'apply_country_product_filter' ));
        add_action( 'restrict_manage_posts', array($this , 'display_country_filter' ));
        $this->csa_init_actions();  

        include_once(CSA_INCLUDE_PATH.'user_table_customizations.php');
        
        /*
         * initalizing affiliate_options class
         */
        include_once(CSA_INCLUDE_PATH.'csa_general_admin.php');
        $this->csa_general = new csa_general();  

        /*
         * initalizing placement_wizard_admin class
         */
        include_once(CSA_INCLUDE_PATH.'placement_wizard_admin.php');
        $this->placement_wizard_admin = new placement_wizard_admin();      

        /*
         * initalizing placement_wizard_admin class
        */
        include_once(CSA_INCLUDE_PATH.'user_notes_admin.php');
        $this->user_notes_admin = new user_notes_admin();  
        /*
         * initalizing placement_wizard_admin class
        */
        include_once(CSA_INCLUDE_PATH.'user_order_admin.php');
        $this->user_order_admin = new user_order_admin();     
    }
    public function apply_country_product_filter( $query ) {

    global $pagenow;


    if ( $query->is_admin && $pagenow == 'edit.php' && isset( $_GET['country_filter'] ) && $_GET['country_filter'] != '' && $_GET['post_type'] == 'product' ) {

     
      $meta_key_query = array(
        array(
          'key'     => '_wcj_product_by_country_visible',
          'value'   => $_GET['country_filter'],
          'compare' => 'like'
        )
      );
      $query->set( 'meta_query', $meta_key_query );
      

    }

    }
    public  function display_country_filter( $post_type ) {
        global $woocommerce;
        $c_val=get_option('country_filter');

        if( $post_type == 'product' && $c_val == "on" ) {
        $countries_list=WC()->countries->get_allowed_countries();
        //print_r(wcj_get_countries);



        echo '<select name="country_filter">';

        echo '<option value>Select Country</option>';
        foreach ($countries_list as $key => $value) {
        if( isset( $_GET['country_filter'] ) && $_GET['country_filter'] == $key ) {

        echo "<option value='".$key."' selected>".$value."</option>";

        }else{

        echo "<option value='".$key."'>".$value."</option>";
        }

        }

        echo '</select>';

        }

    }
    public function nimbus_admin_csa_scripts() { 
        
        /* ----- Adding autocomplete js ----- */
        wp_enqueue_script( 'jquery-ui-autocomplete' );

        //Enqueue dialog script
        wp_enqueue_script( 'jquery-ui-dialog' );
        //Enqueue dialog style
        wp_enqueue_style (  'wp-jquery-ui-dialog');

        /* ----- Adding nimbus csa css ----- */
        wp_register_style('csa-ui', CSA_CSS_URL . 'style.css');
        wp_enqueue_style('csa-ui');

        // Adding nimbus csa scripts
        wp_register_script('csa-script', CSA_JS_URL . 'customer_service_admin.js', array('jquery'), '4.9.7', true);
        wp_enqueue_script('csa-script');        

    }

    /*==========================================================*/
    /*** ------ ***** ----- USER PAGE TABLE ----- ***** ----- ***/
    /*==========================================================*/
 
    //Search Filter Dashboard User Table 
    public function add_country_section_filter() {

        global $woocommerce;
        $countries_obj   = new WC_Countries();
        $woo_countries   = $countries_obj->__get('countries');    

        if ( isset( $_GET[ 'country_section' ]) ) {
            $section = $_GET[ 'country_section' ];
            $section = !empty( $section[ 0 ] ) ? $section[ 0 ] : $section[ 1 ];
        } else {
            $section = -1;
        }
        echo ' <select name="country_section[]" style="float:none;">';
        echo '<option value="0">Select Country ...</option>';
        
        foreach ( $woo_countries as $country_code => $country_name ) {
            echo '<option value="' . $country_code . '"> ' . $country_name . '</option>';
        }
        echo '</select>';
        echo '<input type="submit" class="button" value="Filter">';
    }


    public function filter_users_by_country_section($query){

        global $pagenow;     

        if (is_admin() && 'users.php' == $pagenow && isset( $_GET[ 'country_section' ] ) && 
             is_array( $_GET[ 'country_section' ] ) ) {

            $section = $_GET[ 'country_section' ];

            $section = !empty( $section[ 0 ] ) ? $section[ 0 ] : $section[ 1 ];

            $button = key( array_filter( $_GET, function($v) { return __( 'Filter' ) === $v; } ) );
           
            if ($section != '0') {

                $meta_query = array(
                                array(
                                    'key' => 'billing_country',
                                    'value' => $section,
                                    'compare' => 'LIKE'
                                )
                            );
             
                $query->set('meta_key', 'billing_country');
                $query->set('meta_query', $meta_query);


            }
        }

    }

    // define the manage_users_sortable_columns callback 
    public function filter_manage_users_sortable_columns( $array_user_table_cols) { 

        $array_user_table_cols['country'] = 'country';
        $array_user_table_cols['enrollment_date'] = 'enrollment_date';

        return $array_user_table_cols; 

    }     

    /*====================================================*/
    /*** ------ ***** ----- CONSTANTS ----- ***** ----- ***/
    /*====================================================*/

    public function csa_init_constants(){

        $this->define('CSA_PLUGIN_PATH', plugin_dir_path(__FILE__));
        $this->define('CSA_INCLUDE_PATH', CSA_PLUGIN_PATH.'includes/');
        $this->define('CSA_VIEW_PATH', CSA_PLUGIN_PATH.'views/');
        
        $this->define('CSA_PLUGIN_URL', plugin_dir_url(__FILE__));
        $this->define('CSA_ASSETS_URL', CSA_PLUGIN_URL.'assets/');
        $this->define('CSA_CSS_URL', CSA_ASSETS_URL.'css/');
        $this->define('CSA_JS_URL', CSA_ASSETS_URL.'js/');
    }

    private function define($name, $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * Add a column to a manually  created table
     */
    public function wdt_addNew_manual_column() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtFrontendEditTableNonce')) {
            exit();
        }

        $tableId = (int)$_POST['table_id'];
        $columnData = $_POST['column_data'];
        var_dump($_POST['column_data']);
        wpDataTableConstructor::addNewManualColumn($tableId, $columnData);

        exit();
    }

    /*======================================================*/
    /***** ------ ***** ----- ACTIONS ----- ***** ----- *****/
    /*======================================================*/

    public function csa_init_actions(){

        /* ----- Adding plugin pages menu ----- */
        add_action('admin_menu', array($this, 'customer_service_admin_menu'));   

        // add_action('wp_ajax_filter_commission_period_results', array($this, 'filter_commission_period_results'));
        // add_action('wp_ajax_nopriv_filter_commission_period_results', array($this, 'filter_commission_period_results'));

    }

    public function on_csa_plugin_activation() {

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
       
        $wpdb->get_var("CREATE TABLE `order_assignment_changes` (
              `ID` int(10) NOT NULL AUTO_INCREMENT COMMENT 'UNQ',
              `order_id` bigint(20) NOT NULL COMMENT 'The order_id being updated',
              `previous_customer` varchar(30) NOT NULL COMMENT 'The key of previous_customer otherwise empty if no changes',
              `previous_order_type` varchar(30) NOT NULL COMMENT 'The updated order_type, empty if no change',
              `reprocessed` enum('yes','no') DEFAULT NULL COMMENT 'Yes if reprocess checkbox was checked otherwise No',
              `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Record created datetime',
              PRIMARY KEY (`transaction_ID`)
            ) $charset_collate"
        );

        $wpdb->get_var("CREATE TABLE `user_placement_changes` (
              `transaction_ID` int(10) NOT NULL AUTO_INCREMENT COMMENT 'UNQ',
              `to_be_placed` varchar(30) NOT NULL COMMENT 'The user_key of to_be_placed affiliate',
              `parent` varchar(30) NOT NULL COMMENT 'The user_key of parent affiliate',
              `leg` enum('0','1','none') NOT NULL DEFAULT 'none' COMMENT 'Placement leg',
              `temporary_unplaced` varchar(30) NOT NULL COMMENT 'The user_key of temporary_unplaced affiliate',
              `current_user` varchar(30) DEFAULT NULL COMMENT 'Current logged in user key',
              `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Record created datetime',
              PRIMARY KEY (`transaction_ID`)
            ) $charset_collate"
        );

        $wpdb->get_var("CREATE TABLE `user_sponsor_changes` (
              `transaction_ID` int(10) NOT NULL AUTO_INCREMENT COMMENT 'UNQ',
              `user` varchar(30) NOT NULL COMMENT 'The user_key of to be sponsored affiliate',
              `current_sponsor` varchar(30) NOT NULL COMMENT 'The user_key of existing sponsor affiliate',
              `new_sponsor` varchar(30) NOT NULL COMMENT 'The user_key of new sponsor affiliate',
              `admin_user` varchar(30) DEFAULT NULL COMMENT 'Current logged in user key',
              `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Record created datetime',
              PRIMARY KEY (`transaction_ID`)
            ) $charset_collate"
        ); 

        $wpdb->get_var("CREATE TABLE `user_notes` (
              `note_id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'UNQ',
              `user_id` int(10) NOT NULL COMMENT 'wp_users ID of the customer',
              `admin_id` int(10) NOT NULL COMMENT 'wp_users ID of the admin (logged in user)',
              `subject` varchar(255) NOT NULL COMMENT 'the subject of the note',
              `text` varchar(255) DEFAULT NULL COMMENT ' the body of the note text',
              `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'the timestamp of the note creation',
              `last_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'the timestamp of the last updated', 
              `last_updated_by` int(10) NOT NULL DEFAULT '0' COMMENT ' the wp_users ID of the admin that last updated ',
              PRIMARY KEY (`note_id`)
            ) $charset_collate"
        );

    }

    public function customer_service_admin_menu(){

        add_menu_page('Customer Service Admin', 'Customer Service', 'manage_options', 'customer-service-admin', array($this, 'placement_wizard'), CSA_ASSETS_URL.'images/icon-small.png', 10);     

        add_submenu_page('customer-service-admin', 'General', 'General', 'manage_options', 'csa-general', array($this->csa_general, 'csa_general_load_view'));   

        add_submenu_page('customer-service-admin', 'Placement', 'Placement', 'manage_options', 'placement-wizard-admin', array($this->placement_wizard_admin, 'placement_wizard'));

        add_submenu_page('customer-service-admin', 'User Notes', 'User Notes', 'manage_options', 'user-notes-admin', array($this->user_notes_admin, 'user_notes'));

        add_submenu_page('customer-service-admin', 'Order Admin', 'Order Admin', 'manage_options', 'user-order-admin',array($this->user_order_admin, 'order_admin'));
        
        remove_submenu_page('customer-service-admin', 'customer-service-admin');

    }  

    public function filter_commission_period_results(){

        $arr_session = array( 'status' => 'fail');

        if( isset($_POST['commission_period_id']) ){
            error_log( __FILE__ . __LINE__ . "post_commission_period_id:" . print_r( $_POST['commission_period_id'] , true ) );

            set_transient('current_commission_period_id', $_POST['commission_period_id']);            
            // $_SESSION['commission_period_id'] = $_POST['commission_period_id'];
            // $_SESSION['wp_table_id'] = $_POST['wp_table_id'];

            $arr_session = array(
                                'status' => 'success'
                            );

        }        

        echo json_encode($arr_session);
        die();
    } 


    public function resolve_commission_period_placeholder( $query_string, $ID ) {

        // ini_set("display_errors", "1");
        // error_reporting(E_ALL & ~E_NOTICE);


        $commission_period_id = get_transient('current_commission_period_id');
        error_log( __FILE__ . __LINE__ . "commission_period_id:" . print_r( $commission_period_id , true ) );

        if ( empty( $commission_period_id ) ) {
            
            $commission_period_id = $this->get_latest_comission_period_id();
            
        } 
        error_log( __FILE__ . __LINE__ . "query_string_prev:" . print_r( $query_string , true ) );

        if ( strpos( $query_string, '%COMMISSION_PERIOD_ID%' ) !== false ) {
            $query_string = str_replace( '%COMMISSION_PERIOD_ID%', $commission_period_id, $query_string ) ;
            error_log( __FILE__ . __LINE__ . "query_string:" . print_r( $query_string , true ) );
        } 
        

        delete_transient('current_commission_period_id');  

        return $query_string;
    }

    public function get_latest_comission_period_id() {
        global $wpdb;

        $commission_period_id = '';

        $result = $wpdb->get_var( "SELECT commission_period_id FROM commission_periods ORDER BY start_date DESC LIMIT 1", ARRAY_A );

        if ( $result ) {
        $commission_period_id = $result;
        }

        return $commission_period_id; 
    }

}

$customer_service_admin = new Customer_Service_Admin();



