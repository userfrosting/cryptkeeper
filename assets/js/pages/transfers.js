/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on widgets/course.js, uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /transfers
 */
$("#widget-transfers").ufTable({
    dataUrl: site.uri.public + "/api/transfers"
});
    
// Bind creation button
bindTransferCreationButton($('.js-transfer-create'));
    
function attachTransferForm() {
    $("body").on('renderSuccess.ufModal', function (data) {
        var modal = $(this).ufModal('getModal');
        var form = modal.find('.js-form');

        /**
         * Set up modal widgets
         */
        form.find("select[name=currency_id]").select2();

        // Set up the form for submission
        form.ufForm({
            validators: page.validators
        }).on("submitSuccess.ufForm", function() {
            // Reload page on success
            window.location.reload();
        });
    });
}

/**
 * Link create button
 */
function bindTransferCreationButton(el) {
    el.click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/transfers/create",
            msgTarget: $("#alerts-page")
        });

        attachTransferForm();
    });
}
