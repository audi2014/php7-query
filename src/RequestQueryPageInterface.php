<?php
/**
 * Created by PhpStorm.
 * User: arturmich
 * Date: 2/20/19
 * Time: 11:49 AM
 */

namespace Audi2014\RequestQuery;

interface RequestQueryPageInterface  extends RequestQueryInterface {
    /**
     * @return mixed
     */
    public function getOffset() : int;

    /**
     * @param int $offset
     * @return AbstractRequestQueryPage
     * @throws QueryException
     */
    public function setOffset(int $offset);

    /**
     * @return mixed
     */
    public function getCount(): int;

    /**
     * @param int $count
     * @return AbstractRequestQueryPage
     * @throws QueryException
     */
    public function setCount(int $count);

    /**
     * @param null|string $order_by
     * @return AbstractRequestQueryPage
     */
    public function setOrderBy(?string $order_by = null);

    /**
     * @return string
     */
    public function getOrderBy(): ?string;

    /**
     * @param bool $desc
     * @return AbstractRequestQueryPage
     */
    public function setDesc(bool $desc): AbstractRequestQueryPage;

    /**
     * @return bool
     */
    public function isDesc(): bool;
}