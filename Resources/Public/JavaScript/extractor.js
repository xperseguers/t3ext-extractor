Extractor = {

    copyToClipboard: function () {
        $('#tx-extractor-json').select();
        document.execCommand('copy');

        // Prevent save action from configuration form
        return false;
    },

    extractMetadata: function () {
        var file = $('#tx-extractor-file').val();
        var service = $('#tx-extractor-service').val();

        if (!(file && service)) return;

        $.ajax({
            url: configurationAjaxUrl,
            data: {
                'file': file,
                'service': service
            },
            success: function (data) {
                $('#tx-extractor-metadata').html(data.html);
                $('#tx-extractor-preview').html(data.preview);
                $('#tx-extractor-property').val('');
                $('#tx-extractor-processor').val('').trigger('change');
                $('#tx-extractor-json').val('');

                if (data.success) {
                    Extractor.initializePropertyActions();
                }
            }
        });
    },

    updateJson: function () {
        var falField = $('#tx-extractor-fal');
        var jsonField = $('#tx-extractor-json');
        var property = $('#tx-extractor-property').val();
        var processor = $('#tx-extractor-processor').val();

        if (processor != '') property += '->' + processor;

        jsonField.val("{\n  \"FAL\": \"" + falField.val() + "\",\n  \"DATA\": \"" + property.replace(/\\/g, '\\\\') + "\"\n}");
    },

    initializePropertyActions: function () {
        $('.tx-extractor-property').click(function () {
            var property = $(this).data('property');
            var processor = $(this).data('processor');
            $('#tx-extractor-property').val(property);
            $('#tx-extractor-processor').val(processor).trigger('change');

            // Prevent save action from configuration form
            return false;
        })
    }

};
