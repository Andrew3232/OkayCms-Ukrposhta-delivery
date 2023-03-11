<?php

namespace Okay\Modules\Custom\UkrposhtaInfo\Controllers;

use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPCitiesEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPDistrictsEntity;
use Okay\Modules\Custom\UkrposhtaInfo\Entities\UPOfficesEntity;
use Okay\Modules\Custom\UkrposhtaInfo\UkrposhtaInfo;

class UkrposhtaInfoSearchController
{
	public function findDistrict(
		Request $request,
		Response $response,
		UPDistrictsEntity $districtsEntity,
		UPOfficesEntity $officesEntity
	){
		$filter['keyword'] = $request->get('query');
		if($request->get('region_id'))
		{
			$filter['region_id'] = $request->get('region_id');
		}
		$filter['limit'] = 20;

		$districts = $districtsEntity->find($filter);

		$suggestions = [];
		if(!empty($districts))
		{
			foreach($districts as $district)
			{
				if(!$officesEntity->find(['district_id' => $district->district_id]))
				{
					continue;
				}
				$suggestion = new \stdClass();

				$suggestion->value = $district->name;
				$suggestion->data = $district;
				$suggestions[] = $suggestion;
			}
		}

		$res = new \stdClass;
		$res->query = $filter['keyword'];
		$res->suggestions = $suggestions;

		$response->setContent(json_encode($res), RESPONSE_JSON);
	}

	public function findCity(Request $request, Response $response,
		UPDistrictsEntity $districtsEntity, UPCitiesEntity $citiesEntity)
	{
		$filter['keyword'] = $request->get('query');
		if($request->get('region_id')){
			$filter['region_id'] = $request->get('region_id');
		}
		if($request->get('district_id')){
			$filter['district_id'] = $request->get('district_id');
		}

		$cities = $citiesEntity->find($filter);

		$suggestions = [];
		if(!empty($cities))
		{
			foreach($cities as $city)
			{
				$suggestion = new \stdClass();
				$district = $districtsEntity->findOne(['district_id' => $city->district_id]);
				$city->district_name = $district->name;
				$suggestion->value = $city->name;
				$suggestion->data = $city;
				$suggestions[] = $suggestion;
			}
		}

		$res = new \stdClass;
		$res->query = $filter['keyword'];
		$res->suggestions = $suggestions;

		$response->setContent(json_encode($res), RESPONSE_JSON);
	}

	public function getPostOffices(Request $request, Response $response, UkrposhtaInfo $ukrposhtaInfo)
	{
		$region = $request->post('region_id');
		$district = $request->post('district_id');
		$city = $request->post('city_id');
		$office = $request->post('office_id');
		$result['city_id'] = $city;
		$result['response'] = $ukrposhtaInfo->getPostOffices($region, $district, $city, $office);

		$response->setContent(json_encode($result), RESPONSE_JSON);
	}
}
