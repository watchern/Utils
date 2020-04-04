<?php
/**
 * Created by PhpStorm.
 * @author  charles <watchern@126.com>
 */

namespace Charles\Utils\Facades;

use Charles\Utils\Results as ResultsEntity;

class Results extends Facade
{
    /**
     * @return  ResultsEntity
     */
    protected static function getFacadeAccessor()
    {
        return \Charles\Utils\Results::class;
    }
}
