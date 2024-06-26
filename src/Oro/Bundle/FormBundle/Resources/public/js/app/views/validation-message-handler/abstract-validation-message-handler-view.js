define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const {isIOS, isSoftwareKeyboardEnabled} = require('oroui/js/tools');
    const Popper = require('popper').default;
    const BaseView = require('oroui/js/app/views/base/view');
    const VALIDATOR_ERROR_CLASS = 'validation-failed';

    function getScrollParent(element) {
        if (!element) {
            return document.body;
        }

        switch (element.nodeName) {
            case 'HTML':
            case 'BODY':
                return element.ownerDocument.body;
            case '#document':
                return element.body;
        }

        const needles = ['auto', 'scroll', 'overlay'];

        if (needles.indexOf($(element).css('overflow-x')) > -1 || needles.indexOf($(element).css('overflow-y')) > -1) {
            return element;
        }

        return getScrollParent(element.parentNode);
    }

    const AbstractValidationMessageHandlerView = BaseView.extend({
        autoRender: true,

        label: null,

        scrollParent: null,

        popper: null,

        active: false,

        useMessageLabelWidth: true,

        template: require('tpl-loader!oroform/templates/floating-error-message.html'),

        /**
         * @inheritdoc
         */
        constructor: function AbstractValidationMessageHandlerView(options) {
            AbstractValidationMessageHandlerView.__super__.constructor.call(this, options);
        },

        initialize: function(options) {
            this.label = options.label;
            this.labelContainer = this.label.parent();
            this.scrollParent = options.scrollParent;
            this.active = this.isActive();

            AbstractValidationMessageHandlerView.__super__.initialize.call(this, options);
        },

        getPopperReferenceElement: function() {
            throw new Error('Method `getPopperReferenceElement` has to be overridden in descendant');
        },

        /**
         * Checks if a control has opened dropdown in position when an error label has to be placed above
         *
         * @returns {boolean}
         */
        isActive: function() {
            throw new Error('Method `isActive` has to be overridden in descendant');
        },

        render: function() {
            if (this.popper) {
                this.popper.destroy();
                this.popper = null;
            }

            const message = this.label.text();

            if (message.length) {
                const messageEl = $(this.template({content: message}));

                this.labelContainer.append(messageEl);

                if (this.useMessageLabelWidth) {
                    messageEl.css({'max-width': Math.ceil(this.label.width())});
                }

                const popperReference = this.getPopperReferenceElement();

                this.scrollParent = getScrollParent(popperReference[0]);

                const getBoundariesElement = () => {
                    const boundariesElementSelector = this.$el.attr('data-boundaries-element');

                    if (boundariesElementSelector) {
                        const boundaryElement = this.$el.parents(boundariesElementSelector).last()[0];

                        if (boundaryElement) {
                            return boundaryElement;
                        }
                    }

                    return 'window';
                };

                const placement = this.getVerticalReferencePlacement(popperReference);
                this.popper = new Popper(popperReference, messageEl, {
                    placement,
                    positionFixed: true,
                    removeOnDestroy: true,
                    modifiers: {
                        flip: {enabled: false},
                        arrow: {
                            element: '.arrow',
                            fn: this.arrowModifier.bind(this)
                        },

                        preventOverflow: {
                            boundariesElement: getBoundariesElement()
                        },

                        hide: {
                            enabled: true,
                            fn: this.hideModifier.bind(this)
                        }
                    },

                    onUpdate({instance, positionFixed}) {
                        if (isIOS() && positionFixed && isSoftwareKeyboardEnabled()) {
                            // Set top fixed position by body overscroll
                            instance.popper.style.top = `${Math.abs(visualViewport.offsetTop)}px`;
                        }
                    },

                    onCreate({instance}) {
                        if (isIOS()) {
                            // Update popper when keyboard is enabled, update popper properly when user did overscroll
                            instance.scheduleUpdate();
                        }
                    }
                });
            }
            return this;
        },

        arrowModifier: function(data, options) {
            Popper.Defaults.modifiers.arrow.fn(data, options);

            if (data.placement.split('-')[1] === 'end') {
                data.offsets.arrow.left = data.offsets.reference.right - data.offsets.popper.left;
            } else {
                data.offsets.arrow.left = data.offsets.reference.left - data.offsets.popper.left;
            }

            return data;
        },

        hideModifier: function(data, options) {
            const scrollRect = this.scrollParent.getBoundingClientRect();

            if (!this.active || data.offsets.reference.top < scrollRect.top ||
                data.offsets.reference.top > scrollRect.bottom || data.offsets.left > scrollRect.right ||
                data.offsets.left > scrollRect.left
            ) {
                data.hide = true;
                data.attributes['x-out-of-boundaries'] = '';
            } else {
                Popper.Defaults.modifiers.hide.fn(data, options);
            }

            return data;
        },

        update: function() {
            if (this.active) {
                const $lastLabel = this.label.nextAll('.' + VALIDATOR_ERROR_CLASS).last();

                if ($lastLabel.length) {
                    $lastLabel.after(this.label);
                }

                this.label.css('visibility', 'hidden');
            } else {
                this.label.css('visibility', '');
            }

            if (!this.disposed && this.popper) {
                this.popper.scheduleUpdate();
            }
        },

        getVerticalReferencePlacement(reference) {
            const placement = reference.attr('placement') ?? 'top';

            return _.isRTL() ? `${placement}-end` : `${placement}-start`;
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.label.css('visibility', '');

            if (this.popper) {
                this.popper.destroy();
                this.popper = null;
            }

            AbstractValidationMessageHandlerView.__super__.dispose.call(this);
        }
    }, {
        test: function(element) {
            throw new Error('Method `test` has to be overridden in descendant');
        }
    });

    return AbstractValidationMessageHandlerView;
});
