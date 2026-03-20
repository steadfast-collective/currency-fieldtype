import Fieldtype from './Currency.vue';

Statamic.booting(() => {
    // Should be named [snake_case_handle]-fieldtype
    Statamic.$components.register('currency-fieldtype', Fieldtype);
});
