<?php
Class user_order_admin{

	public function __construct() {

       $this->order_admin_actions();
    }
    /* -----Render View page ----- */
    public function order_admin(){
    	$order_types = $this->get_all_custom_order_types();
        require_once(CSA_PLUGIN_PATH . 'views/user_orders_admin_page.php');
    }
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
    public function order_admin_actions(){

        add_action('wp_ajax_get_order_nos', array($this , 'get_order_nos' ));
        add_action('wp_ajax_after_order_selection', array($this , 'after_order_selection' ));        
        add_action('wp_ajax_update_order_assignment', array($this , 'update_order_assignment' )); 
        add_action('wp_ajax_update_order_backdates', array($this , 'update_order_backdates' ));
    }
    /* -----Check for Order ID ----- */

    public function get_order_nos(){

        global $wpdb;

        $order_ids = array();

        if (isset($_REQUEST['q'])) {

            $order_ids = $wpdb->get_results("SELECT wp.ID FROM wp_posts wp WHERE (post_type = 'shop_order' AND ID LIKE '%" . $_REQUEST['q'] . "%')");
            
        }

        echo json_encode($order_ids);
        die();

    }
    /* ----- Check for processing volume ----- */
    public function after_order_selection(){

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
    }
    public function update_order_assignment(){

       /* ini_set("display_errors", "1");
        error_reporting(E_ALL);*/
        
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
    public function update_order_backdates(){
        global $wpdb;
        $table="order_backdates";
        $comm_period = $wpdb->get_results('SELECT MAX(commission_period_id) AS second_highest,(SELECT MAX(commission_period_id) FROM commission_periods) AS highest FROM commission_periods WHERE commission_period_id NOT IN (SELECT MAX(commission_period_id) FROM commission_periods);
        ');
       
        $from_commission_period_id = $comm_period[0]->highest;
        $to_commission_period_id   = $comm_period[0]->second_highest;
        $allowed_ext = array("csv");
        $extension = end(explode(".", $_FILES["csv"]["name"]));

        if(in_array($extension, $allowed_ext)){

            $file_data = fopen($_FILES["csv"]["tmp_name"], 'r');  
            fgetcsv($file_data);  

            while($row = fgetcsv($file_data)){

                
                foreach ($row as $key => $value) {
                   $my_date = date("Y-m-d H:i:s");
                    $insert_id=$wpdb->insert( 
                        $table, 
                        array( 
                            'order_id' => $value,
                            'from_commission_period_id'=>$from_commission_period_id,
                            'to_commission_period_id'=>$to_commission_period_id,
                            'processed'=>0,
                            'created'=>$my_date,
                        )
                    );
                    
                }

            }
            echo json_encode(array('update_msg' => "Updated Successfully." ) );

        }else{
            $ext = "Wrong Extension";
            echo json_encode( array('update_msg' => $ext ) );
        }
        
        die();
    }
}
