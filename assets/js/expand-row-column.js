var ExpandRow = function (options) {
    var ajaxUrl = options.ajaxUrl,
        ajaxMethod = options.ajaxMethod,
        ajaxErrorMessage = options.ajaxErrorMessage,
        countColumns = options.countColumns,
        enableCache = options.enableCache,
        loadingIcon = options.loadingIcon;

    this.hideNotCurrent = function (rowID, current) {
        $("tr[id^=expand-row-column-detail" + rowID + "]").not(current).slideUp();
    };

    this.run = function ($el) {
        var row_id = $el.data('row_id'),
            col_id = $el.data('col_id'),
            ajaxData = $el.data('info'),
            parent = $el.parents('tr').eq(0),
            tr = $('#expand-row-column-detail' + row_id + col_id);

        if (tr.length && !tr.is(':visible') && enableCache) {
            this.hideNotCurrent(row_id, tr);
            tr.slideDown();
            return;
        } else if (tr.length && tr.is(':visible')) {
            tr.slideUp();
            return;
        }

        if (tr.length) {
            this.hideNotCurrent(row_id, tr);
            tr.find('td').html(loadingIcon);
            if (!tr.is(':visible')) {
                tr.slideDown();
            }
        } else {
            $("tr[id^=expand-row-column-detail" + row_id + "]").slideUp();
            var td = $('<td/>').html(loadingIcon).attr({'colspan': countColumns});
            tr = $('<tr/>').prop({'id': 'expand-row-column-detail' + row_id + col_id}).append(td);
            parent.after(tr);
        }

        $.ajax({
            url: ajaxUrl,
            method: ajaxMethod,
            data: ajaxData ? ajaxData : {'id': row_id},
            success: function (data) {
                tr.find('td').html(data);
            },
            error: function () {
                tr.find('td').html(ajaxErrorMessage);
            }
        });
    };

};
