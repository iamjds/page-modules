<?php

class CPI_Page_Module_Admin_Form_Builder
{      
    private $field_width;

    function __construct()
    {
        $this->field_width = 400;
    }
    
    function create_table( $table_data )
    {                
        $table = '<table class="form-table">';        

        if ( array_key_exists( 'fields', (array)$table_data ) && count( $table_data['fields'] ) > 0 )
        {
            $table .= $this->get_fields( $table_data['fields'] );
        }

        $table .= '</table>';

        return $table;
    }

    function get_fields( $requested_fields )
    {
        $created_fields = '';        

        foreach ($requested_fields as $field) {
            if( $field['type'] == 'text' ) 
            {
                $created_fields .= $this->text_field( $field );
            }
                        
            if( $field['type'] == 'text-area' ) 
            {
                $created_fields .= $this->text_area_field( $field['label'], $field['name'], $field['value'] );
            }            

            if( $field['type'] == 'radio' )
            {
                $created_fields .= $this->radio_field( $field );
            }

            if( $field['type'] == 'date' )
            {
                $created_fields .= $this->date_field( $field );
            }

            if( $field['type'] == 'select' )
            {
                $created_fields .= $this->select_field( $field['label'], $field['name'], $field['options'], $field['selected'] );
            }
        }        

        return $created_fields;
    }

    function text_field( $field )
    {
        $field_name = $field['label'];
        $input_name = $field['name'];
        $input_value = $field['value'];        

        $table_row = '<tr valign="top">';
        $table_row .= '<th scope="row">' . $field_name . '</th><td>';        
        $table_row .= '<input type="text" style="width:' . $this->field_width . 'px;" name="' . $input_name . '" value="' . $input_value . '" required />';

        if ( array_key_exists( 'desc', $field ) )
        {
            $table_row .= '<p class="description">' . $field['desc'] . '</p>';
        }

        $table_row .= '</td></tr>';        
        $table_row .= '</tr>';

        return $table_row;
    }

    function text_area_field( $field_name, $input_name, $area_value )
    {        
        if( $area_value != '' || $area_value != null )
        {
            return '<tr valign="top"><th scope="row">' . $field_name . '</th><td><textarea style="width:' . $this->field_width . 'px;height:100px" name="' . $input_name . '" />'. apply_filters( 'wpautop', $area_value ) .'</textarea></td></tr>';
        }
        else 
        {
            return '<tr valign="top"><th scope="row">' . $field_name . '</th><td><textarea style="width:' . $this->field_width . 'px;height:100px" name="' . $input_name . '" /></textarea></td></tr>';
        }        
    }  
    
    function check_field( $field )
    {
        $field_name = $field['label'];
        $input_name = $field['name'];
        $radio_options = $field['options']; 
        $checked = $field['checked'];

        $row = '<tr valign="top"><th scope="row">' . $field_name . '</th><td>';
        $checkbox = '';
                   
        if( intval( $checked ) ===  0 )
        {
            $checkbox .= '<input type="checkbox" name="' . $input_name . '" checked required />';
        }
        else 
        {
            $checkbox .= '<input type="checkbox" name="' . $input_name . '" required />';
        }            

        $row .= $checkbox;
        $row .= '</select>';
        
        if ( array_key_exists( 'desc', $field ) )
        {
            $row .= '<p class="description">' . $field['desc'] . '</p>';
        }

        $row .= '</td></tr>';

        return $row;
    }

    function radio_field( $field )
    {
        $field_name = $field['label'];
        $input_name = $field['name'];
        $radio_options = $field['options']; 
        $selected = $field['selected'];

        $row = '<tr valign="top"><th scope="row">' . $field_name . '</th><td>';
        $radio = '';        
        
        foreach ($radio_options as $option) {            
            if( intval( $selected ) ===  $option['value'] )
            {
                $radio .= '<input type="radio" name="' . $input_name . '" value="' . $option['value'] . '" checked required /><label style="vertical-align:text-top" for="">' . $option['label'] . '</label><br>';
            }
            else 
            {
                $radio .= '<input type="radio" name="' . $input_name . '" value="' . $option['value'] . '" required /><label style="vertical-align:text-top" for="">' . $option['label'] . '</label><br>';
            }            
        }                

        $row .= $radio;
        $row .= '</select>';
        
        if ( array_key_exists( 'desc', $field ) )
        {
            $row .= '<p class="description">' . $field['desc'] . '</p>';
        }

        $row .= '</td></tr>';

        return $row;
    }

    function date_field( $field )
    {   
        $field_name = $field['label']; 
        $input_name = $field['name']; 
        $input_value = $field['value'];
        $row = '<tr valign="top"><th scope="row">' . $field_name . '</th><td>';

        if( $input_value != '' || $input_value != null )
        {
            $row .= '<input type="date" name="' . $input_name . '" value="' . $input_value . '" required />';
        }
        else 
        {
            $row .= '<input type="date" name="' . $input_name . '" required />';
        }

        if ( array_key_exists( 'desc', $field ) )
        {
            $row .= '<p class="description">' . $field['desc'] . '</p>';
        }

        $row .= '</td></tr>';

        return $row;
    }

    function select_field( $field_name, $select_name, $select_options, $selected )
    {
        $select = '<tr valign="top"><th scope="row">' . $field_name . '</th><td><select style="width:' . $this->field_width . 'px;" name="'. $select_name .'"><option>---</option>';
        
        foreach ($select_options as $option) 
        {                                    
            if ( gettype( $selected ) == 'string' && intval( $selected ) == 0 ) // $selected is text
            {
                if ( $selected == $option['value'] )
                {
                    $select .= '<option value="'. $option['value'] .'" selected>'. $option['label'] .'</option>';    
                }
                else 
                {
                    $select .= '<option value="'. $option['value'] .'">'. $option['label'] .'</option>';    
                }
            }
            else if ( gettype( $selected ) == 'string' && intval( $selected ) != 0 ) // $selected is an integer wrapped in quotes
            {
                if ( intval( $selected ) === intval( $option['value'] ) )
                {
                    $select .= '<option value="'. $option['value'] .'" selected>'. $option['label'] .'</option>';    
                }
                else 
                {
                    $select .= '<option value="'. $option['value'] .'">'. $option['label'] .'</option>';
                }
            }
            elseif ( gettype( $selected ) == 'integer' )
            {
                if ( $selected === intval( $option['value'] ) )
                {
                    $select .= '<option value="'. $option['value'] .'" selected>'. $option['label'] .'</option>';    
                }
                else 
                {
                    $select .= '<option value="'. $option['value'] .'">'. $option['label'] .'</option>';
                }
            }
            
        }                

        $select .= '</select></td></tr>';

        return $select;
    }
}
