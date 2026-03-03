define([
    'uiComponent',
    'ko',
    'uiRegistry',
    'wysiwygAdapter',
    'Magento_Ui/js/modal/alert',
    'MageWorx_SeoAI/js/utils/target-component-resolver',
    'mage/translate'
], function (Component, ko, registry, tinyMCE, alert, targetResolver, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            isVisible: false,
            isForPageBuilder: false,
            applyText: $t('Apply'),
            responses: [],
            entityType: null
        },

        observableProperties: [
            'isVisible',
            'isForPageBuilder',
            'messageType',
            'applyText'
        ],

        initialize: function () {
            this._super()
                .initListeners();

            return this;
        },

        initObservable: function () {
            this._super();
            this.responses = ko.observableArray(this.responses);
            this.observe(this.observableProperties);

            this.messageType.subscribe((value) => {
                let isForPageBuilder = false;

                try {
                    const targetComponent = this.getTargetComponent(value);
                    isForPageBuilder = 'pageBuilder' in targetComponent;
                } catch (e) {
                    console.log(e);
                    return;
                }

                this.applyText(isForPageBuilder ? $t('Copy') : $t('Apply'));
                this.isForPageBuilder(isForPageBuilder);
                this.responses([]);
            });

            return this;
        },

        initListeners: function () {
            this.responses.subscribe(function (value) {
                this.isVisible(value && value.length > 0);
            }.bind(this));
        },

        getTargetComponent: function (optionalMessageType) {
            let messageTypeToUse = optionalMessageType ? optionalMessageType : this.messageType;
            return targetResolver.getTargetComponent(messageTypeToUse, this.entityType);
        },

        selectResponse: function (response) {
            let targetComponent;

            try {
                targetComponent = this.getTargetComponent();
            } catch (e) {
                console.log(e);
                return;
            }

            if (targetComponent.pageBuilder !== undefined) {
                // Working with page builder
                navigator.clipboard.writeText(response).then(
                    () => {
                        this.closeModal();
                        alert({
                            content: $t('Text successfully copied to clipboard. You can paste result where you want using the page builder.')
                        });
                    },
                    () => {
                        alert({
                            content: $t('Failed to copy text to clipboard. Please do it manually and paste the result where you want using the page builder.')
                        });
                    },
                );
            } else {
                // Working with regular component
                targetComponent.value(response);

                let editorId = targetComponent.wysiwygId;
                if (editorId) {
                    let editorInstance = tinyMCE.get(editorId);
                    if (editorInstance) {
                        editorInstance.setContent(response);
                    }
                }

                if (typeof targetComponent.setData === 'function') {
                    targetComponent.setData(response);
                }

                this.closeModal();
            }
        },

        closeModal: function () {
            registry.get('${ $.provider }').actionDone();
        }
    });
});
