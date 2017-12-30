/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /holdings
 */

$(document).ready(function() {
    $("#widget-holdings").ufTable({
        dataUrl: site.uri.public + '/api/holdings'
    });
});
