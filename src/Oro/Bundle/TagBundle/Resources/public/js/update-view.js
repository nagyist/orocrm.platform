define(function(require) {
    'use strict';

    const _ = require('underscore');
    const $ = require('jquery');
    const TagView = require('./view');

    /**
     * @export  orotag/js/update-view
     * @class   orotag.UpdateView
     * @extends orotag.View
     */
    const TagUpdateView = TagView.extend({
        /** @property */
        template: require('tpl-loader!../templates/update-tag-list.html'),

        /** @property */
        tagsOverlayTemplate: _.template(
            '<div class="controls">' +
                '<div class="well well-small">' +
                    '<div class="tags-holder"></div>' +
                '</div>' +
            '</div>'
        ),

        /** @property {Object} */
        options: _.extend({}, TagView.prototype.options, {
            autocompleteFieldId: null,
            fieldId: null,
            ownFieldId: null,
            unassign: false,
            tagOverlayId: null
        }),

        events: {
            'click [data-action="remove-tag"]': '_removeItem'
        },

        /**
         * @inheritdoc
         */
        constructor: function TagUpdateView(options) {
            TagUpdateView.__super__.constructor.call(this, options);
        },

        /**
         * Initialize widget
         *
         * @param {Object} options
         * @param {Backbone.Collection} options.tags
         * @param {string} options.autocompleteFieldId DomElement ID of autocomplete widget
         * @param {string} options.fieldId DomElement ID of hidden field with all tags
         * @param {string} options.ownFieldId DomElement ID of hidden field with own tags
         * @throws {TypeError} If mandatory options are undefined
         */
        initialize: function(options) {
            options = options || {};
            this.options = _.defaults(options, this.options);

            if (!options.autocompleteFieldId) {
                throw new TypeError('"autocompleteFieldId" is required');
            }

            if (!options.fieldId) {
                throw new TypeError('"fieldId" is required');
            }

            if (!options.ownFieldId) {
                throw new TypeError('"ownFieldId" is required');
            }

            this.$tagOverlayId = $(this.options.tagOverlayId);
            this._renderOverlay();

            TagView.prototype.initialize.call(this, options);

            this._prepareCollections();

            const onCollectionChange = () => {
                this.render();
                this._updateHiddenInputs();
            };
            this.listenTo(this.getCollection(), 'add', onCollectionChange);
            this.listenTo(this.getCollection(), 'remove', onCollectionChange);

            $(this.options.autocompleteFieldId).on('change', this._addItem.bind(this));
        },

        /**
         * Add item from autocomplete to internal collection
         *
         * @param {Object} e select2.change event object
         * @private
         */
        _addItem: function(e) {
            e.preventDefault();
            const tag = e.added;

            if (!_.isUndefined(tag)) {
                this.getCollection().addItem(tag);
            }

            // clear autocomplete
            $(e.target).inputWidget('val', '');
        },

        /**
         * Removes item
         *
         * @param e
         * @returns {*}
         * @private
         */
        _removeItem: function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const $el = $(e.currentTarget).parents('li');
            const id = $($el).data('id');
            if (!_.isUndefined(id)) {
                this.getCollection().removeItem(id, this.options.filter);
            }
            return this;
        },

        /**
         * Render overlay block
         *
         * @returns {*}
         * @private
         */
        _renderOverlay: function() {
            this.$tagOverlayId.append(this.tagsOverlayTemplate());
            return this;
        },

        /**
         * Fill data to collections from hidden inputs
         *
         * @returns {*}
         * @private
         */
        _prepareCollections: function() {
            let allTags = JSON.parse($(this.options.fieldId).val());
            if (!Array.isArray(allTags)) {
                allTags = [];
            }

            this.getCollection().reset(allTags);
            return this;
        },

        /**
         * Update hidden inputs triggered by collection change
         *
         * @private
         */
        _updateHiddenInputs: function() {
            $(this.options.fieldId).val(JSON.stringify(this.getCollection()));
            $(this.options.ownFieldId).val(JSON.stringify(this.getCollection().getFilteredCollection('owner')));
        }
    });

    return TagUpdateView;
});
