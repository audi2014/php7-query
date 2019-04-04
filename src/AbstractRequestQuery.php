<?php
/**
 * Created by PhpStorm.
 * User: arturmich
 * Date: 2/1/19
 * Time: 10:23 AM
 */

namespace Audi2014\RequestQuery;


abstract class AbstractRequestQuery implements RequestQueryInterface {

    protected $_max_offset;
    protected $_max_count;
    protected $_max_in_count;

    protected $_execute_args = [];

    protected $_SQL_where_string = null;
    protected $_SQL_having_string = null;

    public function __construct(?int $_max_count = 200, int $_max_in_count = 200, ?int $_max_offset = null) {
        $this->_max_offset = $_max_offset;
        $this->_max_count = $_max_count;
        $this->_max_in_count = $_max_in_count;
    }

    /**
     * @param string $key
     * @param string $groupId
     * @param bool $isOrForConditions
     * @param bool $isOrForGroup
     * @param bool $isHaving
     * @param bool $orNullConditionMode
     */
    protected function getConditionGroup(
        string $key,
        string &$groupId,
        bool &$isOrForConditions,
        bool &$isOrForGroup,
        bool &$isHaving,
        bool &$orNullConditionMode
    ): void {
    }

    protected function getQueryConstants(): array {
        return [];
    }

    protected function getNotNullKeys(): array {
        return [];
    }

    protected function getNullKeys(): array {
        return [];
    }

    protected function getEqKeys(): array {
        return [];
    }

    protected function getNotEqKeys(): array {
        return [];
    }

    protected function getInKeys(): array {
        return [];
    }

    protected function getGthEqKeys(): array {
        return [];
    }

    protected function getGthKeys(): array {
        return [];
    }

    protected function getLthKeys(): array {
        return [];
    }

    protected function getLthEqKeys(): array {
        return [];
    }

    protected function getLikeKeys(): array {
        return [];
    }

    public function initFromArray(array $data) {
        foreach ($data as $key => $value) {
            if ($value === null) continue;
            $this->setProp($key, $value);
        }
        return $this;
    }

    public function build() {
        $groupId_data = [];
        foreach ([
                     ['getNullKeys', 'null'],
                     ['getNotNullKeys', 'not null'],
                     ['getEqKeys', '='],
                     ['getNotEqKeys', '<>'],
                     ['getInKeys', 'in'],
                     ['getGthEqKeys', '>='],
                     ['getGthKeys', '>'],
                     ['getLthKeys', '<'],
                     ['getLthEqKeys', '<='],
                     ['getLikeKeys', 'like'],
                 ] as $getter_operator) {
            $keys = $this->{$getter_operator[0]}();

            $this->mapOperatorKeyArray($groupId_data, $getter_operator[1], $keys);
        }

        $this->_SQL_where_string = '';
        $this->_SQL_having_string = '';
        foreach ($groupId_data as $groupId => $data) {
            $conditions = $data['conditions'];
            $orAndForGroup = $data['orAndForGroup'];
            $isHaving = $data['isHaving'];
            $orAndForConditions = $data['orAndForConditions'];
            if (empty($conditions)) continue;
            $conditions_sql = implode(" $orAndForConditions ", $conditions);
            if (empty($this->_SQL_having_string) && $isHaving) {
                $this->_SQL_having_string = "($conditions_sql)";
            } else if (!empty($this->_SQL_having_string) && $isHaving) {
                $this->_SQL_having_string = "{$this->_SQL_having_string} $orAndForGroup ($conditions_sql)";
            } else if (empty($this->_SQL_where_string) && !$isHaving) {
                $this->_SQL_where_string = "($conditions_sql)";
            } else if (!empty($this->_SQL_where_string) && !$isHaving) {
                $this->_SQL_where_string = "{$this->_SQL_where_string} $orAndForGroup ($conditions_sql)";
            }
        }
        if (!empty($this->_SQL_where_string)) $this->_SQL_where_string = "WHERE {$this->_SQL_where_string}";
        if (!empty($this->_SQL_having_string)) $this->_SQL_having_string = "HAVING {$this->_SQL_having_string}";

        return $this;
    }

    public function getExecuteValues(): array {
        return $this->_execute_args;
    }

    public function getOrderBySql(): string {
        return '';
    }

    public function getWhereSql(): string {
        return $this->_SQL_where_string;
    }

    public function getHavingSql(): string {
        return $this->_SQL_having_string;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function setProp(string $key, $value) {
        if (property_exists($this, $key)) {
            $this->{$key} = $value;
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getProp(string $key) {
        return $this->{$key};
    }


    /**
     * @param array $groupId_data
     * @param $operator
     * @param array $keys
     */
    private function mapOperatorKeyArray(array &$groupId_data, $operator, array $keys): void {

        foreach ($keys as $alias => $dbSourceKey) {
            if (!$dbSourceKey) $dbSourceKey = $alias;
            $groupId = '0';
            $isOrForConditions = false;
            $isOrForGroup = false;
            $isHaving = false;
            $orNullConditionMode = false;

            $this->getConditionGroup(
                $alias,
                $groupId,
                $isOrForConditions,
                $isOrForGroup,
                $isHaving,
                $orNullConditionMode
            );

            $groupId = $groupId . "-" . (int)$isHaving;
            if (!isset($groupId_data[$groupId])) {
                $groupId_data[$groupId]['conditions'] = [];
                $groupId_data[$groupId]['orAndForGroup'] = $isOrForGroup ? 'OR' : 'AND';
                $groupId_data[$groupId]['orAndForConditions'] = $isOrForConditions ? 'OR' : 'AND';
                $groupId_data[$groupId]['isHaving'] = $isHaving;
            }
            $this->mapOperatorKey(
                $operator,
                $alias,
                $dbSourceKey,
                $orNullConditionMode,
                $this->_execute_args,
                $groupId_data[$groupId]['conditions']
            );
        }

    }

    /**
     * @param string $operator
     * @param string $key
     * @param string $dbSourceKey
     * @param bool $orNullConditionMode
     * @param array $args
     * @param array $conditions
     */
    private function mapOperatorKey(
        string $operator,
        string $key,
        string $dbSourceKey,
        bool $orNullConditionMode,
        array &$args,
        array &$conditions
    ): void {
        $value = $this->getProp($key);
        if ($value === null) return;

        $newCondition = null;
        $ignore_OR_NULL_COND_MODE = false;

        switch ($operator) {
            case 'in':
                if (is_string($value) && !empty($value)) $value = explode(',', $value);
                if (is_array($value) && count($value) > 0 && count($value) < $this->_max_in_count) {
                    $in_keys = [];
                    foreach ($value as $in_idx => $in_item) {
                        $in_keys[] = ":in_{$key}_{$in_idx}";
                        $args["in_{$key}_{$in_idx}"] = $in_item;
                    }
                    $newCondition = "$dbSourceKey in (" . implode(',', $in_keys) . ")";
                }
                break;
            case '=':
            case '<>':
            case '>':
            case '<':
            case '<=':
            case '>=':
                if (
                    !is_numeric($value)
                    && is_string($value)
                    && isset($this->getQueryConstants()[$value])
                ) {
                    $newCondition = "$dbSourceKey $operator {$this->getQueryConstants()[$value]}";
                } else {
                    $newCondition = "$dbSourceKey $operator :$key";
                    $args[$key] = $value;
                }
                break;
            case 'like':
                $newCondition = "$dbSourceKey $operator :$key";
                $args[$key] = "%$value%";
                break;
            case 'not null':
            case 'null':
                $ignore_OR_NULL_COND_MODE = true;
                if (($operator === 'null' && $value) || ($operator === 'not null' && !$value)) {
                    $newCondition = "$dbSourceKey IS NULL";
                } else {
                    $newCondition = "$dbSourceKey IS NOT NULL";
                }
                break;
            default:
                break;
        }
        if ($newCondition && $orNullConditionMode && !$ignore_OR_NULL_COND_MODE) {
            $conditions[] = "($newCondition OR $dbSourceKey IS NULL)";
        } else if ($newCondition) {
            $conditions[] = $newCondition;
        }
    }

}