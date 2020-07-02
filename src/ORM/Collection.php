<?php
/**
 * @author debuss-a
 */

namespace Borsch\ORM;

use Laminas\Db\ResultSet\ResultSet;

/**
 * Class Collection
 * @package Borsch\ORM
 */
class Collection extends ResultSet
{

    /**
     * @return Model|null
     */
    public function first()
    {
        /** @var Model $current */
        $current = $this->current();

        return $current ?? null;
    }
}
