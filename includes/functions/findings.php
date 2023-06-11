<?php

/**
 * Check string for <a href=""> matches and returns array of cases.
 * 
 * @param string $content
 * @return array()
 */
function get_one_post_cases($post)
{
    $content = $post->post_content;
    $post_cases = array();
    $success = preg_match_all('/<a\s+href=["\']([^"\']+)["\']/i', $content, $match);

    foreach($match[1] as $url)
    {
        $status_code = get_url_status_code($url);
        // Good links are not cases.
        if ($status_code == 200) continue;
        $case = array('post_id' => $post->ID, 'url'=> $url, 'status_code' => $status_code);
        $post_cases[] = $case;
    }
    return $post_cases;
}

/**
 * Iterates over cases and findings in order to save them.
 * Also updates post meta last_time_checked.
 * 
 * @param WP_post $post
 * @return void
 */
function save_one_post_cases_and_findings($post)
{
    global $wpdb;

    $findings_table = $wpdb->prefix . 'link_monitor_findings';
    $cases_table = $wpdb->prefix . 'link_monitor_cases';
    $post_id = $post->ID;
    $cases_ids = array();

    // 1.First flush all cases and findings from post.
    $wpdb->query("DELETE FROM $cases_table WHERE id IN (SELECT case_id FROM $findings_table WHERE post_id = $post_id)");
    $wpdb->query("DELETE FROM $findings_table WHERE post_id = $post_id");

    // 2.Get all the post cases from post.
    $cases = get_one_post_cases($post);
    
    // 3.Save or update the case if exists for another post.
    foreach ($cases as $case) {
        $status_code = $case['status_code'];
        $url = $case['url'];
        // The case exists for another post
        $maybe_id = $wpdb->get_var("SELECT id from $cases_table WHERE url = '$url'");
        if($maybe_id)
        {
            // 4a. Update same case belonging to another post and collect old id for saving finding.       
            $wpdb->update($cases_table, array( 'status_code' => $status_code), array('url' => $url));
            $cases_ids[] = $maybe_id;
        }
        else
        // 4.b Save new case and collect new id for saving findings.
        $wpdb->replace($cases_table, array( 'url' => $url, 'status_code' => $status_code));
        $cases_ids[] = $wpdb->insert_id;
    }
    
    // 4.Save findings.
    foreach ($cases_ids as $case_id) {
        $wpdb->query("INSERT INTO $findings_table(case_id, post_id) VALUES ($case_id, $post_id)");
    }
    
    // 5.Save timestamp for cron job.
    update_post_meta($post->ID, 'last_time_checked', date('Y-m-d H:i:s') );
}

/**
 * Iterates over array of posts for saving findings
 *
 * @param array $posts
 * @return void
 */
function save_posts_findings(array $posts)
{
   foreach($posts as $post) save_one_post_cases_and_findings($post);
}