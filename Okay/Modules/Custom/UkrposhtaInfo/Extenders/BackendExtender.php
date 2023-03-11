<?php

namespace Okay\Modules\Custom\UkrposhtaInfo\Extenders;

use Exception;
use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Core\Modules\Module;
use Okay\Core\Request;
use Okay\Entities\DeliveriesEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPCitiesEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPDeliveryDataEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPDistrictsEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPOfficesEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPRegionsEntity;

class BackendExtender implements ExtensionInterface
{
	private $request;
	private $entityFactory;
	private $design;
	private $module;

	public function __construct(Request $request, EntityFactory $entityFactory, Design $design, Module $module)
	{
		$this->request = $request;
		$this->entityFactory = $entityFactory;
		$this->design = $design;
		$this->module = $module;
	}

	/**
	 * @throws Exception
	 */
	public function getDeliveryDataProcedure($order)
	{
		$moduleId = $this->module->getModuleIdByNamespace(__NAMESPACE__);
		$this->design->assign('urkposhta_module_id', $moduleId);

		if(!empty($order->id))
		{
			/** @var UPDeliveryDataEntity $upDdEntity */
			$upDdEntity = $this->entityFactory->get(UPDeliveryDataEntity::class);
			$regionsEntity = $this->entityFactory->get(UPRegionsEntity::class);
			$districtsEntity = $this->entityFactory->get(UPDistrictsEntity::class);
			$citiesEntity = $this->entityFactory->get(UPCitiesEntity::class);
			$officesEntity = $this->entityFactory->get(UPOfficesEntity::class);

			$upDeliveryData = $upDdEntity->getByOrderId($order->id);
			$regions = $regionsEntity->mappedBy('region_id')->noLimit()->find([]);
			$district = $districtsEntity->findOne(['district_id' => $upDeliveryData->district_id]);
			$city = $citiesEntity->findOne(['city_id' => $upDeliveryData->city_id]);
			$offices = $officesEntity->find(['city_id' => $upDeliveryData->city_id]);
			foreach($regions as $region)
			{
				$region->disabled = 0;
				if(!$officesEntity->findOne(['region_id' => $region->region_id]))
				{
					$region->disabled = 1;
				}
			}

			$this->design->assign('ukrposhta_regions', $regions);
			$this->design->assign('ukrposhta_district', $district);
			$this->design->assign('ukrposhta_city', $city);
			$this->design->assign('ukrposhta_offices', $offices);
			$this->design->assign('ukrposhta_delivery_data', $upDeliveryData);
		}
	}

	public function updateDeliveryDataProcedure($order)
	{
		if(!empty($order->id))
		{
			$moduleId = $this->module->getModuleIdByNamespace(__NAMESPACE__);

			/** @var UPDeliveryDataEntity $upDdEntity */
			$upDdEntity = $this->entityFactory->get(UPDeliveryDataEntity::class);

			if(!$upDeliveryData = $upDdEntity->getByOrderId($order->id))
			{
				$upDeliveryData = new \stdClass();
			}
			if(!empty($order->delivery_id))
			{
				/** @var DeliveriesEntity $deliveryEntity */
				$deliveryEntity = $this->entityFactory->get(DeliveriesEntity::class);
				$delivery = $deliveryEntity->get($order->delivery_id);

				if($delivery->module_id == $moduleId)
				{
					$upDeliveryData->region_id = $this->request->post('ukrposhta_region_id');
					$upDeliveryData->district_id = $this->request->post('ukrposhta_district_id');
					$upDeliveryData->city_id = $this->request->post('ukrposhta_city_id');
					$upDeliveryData->office_id = $this->request->post('ukrposhta_office');
					$upDeliveryData->redelivery = $this->request->post('ukrposhta_redelivery');

					if(!empty($upDeliveryData->id))
					{
						$upDdEntity->update($upDeliveryData->id, $upDeliveryData);
					} else
					{
						$upDeliveryData->order_id = $order->id;
						$upDdEntity->add($upDeliveryData);
					}
				} elseif(!empty($upDeliveryData->id))
				{
					$upDdEntity->delete($upDeliveryData->id);
				}
			} elseif(!empty($upDeliveryData->id))
			{
				$upDdEntity->delete($upDeliveryData->id);
			}

			$this->design->assign('ukrposhta_delivery_data', $upDeliveryData);
		}
	}
}