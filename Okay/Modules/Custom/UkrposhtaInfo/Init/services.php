<?php

namespace Okay\Modules\Custom\UkrposhtaInfo;

use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\FrontTranslations;
use Okay\Core\Languages;
use Okay\Core\Modules\Module;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Core\Request;
use Okay\Core\Settings;
use Okay\Modules\Custom\UkrposhtaInfo\Extenders\BackendExtender;
use Okay\Modules\Custom\UkrposhtaInfo\Extenders\FrontExtender;

return [
	FrontExtender::class => [
		'class' => FrontExtender::class,
		'arguments' => [
			new SR(Request::class),
			new SR(EntityFactory::class),
			new SR(FrontTranslations::class),
			new SR(Design::class),
		],
	],
	BackendExtender::class => [
		'class' => BackendExtender::class,
		'arguments' => [
			new SR(Request::class),
			new SR(EntityFactory::class),
			new SR(Design::class),
			new SR(Module::class),
		],
	],
	UkrposhtaInfo::class => [
		'class' => UkrposhtaInfo::class,
		'arguments' => [
			new SR(Settings::class),
			new SR(EntityFactory::class),
			new SR(Languages::class),
		],
	],
];