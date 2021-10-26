'use strict';

var AutocompleteInput = (function ($) {
    var module, proto;

    module = function (id, url) {
        this.id = id;
        this.url = url;
        this.$wrap = $('#' + this.id);
        this.isSearchMode = this.$wrap.hasClass('search');
        this.$result = this.$wrap.find('[data-result]');
        this.$input = this.$wrap.find('input');

        if (!this.isSearchMode) {
            this.$input.after('<button type="button"></button>')
            this.$button = this.$wrap.find('button');
            this.$button.click($.proxy(function (event) {
                if (this.$result.is(':visible')) {
                    this.$result.slideUp(100);
                } else {
                    this.$wrap.find('[data-result] > span').show();
                    this.$result.slideDown(100);
                }
            }, this));
            this.$wrap.find('[data-result] > span').click($.proxy(this.select, this));
        }

        this.$input.on('keyup', $.proxy(this.changeInput, this));
        this.$input.on('change', $.proxy(this.changeInput, this));

        $(document).click($.proxy(function (event) {
            if (!$(event.target).closest('[data-result]').is(this.$result)) {
                if (!this.$button || !$(event.target).closest('button').is(this.$button)) {
                    this.$result.slideUp(100);
                }
            }
        }, this));
    };

    proto = module.prototype;

    proto.select = function (event) {
        this.$input.val($(event.target).closest('[data-value]').attr('data-value'));
        this.$result.slideUp(50);
    };

    proto.changeInput = function (event) {
        if (this.isSearchMode) {
            this.search();
            return;
        }

        var phrase = $(event.target).val().trim();
        var regexp = new RegExp(phrase.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"), 'i');

        var $find = this.$wrap.find('[data-result] > span').hide().filter(function () {

            var name = $(this).data('name'),
                html = $(this).html(),
                string = name || html,
                result = false;

            if (typeof string !== 'string') {
                result = false;
            } else if (regexp.test(string)) {
                result = true;
            }
            return result;
        }).show();

        if ($find.length > 0 && phrase.length > 0) {
            this.$result.slideDown(50);
        } else {
            this.$result.slideUp(50);
        }
    };

    proto.search = function () {
        if (this.$input.val().length <= 1) {
            this.$result.slideUp(100);
            return;
        }

        if (this._lastQuery === this.$input.val()) {
            return;
        }

        this._lastQuery = this.$input.val();

        this.$wrap.addClass('loading');
        $.post({
            url: this.url,
            data: {
                'reference-page': 'Y',
                'reference-autocomplete': 'Y',
                'reference-search': this.$input.val()
            },
            dataType: 'json'
        }).done(
            $.proxy(this.searchHandler, this)
        );
    };

    proto.searchHandler = function (data) {
        var result, i, show;

        this.$wrap.removeClass('loading');

        result = data.result;

        if (typeof result !== 'object') {
            this.$result.slideUp(100);
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
            this.$result.slideDown(100);
        } else {
            this.$result.slideUp(100);
        }
    };

    return module;

})(jQuery);
