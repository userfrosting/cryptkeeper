/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on widgets/course.js, uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /transactions
 */

$("#widget-transactions").ufTable({
    dataUrl: site.uri.public + "/api/transactions"
});

// Bind creation button
bindTransactionCreationButton($('.js-transaction-create'));

function attachTransactionForm() {
    $("body").on('renderSuccess.ufModal', function (data) {
        var modal = $(this).ufModal('getModal');
        var form = modal.find('.js-form');

        /**
         * Set up modal widgets
         */

        // Initialize date/time pickers
        form.find('.js-date-picker').datetimepicker({
            format: 'MM/DD/YYYY LT'
        });

        var marketSelect = form.find("select[name=market_id]");
        marketSelect.select2({
            ajax: {
                url: site.uri.public + '/api/markets',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        filters: {
                            name : params.term
                        },
                        sorts: {
                            name: 'asc'
                        },
                        page: params.page || 0,
                        size: params.size || 10
                    };
                },
                processResults: function (data, params) {
                    var suggestions = [];
                    // Process the data into dropdown options
                    if (data && data.rows) {
                        $.each(data.rows, function(idx, row) {
                            row.text = row.primary_currency.symbol + '/' + row.secondary_currency.symbol;
                            suggestions.push(row);
                        });
                    }

                    params.page = params.page || 0;
                    params.size = params.size || 10;

                    return {
                        results: suggestions,
                        pagination: {
                            more: (params.page * params.size) < data.count_filtered
                        }
                    };
                }
            },
            width: '100%',
            cache: true
        });

        // Update form units when a market is selected
        marketSelect.on('select2:select', function(e) {
            var market = e.params.data;

            var quantityUnitsEl = form.find('.js-units-quantity');
            quantityUnitsEl.html(market.secondary_currency.symbol);

            var priceUnitsEl = form.find('.js-units-price');
            priceUnitsEl.html(market.primary_currency.symbol + '/' + market.secondary_currency.symbol);

            var feeUnitsEl = form.find('.js-units-fee');
            feeUnitsEl.html(market.primary_currency.symbol);
        });

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
function bindTransactionCreationButton(el) {
    el.click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/transactions/create",
            msgTarget: $("#alerts-page")
        });

        attachTransactionForm();
    });
}
