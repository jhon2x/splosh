define([
    'jquery',
    'underscore',
    'uiCollection',
    'codemirror/codemirror',
    'editor',
    'uiRegistry',
    'Mirasvit_EmailDesigner/js/preview'
], function($, _, Collection, CodeMirror, Editor, registry) {
    'use strict';

    return Collection.extend({
        defaults: {
            counter: 0
        },

        initialize: function() {
            _.bindAll(this, 'afterRender');

            this._super();

            return this;
        },

        afterRender: function(target, viewModel) {
            var self = this;

            this.initEditor();
            this.initCodeMirror(target);

            if (this._elems.length === ++this.counter) {
                setInterval(function () {
                    self.Editor.fitFrame();
                }, 500);

                this.Editor.updatePreview();
            }
        },

        initEditor: function() {
            if (!this.Editor) {
                this.Editor = $.mirasvit.editor({url: this.dropUrl});
            }
        },

        initCodeMirror(element) {
            var delay  = 0;
            var self   = this;
            var $el    = $(element);
            var editor = CodeMirror.fromTextArea(element, {
                mode: 'htmlmixed',
                tabMode: 'indent',
                matchTags: true,
                viewportMargin: Infinity,
                styleActiveLine: true,
                tabSize: 2,
                lineNumbers: true,
                lineWrapping: false
            });

            editor.on('change', function (instance, changeObj) {
                // update editor and element values
                editor.refresh();
                editor.save();

                var area = $(editor.getTextArea());

                // update value in UI component for successful save on server side
                var component = registry.get({inputName: area.attr('name')});
                component.value(area.val());

                if (self.Editor.autoRefresh) {
                    clearTimeout(delay);
                    delay = setTimeout(self.Editor.updatePreview, 300);
                }
            });

            // change cursor position when changing data in the editor
            editor.doc.on('cursorActivity', function(doc) {
                $(editor.getTextArea()).selectRange(
                    doc.sel.ranges[0].anchor.line,
                    doc.sel.ranges[0].anchor.ch
                );
            });

            // update cursor position in a textarea
            editor.on('beforeSelectionChange', function(doc, selection) {
                $(editor.getTextArea()).selectRange(
                    selection.ranges[0].anchor.line,
                    selection.ranges[0].anchor.ch
                );
            });

            // update editor values on changing textarea
            $el.on('change', function() {
                editor.setValue(this.value);
                editor.refresh();
                editor.save();
            });

            this.Editor.editors[$el.attr('name')] = editor;
        }
    });
});