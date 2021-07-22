<?php

class CPI_Simple_Page_Module
{
    static function _cpi_simple_home()
    {        
        $post_id = $_GET['post'];
        $post = get_post( $post_id );             

        $_Helpers = new CPI_Page_Module_Helpers();
        $_Form_Builder = new CPI_Page_Module_Admin_Form_Builder;


        if ( $post != null )
        {
            
            $simple_type_page_module_fields = get_option( '_cpi_' . $post->post_name . '_simple_type_fields' );            

            // get each field's stored value in post_meta table            
            $field_sections = $_Helpers->_cpi_get_page_module_field_values( $post_id, $simple_type_page_module_fields );

            echo '<div class="wrap"><h2>Page Modules - ' . $post->post_title . '</h2>';

            if ( isset( $_GET ) && isset( $_GET['edited'] ) ) 
            {
                echo '<div id="message" class="updated notice notice-success is-dismissible"><p>Page Module updated. <a href="'. esc_attr( admin_url('admin.php?page=page-modules') ) . '">View Page Modules</a></p></div>';
            }

            echo '<form method="post" action="' . esc_attr( admin_url("admin-post.php") ) . '">';
            echo '<input type="hidden" name="action" value="cpi_'. $post->post_name .'_module_fields">';
            echo '<input type="hidden" name="post" value="' . $post_id . '" />';

            submit_button();

            if ( $field_sections != null )
            {                
                foreach ($field_sections as $section) {            
                    echo '<h3>' . $section['title'] . '</h3>';        
                    echo $_Form_Builder->create_table( $section );
                }
            }
            else 
            {
                echo '<p><strong>no fields available for this page module</strong></p>';
            }

            submit_button();

            echo '</form></div>';   
        } 
        else {
            echo '<div class="wrap"><p>no post found</></div>';
        }
    }

    public function save_page_add_simple_module_post()
    {      
        $_Helpers = new CPI_Page_Module_Helper_Functions;
        $customFields = array('page_module_name','page_module_type','page_module_user');        
        $module_post = array();            
            
        
        // Create post object data
        $module_post['post_title'] = wp_strip_all_tags( $_POST['page_module_name'] );
        $module_post['post_content'] = serialize($_POST);
        $module_post['post_status'] = 'publish';
        $module_post['post_author'] = intval( $_POST['page_module_user'] );
        $module_post['post_type'] = 'page_module';

        // Insert the post into the database
        $new_post_id = wp_insert_post( $module_post );

        foreach ($customFields as $field) 
        {
            if (array_key_exists($field, $_POST)) 
            {
                update_post_meta($new_post_id, $field, $_POST[$field]);

                if($field == 'page_module_type' && $_POST[$field] == 2)
                {
                    update_post_meta($new_post_id, 'list_items_count', 0);
                }
            }
        }
        
        wp_redirect( admin_url( 'admin.php?page=page-modules' ) );
    }
}
