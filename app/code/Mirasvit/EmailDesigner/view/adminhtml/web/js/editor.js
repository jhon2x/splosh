define([
    'jquery',
    'underscore',
    'uiRegistry'
], function ($, _, registry) {
    'use strict';

    /**
     * Adds selectRange method to jQuery.
     *
     * This method changes cursor position in the textarea if 3rd parameter is not passed
     * and choose selection of a text if 3rd parameter exist.
     *
     * @param line  int - line number
     * @param start int - start number of selection
     * @param end   int - end number of selection
     */
    $.fn.selectRange = function(line, start, end) {
        // change cursor start position according to line number
        if (line > 0) {
            // add number of new line symbols
            start += line;
            // ignore current line
            line--;
            var content = this.val().split('\n');
            for (; line >= 0; line--) {
                start += content[line].length;
            }
        }

        if(end === undefined) {
            end = start;
        }

        return this.each(function() {
            if('selectionStart' in this) {
                this.selectionStart = start;
                this.selectionEnd = end;

                // Safari issue, after changing selection start and end values
                // the textarea loses the focus, so we re-focus it force again
                var areas = registry.get(
                    "email_designer_template_form.email_designer_template_form.general.editor.areas"
                );

                if (!areas) {
                    areas = registry.get(
                        "email_designer_theme_form.email_designer_theme_form.general.editor.areas"
                    );
                }

                if (areas) {
                    var editor = areas.Editor.editors[$(this).data('area')];
                    if (editor) {
                        editor.focus();
                    }
                }

            } else if(this.setSelectionRange) {
                this.setSelectionRange(start, end);
            } else if(this.createTextRange) {
                var range = this.createTextRange();
                range.collapse(true);
                range.moveEnd('character', end);
                range.moveStart('character', start);
                range.select();
            }
        });
    };

    $.widget('mirasvit.editor', {
        options: {
            name: null
        },

        _create: function () {
            var self = this;

            self.editors = {};
            self.autoRefresh = false;

            self.$previewFrame = $('[data-role=preview]');
            self.$preview = $(self.$previewFrame[0].contentDocument || self.$previewFrame[0].contentWindow.document);
            self.$desktopView = $('[data-role=desktop-view]');
            self.$mobileView = $('[data-role=mobile-view]');
            self.$autoRefresh = $('[data-role=auto-refresh]');
            self.$refresh = $('[data-role=refresh]');
            self.$spinner = $('.email-designer__editor-preview .spinner[data-role=spinner]');

            _.bindAll(this,
                'fitFrame',
                'updatePreview',
                'changeView'
            );

            self.$desktopView.on('click', function (e) {
                e.preventDefault();
                self.changeView('desktop');
            });

            self.$mobileView.on('click', function (e) {
                e.preventDefault();
                self.changeView('mobile');
            });

            self.$autoRefresh.on('change', function (e) {
                self.$refresh.parent().toggle();
                self.autoRefresh = self.$autoRefresh.attr('checked');
            });

            self.$refresh.on('click', function (e) {
                e.preventDefault();
                self.updatePreview();
            });
        },

        fitFrame: function () {
            var self = this;

            var areas = $('.email-designer__editor-areas');
            var areasHeight = areas.height();
            var previewHeight = $('body', self.$preview).height();

            var max = areasHeight;
            if (previewHeight > max) {
                max = previewHeight;
            }

            if (areas.width() < 400) {
                _.each(self.editors, function(editor) {
                    editor.display.sizer.style.minWidth = '700px';
                });
            }

            self.$previewFrame.height(max);
        },

        updatePreview: function () {
            var self = this;
            var data = {};

            self.$previewFrame.css('opacity', '0.5');
            self.$spinner.show();

            _.each(self.editors, function (editor, key) {
                editor.refresh();
                editor.save();

                var $textarea = $(editor.getTextArea());
                data[$textarea.data('area')] = $textarea.val();
            });

            self.abortAllAjax();

            var jqXHR = $.ajax(self.options.url, {
                type: 'POST',
                data: data,

                complete: function (response) {
                    self.$preview[0].open();
                    self.$preview[0].write(response.responseText);
                    self.$preview[0].close();

                    self.fitFrame();

                    self.$previewFrame.css('opacity', '1');
                    self.$spinner.hide();
                }
            });

            window.xhrPool.push(jqXHR);
        },

        changeView: function (mode) {
            var self = this;

            if (mode == 'desktop') {
                self.$desktopView.removeClass('tertiary').addClass('secondary');
                self.$mobileView.removeClass('primary').addClass('tertiary');
                self.$previewFrame.width('100%');
            } else {
                self.$mobileView.removeClass('tertiary').addClass('secondary');
                self.$desktopView.removeClass('primary').addClass('tertiary');
                self.$previewFrame.width('300px');
            }

            self.fitFrame();
        },

        abortAllAjax: function () {
            $(window.xhrPool).each(function (idx, jqXHR) {
                jqXHR.abort();
            });
            window.xhrPool = [];
        }
    });

    window.xhrPool = [];

    $.ajaxSetup({
        complete: function (jqXHR) {
            var index = window.xhrPool.indexOf(jqXHR);
            if (index > -1) {
                window.xhrPool.splice(index, 1);
            }
        }
    });

    return $.mirasvit.editor;
});