<?php

namespace Okay\Modules\Custom\UkrposhtaInfo\Backend\Controllers;

use Okay\Admin\Controllers\IndexAdmin;
use Okay\Entities\PaymentsEntity;
use Okay\Modules\Custom\UkrposhtaInfo\UkrposhtaInfo;

class UkrposhtaInfoAdmin extends IndexAdmin
{
	public function fetch(PaymentsEntity $paymentsEntity, UkrposhtaInfo $ukrposhtaInfo)
	{
		if($this->request->method('POST'))
		{
			$this->settings->set('ukrpost_api_token', $this->request->post('ukrpost_api_token'));
			$this->settings->set('ukrpost_api_brearer', $this->request->post('ukrpost_api_brearer'));
			$this->design->assign('message_success', 'saved');

			$this->settings->set('up_auto_update_data', $this->request->post('up_auto_update_data'));
			$this->settings->set('up_cache_lifetime', $this->request->post('up_cache_lifetime'));

			// Обновляем кеш в мануальном режиме
			if($this->request->post('update_cache'))
			{
				if($this->request->post('update_type') == 'all')
				{
					$ukrposhtaInfo->parseRegionsToCache();
					$ukrposhtaInfo->parseDistrictsToCache();
					$ukrposhtaInfo->parsePostOfficesAndCitiesToCache();
				} elseif($this->request->post('update_type') == 'regions')
				{
					$ukrposhtaInfo->parseRegionsToCache();
				} elseif($this->request->post('update_type') == 'districts')
				{
					$ukrposhtaInfo->parseDistrictsToCache();
				} else
				{
					$ukrposhtaInfo->parsePostOfficesAndCitiesToCache();
				}
			}
		}

		$paymentMethods = $paymentsEntity->find();
		$this->design->assign('payment_methods', $paymentMethods);

		$this->response->setContent($this->design->fetch('ukrposhta_info.tpl'));
	}

}