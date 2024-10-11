define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/confirm'
], function ($, Component, confirm) {
    'use strict';

    return Component.extend({

        customerRightToForgetConfig: window.rtfConfig,

        onButtonClickRtfRequest: function () {
            var actionsConfig = this.getActionsConfig();

            confirm({
                title: 'Confirm Right to Forget',
                content: 'Do you wish to submit a Right to Forget request?',
                actions: {
                    confirm: function () {
                        $.ajax({
                            showLoader: true,
                            url: actionsConfig.submitRtfRequestUrl,
                            data: {
                                form_key: actionsConfig.form_key,
                            },
                            type: "POST",
                            success: function (data) {
                                // No need to log to the customer as MessageManager already does so
                            },
                            error: function (error) {
                                // No need to log to the customer as MessageManager already does so
                            }
                        });
                    },

                    cancel: function () {
                        return false;
                    }
                }
            });
        },

        getGeneralAccountDetails: function () {
            return this.customerRightToForgetConfig.generalAccountDetails;
        },

        getCustomerAddresses: function () {
            return this.customerRightToForgetConfig.addresses;
        },

        getCustomerOrders: function () {
            return this.customerRightToForgetConfig.orders;
        },

        getActionsConfig: function () {
            return this.customerRightToForgetConfig.actionConfig;
        }
    });
});
