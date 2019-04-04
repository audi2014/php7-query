<?php
/**
 * Created by PhpStorm.
 * User: arturmich
 * Date: 2/1/19
 * Time: 10:28 AM
 */

namespace Audi2014\RequestQuery;
use Throwable;

class QueryException extends \Exception {
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}