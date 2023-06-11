<?php

if ( ! defined('ABSPATH') ) {
    die('Direct access not permitted.');
}

/**
 * Creates Link Monitor tables
 *
 * @return void
 */
function create_link_monitor_tables() {
    global $wpdb;

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $charset_collate = $wpdb->get_charset_collate();

    $table_name_link_monitor_cases = $wpdb->prefix . 'link_monitor_cases';
    $sql =  "CREATE TABLE $table_name_link_monitor_cases (
        id bigint(20) AUTO_INCREMENT,
        url varchar(500) NOT NULL UNIQUE,
        status_code int(3) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate";
    dbDelta( $sql );

    $table_name_link_monitor_findings = $wpdb->prefix . 'link_monitor_findings';
    $sql = "CREATE TABLE $table_name_link_monitor_findings (
        id bigint(20) AUTO_INCREMENT,
        case_id bigint(20) NOT NULL,
        post_id bigint(20) unsigned NOT NULL, 
        PRIMARY KEY  (id)
    ) $charset_collate";
    dbDelta( $sql );

    $sql = "ALTER TABLE $table_name_link_monitor_findings
        ADD FOREIGN KEY (case_id)
        REFERENCES $table_name_link_monitor_cases (id)
        ON DELETE CASCADE,
        ADD FOREIGN KEY (post_id)
        REFERENCES pp_posts (ID)
        ON DELETE CASCADE;";
    $wpdb->query($sql);
}

/**
 * Finds and returns url status code. See ERROR_CODES constant.
 *
 * @param string $url
 * @return int|string
 */
function get_url_status_code($url)
{
    if (substr($url, 0, 7) == "http://")
        $status_code = 1;
    elseif (substr($url, 0, 8) != "https://")
        $status_code = 2;
    elseif (!filter_var($url, FILTER_VALIDATE_URL))
        $status_code = 3;
    else
    {
        $respuesta = wp_remote_retrieve_response_code(wp_remote_get($url));
        if ( ($respuesta>300 || $respuesta < 200) && $respuesta !='')
        {
            $status_code = $respuesta;
        }
        //http://localhost or http://x are valid urls!;
        else $status_code = 200;
    }
    return $status_code;
}