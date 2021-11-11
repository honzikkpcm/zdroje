$(function() {
    $('input[data-datepickerenable="on"]').datepicker();
    $('input[data-datetimepickerenable="on"]').datetimepicker({
        locale: 'cs'
    });
});

/**
 * @param message
 * @param type
 */
function createFlash(message, type) {
    if (!type)
        type = 'success';

    if (message) {
        $('.content-wrapper > .content').prepend('<div class="row">'
            + '<div class="col-md-12">'
            + '<div class="alert alert-' + type + ' alert-dismissible">'
            + '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'
            + message
            + '</div>'
            + '</div></div>');
    }
}

function syntaxHighlight(json) {
    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
        var cls = 'number';
        if (/^"/.test(match)) {
            if (/:$/.test(match)) {
                cls = 'key';
            } else {
                cls = 'string';
            }
        } else if (/true|false/.test(match)) {
            cls = 'boolean';
        } else if (/null/.test(match)) {
            cls = 'null';
        }
        return '<span class="' + cls + '">' + match + '</span>';
    });
}
