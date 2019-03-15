if (!Omeka) {
    var Omeka = {};
}

Omeka.CsvImport = {};

(function ($) {
    /**
     * Allow multiple mappings for each field, and add buttons to allow a mapping
     * to be removed.
     */
    Omeka.CsvImport.enableElementMapping = function () {
        $('form#csvimport .map-element').change(function () {
            var select = $(this);
            var addButton = select.siblings('span.add-element');
            if (!addButton.length) {
                var addButton = $('<span class="add-element"></span>');
                addButton.click(function() {
                    var copy = select.clone(true);
                    select.after(copy);
                    $(this).remove();
                });
                select.after(addButton);
            };
        });
    };

    /**
     * Add a confirm step before undoing an import.
     */
    Omeka.CsvImport.confirm = function () {
        $('.csv-undo-import').click(function () {
            return confirm("Undoing an import will delete all of its imported items. Are you sure you want to undo this import?");
        });
    };

    /**
     * Disable most options if Import from Csv Report is checked
     */
    Omeka.CsvImport.updateImportOptions = function () {
        // we need to test whether the checkbox is checked
        // because fields will all be displayed if the form fails validation
        var fields = $('div.field').has('#bsf_autodetect_type_collection, #automap_columns_names_to_elements, #item_type_id, #collection_id, #items_are_public, #items_are_featured, #column_delimiter, #element_delimiter, #tag_delimiter, #file_delimiter');
        if ($('#omeka_csv_export').is(':checked')) {
            fields.slideUp();
        } else {
            fields.slideDown();
        }
    };

    /**
     * Custom for BSF
     * Disable some options if Import item type / collections is checked
     */
    Omeka.CsvImport.bsf_updateImportOptions = function () {
        var fields_type_collection = $('div.field').has('#item_type_id, #collection_id');
        if ($('#bsf_autodetect_type_collection').is(':checked')) {
            fields_type_collection.slideUp();
        } else {
            fields_type_collection.slideDown();
        }
    };
         
})(jQuery);