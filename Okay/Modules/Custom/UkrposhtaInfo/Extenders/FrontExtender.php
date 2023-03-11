<?php

namespace Okay\Modules\Custom\UkrposhtaInfo\Extenders;

use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\FrontTranslations;
use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Core\Modules\Module;
use Okay\Core\Request;
use Okay\Core\Router;
use Okay\Core\ServiceLocator;
use Okay\Entities\OrdersEntity;
use Okay\Entities\PaymentsEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPCitiesEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPDeliveryDataEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPDistrictsEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPOfficesEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPRegionsEntity;

class FrontExtender implements ExtensionInterface
{
	/** @var Request */
	private $request;

	/** @var EntityFactory */
	private $entityFactory;

	/** @var FrontTranslations */
	private $frontTranslations;

	/** @var Design $design */
	private $design;

	public function __construct(
		Request $request,
		EntityFactory $entityFactory,
		FrontTranslations $frontTranslations,
		Design $design
	){
		$this->request = $request;
		$this->entityFactory = $entityFactory;
		$this->frontTranslations = $frontTranslations;
		$this->design = $design;
	}

	/**
	 * @param $deliveries
	 * @param $cart
	 *
	 * @return array
	 * @throws \Exception
	 *
	 * Метод проходится по способам доставки, и подменяет текст стоимости доставки.
	 *
	 */
	public function getCartDeliveriesList($deliveries, $cart)
	{
		$SL = ServiceLocator::getInstance();

		/** @var FrontTranslations $frontTranslations */
		$frontTranslations = $SL->getService(FrontTranslations::class);

		/** @var Module $module */
		$module = $SL->getService(Module::class);

		/** @var Design $design */
		$design = $SL->getService(Design::class);

		/** @var PaymentsEntity $paymentsEntity */
		$paymentsEntity = $this->entityFactory->get(PaymentsEntity::class);
		/** @var UPRegionsEntity $regionsEntity */
		$regionsEntity = $this->entityFactory->get(UPRegionsEntity::class);
		/** @var UPRegionsEntity $regionsEntity */
		$officesEntity = $this->entityFactory->get(UPOfficesEntity::class);

		$regions = $regionsEntity->find();
		foreach($regions as &$region)
		{
			$region->disabled = 0;
			if(!$officesEntity->find(['region_id' => $region->region_id]))
			{
				$region->disabled = 1;
			}
		}

		$redeliveryPaymentsIds = $paymentsEntity->cols(['id'])->find(['ukrposhta_info__cash_on_delivery' => 1]);
		foreach($redeliveryPaymentsIds as $k => $id)
		{
			$redeliveryPaymentsIds[$k] = (int)$id;
		}
		$design->assignJsVar('up_redelivery_payments_ids', $redeliveryPaymentsIds);
		$design->assignJsVar('up_ukrpostha_not_found', $frontTranslations->getTranslation('up_ukrpostha_empty_results'));
		$design->assign('up_redelivery_payments_ids', $redeliveryPaymentsIds);
		$design->assign('up_regions', $regions);

		$upModuleId = $module->getModuleIdByNamespace(__CLASS__);
		$design->assignJsVar('up_delivery_module_id', $upModuleId);
		$design->assign('up_delivery_module_id', $upModuleId);

		foreach($deliveries as $delivery)
		{
			if($delivery->module_id == $upModuleId)
			{
				$delivery->delivery_price_text = $frontTranslations->getTranslation('up_delivery_price');
			}
		}

		return ExtenderFacade::execute(__METHOD__, $deliveries, func_get_args());
	}

	/**
	 * @param $defaultData
	 * @param $user
	 *
	 * @return array
	 * @throws \Exception
	 *
	 * Если у пользователя был ранее заказ, и он был на Укрпочту, заполним данными
	 */
	public function getDefaultCartData($defaultData, $user)
	{
		if(!empty($user->id))
		{
			/** @var OrdersEntity $ordersEntity */
			$ordersEntity = $this->entityFactory->get(OrdersEntity::class);

			/** @var UPDeliveryDataEntity $upDeliveryDataEntity */
			$upDeliveryDataEntity = $this->entityFactory->get(UPDeliveryDataEntity::class);

			/** @var UPRegionsEntity $upRegionsEntity */
			$upRegionsEntity = $this->entityFactory->get(UPRegionsEntity::class);

			/** @var UPDistrictsEntity $upDistrictsEntity */
			$upDistrictsEntity = $this->entityFactory->get(UPDistrictsEntity::class);

			/** @var UPCitiesEntity $upCitiesEntity */
			$upCitiesEntity = $this->entityFactory->get(UPCitiesEntity::class);

			/** @var UPOfficesEntity $upOfficesEntity */
			$upOfficesEntity = $this->entityFactory->get(UPOfficesEntity::class);
			
			if(($lastOrder = $ordersEntity->findOne(['user_id' => $user->id])) && ($upDeliveryData = $upDeliveryDataEntity->getByOrderId($lastOrder->id)))
			{
				$defaultData['ukrposhta_delivery_region_id'] = $upDeliveryData->region_id;
				$defaultData['ukrposhta_delivery_district_id'] = $upDeliveryData->district_id;
				$defaultData['ukrposhta_delivery_city_id'] = $upDeliveryData->city_id;
				$defaultData['ukrposhta_delivery_office_id'] = $upDeliveryData->office_id;
				$defaultData['ukrposhta_delivery_post_index'] = $upDeliveryData->post_index;

				if(!empty($upDeliveryData->city_id) && empty($upDeliveryData->city_name))
				{
					$upDeliveryData->city_name = $upCitiesEntity->col('name')->findOne(['ref' => $upDeliveryData->city_id]);
				}

				$defaultData['ukrposhta_region'] = $upRegionsEntity->col('name')->findOne(['region_id' => $upDeliveryData->region_id]);
				$defaultData['ukrposhta_district'] = $upDistrictsEntity->col('name')->findOne(['district_id' => $upDeliveryData->district_id]);
				$defaultData['ukrposhta_city'] = $upCitiesEntity->col('name')->findOne(['city_id' => $upDeliveryData->city_id]);
				$defaultData['ukrposhta_office'] = $upOfficesEntity->col('name')->findOne(['office_id' => $upDeliveryData->office_id]);
			}
		}

		return ExtenderFacade::execute(__METHOD__, $defaultData, func_get_args());
	}

	/**
	 * @param $in
	 * @param $order
	 *
	 * @return mixed|void|null
	 * @throws \Exception Добавляем данные по доставке, для этого заказа
	 */
	public function setCartDeliveryDataProcedure($in, $order)
	{
		if($this->request->post('is_ukrposhta_delivery', 'boolean'))
		{
			/** @var UPDeliveryDataEntity $upDeliveryDataEntity */
			$upDeliveryDataEntity = $this->entityFactory->get(UPDeliveryDataEntity::class);
			$deliveryData = new \stdClass();

			$deliveryData->order_id = $order->id;
			$deliveryData->region_id = $this->request->post('ukrposhta_delivery_region_id');
			$deliveryData->district_id = $this->request->post('ukrposhta_delivery_district_id');
			$deliveryData->city_id = $this->request->post('ukrposhta_delivery_city_id');
			$deliveryData->office_id = $this->request->post('ukrposhta_delivery_office_id');
			$deliveryData->redelivery = $this->request->post('ukrposhta_redelivery');

			$addId = $upDeliveryDataEntity->add($deliveryData);

			return ExtenderFacade::execute(__METHOD__, [$addId, $deliveryData], func_get_args());
		}
	}

	/**
	 * @param $error
	 *
	 * @return null|string
	 */
	public function getCartValidateError($error)
	{
		if(is_null($error) && $this->request->post('is_ukrposhta_delivery', 'boolean'))
		{
			if(empty($this->request->post('ukrposhta_region')))
			{
				$error = $this->frontTranslations->getTranslation('up_cart_error_region');
			} elseif(empty($this->request->post('ukrposhta_district')))
			{
				$error = $this->frontTranslations->getTranslation('up_cart_error_district');
			} elseif(empty($this->request->post('ukrposhta_city')))
			{
				$error = $this->frontTranslations->getTranslation('up_cart_error_city');
			} elseif(empty($this->request->post('ukrposhta_office')))
			{
				$error = $this->frontTranslations->getTranslation('up_cart_error_office');
			}
		}

		return ExtenderFacade::execute(__METHOD__, $error, func_get_args());
	}
}