$(document).ready(function () {
    $('#tx-extractor-files').hide();

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
