<?php

class CPI_Page_Module_Admin
{
    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) 
    {
		  $this->plugin_name = $plugin_name;
  		$this->version = $version;

      $this->load_dependencies();
	  } 

    private function load_dependencies()
    {
      /**
       * The class used for display HTML views in the page module admin
       */
      require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/assets/partials/main-page-module-views.php';
      
      require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helpers/miscellaneous.php';      
      require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helpers/admin-form-builder.php';
      
      require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cpi-list-table.php'; // WP List Table extension
      require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cpi-simple-page-module.php';      
      require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cpi-list-page-module.php';
    }    
    
    public function enqueue_scripts( $hook ) 
    {              
      if ( strpos( $hook, '-page-module' ) > 0 )
      {
          wp_enqueue_script( 'tinyMCE', 'https://cdn.tiny.cloud/1/or3w20xwvy4j2inupovfvx6lt6a0bmssmg6pdg9vzxvjax0r/tinymce/5/tinymce.min.js', array(), true, true );
          wp_enqueue_script( 'cpi-cms-events', get_template_directory_uri() . '/includes/assets/js/cpi-form-events.js', array( 'jquery' ), true, true );
      }    
      
      wp_enqueue_script( 'cpi-list-table-builder', get_template_directory_uri() . '/includes/assets/js/cpi-list-table-builder.js', array( 'jquery' ), true, true );          
    }    

    public function _cpi_get_module_meta_data( $request )
    {
        $post = $request['post_id'];

        if( empty($post) )
        {
            return new WP_Error( 'invalid page module ID', 'need to supply a valid page module post ID', array( 'status' => 404 ) );
        }

        $post_meta = get_post_meta( $post );
        $meta = array_map( function( $p ) { return $p[0]; }, $post_meta );    

        $response = new WP_REST_Response( $meta );
        $response->set_status( 200 );

        return $response;
    }
    public function _cpi_page_module_meta_end_point() 
    {
        register_rest_route( 'wp/v2', '/'. CPI_ADMIN_ENV .'/page_module/(?P<post_id>\d+)/meta', array(
            'method' => 'GET',
            'callback' => array( $this, '_cpi_get_module_meta_data' ),
            'permission_callback' => '__return_true'
        ) );
    }

    public function _cpi_page_module_menu() 
    {    
        $plugin_view = new CPI_Page_Module_Views( $this->plugin_name );

        add_menu_page('All Page Modules', 'Page Modules', 'manage_options', 'page-modules', array( $plugin_view, '_cpi_page_modules_main_table' ), 'dashicons-align-wide', 22);  
        add_submenu_page('page-modules', 'Add module', 'Add module', 'manage_options', 'add-page-module', array( $plugin_view, '_cpi_add_page_module' ) );
        add_submenu_page('add-page-modules', 'Add module field', 'Add module field', 'manage_options', 'add-page-module-field', array( $plugin_view, '_cpi_add_page_module_field' ) );

        /**
         * use add submenu page as parent to hide the edit page
         */
        add_submenu_page('add-page-module', 'Edit module', 'Edit module', 'manage_options', 'edit-page-module', array( $plugin_view, '_cpi_edit_page_module' ) );
        add_submenu_page('add-page-module', 'Delete module', 'Delete module', 'manage_options', 'delete-page-module', array( $plugin_view, '_cpi_delete_page_module') );

        /**
         * sub-pages for editing / deleting page module fields
         */
        add_submenu_page('add-page-module', 'Edit module field', 'Edit module field', 'manage_options', 'edit-page-module-field', array( $plugin_view, '_cpi_edit_page_module_field' ) );
        add_submenu_page('add-page-module', 'Delete module field', 'Delete module field', 'manage_options', 'delete-page-module-field', array( $plugin_view, '_cpi_delete_page_module_field' ) );


        /**
         * create an admin list table page or simple-type form page 
         * for each new module created
         */
        $post_modules = get_posts( array( 'posts_per_page' => -1, 'post_type' => 'page_module' ) );        

        foreach ($post_modules as $module) {
            $module_type = get_post_meta( $module->ID, 'page_module_type', true );        

            if( intval( $module_type ) === 1 ){
                add_submenu_page('add-page-module', $module->post_title, $module->post_title, 'manage_options', $module->post_name . '-page-module', array( 'CPI_Simple_Page_Module', '_cpi_simple_home' ) );
            }
            elseif( intval( $module_type ) === 2 ) 
            {
                add_submenu_page('add-page-module', $module->post_title, $module->post_title, 'manage_options', $module->post_name . '-page-module', array( 'CPI_List_Page_Module', '_cpi_list_home' ) );
                add_submenu_page('add-page-module', $module->post_title, $module->post_title, 'manage_options', 'add-' . $module->post_name . '-list-table', array( 'CPI_List_Page_Module', '_cpi_list_create_table' ) );
            }
        }
    }
}
