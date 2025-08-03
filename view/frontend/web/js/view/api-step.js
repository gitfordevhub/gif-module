define([
    'ko',
    'uiComponent',
    'underscore',
    'Magento_Checkout/js/model/step-navigator',
    'mage/storage',
    'Magento_Customer/js/customer-data'
], function (ko, Component, _, stepNavigator, storage, customerData) {
    'use strict';

    console.log('[Meme Step] Before navigation');

    /**
     * mystep - is the name of the component's .html template,
     * <Vendor>_<Module>  - is the name of your module directory.
     */

    return Component.extend({
        defaults: {
            template: 'Study_Meme/api_step',
            isVisible: ko.observable(false),
            giphyImages: ko.observableArray([]),
            selectedGif: ko.observable(null),
            apiLoadFailed: ko.observable(false),
            errorMessage: ko.observable('')
        },

        /**
         * @returns {*}
         */
        initialize: function () {
            this._super();
            console.log('[Meme Step] initialize called');

            var customer = customerData.get('customer')();

            if (!customer.firstname) {
                return this;
            }

            if (this.giphyImages().length === 0) {
                this.loadGiphyImages();
            }

            stepNavigator.registerStep(
                'api-step',
                null,
                'Images',
                this.isVisible,
                _.bind(this.navigate, this),
                15
            );
            console.log('[Meme Step] All steps:', stepNavigator.steps());

            return this;
        },

        navigate: function () {
            console.log('[Meme Step] navigate called');
            this.isVisible(true);
        },

        loadGiphyImages: function () {
            let self = this;
            console.log('[Meme Step] loadGiphyImages called');

            storage.get('meme/request/giphy')
                .done(function (response) {
                    if (response && response.success === false) {
                        self.apiLoadFailed(true);
                        self.errorMessage(response.error || 'GIFs are currently unavailable.');
                        console.error('[Meme Step] API Error Response:', response);
                    } else if (Array.isArray(response) && response.length > 0) {
                        self.giphyImages(response);
                        self.apiLoadFailed(false);
                    } else {
                        self.apiLoadFailed(true);
                        self.errorMessage('No GIFs found.');
                    }
                })
                .fail(function (error) {
                    console.error('[Meme Step] API Request Failed:', error);
                    self.apiLoadFailed(true);
                    self.errorMessage('Failed to load GIFs. Please try again later.');
                });
        },

        selectGif: function (gifUrl) {
            this.selectedGif(gifUrl);
            console.log('[Meme Step] Selected GIF:', gifUrl);
        },

        navigateToNextStep: function (formElement, event) {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }

            const selected = this.selectedGif();
            const allGifs = this.giphyImages();

            if (!selected && this.apiLoadFailed()) {
                stepNavigator.next();
                return;
            }

            if (!selected) {
                alert('Please select a GIF before continuing.');
                return false;
            }

            const gifArray = [selected, ...allGifs.filter(url => url !== selected)];

            return storage.post(
                'meme/quote/savegif',
                JSON.stringify({ gifs: gifArray }),
                true
            ).done(function (response) {
                console.log('[Meme Step] GIFs saved to quote', response);
                stepNavigator.next();
            }).fail(function (error) {
                console.error('[Meme Step] Error saving GIFs', error);
                alert('Error saving GIFs. Please try again.');
            });
        }

        /**
         * @returns void
         */
        /*navigateToNextStep: function () {
            stepNavigator.next();
        }*/
    });
});
