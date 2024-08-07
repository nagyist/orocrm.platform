define([
    'jquery',
    'underscore',
    'oroui/js/app/views/base/view',
    'tpl-loader!orodatagrid/templates/datagrid/visible-items-counter.html'
], function($, _, BaseView, template) {
    'use strict';

    /**
     * Datagrid simple pagination widget
     *
     * @class   orodatagrid.datagrid.VisibleItemsCounter
     * @extends Backbone.View
     */
    const VisibleItemsCounter = BaseView.extend({
        /** @property */
        enabled: true,

        /** @property */
        hidden: false,

        /** @property */
        template: template,

        /** @property */
        className: 'visible-items-counter',

        /** @property */
        themeOptions: {
            optionPrefix: 'itemscounter'
        },

        /** @property */
        transTemplate: null,

        /**
         * @inheritdoc
         */
        constructor: function VisibleItemsCounter(options) {
            VisibleItemsCounter.__super__.constructor.call(this, options);
        },

        /**
         * Initializer.
         *
         * @param {Object} options
         * @param {Backbone.Collection} options.collection
         * @param {Object} options.fastForwardHandleConfig
         * @param {Number} options.windowSize
         */
        initialize: function(options) {
            options = options || {};
            this.hidden = options.hidden !== false;

            if (options.template) {
                this.template = options.template;
            }

            if (options.transTemplate) {
                this.transTemplate = options.transTemplate;
            }

            if (!options.collection) {
                throw new TypeError('"collection" is required');
            }
            this.collection = options.collection;
            this.listenTo(this.collection, 'add', this.render);
            this.listenTo(this.collection, 'remove', this.render);
            this.listenTo(this.collection, 'reset', this.render);

            this.hidden = options.hide === true;

            VisibleItemsCounter.__super__.initialize.call(this, options);
        },

        /**
         * Disables view
         *
         * @return {*}
         */
        disable: function() {
            return this;
        },

        /**
         * Enable view
         *
         * @return {*}
         */
        enable: function() {
            return this;
        },

        getTemplateData() {
            const {state} = this.collection;

            return {
                disabled: !this.enabled || !state.totalRecords,
                state: {length: this.collection.length, ...state},
                transTemplate: this.transTemplate
            };
        },

        /**
         * Render pagination
         *
         * @return {*}
         */
        render: function() {
            const state = this.collection.state;

            // prevent render if data is not loaded yet
            if (state.totalRecords === null) {
                return this;
            }

            VisibleItemsCounter.__super__.render.call(this);

            if (this.hidden) {
                this.$el.hide();
            }

            return this;
        }
    });

    return VisibleItemsCounter;
});
