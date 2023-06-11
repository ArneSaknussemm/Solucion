<?php

if (!class_exists('WP_List_Table')) {
      require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class link_monitor_table extends WP_List_Table
{
    private string $post_separator = ', ';

    function get_columns()
    {
        $columns = array(
                'cb'            => '<input type="checkbox" />',
                'url'           => 'URL',
                'status_code'   => 'Estado',
                'posts_id'       => 'Origen',
        );
        return $columns;
    }

    function prepare_items()
    {
        $this->_column_headers = array($this->get_columns(),array() ,array() );
        
        $this->items = $this->get_table_data();
    }

    private function get_table_data() {
        global $wpdb;

        $findings_table = $wpdb->prefix . 'link_monitor_findings';
        $cases_table = $wpdb->prefix . 'link_monitor_cases';

        error_log('Consultando base de datos...');
        $cases_id = $wpdb->get_results("SELECT cases.id, cases.url, cases.status_code FROM $cases_table cases");
        foreach ($cases_id as $key => $value) {
            $posts_id = $wpdb->get_results("SELECT findings.post_id FROM $findings_table findings WHERE findings.case_id = $value->id");
            $findings[] = array('url' => $value->url, 'status_code' => $value->status_code, 'posts_id' => $posts_id);
        }

        // Pure SQL alternative
        // $results = $wpdb->get_results("SELECT cases.url, cases.status_code, (SELECT GROUP_CONCAT(DISTINCT findings.post_id SEPARATOR '$this->post_separator') FROM $findings_table findings WHERE findings.case_id = cases.id ) post_id FROM $cases_table cases GROUP by 1,2;", ARRAY_A);
        return $findings;
    }
    
    function column_default($item, $column_name)
    {
         switch ($column_name) {
                case 'status_code':
                    $arrange_status = array_combine(array_values(array_column(ERROR_CODES, 'status_code')), array_values(array_column(ERROR_CODES, 'name')))[$item[$column_name]];
                    return '<strong style="color:darkorange;">' . $arrange_status . '</strong>';
                case 'posts_id':
                    return implode( $this->post_separator, array_map(function ($post_id_object) {
                        return '<a href="' . get_the_permalink($post_id_object->post_id) . '">' . get_the_title($post_id_object->post_id) . '</a>';
                        }, $item[$column_name]));
                case 'url':
                    return $item[$column_name];
            default:
                return 'nones';
        }
    }

    function column_cb($item)
    {
        return sprintf(
                '<input type="checkbox" name="element[]" value="%s" />',
                'hola' //$item['id']
        );
    } 
}

function my_add_menu_items()
{
    add_menu_page('Link monitor', 'Link monitor', 'activate_plugins', 'link-monitor', 'link_monitor_table_callback', 'dashicons-editor-unlink', 8);
}
add_action('admin_menu', 'my_add_menu_items');

function link_monitor_table_callback()
{
    error_log('Inicializando tabla...');
    $table = new link_monitor_table();

    echo '<div class="wrap"><h2>Link Monitor</h2>';
    $table->prepare_items();
    $table->display();
    echo '</div>';
}