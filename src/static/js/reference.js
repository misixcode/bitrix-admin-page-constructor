'use strict';

var ReferenceInput = (function ($) {

    var module, proto;

    module = function (id, url) {
        this.id = id;
        this.url = url;

        $(document).ready($.proxy(this.prepare, this));

        document.changeInput = function (selector) {
            $(selector).change();
        }
    };

    proto = module.prototype;

    proto.prepare = function () {
        this.$result = $(document.createElement('div'))
            .addClass('ac-reference-list')
            .addClass('SEARCH_' + this.id);

        this.$input = $(document.getElementById(this.id));
        this.$form = this.$input.closest('.ac-reference').append(this.$result);
        this.$search = this.$form.find('.ac-reference-search');
        this.$button = this.$form.find('.ac-reference-get');

        this.$button.click($.proxy(this.modal, this));
        this.$input.change($.proxy(this.change, this)).change();
        this.$search.keyup($.proxy(this.search, this));

        $('body').click($.proxy(this.bodyAction, this));
    };

    proto.change = function () {
        if (this.$input.val().length <= 0) {
            this.$form.removeClass('more');

            if (typeof this.$more === 'object') {
                this.$more.remove();
            }

            return;
        }

        if (typeof this.$more === 'object') {
            this.$more.remove();
        }

        this.$form.removeClass('more').addClass('loading');
        this.$input.prop('disabled', true);
        this.$button.prop('disabled', true);
        this.$search.val('').prop('disabled', true);

        $.post({
            url: this.url,
            data: {
                'reference-page': 'Y',
                'reference-name': this.$input.val()
            },
            dataType: 'json'
        }).done(
            $.proxy(this.changeHandler, this)
        );
    };

    proto.changeHandler = function (data) {
        this.$form.removeClass('loading');
        this.$input.val(data.value).removeAttr('disabled');
        this.$button.removeAttr('disabled');
        this.$search.val(data.name).removeAttr('disabled');

        if (typeof data.more === 'string') {
            this.lastMoreUrl = data.more;

            if (typeof this.$more === 'object') {
                this.$more.remove();
            }

            this.$more = $(document.createElement('button'))
                .attr('type', 'button')
                .addClass('ac-reference-more');

            if (data.icon) {
                this.$more.addClass(data.icon);
            }

            this.$form.addClass('more');
            this.$form.append(this.$more);
            this.$more.click($.proxy(this.moreClick, this));
        }
    };

    proto.search = function () {
        if (this.$search.val().length <= 1) {
            this.$result.slideUp();
            return;
        }

        this.$form.addClass('loading');
        this.$button.prop('disabled', true);

        $.post({
            url: this.url,
            data: {
                'reference-page': 'Y',
                'reference-search': this.$search.val()
            },
            dataType: 'json'
        }).done(
            $.proxy(this.searchHandler, this)
        );
    };

    proto.searchHandler = function (data) {
        var result, i, show;

        this.$form.removeClass('loading');
        this.$button.removeAttr('disabled');

        result = data.result;

        if (typeof result !== 'object') {
            this.$result.slideUp();
            return;
        }

        this.$result.html('');
        show = false;

        for (i in result) {
            if (!result.hasOwnProperty(i)) {
                continue;
            }

            show = true;

            this.$result.append(
                $(document.createElement('span'))
                    .attr('data-value', i)
                    .html(result[i])
                    .click($.proxy(this.select, this))
            );
        }

        if (show) {
            this.$result.slideDown();
        } else {
            this.$result.slideUp();
        }
    };

    proto.bodyAction = function (event) {
        if ($(event.target).closest('.SEARCH_' + this.id).length <= 0) {
            this.$result.slideUp();
        }
    };

    proto.select = function (event) {
        var span, value;

        span = $(event.target).closest('span');
        value = span.data('value');

        this.$result.slideUp();

        this.$search.val('');
        this.$input.val(value).change();
    };

    proto.moreClick = function () {
        var path, name, params;

        path = this.lastMoreUrl;
        name = 'MORE_INFO_' + this.id;
        params = 'scrollbars=yes,resizable=yes,width=800,height=500,'
            + 'top=' + Math.floor(((screen.height - 500) / 2) - 14) + ','
            + 'left=' + Math.floor(((screen.width - 800) / 2) - 5);

        window.open(path, name, params);
    };

    proto.modal = function () {
        var path, name, params;

        path = this.url + '?lang=ru&reference-page=Y&reference-input=' + this.id;
        name = 'REFERENCE_' + this.id;
        params = 'scrollbars=yes,resizable=yes,width=800,height=500,'
            + 'top=' + Math.floor(((screen.height - 500) / 2) - 14) + ','
            + 'left=' + Math.floor(((screen.width - 800) / 2) - 5);

        window.open(path, name, params);
    };

    return module;

})(jQuery);