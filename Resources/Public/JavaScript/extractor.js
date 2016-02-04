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
            url: extractorAnalyzeAction,
            data: {
                'file': file,
                'service': service
            },
            success: function (data) {
                $('#tx-extractor-metadata').html(data.html);
                $('#tx-extractor-preview').html(data.preview);
                if (data.files.length > 0) {
                    $('#tx-extractor-files ol').html('<li>' + data.files.join('</li><li>') + '</li>');
                    $('#tx-extractor-files').show();
                } else {
                    $('#tx-extractor-files').hide();
                }
                $('#tx-extractor-property').val('');
                $('#tx-extractor-sample').val('');
                $('#tx-extractor-output').val('');
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

    processSample: function () {
        var sample = $('#tx-extractor-sample').val();
        var processor = $('#tx-extractor-processor').val();
        var outputField = $('#tx-extractor-output');

        if (!(sample && processor)) {
            outputField.val(sample);
        } else {
            $.ajax({
                url: extractorProcessAction,
                data: {
                    'sample': sample,
                    'processor': processor
                },
                success: function (data) {
                    if (data.success) {
                        outputField.val(data.text);
                    }
                }
            });
        }
    },

    initializePropertyActions: function () {
        $('.tx-extractor-property').click(function () {
            var property = $(this).data('property');
            var processor = $(this).data('processor');
            var sample = $(this).data('sample');

            $('#tx-extractor-property').val(property);
            $('#tx-extractor-sample').val($.isArray(sample) ? JSON.stringify(sample) : sample);
            $('#tx-extractor-processor').val(processor).trigger('change');

            // Prevent save action from configuration form
            return false;
        })
    }

};
