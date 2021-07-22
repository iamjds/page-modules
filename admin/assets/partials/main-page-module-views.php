<?php

class CPI_Page_Module_Views {
    
    private $text_domain;

    public function __construct( $text_domain )
    {        
        $this->text_domain = $text_domain;
    }

    public function _cpi_page_modules_main_table()
    {                  
        $column_headers = array(
            'cb' => '',
            'module' => __( 'Name', $this->text_domain ),
            'type' => __( 'Type', $this->text_domain ),
            'author' => __( 'Author', $this->text_domain ),
            'last_modified' => __( 'Date', $this->text_domain )
        );

        $post_data = get_posts( array('posts_per_page' => -1, 'post_type' => 'page_module', 'post_parent' => 0 ) );    

        $columns = array(
            'cb'            => '',
            'module'        => __( 'Name', $this->text_domain ),
            'type'          => __( 'Type', $this->text_domain ),
            'author'        => __( 'Author', $this->text_domain ),
            'last_modified' => __( 'Date', $this->text_domain )
        );

        $list_data = array_map( function($post)
        {
            $module_type_id = get_post_meta( $post->ID, 'page_module_type', true );
            $module_type = 'simple';        
            
            switch ($module_type_id) {
                case '1':
                    $module_type = 'simple';
                    break;
                case '2':
                    $module_type = 'list';
                    break;
            }

            return array(
                'ID'            => $post->ID,
                'module'        => $post->post_title,
                'type'          => $module_type,
                'author'        => get_the_author_meta('display_name', get_post_meta( $post->ID, 'page_module_user', true ) ),
                'last_modified' => $post->post_modified
            );
        }, $post_data);      

        $args = array(
            'column_headers'    => $columns,    
            'text_domain'       => $this->text_domain,
            'module_data'       => $list_data
        );
        $page_module_list_table = new CPI_Page_Modules_List_Table( $args );    

        echo '<div class="wrap"><h2 style="display:inline-block;margin-right:5px">Page Modules</h2>'; 
        echo '<a href="/admin/wp-admin/admin.php?page=add-page-module" class="page-title-action">Add New Module</a>';

        $page_module_list_table->prepare_items(); 
        $page_module_list_table->display(); 

        echo '</div>';     
    }

    public function _cpi_add_page_module()
    {    
        $admin_users = get_users();    
        $non_default_columns = [];
        $table_data = [];    

        $user_select_options = array_map( function($user)
        {   
            return [ 'label' => $user->display_name, 'value' => $user->ID ];
        }, $admin_users);           

            
        $table_data = array(
            'fields' => array(
                array(
                    'type'        => 'text',
                    'label'       => 'Module name',
                    'name'        => 'page_module_name',
                    'value'       => ''
                ),
                array(
                    'type'        => 'select',
                    'label'       => 'Module type',
                    'name'        => 'page_module_type',
                    'options'     => [['label' => 'simple', 'value' => '1'], ['label' => 'list', 'value' => '2']],
                    'selected'    => 0 
                ),
                array(
                    'type'        => 'select',
                    'label'       => 'Module author',
                    'name'        => 'page_module_user',
                    'options'     => $user_select_options,
                    'selected'    => 0
                )
            )
        );

        echo '<div class="wrap"><h2>Add Page Module</h2>';
        echo '<form method="post" action="'. esc_attr( admin_url("admin-post.php") ) .'">';
        echo '<input type="hidden" name="action" value="cpi_add_page_module_submit">';        

        $form_builder = new CPI_Page_Module_Admin_Form_Builder();
        echo $form_builder->create_table( $table_data );

        submit_button();

        echo '</form></div>';       
    }

    public function _cpi_edit_page_module()
    { 
        $admin_users = get_users();   
        $post_id = $_GET['post'];
        $post_details = get_post( $post_id );

        $user_select_options = array_map( function($user)
        {   
            return [ 'label' => $user->display_name, 'value' => $user->ID ];
        }, $admin_users);


        /** 
         * TODO: store fields in WP db options
         */
        $table_data = array(
            'fields' => array(
                array(
                    'type'        => 'text',
                    'label'       => 'Module name',
                    'name'        => 'page_module_name',
                    'value'       => get_post_meta( $post_id, 'page_module_name', true )
                ),
                array(
                    'type'        => 'select',
                    'label'       => 'Module type',
                    'name'        => 'page_module_type',
                    'options'     => [['label' => 'simple', 'value' => '1'], ['label' => 'list', 'value' => '2']],
                    'selected'    => get_post_meta( $post_id, 'page_module_type', true ) 
                ),
                array(
                    'type'        => 'select',
                    'label'       => 'Module author',
                    'name'        => 'page_module_user',
                    'options'     => $user_select_options,
                    'selected'    => get_post_meta( $post_id, 'page_module_user', true ) 
                )
            )
        );    

        echo '<div class="wrap"><h2 style="display:inline-block;margin-right:5px">Edit Page Module</h2>';
        echo '<form method="post" action="'. esc_attr( admin_url("admin-post.php") ) .'">';
        echo '<input type="hidden" name="action" value="cpi_edit_page_module_submit">';
        echo '<input type="hidden" name="post" value="' . $post_id . '">';

        $form_builder = new CPI_Page_Module_Admin_Form_Builder();
        echo $form_builder->create_table( $table_data );

        $simple_type_page_module_fields = get_option( '_cpi_' . $post_details->post_name . '_simple_type_fields' );              

        // get each field's stored value in post_meta table
        $_Helpers = new CPI_Page_Module_Helpers;
        $field_sections = $_Helpers->_cpi_get_page_module_field_values( $post_id, $simple_type_page_module_fields );

        submit_button();

        echo '</form>'; // close page form

        ?>

        <style>
            .wp-list-table th {
                font-weight: bold;
            }
            .wp-list-table th:last-of-type {
                width: 100px;            
            }
            .wp-list-table th.name-header,
            .wp-list-table th.desc-header {
                width: 400px;
            }     
            .wp-list-table th.type-header {
                width: 150px;
            }   
            .wp-list-table tr:nth-of-type(2n) {
                background-color: #eff5ef;
            }
            .wp-list-table tr td:last-of-type {
                text-align: center;
            }
            .field-header {
                display: inline-block;
                margin-bottom: 25px;
            }
        </style>

        <?php

        echo '<h2 class="field-header" style="margin-top:60px">Available Fields</h2>';    
        echo '<a href="/admin/wp-admin/admin.php?page=add-page-module-field&post=' . $post_id . '" class="page-title-action">Add New Field</a>';
        echo '<table class="wp-list-table widefat">';
        echo '<thead><tr><th class="name-header">Name</th><th class="type-header">Type</th><th class="desc-header">Description</th><th>Options</th><th></th></tr></thead><tbody>';

        if ( $field_sections != null )
        {
            foreach ($field_sections as $section) {            
                foreach ($section['fields'] as $field) {
                    echo '<tr style="height:50px">';
                    echo '<td>' . $field['name'] . '</td>';
                    echo '<td>' . $field['type'] . '</td>';

                    echo ( isset( $field['desc'] ) ? '<td>' . $field['desc'] . '</td>' : '<td></td>' );
                    
                    echo ( isset( $field['options'] ) ? '<td>' . json_encode($field['options']) . '</td>' : '<td></td>' );
                    

                    $edit_action_url = esc_attr( admin_url( "admin.php?page=edit-page-module-field&post=" . $post_id . "&action=edit&field=" . $field['name'] ) );
                    $delete_action_url = esc_attr( admin_url( "admin.php?page=delete-page-module-field&post=" . $post_id . "&action=delete&field=" . $field['name'] ) );

                    echo '<td><a href="'.$edit_action_url.'">Edit</a> | <a href="'.$delete_action_url.'">Delete</a></td>';

                    echo '</tr>';   
                }        
            }
        }
        else 
        {
            echo '<tr><td>no fields available for this page module</td></tr>';
        }
        
        echo '</tbody></table>';
        
        echo '<h2 style="margin-top:60px">API end point</h2>';
        echo '<input style="width:500px" type="text" value="' . get_rest_url() . 'wp/v2/' . CPI_ADMIN_ENV . '/page_module/' . $post_id . '/meta">';
        
        echo '</div>'; // close page wrap
    }

    public function _cpi_add_page_module_field()
    {
        $post_id = $_GET['post'];
        $post_details = get_post( $post_id );    


        /** 
         * TODO: store fields in WP db options
         * 
         * Also, add fields to allow user to segment fields
         */
        $table_data = array(
            'fields' => array(
                array(
                    'type'        => 'text',
                    'label'       => 'Field name',
                    'name'        => 'page_module_field_label',
                    'value'       => ''
                ),
                array(
                    'type'        => 'select',
                    'label'       => 'Field type',
                    'name'        => 'page_module_field_type',
                    'options'     => [['label' => 'text', 'value' => 'text'], ['label' => 'text area', 'value' => 'text-area'], ['label' => 'checkbox', 'value' => 'checkbox'], ['label' => 'radio', 'value' => 'radio'], ['label' => 'date', 'value' => 'date'], ['label' => 'select', 'value' => 'select']],
                    'selected'    => 'text'
                ),
                array(
                    'type'        => 'text-area',
                    'label'       => 'Field description',
                    'name'        => 'page_module_field_desc',                
                    'value'       => ''
                )
            )
        );

        echo '<div class="wrap"><h2>Add Page Module Field</h2>';
        echo '<a href="' . esc_attr( admin_url( "admin.php?page=edit-page-module&post=" . $post_id . "&action=edit" ) ) . '">back to ' . $post_details->post_title . '</a>';
        echo '<form method="post" action="'. esc_attr( admin_url("admin-post.php") ) .'">';
        echo '<input type="hidden" name="action" value="cpi_add_page_module_field_submit">';
        echo '<input type="hidden" name="post_id" value="' . $post_id . '" />';

        $form_builder = new CPI_Page_Module_Admin_Form_Builder();
        echo $form_builder->create_table( $table_data );

        submit_button();

        echo '</form></div>';
    }

    public function _cpi_delete_page_module()
    {
        $post_id = $_GET['post'];
        $module_name = esc_attr( get_post_meta( $post_id, 'page_module_name', true ) );

        ?>

        <div class="wrap"><h2 style="display:inline-block;margin-right:5px">Delete Page Module</h2>
            <p>Are you sure you want to delete module: <strong>"<?php echo $module_name; ?>"</strong>
            <p style="text-decoration:underline">there is no undoing this action</p>

            <form method="post" action="<?php echo esc_attr( admin_url('admin-post.php') ); ?>">
                <input type="hidden" name="action" value="cpi_delete_page_module_submit">
                <input type="hidden" name="post" value="<?php echo $post_id; ?>">
                <br/>
                <a href="<?php echo admin_url('admin.php?page=page-modules'); ?>" class="button">Cancel</a>
                <button class="button" style="background-color:#dc4949;border-color:#dc4949;color:white;" type="submit">Delete</button>
            </form>
        </div>

        <?php
    }

    public function _cpi_edit_page_module_field() 
    {
        $post_id = $_GET['post'];
        $post_details = get_post( $post_id );

        $_field_name = $_GET['field'];
        $_option_key = '_cpi_' . $post_details->post_name . '_simple_type_fields';
        $page_module_options = get_option( $_option_key );  
        $fields_array = $page_module_options[0]['fields'];

        $field_label_value = '';
        $field_type_value = '';
        $field_desc_value = '';

        foreach ($fields_array as $field) 
        {
            if( $field['name'] == $_field_name ) 
            {
                $field_label_value = $field['label'];
                $field_type_value = $field['type'];
                $field_desc_value = (array_key_exists('desc', $field) ? $field['desc'] : '');
            }
        }    
        
        $table_data = array(
            'fields' => array(
                array(
                    'type'        => 'text',
                    'label'       => 'Field label',
                    'name'        => 'page_module_field_label',
                    'value'       => $field_label_value
                ),            
                array(
                    'type'        => 'select',
                    'label'       => 'Field type',
                    'name'        => 'page_module_field_type',
                    'options'     => [['label' => 'text', 'value' => 'text'], ['label' => 'text area', 'value' => 'text-area'], ['label' => 'checkbox', 'value' => 'checkbox'], ['label' => 'radio', 'value' => 'radio'], ['label' => 'date', 'value' => 'date'], ['label' => 'select', 'value' => 'select']],
                    'selected'    => $field_type_value
                ),
                array(
                    'type'        => 'text-area',
                    'label'       => 'Field description',
                    'name'        => 'page_module_field_desc',                
                    'value'       => $field_desc_value
                )                     
            )
        );

        echo '<div class="wrap"><h2>Edit Page Module Field</h2>';
        echo '<a href="' . esc_attr( admin_url( "admin.php?page=edit-page-module&post=" . $post_id . "&action=edit" ) ) . '">back to ' . $post_details->post_title . ' editor</a>';
        echo '<form method="post" action="'. esc_attr( admin_url("admin-post.php") ) .'">';
        echo '<input type="hidden" name="action" value="cpi_edit_page_module_field_submit">';
        echo '<input type="hidden" name="post_id" value="' . $post_id . '" />';

        $form_builder = new CPI_Page_Module_Admin_Form_Builder();
        echo $form_builder->create_table( $table_data );

        /**
         *  TODO: radio/select field options  
         */ 

        submit_button();

        echo '</form></div>';
    }

    public function _cpi_delete_page_module_field()
    {
        $post_id = $_GET['post'];
        $field_name = $_GET['field'];

        $post_details = get_post( $post_id );
        $_option_key = '_cpi_' . $post_details->post_name . '_simple_type_fields';
        $page_module_options = get_option( $_option_key ); 
        
        $field_label = '';

        foreach ($page_module_options[0]['fields'] as $field) {
            if( $field['name'] == $field_name )
            {
                $field_label = $field['label'];
            }
        }
        

        ?>

        <div class="wrap"><h2 style="display:inline-block;margin-right:5px">Delete Page Module Field</h2>
            <p>Are you sure you want to delete field: <strong>"<?php echo $field_label; ?>"</strong>
            <p style="text-decoration:underline">there is no undoing this action</p>

            <form method="post" action="<?php echo esc_attr( admin_url('admin-post.php') ); ?>">
                <input type="hidden" name="action" value="cpi_delete_page_module_field_submit">
                <input type="hidden" name="post" value="<?php echo $post_id; ?>">
                <input type="hidden" name="field" value="<?php echo $field_name; ?>">
                <br/>
                <a href="<?php echo admin_url('admin.php?page=edit-page-module&post='.$post_id.'&action=edit'); ?>" class="button">Cancel</a>
                <button class="button" style="background-color:#dc4949;border-color:#dc4949;color:white;" type="submit">Delete</button>
            </form>
        </div>

        <?php
    }
}
