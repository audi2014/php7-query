<?php
/**
 * Created by PhpStorm.
 * User: arturmich
 * Date: 2/1/19
 * Time: 10:23 AM
 */

namespace Audi2014\RequestQuery;


abstract class AbstractRequestQueryPage extends AbstractRequestQuery implements RequestQueryPageInterface {

    protected $offset = 0;
    protected $count = 30;
    protected $orderBy = null;
    protected $desc = 0;
    protected $sort = [];
    protected $_SQL_orderBy_string = null;

    /**
     * @return array
     */
    public function getSort(): array {
        return $this->sort;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setSort($value) {
        if (!is_array($value)) {
            $value = json_decode($value);
        }
        $this->sort = $value;
        return $this;
    }

    public function build() {
        parent::build();
        $this->_SQL_orderBy_string = $this->_buildOrderBySql();
        return $this;
    }


    public function getOrderBySql(): string {
        return $this->_SQL_orderBy_string;
    }


    protected function getOrderByKeys(): array {
        return [];
    }


    /**
     * @return mixed
     */
    public function getOffset(): int {
        return $this->offset;
    }

    /**
     * @return string
     */
    public function getOrderBy(): ?string {
        return $this->orderBy;
    }

    /**
     * @param int $offset
     * @return AbstractRequestQueryPage
     * @throws QueryException
     */
    public function setOffset(int $offset) {
        if ($this->_max_offset && $this->_max_offset < $offset) {
            throw new QueryException("max_offset is {$this->_max_offset}");
        } else if ($offset < 0) {
            throw new QueryException("min_offset is 0. `$offset` given");

        }
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCount(): int {
        return $this->count;
    }

    /**
     * @param int $count
     * @return AbstractRequestQueryPage
     * @throws QueryException
     */
    public function setCount(int $count) {
        if ($this->_max_count && $this->_max_count < $count) {
            throw new QueryException("max_count is {$this->_max_count}");
        }
        $this->count = $count;
        return $this;
    }

    /**
     * @param null|string $orderBy
     * @return AbstractRequestQueryPage
     */
    public function setOrderBy(?string $orderBy = null) {
        $this->orderBy = $orderBy;
        return $this;
    }


    /**
     * @param bool $desc
     * @return AbstractRequestQueryPage
     */
    public function setDesc(bool $desc): AbstractRequestQueryPage {
        $this->desc = $desc;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDesc(): bool {
        return $this->desc;
    }

    private function _buildOrderBySql(): string {

        if ($this->orderBy) {
            $this->sort[] = ['key' => $this->orderBy, 'desc' => $this->desc];
        }

        $all_keys = $this->getOrderByKeys();
        $sql = [];
        $i = 0;
        $used_keys = [];
        foreach ($this->sort as $data) {
            if ($i > 10) break;
            $i++;
            $key = $data['key'];
            $desc = $data['desc'];
            $dbSourceKey = $all_keys[$key] ?? null;
            if ($dbSourceKey && !in_array($dbSourceKey, $used_keys)) {
                $sql[] = "$dbSourceKey " . ($desc ? 'DESC' : 'ASC');
                $used_keys[] = $dbSourceKey;
            }
        }
        return empty($sql) ? '' : 'ORDER BY ' . implode(', ', $sql);
    }

}