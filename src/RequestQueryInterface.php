<?php
/**
 * Created by PhpStorm.
 * User: arturmich
 * Date: 2/20/19
 * Time: 10:29 AM
 */

namespace Audi2014\RequestQuery;

interface RequestQueryInterface {
    /**
     * @param array $data
     */
    public function initFromArray(array $data);

    public function getExecuteValues(): array;

    public function getWhereSql(): string;

    public function getHavingSql(): string;

    public function getOrderBySql(): string;
}