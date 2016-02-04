// IIFE for faster access to $ and safe $ use
(function ($) {

    $(document).ready(function () {
        $('.tx-extractor select').select2({width: '100%'});

        $('#tx-extractor-copy').click(Extractor.copyToClipboard);
        $('#tx-extractor-file').change(Extractor.extractMetadata);
        $('#tx-extractor-service').change(Extractor.extractMetadata);
        $('#tx-extractor-fal').change(Extractor.updateJson);
        $('#tx-extractor-processor').change(function() {
            Extractor.updateJson();
            Extractor.processSample();
        });

        Extractor.initializePropertyActions();
    });

}(jQuery || TYPO3.jQuery));
