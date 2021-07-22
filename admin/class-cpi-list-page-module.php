<?php

class CPI_List_Page_Module 
{        

    static function _cpi_list_home()
    {
        global $wpdb;

        $post_id = $_GET['post'];
        $post = get_post( $post_id );             

        $_Helpers = new CPI_Page_Module_Helpers();
        $_Form_Builder = new CPI_Page_Module_Admin_Form_Builder;

        if ( $post != null )
        {            
            $_columns_option_key = '_cpi_' . $_Helpers->sanitize_with_underscores( $post->post_name ) . '_list_module_columns';
            $list_type_page_table_column_model = get_option( $_columns_option_key );
            $_key = $_Helpers->sanitize_with_underscores( $post->post_name ) . '_row_item';
            $list_items = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '{$_key}_%'" );
                                              
            $list_items_count = get_post_meta( $post_id, 'list_items_count', true );

            $table_source = '<table data-ajax-url=' . admin_url('admin-ajax.php') . ' data-item-count=' .  $list_items_count . ' id="list-module-table" class="wp-list-table widefat"><thead><tr>';

            if( $list_type_page_table_column_model != null )
            {            
                $counter = 1;
                $column_count = count( $list_type_page_table_column_model['columns'] );
                $column_width = 90 / $column_count;

                foreach ($list_type_page_table_column_model['columns'] as $key => $column) 
                {
                    if( $counter == $column_count )
                    {
                        $table_source .= '<th data-type-' . $column['type'] . ' colspan="2" style="font-weight:bold; width: '. $column_width .'%">' . $column['label'] . '</th>';
                    }
                    else
                    {
                        $table_source .= '<th data-type-' . $column['type'] . ' style="font-weight:bold; width: '. $column_width .'%;">' . $column['label'] . '</th>';
                    }                    

                    $counter++;
                }

                $table_source .= '</tr></thead><tbody>';

                foreach ($list_items as $row) {                                    
                    // $row_key = $_Helpers->sanitize_with_underscores( $post->post_name ) . '_row_item_' . $i;
                    $row_data = get_post_meta( $post_id, $row->meta_key, true );
                    // $row_data_decoded = json_decode(json_encode($row_data);

                    $table_row = '<tr>';                    

                    foreach ($list_type_page_table_column_model['columns'] as $key => $column)
                    {
                        $index = $_Helpers->sanitize_with_underscores( $column['label'] );
                        $table_row .= '<td>' . $row_data[$index] . '</td>';
                        // $table_row .= '<td>testing</td>';
                    }

                    $table_row .= '<td style="width:7%;text-align:right"><a href="' . esc_attr( admin_url("admin.php?row=" . $row->meta_key) ) . '">Edit</a> | <a href="' . esc_attr( admin_url("admin.php") ) . '">Delete</a></td>';

                    $table_row .= '</tr>';
                    $table_source .= $table_row;
                }

                $table_source .= '</tbody></table>';

                // table styles
                echo '<style>.wp-list-table tr{height:50px}.wp-list-table tr:nth-of-type(2n){background-color: #eff5ef}</style>';

                echo '<div class="wrap"><h2 style="margin-bottom:25px">Page Modules - ' . $post->post_title . '</h2>';   

                echo '<ul class="subsubsub"><li class="add"><a data-add-row href="/" onclick="return false">Add Row +</a></ul>';

                echo $table_source;
                echo '</div>';                
            }
            else
            {
                no_list_table_template( $post_id );
            }
        }
        else 
        {
            no_list_table_template( $post_id );
        }        
    }

    static function _cpi_list_create_table()
    {
        $post_id = $_GET['post'];
        $post = get_post( $post_id );

        $form_field_data = array(
            'fields' => array(
                array(
                    'type'        => 'text',
                    'label'       => 'Column name',
                    'name'        => 'list_module_column_label',
                    'value'       => ''
                ),
                array(
                    'type'        => 'select',
                    'label'       => 'Column type',
                    'name'        => 'list_module_column_type',
                    'options'     => [['label' => 'text', 'value' => 'text'], ['label' => 'text area', 'value' => 'text-area'], ['label' => 'checkbox', 'value' => 'checkbox'], ['label' => 'radio', 'value' => 'radio'], ['label' => 'date', 'value' => 'date'], ['label' => 'select', 'value' => 'select']],
                    'selected'    => 'text'
                ),
                array(
                    'type'        => 'text-area',
                    'label'       => 'Column options',
                    'name'        => 'list_module_column_options',
                    'value'       => ''                    
                )
            )
        );

        echo '<div class="wrap"><h2>' . $post->post_title . ' Table Setup</h2>';        

        echo '<form method="post" action="'. esc_attr( admin_url("admin-post.php") ) .'">';
        echo '<input type="hidden" name="action" value="cpi_add_list_module_column_submit">';       
        echo '<input type="hidden" name="post" value="' . $post_id . '" />'; 

        $form_builder = new CPI_Admin_Form_Builder();
        echo $form_builder->create_table( $form_field_data );
        echo '<p class="list_module_column_options_desc" style="margin-top: 0; margin-left:220px;">comma-separated list of options</p>';

        // Save button container
        echo '<div style="margin-top:30px" class="submit-container">';
        submit_button( 'Save Changes', 'primary', 'submit', false );
        echo '<span style="margin:0 10px"></span>';
        submit_button( 'Save & Add Another', 'primary', 'submit_and', false, array( 'continue' => true ) );        

        echo '</div></form></div>';        
    }

    static function save_list_module_columns()
    {
        $_Helpers = new CPI_CMS_Helper_Functions();

        $post_id = $_POST['post'];
        $post = get_post( $post_id );
        $_option_key = '_cpi_' . $_Helpers->sanitize_with_underscores( $post->post_name ) . '_list_module_columns';
        $column_select_options = ( $_POST['list_module_column_options'] == null ? explode( ",", $_POST['list_module_column_options'] ) : array() );

        $list_module_columns = get_option( $_option_key );
        $new_column = array( 'label' => $_POST['list_module_column_label'], 'type' => $_POST['list_module_column_type'], 'options' => $column_select_options );

        if( $list_module_columns == null )
        {                        
            add_option( $_option_key, array( 'columns' => array( $new_column ) ) );
        }
        else 
        {
            $columns_array = $list_module_columns['columns'];
            $new_columns_array = [];

            foreach ($columns_array as $column) {
                array_push( $new_columns_array, $column );
            } 

            array_push( $new_columns_array, $new_column );

            $list_module_columns['columns'] = $new_columns_array;

            update_option( $_option_key, $list_module_columns );
        }
        

        if( array_key_exists( 'submit', $_POST ) )
        {
            wp_redirect( admin_url( 'admin.php?page=' . $post->post_name . '-page-module&post='.$post_id ) );
        }

        if( array_key_exists( 'submit_and', $_POST ) )
        {
            wp_redirect( admin_url( 'admin.php?page=add-' . $post->post_name . '-list-table&post='.$post_id ) );
        }
    }

    static function cpi_save_list_row_data()
    {      
        $_Helpers = new CPI_CMS_Helper_Functions();  
        $post_id = $_POST['post'];
        $post = get_post( $post_id );
        $data = json_decode( json_encode( $_POST ) ); 
        $row_data = json_decode( json_encode( $data->rowData ) );
        $row_array = [];        

        foreach ( $row_data as $key => $value ) 
        {
            $row_array[$key] = $value;
        }

        // update post row count in table
        $post_item_count = get_post_meta( $post_id, 'list_items_count', true );
        $post_item_count = $post_item_count + 1;                

        update_post_meta( $post_id, $_Helpers->sanitize_with_underscores( $post->post_name ) . '_row_item_' .  $post_item_count, $row_array );
        update_post_meta( $post_id, 'list_items_count', $post_item_count++ );
                
        
        wp_redirect( admin_url( 'admin.php?page=' . $post->post_name . '-page-module&post=' . $post_id ) );

        exit;
    }


    // static function save_page_add_list_module_post()
    // {
    //     // taken from functions.php when starting to work on multiple types
    //     // may not need it, but wanted to keep it just in case...


    //     if( $is_multiple )
    //     {
    //         $parent_post_id = $_POST['parent_post'];
    //         $post_parent_details = get_post( $parent_post_id );
    //         $new_multiple_post_structure = get_option( '_cpi_' . $post_parent_details->post_name . '_multiple_type_columns' );
    //         $title_display_name = '';
    //         $title_slug = '';

    //         foreach ($new_multiple_post_structure as $key => $value) 
    //         {            
    //             if (array_key_exists($key, $_POST)) 
    //             {
    //                 /**
    //                  * if field being save has 'name'
    //                  * somewhere in the string, 
    //                  * use that value for the $title_slug
    //                  * 
    //                  * this is not ideal, but only 
    //                  * solution at this time
    //                  */
    //                 if( strpos( $key, 'name' ) != false )
    //                 {
    //                     $title_display_name = $_POST[$key];
    //                     $title_slug = $_Helpers->sanitize_with_underscores( $_POST[$key] );
    //                 }
    //             }
    //         }   

    //         // echo 'title: ' . $title_slug . '<br>';
            
    //         // echo 'options: ' . json_encode( $new_multiple_post_structure ) . '<br>';

    //         // echo 'HTTP GET values: ' . json_encode( $_GET ) . '<br>';

    //         // echo 'HTTP POST values: ' . json_encode( $_POST );

    //         // die();

    //         register_post_type( $title_slug . '_item',    
    //             array(
    //                 'labels' => array(
    //                     'name' => __( $title_display_name . ' Items' ),
    //                     'singular_name' => __( $title_display_name . ' Item' )
    //                 ),
    //                 'public' => true,
    //                 'has_archive' => false,            
    //                 'rest_base' => CPI_ADMIN_ENV . '/page_module/(?P<post_id>\d+)/' . $title_slug . '_item',                
    //                 'show_in_menu' => false,
    //                 'show_in_rest' => true
    //             )
    //         );
    //     }
    // }

    function no_list_table_template( $post_id )
    {
        $post = get_post( $post_id );

        return '<div class="wrap"><h2>Page Modules - ' . $post->post_title . '</h2><p>no data structure found</p><a class="button-primary" href="' . esc_attr( admin_url('admin.php?page=add-' . $post->post_name . '-list-table&post=' . $post_id) ) .'">create table</a></div>';
    }
}
