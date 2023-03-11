<?php

namespace Okay\Modules\Custom\UkrposhtaInfo\Plugins;

use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\SmartyPlugins\Modifier;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPCitiesEntity;

class UkrposthaCityPlugin extends Modifier
{
    protected $tag = 'get_ukrpostha_city';

    protected $design;
    
    /** @var UPCitiesEntity */
    protected $citiesEntity;

    public function __construct(Design $design, EntityFactory $entityFactory)
    {
        $this->design = $design;
        $this->citiesEntity = $entityFactory->get(UPCitiesEntity::class);
    }

    public function run($cityId)
    {
        if (empty($cityRef)) {
            return '';
        }

        return $this->citiesEntity->findOne(['city_id' => $cityId]);
    }
}