<?php

class CPI_Page_Module
{
    protected $plugin_name;
    protected $version;

    public function __construct() 
    {
		if ( defined( 'CPI_PAGE_MODULE_VERSION' ) ) {
			$this->version = CPI_PAGE_MODULE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'cpi-page-module';

		$this->load_dependencies();
		$this->define_admin_hooks();
	}

    private function load_dependencies()
    {
        /**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cpi-page-module-loader.php';		        
        
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cpi-page-module-admin.php';		

		$this->loader = new CPI_Page_Module_Loader();
    }    

    private function define_admin_hooks()
    {
        $this->loader->add_action( 'init', $this, '_cpi_create_page_module_post_type' );

        $plugin_admin = new CPI_Page_Module_Admin( $this->get_plugin_name(), $this->get_version() );        

		// $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );        
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'rest_api_init', $plugin_admin, '_cpi_page_module_meta_end_point', 10, 2 );
        $this->loader->add_action( 'admin_menu', $plugin_admin, '_cpi_page_module_menu' );

        // saves
        $this->loader->add_action( 'admin_post_nopriv_cpi_add_page_module_submit', $plugin_admin, 'save_page_add_simple_module_post' );
        $this->loader->add_action( 'admin_post_cpi_add_page_module_submit', $plugin_admin, 'save_page_add_simple_module_post' );
    }    
    
	public function run() 
    {
		$this->loader->run();
	}

    public function get_plugin_name() 
    {
		return $this->plugin_name;
	}

    public function get_loader() 
    {
		return $this->loader;
	}

    public function get_version() 
    {
		return $this->version;
	}

    /**
     * register "Page Module" 
     * custom post type for
     * API capabilities
     */
    public function _cpi_create_page_module_post_type() 
    { 
        register_post_type( 'page_module',    
            array(
                'labels' => array(
                    'name' => __( 'Page Modules' ),
                    'singular_name' => __( 'Page Module' )
                ),
                'public' => true,
                'has_archive' => false,            
                'rest_base' => CPI_ADMIN_ENV . '/page_module',
                'rewrite' => array('slug' => 'page-modules'),
                'show_in_menu' => false,
                'show_in_rest' => true
            )
        );
    }
}
