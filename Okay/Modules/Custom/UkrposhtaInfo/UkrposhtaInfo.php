<?php

namespace Okay\Modules\Custom\UkrposhtaInfo;

use Exception;
use Okay\Core\EntityFactory;
use Okay\Core\Languages;
use Okay\Core\Settings;
use Okay\Entities\LanguagesEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPCitiesEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPDistrictsEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPOfficesEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPRegionsEntity;

class UkrposhtaInfo
{
	private $apiKey;

	/** @var Settings */
	private $settings;

	/** @var EntityFactory */
	private $entityFactory;

	/** @var Languages */
	private $languages;

	private $cacheLifetime;

	public function __construct(Settings $settings, EntityFactory $entityFactory, Languages $languages)
	{
		$this->entityFactory = $entityFactory;
		$this->apiKey = $settings->get('ukrpost_api_brearer');
		$this->settings = $settings;
		$this->languages = $languages;

		$cacheLifetime = $settings->get('up_cache_lifetime');
		$this->cacheLifetime = !empty($cacheLifetime) ? $cacheLifetime : 86400;
	}

	/**
	 * Выборка отделений Укр Почты
	 *
	 * @param  int  $regionId  id области
	 * @param  int  $districtId  id района
	 * @param  int  $cityId  id города
	 * @param  int  $postOfficeId  id отделения Укр Почты
	 *
	 * @return array|false
	 * @throws Exception
	 */
	public function getPostOffices($regionId = '', $districtId = '', $cityId = '', $postOfficeId = '')
	{
		if(!$regionId || !$districtId || !$cityId)
		{
			return false;
		}

		$officesEntity = $this->entityFactory->get(UPOfficesEntity::class);

		$filter = [
			'region_id' => $regionId,
			'district_id' => $districtId,
			'city_id' => $cityId,
		];

		// Если таблица пунктов пуста, спарсим все пункты
		if(!$officesEntity->count())
		{
			$this->parsePostOfficesAndCitiesToCache();
		}

		$postOffices = $officesEntity->find($filter);

		$result['success'] = true;
		$result['offices'] = '<option'.(!$postOfficeId ? ' selected' : '').' disabled value="">Выберите отделение доставки</option>';
		foreach($postOffices as $postOffice)
		{
			$result['offices'] .= '<option value="'.$postOffice->office_id.'" data-region_id="'.$postOffice->region_id.'" data-office_id="'.$postOffice->office_id.'"'.($postOfficeId && $postOfficeId == $postOffice->office_id ? 'selected' : '').'>'.$postOffice->description.'</option>';
		}

		return $result;
	}

	/**
	 * Выборка городов Укр Почты
	 *
	 * @param  string  $selectedCity  id города Укр Почты
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getCities($selectedCity = '')
	{
		$citiesEntity = $this->entityFactory->get(UPCitiesEntity::class);

		if(!$cities = $citiesEntity->find())
		{
			$cities = $this->parsePostOfficesAndCitiesToCache();
		}

		// Если включено автообновление городов и уже пора их обновить, тогда обновляем
		if($this->settings->get('up_auto_update_data') && (int)$this->settings->get('up_last_update_cities_date') + $this->cacheLifetime < time())
		{
			$cities = $this->parsePostOfficesAndCitiesToCache();
		}

		$result['success'] = true;
		$result['cities'] = '<option value=""></option>';
		foreach($cities as $city)
		{
			$result['cities'] .= '<option value="'.$city->name.'" data-city_ref="'.$city->ref.'" '.(!empty($selectedCity) && $selectedCity == $city->ref ? 'selected' : '').'>'.$city->name.'</option>';
		}

		return $result;
	}

	/**
	 * Метод сохраняет области в базу данных (локальный кеш)
	 *
	 * @return array|false
	 * @throws Exception
	 */
	public function parseRegionsToCache()
	{
		$regions = $this->upRequest([], 'get_regions_by_region_ua');
		if(!empty($regions))
		{
			$regionsEntity = $this->entityFactory->get(UPRegionsEntity::class);
			$currentRegions = $regionsEntity->mappedBy('region_id')->noLimit()->find();
			$currentRegionsIds = [];
			foreach($currentRegions as $currentRegion)
			{
				$currentRegionsIds[$currentRegion->region_id] = $currentRegion->id;
			}
			foreach($regions as $region)
			{
				unset($currentRegionsIds[$region->REGION_ID]);
				if(!isset($currentRegions[$region->REGION_ID]))
				{
					$newRegion = (object)[
						'name' => $region->REGION_UA,
						'region_id' => $region->REGION_ID,
					];
					$newRegion->id = $regionsEntity->add($newRegion);
					$currentRegions[$newRegion->region_id] = $newRegion;
				} else
				{
					$updateRegion = $currentRegions[$region->REGION_ID];
					$updateRegion->name = $region->REGION_UA;
					$updateRegion->region_id = $region->REGION_ID;
					$regionsEntity->update($updateRegion->id, $updateRegion);
				}
			}

			// Удаляет области которые не пришли
			if(!empty($currentRegionsIds))
			{
				$regionsEntity->delete($currentRegionsIds);
			}

			$this->settings->set('up_last_update_regions_date', time());

			return $currentRegions;
		} else
		{
			return false;
		}
	}

	/**
	 * Метод сохраняет районы в базу данных (локальный кеш)
	 *
	 * @return array|false
	 * @throws Exception
	 */
	public function parseDistrictsToCache()
	{
		$districts = $this->upRequest([], 'get_districts_by_region_id_and_district_ua');
		if(!empty($districts))
		{
			$districtEntity = $this->entityFactory->get(UPDistrictsEntity::class);
			$currentDistricts = $districtEntity->mappedBy('district_id')->noLimit()->find();
			$currentDistrictsIds = [];
			foreach($currentDistricts as $currentDistrict)
			{
				$currentDistrictsIds[$currentDistrict->district_id] = $currentDistrict->id;
			}
			foreach($districts as $district)
			{
				unset($currentDistrictsIds[$district->DISTRICT_ID]);
				if(!isset($currentDistricts[$district->DISTRICT_ID]))
				{
					$newDistrict = (object)[
						'name' => $district->DISTRICT_UA,
						'region_id' => $district->REGION_ID,
						'district_id' => $district->DISTRICT_ID,
					];
					$newDistrict->id = $districtEntity->add($newDistrict);
					$currentDistricts[$newDistrict->district_id] = $newDistrict;
				} else
				{
					$updateDistrict = $currentDistricts[$district->DISTRICT_ID];
					$updateDistrict->name = $district->DISTRICT_UA;
					$updateDistrict->region_id = $district->REGION_ID;
					$updateDistrict->district_id = $district->DISTRICT_ID;
					$districtEntity->update($updateDistrict->id, $updateDistrict);
				}
			}

			// Удаляет районы, которые не пришли
			if(!empty($currentDistrictsIds))
			{
				$districtEntity->delete($currentDistrictsIds);
			}

			$this->settings->set('up_last_update_districts_date', time());

			return $currentDistricts;
		} else
		{
			return false;
		}
	}

	/**
	 * Метод сохраняет населенные пункты в базу данных (локальный кеш)
	 *
	 * @return array|false
	 * @throws Exception
	 */
	public function parseCitiesToCache()
	{
		$currentLangId = $this->languages->getLangId();
		$currentLangLabel = $this->languages->getLangLabel();

		$languagesEntity = $this->entityFactory->get(LanguagesEntity::class);
		$districtsEntity = $this->entityFactory->get(UPDistrictsEntity::class);
		$cityEntity = $this->entityFactory->get(UPCitiesEntity::class);

		$defaultLanguage = $languagesEntity->findOne(['label' => 'ua']);
		$languages = $languagesEntity->find();
		$districts = $districtsEntity->find();
		$currentCities = [];
		//		foreach($districts as $district)
		//		{
		$cities = $this->upRequest([], 'get_city_by_region_id_and_district_id_and_city_ua');
		if(!empty($cities))
		{
			$currentCities = $cityEntity->noLimit()->find();
			$currentCitiesIds = [];
			foreach($currentCities as $currentCity)
			{
				$currentCitiesIds[$currentCity->city_id] = $currentCity->id;
			}
			foreach($cities as $city)
			{
				unset($currentCitiesIds[$city->CITY_ID]);
				if(!isset($currentCities[$city->CITY_ID]))
				{
					$newCity = (object)[
						'name' => $city->{'CITY_'.strtoupper($currentLangLabel)} != '0' ?
							$city->{'CITY_'.strtoupper($currentLangLabel)} :
							$city->{'CITY_'.strtoupper($defaultLanguage->label)},
						'region_id' => $city->REGION_ID,
						'district_id' => $city->DISTRICT_ID,
						'city_id' => $city->CITY_ID,
					];
					$newCity->id = $cityEntity->add($newCity);
					$currentCities[$newCity->city_id] = $newCity;

					foreach($languages as $lang)
					{
						if($lang->id == $currentLangId)
						{
							continue;
						}
						$this->languages->setLangId($lang->id);

						$newCity->name = $city->{'CITY_'.strtoupper($lang->label)} != '0' ?
							$city->{'CITY_'.strtoupper($lang->label)} :
							$city->{'CITY_'.strtoupper($defaultLanguage->label)};
						$cityEntity->update($newCity->id, $newCity);
					}
				} else
				{
					foreach($languages as $lang)
					{
						$this->languages->setLangId($lang->id);
						$updateCity = $currentCities[$city->CITY_ID];

						$updateCity->name = $city->{'CITY_'.strtoupper($lang->label)} != '0' ?
							$city->{'CITY_'.strtoupper($lang->label)} :
							$city->{'CITY_'.strtoupper($defaultLanguage->label)};

						$cityEntity->update($updateCity->id, $updateCity);
					}
					$updateCity = $currentCities[$city->CITY_ID];
					$updateCity->region_id = $city->REGION_ID;
					$updateCity->district_id = $city->DISTRICT_ID;
					$updateCity->city_id = $city->CITY_ID;
					$cityEntity->update($updateCity->id, $updateCity);
				}
				$this->languages->setLangId($currentLangId);
			}

			// Удаляет населенные пункты которые не пришли
			if(!empty($currentCitiesIds))
			{
				$cityEntity->delete($currentCitiesIds);
			}

			$this->settings->set('up_last_update_cities_date', time());
		}

		//		}

		return !empty($currentCities) ? $currentCities : false;
	}

	/**
	 * Метод сохраняет отделения в базу данных (локальный кеш)
	 *
	 * @return array|false
	 * @throws Exception
	 */
	public function parsePostOfficesAndCitiesToCache()
	{
		$offices = $this->upRequest([], 'get_postoffices_by_city_id');
		if(!empty($offices))
		{
			$officesEntity = $this->entityFactory->get(UPOfficesEntity::class);
			$currentOffices = $officesEntity->mappedBy('office_id')->noLimit()->find();
			$currentOfficesIds = [];
			foreach($currentOffices as $currentOffice)
			{
				$currentOfficesIds[$currentOffice->office_id] = $currentOffice->id;
			}

			$citiesEntity = $this->entityFactory->get(UPCitiesEntity::class);
			$currentCities = $citiesEntity->mappedBy('city_id')->noLimit()->find();
			$currentCitiesIds = [];
			foreach($currentCities as $currentCity)
			{
				$currentCitiesIds[$currentCity->city_id] = $currentCity->id;
			}

			foreach($offices as $office)
			{
				unset($currentOfficesIds[$office->ID]);
				unset($currentCitiesIds[$office->CITY_ID]);
				if($office->LOCK_CODE > 0)
				{
					continue;
				}

				if(!isset($currentOffices[$office->ID]))
				{
					$newPostOffice = (object)[
						'region_id' => $office->REGION_ID,
						'district_id' => $office->DISTRICT_ID,
						'city_id' => $office->CITY_ID,
						'office_id' => $office->ID,
						'post_index' => $office->POSTINDEX,
						'description' => $office->PO_LONG,
						'short_description' => $office->PO_SHORT,
						'address' => $office->ADDRESS,
					];
					$newPostOffice->id = $officesEntity->add($newPostOffice);
					$currentOffices[$newPostOffice->office_id] = $newPostOffice;
				} else
				{
					$updatePostOffice = $currentOffices[$office->ID];
					$updatePostOffice->region_id = $office->REGION_ID;
					$updatePostOffice->district_id = $office->DISTRICT_ID;
					$updatePostOffice->city_id = $office->CITY_ID;
					$updatePostOffice->office_id = $office->ID;
					$updatePostOffice->post_index = $office->POSTINDEX;
					$updatePostOffice->description = $office->PO_LONG;
					$updatePostOffice->short_description = $office->PO_SHORT;
					$updatePostOffice->address = $office->ADDRESS;
					$officesEntity->update($updatePostOffice->id, $updatePostOffice);
				}

				if(!isset($currentCities[$office->CITY_ID]))
				{
					$newCity = (object)[
						'name' => $office->CITY_UA,
						'region_id' => $office->REGION_ID,
						'district_id' => $office->DISTRICT_ID,
						'city_id' => $office->CITY_ID,
					];
					$newCity->id = $citiesEntity->add($newCity);
					$currentCities[$newCity->city_id] = $newCity;
				} else
				{
					$updateCity = $currentCities[$office->CITY_ID];
					$updateCity->name = $office->CITY_UA;
					$updateCity->region_id = $office->REGION_ID;
					$updateCity->district_id = $office->DISTRICT_ID;
					$updateCity->city_id = $office->CITY_ID;
					$citiesEntity->update($updateCity->id, $updateCity);
				}
			}

			// Удаляет населенные пункты, которые не пришли
			if(!empty($currentCitiesIds))
			{
				$citiesEntity->delete($currentCitiesIds);
			}

			// Удаляет отделения, которые не пришли
			if(!empty($currentOfficesIds))
			{
				$officesEntity->delete($currentOfficesIds);
			}

			$this->settings->set('up_last_update_post_offices_date', time());

			return $currentOffices;
		} else
		{
			return false;
		}
	}

	/**
	 * @param  string  $request  json параметры запроса
	 *
	 * @return bool|mixed
	 */
	public function upRequest($request = [], $endpoint = '', $type = 'get')
	{
		if(empty($endpoint) || !$this->apiKey)
		{
			return false;
		}

		$link = 'https://www.ukrposhta.ua/address-classifier-ws/'.$endpoint;
		if($type == 'get' && !empty($request))
		{
			$link .= '?'.http_build_query($request);
		}

		$headers = [
			'Accept: application/json',
			'Authorization: Bearer '.$this->apiKey,
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $link);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		switch($type)
		{
			case 'post':
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
				break;
			case 'get':
				curl_setopt($ch, CURLOPT_HTTPGET, 1);
				break;
			case 'put':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				break;
			case 'delete':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;
		}
		$response = curl_exec($ch);
		curl_close($ch);

		return json_decode($response)->Entries->Entry;
	}

}