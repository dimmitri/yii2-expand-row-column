var ExpandRow = function (options) {
    var ajaxUrl = options.ajaxUrl,
        ajaxMethod = options.ajaxMethod,
        ajaxErrorMessage = options.ajaxErrorMessage,
        countColumns = options.countColumns,
        enableCache = options.enableCache,
        loadingIcon = options.loadingIcon,
        showEffect = options.showEffect,
        hideEffect = options.hideEffect;

    this.hideNotCurrent = function (rowID, current) {
        this.hide($("tr[id^=expand-row-column-detail" + rowID + "]").not(current));
    };

    this.hide = function (element) {
        switch (hideEffect) {
            case 'slideUp':
                element.slideUp();
                break;
            case 'fadeOut':
                element.fadeOut();
                break;
            default:
                element.hide();
        }
    };

    this.show = function (element) {
        switch (showEffect) {
            case 'slideDown':
                element.slideDown();
                break;
            case 'fadeIn':
                element.fadeIn();
                break;
            default:
                element.show();
        }
    };

    this.run = function ($el) {
        var row_id = $el.data('row_id'),
            col_id = $el.data('col_id'),
            ajaxData = $el.data('info'),
            parent = $el.parents('tr').eq(0),
            tr = $('#expand-row-column-detail' + row_id + col_id);

        if (tr.length && !tr.is(':visible') && enableCache) {
            this.hideNotCurrent(row_id, tr);
            this.show(tr);
            return;
        } else if (tr.length && tr.is(':visible')) {
            this.hide(tr);
            return;
        }

        if (tr.length) {
            this.hideNotCurrent(row_id, tr);
            tr.find('td').html(loadingIcon);
            if (!tr.is(':visible')) {
                this.show(tr);
            }
        } else {
            this.hide($("tr[id^=expand-row-column-detail" + row_id + "]"));
            var td = $('<td/>').html(loadingIcon).attr({'colspan': countColumns});
            tr = $('<tr/>').prop({'id': 'expand-row-column-detail' + row_id + col_id}).append(td);
            parent.after(tr);
        }

        $.ajax({
            url: ajaxUrl,
            method: ajaxMethod,
            data: ajaxData,
            success: function (data) {
                tr.find('td').html(data);
            },
            error: function () {
                tr.find('td').html(ajaxErrorMessage);
            }
        });
    };

};
