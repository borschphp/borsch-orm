<?php
/**
 * @author debuss-a
 */

namespace Borsch\ORM;

use Borsch\Db\Db;
use Closure;
use InvalidArgumentException;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Predicate\PredicateInterface;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Where;
use Laminas\Hydrator\ReflectionHydrator;
use ReflectionClass;

/**
 * Class QueryBuilder
 * @package Borsch\ORM
 */
class QueryBuilder
{

    /** @var Adapter */
    protected $adapter;

    /** @var string */
    protected $table;

    /** @var Sql */
    protected $sql;

    /** @var Select|Insert|Update|Delete */
    protected $task;

    /** @var string */
    protected $connection = 'default';

    /**
     * QueryBuilder constructor.
     */
    public function __construct()
    {
        $this->adapter = Db::getAdapter($this->connection);
        $this->table = $this->getTable();
        $this->sql = new Sql($this->adapter, $this->table);
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        if (!$this->table) {
            $this->table = strtolower(
                (new ReflectionClass(static::class))->getShortName()
            );
        }

        return $this->table;
    }

    /**
     * @return ResultSetInterface|null
     */
    protected function getQueryResultSet(): ?ResultSetInterface
    {
        $result = $this->sql->prepareStatementForSqlObject($this->task)->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $result_set = new HydratingResultSet(new ReflectionHydrator(), new static());
            $result_set->initialize($result);

            return $result_set;
        }

        return null;
    }

    /**
     * @return ResultSetInterface|null
     */
    public static function all(): ?ResultSetInterface
    {
        $instance = new static();
        $instance->task = $instance->sql->select();

        return $instance->get();
    }

    /**
     * @param  Where|Closure|string|array|PredicateInterface $predicate
     * @param  string $combination How to combine $predicate values (AND or OR)
     * @return static
     */
    public static function where($predicate, string $combination = 'AND')
    {
        $combination = strtoupper($combination);

        if (!in_array($combination, ['AND', 'OR'])) {
            throw new InvalidArgumentException(sprintf(
                'Invalid $combination provided, must be one of "AND" or "OR", "%s" given...',
                $combination
            ));
        }

        $instance = new static();
        $instance->task = $instance->sql->select()->where($predicate, $combination);

        return $instance;
    }

    /**
     * @return ResultSetInterface|null
     */
    public function get(): ?ResultSetInterface
    {
        return $this->getQueryResultSet();
    }

    /**
     * @return static|null
     */
    public function first()
    {
        return $this->getQueryResultSet()->current() ?? null;
    }

    /**
     * @return static|null
     */
    public function last()
    {
        $last = null;

        $result = $this->getQueryResultSet();
        while ($result->valid()) {
            $last = $result->current();
            $result->next();
        }

        return $last;
    }
}
