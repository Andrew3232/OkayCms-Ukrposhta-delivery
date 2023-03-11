<?php

use Okay\Core\Modules\Modules;
use Okay\Modules\Custom\UkrposhtaInfo\UkrposhtaInfo;

chdir(dirname(__DIR__, 5));

require_once('vendor/autoload.php');

$DI = include 'Okay/Core/config/container.php';

/** @var Modules $modules */
$modules = $DI->get(Modules::class);
$modules->startEnabledModules();

/** @var UkrposhtaInfo $ukrposhtaCost */
$ukrposhtaCost = $DI->get(UkrposhtaInfo::class);

$ukrposhtaCost->parseRegionsToCache();
$ukrposhtaCost->parseDistrictsToCache();
$ukrposhtaCost->parsePostOfficesAndCitiesToCache();