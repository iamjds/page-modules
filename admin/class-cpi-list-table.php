<?php

if( ! class_exists( 'WP_List_Table' ) ) 
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CPI_Page_Modules_List_Table extends WP_List_Table 
{       
    private $column_headers = [];
    private $page_module_text_domain = '';
    private $module_data = [];
    
    function __construct( $args )
    {
        $args = wp_parse_args( $args );
    
        $this->column_headers = $args['column_headers'];
        $this->page_module_text_domain = $args['text_domain'];        
        $this->module_data = $args['module_data'];                   

        parent::__construct( array(
            'singular'  => __( 'module', $this->page_module_text_domain ),
            'plural'    => __( 'modules', $this->page_module_text_domain ),
            'ajax'      => false     
        ) );                        
    }

    function admin_header() 
    {
        $page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;                

        if( '_cpi_custom_page_modules' != $page ) return;

        echo '<style type="text/css">';
        echo '.wp-list-table .column-id { width: 5%; }';
        echo '.wp-list-table .column-module { width: 40%; }';
        echo '.wp-list-table .column-author { width: 35%; }';
        echo '.wp-list-table .column-last_modified { width: 20%;}';
        echo '</style>';
    }    

    /**
     * required for WP_List_Table to display table items
     */
    function column_default( $item, $column_name ) 
    {        
        switch( $column_name ) { 
            case 'module':
            case 'type':
            case 'author':
            case 'last_modified':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        }
    }   
    
    function column_module( $item )
    {
        $slug = sanitize_title( $item['module'] );                                  
        $title = '<strong><a href="'. esc_attr( admin_url( 'admin.php?page='. $slug . '-page-module&post=' . $item['ID'] ) ) .'" class="module_link">' . $item['module'] . '</a></strong>';
        $actions['edit_page_module'] = '<a href="'. esc_attr( admin_url( 'admin.php?page=edit-page-module&post='. $item['ID'] .'&action=edit' ) ) .'">' . __( 'Edit module', $this->page_module_text_domain ) . '</a>';
        $actions['delete_page_module'] = '<a href="'. esc_attr( admin_url( 'admin.php?page=delete-page-module&post='. $item['ID'] .'&action=delete' ) ) .'">' . __( 'Delete module', $this->page_module_text_domain ) . '</a>';

        return $title . $this->row_actions( $actions );
    }

    function get_columns()
    {        
        $columns = array(
            'cb' => '',
            'module' => __( 'Module', $this->page_module_text_domain ),
            'type' => __( 'Type', $this->page_module_text_domain ),
            'author' => __( 'Author', $this->page_module_text_domain ),
            'last_modified' => __( 'Date', $this->page_module_text_domain )
        );
        
        return $columns;
    }

    function get_sortable_columns() 
    {
        $sortable_columns = array(
            'module'  => array('module',false),
            'author' => array('author',false),
            'last_modified'   => array('last_modified',false)
        );

        return $sortable_columns;
    }    

    function get_modules()
    {                               
        return $this->module_data;
    }

    private function get_page_module_type( $module_type_id )
    {
        switch ($module_type_id) {
            case 1:
                return 'single';
            
            case 2:
                return 'multiple';
        }
    }

    public static function delete_page_module( $id ) 
    {
        global $wpdb;
      
        $wpdb->delete(
          "{$wpdb->prefix}modules",
          [ 'ID' => $id ],
          [ '%d' ]
        );
    }

    function usort_reorder( $a, $b ) 
    {
        // If no sort, default to title
        $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'module';
        // If no order, default to asc
        $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
        // Determine sort order
        $result = strcmp( $a[$orderby], $b[$orderby] );
        // Send final sort direction to usort
        return ( $order === 'asc' ) ? $result : -$result;
    }

    public function process_bulk_action() 
    {
        //Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) 
        {      
            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );
        
            if ( ! wp_verify_nonce( $nonce, 'sp_delete_page_module' ) ) 
            {
                die( 'Go get a life script kiddies' );
            }
            else 
            {
                self::delete_page_module( absint( $_GET['customer'] ) );
        
                wp_redirect( esc_url( add_query_arg() ) );
                exit;
            }
      
        }
      
        // If the delete bulk action is triggered
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' ) || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' ) ) 
        {      
            $delete_ids = esc_sql( $_POST['bulk-delete'] );
      
            // loop over the array of record IDs and delete them
            foreach ( $delete_ids as $id ) 
            {
                self::delete_page_module( $id );        
            }
      
            wp_redirect( esc_url( add_query_arg() ) );
            exit;
        }
    }

    
    /**
     * Developers should use this class to query and filter data, 
     * handle sorting, and pagination, and any other data-manipulation 
     * required prior to rendering. This method should be called 
     * explicitly after instantiating your class, and before rendering.
     */
    function prepare_items() 
    {
        $columns = $this->get_columns();

        /* Process bulk action */
        $this->process_bulk_action();        

        $hidden   = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $this->column_headers, $hidden, $sortable, 'module' );
        $module_data = $this->get_modules();        

        usort( $module_data, array( &$this, 'usort_reorder' ) );
        
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $total_items = count( $module_data );                   
      
        $this->set_pagination_args( array(
          'total_items' => $total_items,
          'per_page'    => $per_page
        ) );

        // @var $this->items
        // WP_List_Table inherit property
        $this->items = $module_data;        
    }

}
