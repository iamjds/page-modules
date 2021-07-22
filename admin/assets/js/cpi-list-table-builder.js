jQuery(function($) {
    // by default hide column option field
    displayOptionField(false);

    $('#list-module-table').find('th[data-type-text-area]').each(function(i, el){
        var cellIndex = el.cellIndex;

        $(el).closest('table').find('tbody tr td').each(function(si, cell){            
            if(cell.cellIndex === cellIndex){
                var cellText = $(cell).text();

                if(cellText.length > 100){
                    $(cell).text(cellText.substring(0, 100) + '...');
                }
            }
        })
    });

    $('[name="list_module_column_type"]').on('change', function(evt){
        var _selected = $(evt.currentTarget).find('option:selected').val();

        if(_selected == 'radio' || _selected == 'select') {
            displayOptionField(true);
        } else {
            displayOptionField(false);
        }
    });

    $('[data-add-row]').on('click', function(evt){
        var $table = $(evt.currentTarget).closest('.wrap').find('#list-module-table');
        var $columnHeaders = $table.find('th');
        // var rowItemCount = $table.data('item-count');
        var columnCount = $table.find('th').length;

        var $row = $('<tr></tr>');        
        
        for (let index = 0; index < columnCount; index++) {
            var columnTypeObj = $($columnHeaders[index]).data();
            var columnType = Object.keys(columnTypeObj)[0].split('type')[1].toLowerCase();            
            var $columnField = createColumnField(columnType);
            var $column = $('<td></td>');

            $column.append($columnField);
            $row.append($column);            
        }

        // add Save button to final column
        $saveColumns = $('<td style="text-align:right"><button type="submit" class="button-primary" href="/">Save</button></td>');

        bindSaveBtnEvent($saveColumns);

        $row.append($saveColumns);
        $table.append($row);
    });

    function bindSaveBtnEvent($saveBtn){
        $saveBtn.on('click', function(evt){
            var $table = $('#list-module-table');
            var ajaxUrl = $table.data('ajax-url');
            var $tableColumns = $(evt.currentTarget).closest('tr').find('td');
            var columnCount = $tableColumns.length;
            var urlPostParam =  parseInt(new URL(document.location).searchParams.get('post'));
            var rowData = {};

            $.each($tableColumns, function(i, col){
                if(i < (columnCount-1)){
                    var dataKey = $table.find('th')[i].innerText;
                    dataKey = dataKey.split(' ').join('_').toLowerCase();
                    rowData[dataKey] = getValueBasedOnTypeofField($(col).find('input, textarea'));
                }
            });

            $.post(ajaxUrl, {'post': urlPostParam, 'action': 'cpi_save_list_row_data', rowData})
                .done(function(res){
                    console.log('row save successful');
                    location.reload()
                })
                .fail(function(){console.error('row save error')});
        });
    }

    function displayOptionField(isDisplayed) {
        var $fieldRow = $('[name="list_module_column_options"]').closest('tr');
        var $fieldDesc = $('.list_module_column_options_desc');

        if(!isDisplayed){
            $fieldRow.hide();
            $fieldDesc.hide();
        } else {
            $fieldRow.show();
            $fieldDesc.show();
        }
    }

    function getValueBasedOnTypeofField($input) {
        let inputVal;
        
        if($input.is('input')){
            if($input.attr('type') == 'text') inputVal = $input.val();
            if($input.attr('type') == 'checkbox') inputVal = $input.is(':checked');
        }

        if($input.is('textarea')){
            inputVal = $input.val();
        }                            

        return inputVal;
    }

    function createColumnField(_columnType) {
        var $field = ''

        switch (_columnType) {
            case 'text':
                $field = $('<input type="text" />');
                break;

            case 'textarea':
                $field = $('<textarea></textarea>');
                break;

            case 'checkbox':
                $field = $('<input type="checkbox" />');
                break;                

            case 'radio':
                $field = $('<input type="radio" />');
                break;

            case 'select':
                $field = $('<select></select>');
                break;                
        
            default:
                $field = $('<input type="text" />');
                break;            
        }

        return $field;
    }
})
