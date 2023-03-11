<?php

namespace Okay\Modules\Custom\UkrposhtaInfo\Entities;

use Okay\Core\Entity\Entity;

class UPDistrictsEntity extends Entity
{
    protected static $fields = [
        'id',
        'name',
        'region_id',
        'district_id',
    ];

    protected static $table = 'custom__up_districts';
    protected static $tableAlias = 'upd';
    
    protected static $defaultOrderFields = [
        'district_id'
    ];
    
    protected static $searchFields = [
        'name',
        'district_id'
    ];
    
    public function filter__keyword($keywords)
    {
        $keywords = (array)$keywords;

        $tableAlias = $this->getTableAlias();
        $langAlias = $this->lang->getLangAlias(
            $this->getTableAlias()
        );

        $fields = $this->getFields();
        $langFields = $this->getLangFields();

        $searchFields = $this->getSearchFields();
        foreach ($keywords as $keyNum=>$keyword) {
            $keywordFilter = [];
            foreach ($searchFields as $searchField) {
                $searchFieldWithAlias = $searchField;

                if (in_array($searchField, $fields)) {
                    $searchFieldWithAlias = $tableAlias . "." . $searchField;
                } elseif (in_array($searchField, $langFields)) {
                    $searchFieldWithAlias = $langAlias . "." . $searchField;
                }

                $keywordFilter[] = $searchFieldWithAlias . " LIKE :auto_keyword_{$searchField}_{$keyNum}";
                $this->select->bindValue("auto_keyword_{$searchField}_{$keyNum}", $keyword . '%');
            }
            $this->select->where('(' . implode(' OR ', $keywordFilter) . ')');

        }
    }
}