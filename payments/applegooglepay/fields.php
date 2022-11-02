<?php

return [
    'fields' => [
        'setup' => [
            'type' => 'partial',
            'path' => '$/webtronic/applegooglepay/payments/applegooglepay/info',
        ],
        'transaction_mode' => [
            'label' => 'lang:webtronic.applegooglepay::default.label_transaction_mode',
            'type' => 'radiotoggle',
            'default' => 'test',
            'span' => 'left',
            'options' => [
                'live' => 'lang:webtronic.applegooglepay::default.text_live',
                'test' => 'lang:webtronic.applegooglepay::default.text_test',
            ],
        ],

        'live_secret_key' => [
            'label' => 'lang:webtronic.applegooglepay::default.label_live_secret_key',
            'type' => 'text',
            'span' => 'left',
            'trigger' => [
                'action' => 'show',
                'field' => 'transaction_mode',
                'condition' => 'value[live]',
            ],
        ],
        'live_publishable_key' => [
            'label' => 'lang:webtronic.applegooglepay::default.label_live_publishable_key',
            'type' => 'text',
            'span' => 'right',
            'trigger' => [
                'action' => 'show',
                'field' => 'transaction_mode',
                'condition' => 'value[live]',
            ],
        ],
        'test_secret_key' => [
            'label' => 'lang:webtronic.applegooglepay::default.label_test_secret_key',
            'type' => 'text',
            'span' => 'left',
            'trigger' => [
                'action' => 'show',
                'field' => 'transaction_mode',
                'condition' => 'value[test]',
            ],
        ],
        'test_publishable_key' => [
            'label' => 'lang:webtronic.applegooglepay::default.label_test_publishable_key',
            'type' => 'text',
            'span' => 'right',
            'trigger' => [
                'action' => 'show',
                'field' => 'transaction_mode',
                'condition' => 'value[test]',
            ],
        ],
        'locale_code' => [
            'label' => 'lang:webtronic.applegooglepay::default.label_locale_code',
            'type' => 'text',
            'span' => 'left',
        ],

        'order_status' => [
            'label' => 'lang:webtronic.applegooglepay::default.label_order_status',
            'type' => 'select',
            'options' => [\Admin\Models\Statuses_model::class, 'getDropdownOptionsForOrder'],
            'span' => 'right',
            'comment' => 'lang:webtronic.applegooglepay::default.help_order_status',
        ],
    ],
    'rules' => [
        ['transaction_mode', 'lang:webtronic.applegooglepay::default.label_transaction_mode', 'string'],
        ['live_secret_key', 'lang:webtronic.applegooglepay::default.label_live_secret_key', 'string'],
        ['live_publishable_key', 'lang:webtronic.applegooglepay::default.label_live_publishable_key', 'string'],
        ['test_secret_key', 'lang:webtronic.applegooglepay::default.label_test_secret_key', 'string'],
        ['test_publishable_key', 'lang:webtronic.applegooglepay::default.label_test_publishable_key', 'string'],
        ['order_status', 'lang:webtronic.applegooglepay::default.label_order_status', 'integer'],
    ],
];
