<?php


return array(
    // Application defaults
    'default' => array(
        // source: "query_string" or "route"
        'current_page' => array('source' => 'query_string', 'key' => 'page'),
        'total_items' => 0,
        'items_per_page' => 10,
        'view' => 'pagination/basic',
        'auto_hide' => true,
        'first_page_in_url' => false,
        //if use limited template
        'max_left_pages' => 10,
        'max_right_pages' => 10,
    ),
);
