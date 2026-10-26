<?php

return [
    'gateway' => 'neoleap',
    'mode'    => env('NEOLEAP_MODE', 'live'), // live | test
    'status'  => (int) env('NEOLEAP_STATUS', 1),

    /**
     * بوابة الدفع المستضافة (Live).
     * حسب التاجر قد يكون على Al Rajhi أو NeoLeap:
     * - https://digitalpayments.alrajhibank.com.sa/pg/payment/hosted.htm
     * - https://securepayments.alrajhibank.com.sa/pg/payment/hosted.htm
     * - https://digitalpayments.neoleap.com.sa/pg/payment/hosted.htm
     * - https://securepayments.neoleap.com.sa/pg/payment/hosted.htm
     */
    'hosted_url' => env(
        'NEOLEAP_HOSTED_URL',
        'https://digitalpayments.alrajhibank.com.sa/pg/payment/hosted.htm'
    ),

    'live' => [
        'tranportal_id'       => env('NEOLEAP_TRANPORTAL_ID', ''),
        'tranportal_password' => env('NEOLEAP_TRANPORTAL_PASSWORD', ''),
        'resource_key'        => env('NEOLEAP_RESOURCE_KEY', ''),
    ],

    'test' => [
        'tranportal_id'       => env('NEOLEAP_TEST_TRANPORTAL_ID', ''),
        'tranportal_password' => env('NEOLEAP_TEST_TRANPORTAL_PASSWORD', ''),
        'resource_key'        => env('NEOLEAP_TEST_RESOURCE_KEY', ''),
    ],
];
