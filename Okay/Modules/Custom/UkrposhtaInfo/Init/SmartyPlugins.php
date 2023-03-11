<?php

use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Modules\Custom\UkrposhtaInfo\Plugins\UkrposthaCityPlugin;
use Okay\Core\OkayContainer\Reference\ParameterReference as PR;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;

return [
	UkrposthaCityPlugin::class => [
        'class' => UkrposthaCityPlugin::class,
        'arguments' => [
	        new SR(Design::class),
	        new SR(EntityFactory::class),
        ],
    ],
];