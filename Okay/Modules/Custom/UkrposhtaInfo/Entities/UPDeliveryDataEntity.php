<?php

namespace Okay\Modules\Custom\UkrposhtaInfo\Entities;

use Okay\Core\Entity\Entity;

class UPDeliveryDataEntity extends Entity
{
    protected static $fields = [
        'id',
        'order_id',
        'region_id',
        'district_id',
        'city_id',
        'office_id',
        'redelivery',
    ];

    protected static $table = 'custom__up_delivery_data';
    protected static $tableAlias = 'updd';
    
    public function getByOrderId($orderId)
    {
        if (empty($orderId)) {
            return null;
        }

        $this->setUp();

        $filter['order_id'] = (int)$orderId;

        $this->buildFilter($filter);
        $this->select->cols($this->getAllFields());

        $this->db->query($this->select);
        return $this->getResult();
    }
    
}