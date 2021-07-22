<?php 

class CPI_Page_Module_Helpers
{    
    
    public function _cpi_get_page_module_field_values( $post_id, $field_collections )
    {
        $field_sections = array();

        if ( $field_collections != null )
        {
            foreach ($field_collections as $field_collection) {
                $collection_fields_w_values = array();

                foreach ($field_collection['fields'] as $field) {
                    $field = (array)$field;

                    if ( $field['type'] == 'text' || $field['type'] == 'text-area' || $field['type'] == 'date' )
                    {                
                        $field['value'] = get_post_meta( $post_id, $field['name'], true );
                    }            

                    if ( $field['type'] == 'radio' )
                    {
                        $field['selected'] = get_post_meta( $post_id, $field['name'], true );
                    }

                    array_push( $collection_fields_w_values, $field );
                }

                array_push( $field_sections, array( 'title' => $field_collection['title'], 'fields' => $collection_fields_w_values ) );
            }
        }  

        return $field_sections;
    }

    public function sanitize_with_underscores( $str )
    {
        $sanitized_with_dashes = sanitize_title( $str );
        return str_replace( '-', '_', $sanitized_with_dashes );
    }
}
