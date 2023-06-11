<?php

add_action( 'monitor_links', 'check_outdated_posts' );
function check_outdated_posts(){
    $args = array(
        'numberpost' => -1,
        'meta_key'  => 'last_time_checked',
        'meta_value' => date("Y-m-d H:i:s", strtotime("-4 day")),
        'meta_compare' => '<'
    );

    save_posts_findings(get_posts($args));
}

add_action('post_updated', 'monitor_links', 10, 3);
function monitor_links($post_id, $post, $update )
{
    if(wp_is_post_revision($post_id)) return;
    save_one_post_cases_and_findings($post);
}