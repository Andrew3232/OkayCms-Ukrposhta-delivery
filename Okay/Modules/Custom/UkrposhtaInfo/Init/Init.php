<?php

namespace Okay\Modules\Custom\UkrposhtaInfo\Init;

use Okay\Admin\Helpers\BackendExportHelper;
use Okay\Admin\Helpers\BackendImportHelper;
use Okay\Admin\Helpers\BackendOrdersHelper;
use Okay\Admin\Requests\BackendProductsRequest;
use Okay\Core\EntityFactory;
use Okay\Core\Modules\AbstractInit;
use Okay\Core\Modules\EntityField;
use Okay\Core\Scheduler\Schedule;
use Okay\Core\ServiceLocator;
use Okay\Core\Settings;
use Okay\Entities\PaymentsEntity;
use Okay\Entities\VariantsEntity;
use Okay\Helpers\CartHelper;
use Okay\Helpers\DeliveriesHelper;
use Okay\Helpers\OrdersHelper;
use Okay\Helpers\ValidateHelper;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPCitiesEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPDeliveryDataEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPDistrictsEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPOfficesEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPRegionsEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Extenders\BackendExtender;
use Okay\Modules\Custom\UkrposhtaInfo\Extenders\FrontExtender;
use Okay\Modules\Custom\UkrposhtaInfo\UkrposhtaInfo;

class Init extends AbstractInit
{
	const CASH_ON_DELIVERY = 'ukrposhta_info__cash_on_delivery';

	public function install()
	{
		$this->setModuleType(MODULE_TYPE_DELIVERY);
		$this->setBackendMainController('UkrposhtaInfoAdmin');

		$this->migrateEntityTable(UPDeliveryDataEntity::class, [
			(new EntityField('id'))->setIndexPrimaryKey()->setTypeInt(11, false)->setAutoIncrement(),
			(new EntityField('order_id'))->setTypeInt(11)->setIndex(),
			(new EntityField('region_id'))->setTypeVarchar(255, true),
			(new EntityField('district_id'))->setTypeVarchar(255, true),
			(new EntityField('city_id'))->setTypeVarchar(255, true),
			(new EntityField('office_id'))->setTypeVarchar(255, true),
			(new EntityField('redelivery'))->setTypeTinyInt(1, true),
		]);

		$this->migrateEntityTable(UPRegionsEntity::class, [
			(new EntityField('id'))->setIndexPrimaryKey()->setTypeInt(11, false)->setAutoIncrement(),
			(new EntityField('name'))->setTypeVarchar(255, true)->setIndex(100),
			(new EntityField('region_id'))->setTypeInt(10)->setIndex(),
		]);

		$this->migrateEntityTable(UPDistrictsEntity::class, [
			(new EntityField('id'))->setIndexPrimaryKey()->setTypeInt(11, false)->setAutoIncrement(),
			(new EntityField('name'))->setTypeVarchar(255, true)->setIndex(100),
			(new EntityField('region_id'))->setTypeInt(10),
			(new EntityField('district_id'))->setTypeInt(10)->setIndex(),
		]);

		$this->migrateEntityTable(UPCitiesEntity::class, [
			(new EntityField('id'))->setIndexPrimaryKey()->setTypeInt(11, false)->setAutoIncrement(),
			(new EntityField('name'))->setTypeVarchar(255, true)->setIndex(100),
			(new EntityField('region_id'))->setTypeInt(10),
			(new EntityField('district_id'))->setTypeInt(10),
			(new EntityField('city_id'))->setTypeInt(10)->setIndex(),
		]);

		$this->migrateEntityTable(UPOfficesEntity::class, [
			(new EntityField('id'))->setIndexPrimaryKey()->setTypeInt(11, false)->setAutoIncrement(),
			(new EntityField('region_id'))->setTypeInt(10),
			(new EntityField('district_id'))->setTypeInt(10),
			(new EntityField('city_id'))->setTypeInt(10)->setIndex(),
			(new EntityField('office_id'))->setTypeInt(10)->setIndex(),
			(new EntityField('post_index'))->setTypeVarchar(255)->setIndex(),
			(new EntityField('description'))->setTypeVarchar(255, true),
			(new EntityField('short_description'))->setTypeVarchar(255, true),
			(new EntityField('address'))->setTypeVarchar(255, true),
		]);

		$this->migrateEntityField(PaymentsEntity::class, (new EntityField(self::CASH_ON_DELIVERY))->setTypeTinyInt(1));
	}

	public function init()
	{
		$this->registerEntityField(PaymentsEntity::class, self::CASH_ON_DELIVERY);

		$this->addPermission('custom__ukrposhta_info');

		$this->addBackendBlock('order_contact', 'order_contact_block.tpl');
		$this->addFrontBlock('front_cart_delivery', 'front_cart_delivery_block.tpl');
		$this->addFrontBlock('front_scripts_after_validate', 'validation.js');

		$this->registerChainExtension(
			[CartHelper::class, 'getDefaultCartData'],
			[FrontExtender::class, 'getDefaultCartData']
		);

		$this->registerChainExtension(
			[DeliveriesHelper::class, 'getCartDeliveriesList'],
			[FrontExtender::class, 'getCartDeliveriesList']
		);

		$this->registerQueueExtension(
			[OrdersHelper::class, 'finalCreateOrderProcedure'],
			[FrontExtender::class, 'setCartDeliveryDataProcedure']
		);

		$this->registerChainExtension(
			[ValidateHelper::class, 'getCartValidateError'],
			[FrontExtender::class, 'getCartValidateError']
		);

		// В админке в заказе достаём данные по доставке
		$this->registerQueueExtension(
			[BackendOrdersHelper::class, 'findOrder'],
			[BackendExtender::class, 'getDeliveryDataProcedure']
		);

		// В админке в заказе обновляем данные по доставке ???
		$this->registerQueueExtension(
			[BackendOrdersHelper::class, 'executeCustomPost'],
			[BackendExtender::class, 'updateDeliveryDataProcedure']
		);

		$this->registerBackendController('UkrposhtaInfoAdmin');
		$this->addBackendControllerPermission('UkrposhtaInfoAdmin', 'custom__ukrposhta_info');

		$this->registerSchedule(
			(new Schedule([UkrposhtaInfo::class, 'parseRegionsToCache']))
				->name('Parses UP regions to the db cache')
				->time('0 0 * * *')
				->overlap(false)
				->timeout(3600)
		);

		$this->registerSchedule(
			(new Schedule([UkrposhtaInfo::class, 'parseDistrictsToCache']))
				->name('Parses UP districts to the db cache')
				->time('0 0 * * *')
				->overlap(false)
				->timeout(3600)
		);

		$this->registerSchedule(
			(new Schedule([UkrposhtaInfo::class, 'parsePostOfficesAndCitiesToCache']))
				->name('Parses UP post offices to the db cache')
				->time('0 0 * * *')
				->overlap(false)
				->timeout(3600)
		);
	}

}
