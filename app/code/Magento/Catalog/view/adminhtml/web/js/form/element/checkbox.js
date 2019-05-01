/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/single-checkbox'
], function (Checkbox) {
    'use strict';

    return Checkbox.extend({
        defaults: {
            inputCheckBoxName: '',
            prefixElementName: '',
            parentDynamicRowName: 'visual_swatch',
            imports: {
                changeTmpl:
                    'product_attribute_add_form.product_attribute_add_form.base_fieldset.frontend_input:value'
            },
        },

        /**
         * Parses options and merges the result with instance
         *
         * @returns {Object} Chainable.
         */
        initConfig: function () {
            this._super();
            this.value = [];
            this.configureDataScope();

            return this;
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();

            let recordId = this.parentName.split('.').last();
            let optionId = 'option_' + recordId;
            if (optionId in this.source.data.default) {
                this.checked(true);
            }

            return this;
        },

        initObservable: function () {
            return this._super().observe('checkboxTmpl').observe('checkboxClass');
        },

        /**
         * Configure data scope.
         */
        configureDataScope: function () {
            var recordId,
                value;

            recordId = this.parentName.split('.').last();
            value = this.prefixElementName + recordId;

            this.dataScope = 'data.' + this.inputCheckBoxName;
            this.inputName = this.dataScopeToHtmlArray(this.inputCheckBoxName);

            this.initialValue = value;

            this.links.value = this.provider + ':' + this.dataScope;
        },

        /**
         * Get HTML array from data scope.
         *
         * @param {String} dataScopeString
         * @returns {String}
         */
        dataScopeToHtmlArray: function (dataScopeString) {
            var dataScopeArray, dataScope, reduceFunction;

            /**
             * Add new level of nesting.
             *
             * @param {String} prev
             * @param {String} curr
             * @returns {String}
             */
            reduceFunction = function (prev, curr) {
                return prev + '[' + curr + ']';
            };

            dataScopeArray = dataScopeString.split('.');

            dataScope = dataScopeArray.shift();
            dataScope += dataScopeArray.reduce(reduceFunction, '');

            return dataScope;
        },

        onExtendedValueChanged: function (newExportedValue) {
            var isMappedUsed = !_.isEmpty(this.valueMap),
                oldChecked = this.checked.peek(),
                oldValue = this.initialValue,
                newChecked;

            newChecked = newExportedValue.indexOf(oldValue) !== -1;

            if (newChecked !== oldChecked) {
                this.checked(newChecked);
            }
        },

        onCheckedChanged: function (newChecked) {
            var isMappedUsed = !_.isEmpty(this.valueMap),
                oldValue = this.initialValue,
                newValue;

            if (isMappedUsed) {
                newValue = this.valueMap[newChecked];
            } else {
                newValue = oldValue;
            }

            if (this.checkboxTmpl() === 'radio' && newChecked) {
                this.value([newValue]);
            } else if (this.checkboxTmpl() === 'radio' && !newChecked) {
                if (typeof newValue === 'boolean') {
                    this.value([newChecked]);
                } else if (newValue === this.value.peek()) {
                    this.value([]);
                }

                if (isMappedUsed) {
                    this.value(newValue);
                }
            } else if (this.checkboxTmpl() === 'checkbox' && newChecked && this.value.indexOf(newValue) === -1) {
                this.value.push(newValue);
            } else if (this.checkboxTmpl() === 'checkbox' && !newChecked && this.value.indexOf(newValue) !== -1) {
                this.value.splice(this.value.indexOf(newValue), 1);
            }
        },

        changeTmpl: function (type) {
            if (type === 'select') {
                this.checkboxTmpl('radio');
                this.checkboxClass('admin__control-radio')
            } else if (type === 'multiselect') {
                this.checkboxTmpl('checkbox');
                this.checkboxClass('admin__control-checkbox')
            }
        }
    });
});
