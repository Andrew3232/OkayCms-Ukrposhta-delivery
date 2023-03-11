<?php

namespace Okay\Modules\Custom\UkrposhtaInfo;

return [
    'Custom_UkrposhtaInfo_find_district' => [
        'slug' => 'ajax/up/find_district',
        'to_front' => true,
        'params' => [
            'controller' => __NAMESPACE__ . '\Controllers\UkrposhtaInfoSearchController',
            'method' => 'findDistrict',
        ],
    ],
    'Custom_UkrposhtaInfo_find_city' => [
        'slug' => 'ajax/up/find_city',
        'to_front' => true,
        'params' => [
            'controller' => __NAMESPACE__ . '\Controllers\UkrposhtaInfoSearchController',
            'method' => 'findCity',
        ],
    ],
    'Custom_UkrposhtaInfo_get_post_offices' => [
        'slug' => 'ajax/up/get_post_offices',
        'to_front' => true,
        'params' => [
            'controller' => __NAMESPACE__ . '\Controllers\UkrposhtaInfoSearchController',
            'method' => 'getPostOffices',
        ],
    ],
];