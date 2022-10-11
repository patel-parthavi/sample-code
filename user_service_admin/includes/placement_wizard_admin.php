<?php

Class placement_wizard_admin {

    var $is_valid;

    public function __construct() {

        // $this->placement_wizard_filters();
        $this->placement_wizard_actions();
        $this->is_valid = 1;
    }

    public function placement_wizard() {
        $unplaced_affiliates = $this->get_multiselect_options();
        require_once(CSA_PLUGIN_PATH . 'views/placement_wizard_admin_page.php');
    }

    public function placement_wizard_actions() {

        /* Ajax autocomplete */
        add_action('wp_ajax_get_affiliate_autocomplete_results', array($this, 'get_affiliate_autocomplete_results'));
        add_action('wp_ajax_search_affiliate_autocomplete_results', array($this, 'search_affiliate_autocomplete_results'));
        
        add_action('wp_ajax_get_multiselect_options', array($this, 'get_multiselect_options'));

        //Get step 1 affiliate
        add_action('wp_ajax_get_selected_affiliate_node', array($this, 'get_selected_affiliate_node'));

        //To place affiliate
        add_action('wp_ajax_place_affiliate', array($this, 'place_affiliate'));

        //To fetch affiliate for a sponsor
        add_action('wp_ajax_get_affiliate_sponsor', array($this, 'get_affiliate_sponsor'));

        //On redirect to geneology tree view page
        add_action('wp_head', array($this, 'trigger_parent_search'));
    }

    /* ----- Autocomplete affiliate id ----- */

    public function get_affiliate_sponsor(){

         global $wpdb;
        $arr_sponsor = array();

        if ( isset( $_POST['affiliate_id'] ) ) {

            $affiliates_sponsor = $wpdb->get_row("SELECT wwu.sponsor_key, wu.user_login as sponsor_login FROM wp_wpmlm_users wwu LEFT JOIN wp_users wu on (SELECT wwus.user_id FROM wp_wpmlm_users wwus WHERE wwu.sponsor_key = wwus.user_key) = wu.ID WHERE wwu.user_key ='". $_POST['affiliate_id']."'");
         
            echo json_encode($affiliates_sponsor);
        }
        die();

    }

    public function get_affiliate_autocomplete_results() {

        global $wpdb;
        $arr_commission_affiliates = array();

        if (isset($_REQUEST['q'])) {

            $commission_affiliates = $wpdb->get_results("SELECT wwu.user_key, wu.user_login FROM wp_wpmlm_users wwu
            LEFT JOIN wp_users wu on wwu.user_id = wu.ID 
            JOIN wp_usermeta wm on  wwu.user_id = wm.user_id
            WHERE (user_key LIKE '%".$_REQUEST['q']."%' OR user_login LIKE '%".$_REQUEST['q']."%') 
            AND wm.meta_key = 'wp_capabilities' AND wm.meta_value LIKE '%representative%' AND wwu.user_key !='".$_REQUEST['affiliate_id']."'");

            echo json_encode($commission_affiliates);
        }
        die();
    }

    public function search_affiliate_autocomplete_results() {

        global $wpdb;
        $arr_commission_affiliates = array();

        if (isset($_REQUEST['q'])) {

            $commission_affiliates = $wpdb->get_results("SELECT wwu.user_key, wu.user_login FROM wp_wpmlm_users wwu
            LEFT JOIN wp_users wu on wwu.user_id = wu.ID 
            JOIN wp_usermeta wm on  wwu.user_id = wm.user_id
            WHERE (user_key LIKE '%".$_REQUEST['q']."%' OR user_login LIKE '%".$_REQUEST['q']."%') 
            AND wm.meta_key = 'wp_capabilities' AND wm.meta_value LIKE '%representative%'");
            echo json_encode($commission_affiliates);
        }
        die();
    }

    /* ----- Get multiselect options ----- */
    public function get_multiselect_options(){

        global $wpdb;
        $arr_multiselect_options = array();

        $unplaced_affiliates = $wpdb->get_results("SELECT wwu.user_key, wu.user_login FROM wp_wpmlm_users wwu LEFT JOIN wp_users wu on wwu.user_id = wu.ID WHERE wwu.parent_key = '2' AND (wwu.sponsor_key != '' OR wwu.sponsor_key != '0') ");

        return ($unplaced_affiliates);
  
    }

    /* ----- Search parent affiliate on geneology tree view page ----- */
    public function trigger_parent_search(){

        $tree_view_url = $_SERVER['REQUEST_URI'];
        $arr_url = explode('/',$tree_view_url);
        $arr_url = explode('/', $arr_url[1]);

        if(trim($arr_url[0]) == 'geneology-hierarchy'){

            if(isset($_REQUEST['trigger_tree'])){ ?>
                <script type="text/javascript">
                    $(document).ready(function(){
                        $('.affiliate-username').val("<?php echo $_REQUEST['root_node']; ?>");

                        setTimeout(function(){ 
                            $('.search-btn').trigger('click');
                        }, 500);
                        
                    });                    
                </script>

            <?php }

        }

    }

    /* ----- Check whether user is affiliate ----- */

    public function affiliate_check($affiliate_id) {

        global $wpdb;
        $sponsor_key = '';
        $parent_key = 0;
        $is_affiliate = 0;

        $mlm_entry = $wpdb->get_row("SELECT parent_key,sponsor_key
                                     FROM wp_wpmlm_users
                                     WHERE wp_wpmlm_users.user_key ='" . $affiliate_id . "'");
        if (!empty($mlm_entry)) {

            $sponsor_key = $mlm_entry->sponsor_key;
            $parent_key = (int) $mlm_entry->parent_key;


            if ($parent_key == 1 && $sponsor_key == '0') {

                //Company Orphan
                $is_affiliate = 3;
                //$is_affiliate = "//Company Orphan";
            } elseif ($parent_key == 2 && $sponsor_key != '0' && $sponsor_key != '') {

                //Unplaced Affiliate Customer
                $is_affiliate = 1;
                // $is_affiliate = "//Unplaced Representative";
            } elseif ($parent_key == 1 && $sponsor_key != '0' && $sponsor_key != '') {

                //Preferred Customer
                $is_affiliate = 2;
                // $is_affiliate = "//Preferred Customer";
            } elseif ($parent_key != 0 && $parent_key != '' && $sponsor_key != '0' && $sponsor_key != '') {

                //Is Affiliate
                $is_affiliate = 4;
                // $is_affiliate = "//Is Representative";
            } else {

                //Old Customeer
                $is_affiliate = 5;
                // $is_affiliate = "//Old Customer";
            }
        } else {
            $is_affiliate = -1;
        }


        return $is_affiliate;
    }

    /* ----- Get current commission period ----- */

    public function current_commission_period() {

        global $wpdb;
        $current_commission_period_query = " 
                SELECT commission_period_id 
                FROM commission_periods 
                WHERE locked = 0 
                AND posted = 0 
                ORDER BY commission_period_id DESC 
                LIMIT 1 ";

        $current_commission_period = $wpdb->get_results($current_commission_period_query);
        return $current_commission_period[0]->commission_period_id;
    }

    /* ----- Check whether an affiliate is qualified ----- */

    public function is_affiliate_qualified($affiliate_id) {
        global $wpdb;

        if (is_user_logged_in()) {

            //Set the current Title Level 
            $qry = "CALL affiliate_current_commission_title({$affiliate_id})";

            $current_level = $wpdb->get_results($qry);

            //Set the Affiliate's combined total CV
            $cv = $wpdb->get_results("SELECT get_affiliate_qualification_volume('{$affiliate_id}') AS 'total_cv'");

            //Set the qualifications for this Affiliate's current Title
            $qualifications = $wpdb->get_results("
                SELECT ctq.* FROM commission_titles ct 
                JOIN commission_title_qualifications ctq 
                        ON ct.title_id = ctq.title_id 
                WHERE ct.level = {$current_level[0]->qualified_title_level};
            ");

            //Return true if total cv >= qualification minimums
            $qualified = $cv[0]->total_cv >= $qualifications[0]->min_personal_cv;

            if (!empty($qualified)) {
                $is_qualified = 'Y';
            } else {
                $is_qualified = 'N';
            }
            return $is_qualified;
        }
        return $is_qualified;
    }

    /* ----- For Active Users ----- */

    public function is_affiliate_active($current_user_key) {

        global $wpdb;
        $active = false;

        if (is_user_logged_in()) { // if logged in user
            //Previous 3 months Date range
            $today_date = date('Y-m-d');
            $three_months_ago = date('Y-m-d', strtotime('-90 days', strtotime($today_date)));

            $query = "SELECT COUNT(*) AS `order_count` 
                FROM `order_commission_period_xref` 
                WHERE `user_key` = '{$current_user_key}' 
                AND DATE(`created`) BETWEEN DATE(CURRENT_TIMESTAMP - INTERVAL 90 DAY) 
                AND DATE(CURRENT_TIMESTAMP)
                AND `processed` = '1'";

            $active_arr = $wpdb->get_row($query);

            if ($active_arr->order_count > 0) {
                $active = true;
            } else {
                $active = false;
            }
        }
        return $active;
    }

    /* ----- Get current user leg details ----- */

    public function csa_get_user_leg_data($leg_parent) {

        global $wpdb;

        $user_leg_qry = "
            SELECT 
                mlm.user_key, 
                mlm.sponsor_key,
                mlm.own_pv,
                ct.short_abbreviation AS 'title', 
                spu.user_login AS 'sponsor_username', 
                usr.user_nicename, usr.ID,
                um.meta_value AS 'profile_image' 
            FROM wp_wpmlm_users mlm 
            JOIN wp_users usr ON mlm.user_id = usr.ID 
            JOIN commission_titles ct 
                ON mlm.rank = ct.level 
            LEFT JOIN wp_wpmlm_users spmlm 
                ON spmlm.user_key = mlm.sponsor_key
            LEFT JOIN wp_users spu 
                ON spu.ID = spmlm.user_id
            LEFT JOIN wp_usermeta um 
                ON usr.ID = um.user_id 
            AND um.meta_key = 'user_profile_img' 
            WHERE mlm.parent_key = " . $leg_parent . "
            ";
        $user_legs = $wpdb->get_results($user_leg_qry);
        
        return $user_legs;
    }

    /* ----- Get recrusive leg data ----- */

    public function csa_get_user_leg_recursive($current_user_key) {

        global $wpdb;
        $leg_val = array();
        $level_count++;
        //echo "string".$current_user_key->user_key;
        $current_commission_period = $this->current_commission_period();

        $leg_data = $this->csa_get_user_leg_data($current_user_key->user_key);
        //print_r($leg_data);
        $current_user_records = count($leg_data);
        foreach ($leg_data as $key => $leg) {

            $leg_val []= array(
                'level_count' => $level_count,
                'user_key' => $leg->user_key,
                'sponsor_key' => $leg->sponsor_username,
                'qualified' => $this->is_affiliate_qualified($leg->user_key, $current_commission_period),
                
                'own_pv' => $leg->own_pv,
                'current_rank_user' => $leg->title,
                'user_name' => $leg->user_nicename,
                'user_image' => $leg->profile_image,
                'is_active' => $this->is_affiliate_active($leg->user_key),
                'current_user_records' => $current_user_records
            );
           // $leg_val=array();

            /*if ($leg->leg) {

                $leg_val['r'] = array(
                    'level_count' => $level_count,
                    'user_key' => $leg->user_key,
                    'sponsor_key' => $leg->sponsor_username,
                    'qualified' => $this->is_affiliate_qualified($leg->user_key, $current_commission_period),
                    
                    'own_pv' => $leg->own_pv,
                    'current_rank_user' => $leg->title,
                    'user_name' => $leg->user_nicename,
                    'user_image' => $leg->profile_image,
                    'legs' => '',
                    'is_active' => $this->is_affiliate_active($leg->user_key)
                );
            }

            if (!$leg->leg) {

                $leg_val['l'] = array(
                    'level_count' => $level_count,
                    'user_key' => $leg->user_key,
                    'sponsor_key' => $leg->sponsor_username,
                    'qualified' => $this->is_affiliate_qualified($leg->user_key, $current_commission_period),
                   
                    'own_pv' => $leg->own_pv,
                    'current_rank_user' => $leg->title,
                    'user_name' => $leg->user_nicename,
                    'user_image' => $leg->profile_image,
                    'legs' => '',
                    'is_active' => $this->is_affiliate_active($leg->user_key)
                );
            }*/
        }



        $tree_node = array(
            'user_key' => $current_user_key->user_key,
            
            'own_pv' => $current_user_key->own_pv,
            'user_name' => $current_user_key->user_name,
            'user_image' => $current_user_key->user_image,
            'legs' => $leg_val
        );

        return $tree_node;
    }

    /* ----- Get Parent user key ----- */

    public function get_parent_user_key($affiliate_id) {

        global $wpdb, $table_prefix;

        $parent_qry = "
            SELECT mlm.parent_key
            FROM wp_wpmlm_users mlm
            WHERE mlm.user_key = $affiliate_id";
        $parent_data = $wpdb->get_row($parent_qry);
        
        if($parent_data && !empty($parent_data))
            $parent_key = $parent_data->parent_key;
        else
            $parent_key =  '';

        return $parent_key;
    }

    public function get_selected_affiliate_node() {

        $add_new_img = CSA_ASSETS_URL . 'images/add_plus.png';
        $tree_node = array();

        if ($_POST['step_screen'] == 1 || $_POST['step_screen'] == 3) {
            $affiliate_type = $this->affiliate_check($_POST['affiliate_id']);
            $status = "Affiliate";
            $level = 1;

            if ($affiliate_type == 1) {

                $affiliate_data = $this->csa_get_user_data($_POST['affiliate_id']);
                $status = "Unplaced Affiliate";
                
            }

            if ($affiliate_type == 4) {

                $parent_key = $this->get_parent_user_key($_POST['affiliate_id']);
                $affiliate_data = $this->csa_get_user_data($parent_key);

                $status = "Affiliate";

                if (!empty($affiliate_data)) {
                    $leg_data = $this->csa_get_user_leg_recursive($affiliate_data);
                }
            }
            
            if (!empty($affiliate_data)) {
               
                $tree_node = array(
                    'user_key' => $affiliate_data->user_key,
                    'sponsor_key' => $affiliate_data->sponsor_key,
                    'qualified' => '',
                  
                    'own_pv' => $affiliate_data->own_pv,
                    'current_rank_user' => '',
                    'user_name' => ucfirst($affiliate_data->user_nicename),
                    'user_image' => $affiliate_data->profile_image,
                    'user_id' => $affiliate_data->ID,
                    'legs' => $leg_data,
                    'is_active' => ''
                );
            }
        }

        if ($_POST['step_screen'] == 2) {

            $parent_users = $this->get_child_user_keys($_POST['affiliate_id']);
            $parent_data = $this->csa_get_user_data($_POST['affiliate_id']);
            

            foreach ($parent_users as $parent_users_key => $parent_users_value) {
                $affiliate_data[$parent_users_key] = $this->csa_get_user_data($parent_users_value->user_key);
            }

            foreach ($affiliate_data as $affiliate_data_key => $affiliate_data_value) {

                
                $affiliate_details[] = $affiliate_data_value;
            }
           
            $parent_details = array(
                'user_key' => $parent_data->user_key,
                'sponsor_key' => $parent_data->sponsor_key,
                'own_pv' => $parent_data->own_pv,
                'title' => $parent_data->title,
                'sponsor_username' => $parent_data->sponsor_username,
                'user_nicename' => $parent_data->user_nicename,
                'ID' => $parent_data->ID,
                'profile_image' => $parent_data->profile_image,
                'legs' => $affiliate_details
            );
             
        }
        ?>

        <?php if ($affiliate_type == 1 && $_POST['step_screen'] == 1 && !empty($tree_node)) { ?>
            <div class="person tooltip " id="<?php echo $tree_node['user_key']; ?>">
            <?php
            if (trim($tree_node['user_image']) != '') {
                $profile_img = $tree_node['user_image'];
            } else {
                $profile_img = CSA_ASSETS_URL . 'images/no_profile_img.jpeg';
            }
            ?>
                <div class="profile-img-wrap" style="background-image: url('<?php echo $profile_img; ?>')">              
                    <img src="<?php echo $profile_img; ?>" alt="">
                </div>

                <div class="name tooltip-handle">

                <?php echo $tree_node['user_name']; ?>
                    <span class="user-details "> 
                       
                    </span>
                </div>
            </div>

            <ul class="unplaced-user-details">
                <li>Affiliate Status: <?php echo $status; ?></li>
                <li>Affiliate ID: <?php echo $tree_node['user_key']; ?></li>
                <li>Sponsor: <?php echo $tree_node['sponsor_key']; ?></li>
                <li>Personal Volume: <?php echo $tree_node['own_pv']; ?></li>
                
                
            </ul>
                            <?php } ?>

                            <?php if ($affiliate_type == 4 && ($_POST['step_screen'] == 1 || $_POST['step_screen'] == 3) && !empty($tree_node)) { ?>

            <div class="hv-item">
                <div class="hv-item-parent" >

                    <div class="person tooltip " id="<?php echo $tree_node['user_key']; ?>">
            <?php
            if (trim($tree_node['user_image']) != '') {
                $profile_img = $tree_node['user_image'];
            } else {
                $profile_img = CSA_ASSETS_URL . 'images/no_profile_img.jpeg';
            }
            ?>
                        <div class="profile-img-wrap" style="background-image: url('<?php echo $profile_img; ?>')">              
                            <img src="<?php echo $profile_img; ?>" alt="">
                        </div>

                        <div class="name tooltip-handle">
                        <?php echo $tree_node['user_name']; ?>
                            <span class="user-details "> 
                                <ul class="tooltiptext">
                                    <li>Affiliate Status: <?php echo $status; ?></li>
                                    <li>Affiliate ID: <?php echo $tree_node['user_key']; ?></li>
                                    <li>Sponsor: <?php echo $tree_node['sponsor_key']; ?></li>
                                    <li>Personal Volume: <?php echo $tree_node['own_pv']; ?></li>
                                    
                                </ul>
                            </span>
                        </div>
                    </div>

                </div>



                <div class="hv-item-children" >

                    <!-- Left Node -->
            <?php if (!empty($leg_data['legs'])) {
                foreach ($leg_data['legs'] as $key => $value) {
                    
             ?>      
                        <div class="hv-item-child">    
                            <div class="hv-item">                            
                                <div class="person tooltip " data-leg="0" id="<?php echo $value['user_key'] ?>">
                <?php
                if (trim($leg_data['user_image']) != '') {
                    $profile_img = $value['user_image'];
                } else {
                    $profile_img = CSA_ASSETS_URL . 'images/no_profile_img.jpeg';
                }
                ?>
                                    <div class="profile-img-wrap" style="background-image: url('<?php echo $profile_img; ?>')">              
                                        <img src="<?php echo $profile_img; ?>" alt="">
                                    </div>

                                    <div class="name tooltip-handle">
                                    <?php echo $value['user_name']; ?>
                                        <span class="user-details "> 
                                            <ul class="tooltiptext">
                                               
                                                <li>Affiliate ID: <?php echo $value['user_key']; ?></li>
                                                <li>Sponsor: <?php echo $value['sponsor_key']; ?></li>
                                                <li>Personal Volume: <?php echo $value['own_pv']; ?></li>
                                                
                                            </ul>
                                        </span>
                                    </div>            
                                </div>
                            </div>
                        </div>
                        <?php }?>
                        <!-- <div class="hv-item-child">    
                            <div class="hv-item">  

                                <div class="person add-new add-new-left" data-leg="0">
                                    <div class="profile-img-wrap" style="background-image: url('<?php echo $add_new_img; ?>')">              
                                        <img src= "<?php echo $add_new_img; ?>" alt="" scale="0">
                                    </div>  
                                    <div class="name">Empty Node</div>
                                </div>

                            </div>
                        </div> -->
            <?php }  ?>
                        
            


                    <!-- Right Node -->
            

                </div>
            </div>
        <?php } ?>
                <?php if (empty($tree_node) && $_POST['step_screen'] == 1) { ?>
            <span class='error-msg' style='color:red'>Insufficient Record details in database.</span>
        <?php } ?>

        <?php if ($_POST['step_screen'] == 2) { ?>
            <div class="hv-item">
                <div class="hv-item-parent" >

                    <div class="person tooltip " id="<?php echo $parent_details['user_key']; ?>">
            <?php
            if (trim($parent_details['user_image']) != '') {
                $profile_img = $parent_details['user_image'];
            } else {
                $profile_img = CSA_ASSETS_URL . 'images/no_profile_img.jpeg';
            }
            ?>
                        <div class="profile-img-wrap" style="background-image: url('<?php echo $profile_img; ?>')">              
                            <img src="<?php echo $profile_img; ?>" alt="">
                        </div>

                        <div class="name tooltip-handle">
            <?php echo $parent_details['user_nicename']; ?>
                            <span class="user-details "> 
                                <ul class="tooltiptext">
                                    <li>Affiliate Status: <?php echo $status; ?></li>
                                    <li>Affiliate ID: <?php echo $parent_details['user_key']; ?></li>
                                    <li>Sponsor: <?php echo $parent_details['sponsor_key']; ?></li>
                                    <li>Personal Volume: <?php echo $parent_details['own_pv']; ?></li>
                                   
                                </ul>
                            </span>
                        </div>
                    </div>

                </div>



                <div class="hv-item-children" >

                    <!-- Left Node -->
            <?php if (!empty($parent_details['legs'])) {
                foreach ($parent_details['legs'] as $key => $value) {
                   //print_r($value);die;
                
             ?>      
                        <div class="hv-item-child">    
                            <div class="hv-item">                            
                                <div class="person update-child-affiliate tooltip " data-leg='0' data-affiliate_id="<?php echo $value->user_key; ?>" data-affiliate_name="<?php echo $value->user_nicename; ?>">
                        <?php
                        if (trim($value->profile_image) != '') {
                            $profile_img = $value->profile_image;
                        } else {
                            $profile_img = CSA_ASSETS_URL . 'images/no_profile_img.jpeg';
                        }
                        ?>
                                    <div class="profile-img-wrap" style="background-image: url('<?php echo $profile_img; ?>')">              
                                        <img src="<?php echo $profile_img; ?>" alt="">
                                    </div>

                                    <div class="name tooltip-handle">
                                    <?php echo $value->user_nicename; ?>
                                        <span class="user-details "> 
                                            <ul class="tooltiptext">
                                              
                                                <li>Affiliate ID: <?php echo $value->user_key; ?></li>
                                                <li>Sponsor: <?php echo $value->sponsor_key; ?></li>
                                                <li>Personal Volume: <?php echo $value->own_pv; ?></li>
                                            </ul>
                                        </span>
                                    </div>            
                                </div>
                            </div>
                        </div>
            <?php }}  ?>
                        <div class="hv-item-child">    
                            <div class="hv-item">  

                                <div class="person add-new add-new-left">
                                    <div class="profile-img-wrap" style="background-image: url('<?php echo $add_new_img; ?>')">              
                                        <img src= "<?php echo $add_new_img; ?>" alt="" scale="0">
                                    </div>  
                                    <div class="name">Empty Node</div>
                                </div>

                            </div>
                        </div>

                </div>
            </div>
        <?php } ?>
        <?php
        die();
    }

    /* ----- Get current logged in user details ----- */

    public function csa_get_user_data($affiliate_id) {

        global $wpdb, $table_prefix;

        $user_qry = "
            SELECT 
                mlm.user_key, 
                mlm.sponsor_key,
                mlm.own_pv,
                ct.short_abbreviation AS 'title', 
                spu.user_login AS 'sponsor_username', 
                usr.user_nicename, usr.ID,
                um.meta_value AS 'profile_image' 
            FROM wp_wpmlm_users mlm 
            JOIN wp_users usr ON mlm.user_id = usr.ID 
            JOIN commission_titles ct 
                ON mlm.rank = ct.level 
            LEFT JOIN wp_wpmlm_users spmlm 
                ON spmlm.user_key = mlm.sponsor_key
            LEFT JOIN wp_users spu 
                ON spu.ID = spmlm.user_id
            LEFT JOIN wp_usermeta um 
                ON usr.ID = um.user_id 
            AND um.meta_key = 'user_profile_img' 
            WHERE mlm.user_key = $affiliate_id";

        $user_data = $wpdb->get_row($user_qry);

        return $user_data;
    }

    /* ----- Screen 2 ----- */

    /* ----- Get Child user keys ----- */

    public function get_child_user_keys($affiliate_id) {

        global $wpdb, $table_prefix;
        $user_qry = "
            SELECT mlm.user_key
            FROM wp_wpmlm_users mlm
            WHERE mlm.parent_key = $affiliate_id 
            ";
        $user_key = $wpdb->get_results($user_qry);
        return $user_key;
    }

    public function place_affiliate() {

        global $wpdb;
        $session_value = array();
        $session_set = false;
        $affiliate_placed = 0;
        $to_be_placed_username = array();
        $placement_validation = '';
        $valid_placement = false;

        if(trim($_POST['unplaced_affiliate_id']) == ''){
            $_POST['unplaced_affiliate_id'] = 0;
        }
        $valid_placement = $this->is_same_chain($_POST['affiliate_id'], $_POST['parent_id']);
        // var_dump($valid_placement);
        // die();
        if ($valid_placement) {            

            if ($_POST['placement_type'] == 'for_placed_affiliates') {

                session_start();
                $_SESSION['to_be_place'] = $_POST['unplaced_affiliate_id'];
                $session_value = $_SESSION['to_be_place'];
                $session_set = true;
                //Unplace the previous affiliate
                $affiliate_unplaced = $wpdb->update('wp_wpmlm_users', array('parent_key' => '2'), array('user_key' => $_SESSION['to_be_place']));
            }

            //To destroy session
            if($_POST['placement_type'] == 'for_unplaced_affiliates'){
                if( isset($_SESSION['to_be_place']) ){
                    session_destroy();
                }
            }

            //New affiliate placement
            $affiliate_placed = $wpdb->update('wp_wpmlm_users', array('parent_key' => $_POST['parent_id']), array('user_key' => $_POST['affiliate_id']));
            // $affiliate_placed = true;

        } else {
            $placement_validation = "<span style='color: red;'>You cannot place node affiliate under same block chain.<span>";
        }

        if($affiliate_placed){

            $current_user_id = get_current_user_id();
            $arr_user_key = $wpdb->get_row("SELECT user_key FROM wp_wpmlm_users wwu where wwu.user_id='".$current_user_id."'");
            
            $current_user_key = $arr_user_key->user_key;

            $current_timestamp = date('Y-m-d h:i:s');

            $inserted = $wpdb->insert('user_placement_changes', array(
                    'to_be_placed' => $_POST['affiliate_id'], 
                    'parent' => $_POST['parent_id'], 
                    
                    'temporary_unplaced' => $_POST['unplaced_affiliate_id'], 
                    'current_user' => $current_user_key, 
                    'timestamp' => $current_timestamp 
                ) ); 
        }

        $arr_affiliate_placed = array(
            'affiliate_placed' => $affiliate_placed,
            'session_set' => $session_set,
            'session_value' => $session_value,
            'to_be_placed_username' => $_POST['unplaced_affiliate_name'],
            'valid_placement' => $valid_placement,
            'placement_validation' => $placement_validation
        );

        echo json_encode($arr_affiliate_placed);
        die();
    }

    public function is_same_chain($to_be_placed_affiliate, $parent_affiliate) {

        //Get parent for affiliate
        $parent_key = $this->get_parent_user_key($parent_affiliate);
     
        // echo $parent_key."===".$parent_affiliate."<br>";
        $invalid_parent_keys = array('0','1','2');

      
        if ($to_be_placed_affiliate == $parent_key) {
           
            $same_chain = false;
            return $same_chain;
            
        } else {

            if (in_array($parent_key, $invalid_parent_keys) ) {
                $same_chain = true;
                return $same_chain;
            }else if( $parent_key == ''){
                $same_chain = true;
                return $same_chain;
            }else{
                $same_chain = $this->is_same_chain($to_be_placed_affiliate,$parent_key);
                return $same_chain;
                die();

            }
        }

        return $same_chain;
        die();

    }

}
