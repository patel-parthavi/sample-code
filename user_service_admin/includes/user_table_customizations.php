<?php

/*==========================================================*/
/*** ------ ***** ----- USER PAGE TABLE ----- ***** ----- ***/
/*==========================================================*/

/* ----- Add custom column and remove posts column on users table on admin panel ----- */

function np_modify_user_table($column) {

    $col_new = array(
        'cb' => '<input type="checkbox" />',
        'username' => 'Username',
        'name' => 'Name',
        'email' => 'Email',
        'role' => 'Role',
        'affiliate_id' => 'Representative Id',
        'sponsor_name' => 'Sponsor',
        'parent_name' => 'Parent',
        //'position' => 'Position',
        'country' => 'Country',
        'enrollment_date' => 'Enrollment Date',
    );


    return $col_new;
}

add_filter('manage_users_columns', 'np_modify_user_table', 10);

function np_modify_user_table_row($val, $column_name, $user_id) {

    // ini_set("display_errors", "1");
    //     error_reporting(E_ALL & ~E_NOTICE);
    global $wpdb;

    if ($column_name == 'affiliate_id') {

        $affiliate = $wpdb->get_row("SELECT user_key as affiliate_id FROM wp_wpmlm_users WHERE user_id=" . $user_id);

        $affiliate_key = $affiliate->affiliate_id;

        return $affiliate_key;
    }

    if ($column_name == 'country') {
        
        $aff_country = $wpdb->get_row("SELECT meta_value as country FROM wp_usermeta WHERE meta_key='billing_country' AND user_id=" . $user_id);

        $affiliate_country = $aff_country->country;
        return WC()->countries->countries[$affiliate_country];
    }

    if ($column_name == 'enrollment_date') {
        
        $aff_enrollment_date = get_post_meta($user_id, '_paid_date', true);

        if(trim($aff_enrollment_date) != ''){
            return date('Y-n-d',strtotime($aff_enrollment_date));
        }else{
            return $aff_enrollment_date;
        }
        
    }


    return $val;
}
 
add_filter('manage_users_custom_column', 'np_modify_user_table_row', 25, 3);