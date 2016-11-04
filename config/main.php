<?php

return array(
    'modules' => array(
        'paymentsystem' => array(
            'modules' => array(
                'yandexmoney' => array(
                    'import' => array(
                        'application.modules.paymentsystem.models.*',
                        'application.modules.paymentsystem.components.*',
                        'application.modules.paymentsystem.helpers.*',
                        'application.modules.paymentsystem.modules.yandexmoney.helpers.*',
                        'application.modules.admin.modules.finance.models.*',
                    ),
                ),
            ),
        ),
    ),
    'components' => array(
        'request' => array(
            'noCsrfValidationRoutes' => array(
                'paymentsystem/yandexmoney/pay/emulation',
                'paymentsystem/yandexmoney/pay/result',
                'paymentsystem/yandexmoney/pay/success',
                'paymentsystem/yandexmoney/pay/fail'
            ),
        ),
    )
);
