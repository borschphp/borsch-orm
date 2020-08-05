<?php
/**
 * @author debuss-a
 */

namespace Borsch\ORM;

use Laminas\Db\Metadata\Source\Factory;
use Laminas\Db\Sql\Ddl\Column\Column;

/**
 * Class Model
 * @package Borsch\ORM
 */
class Model extends QueryBuilder
{

    protected $id;
    protected $created_at;
    protected $updated_at;

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $value = $this->{$name} ?? null;
        $accessor_method_name = 'get'.ucfirst(str_replace('_', '', $name)).'Property';

        if (method_exists($this, $accessor_method_name)) {
            return $this->{$accessor_method_name}($value);
        }

        return $value;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value): void
    {
        $mutator_method_name = 'set'.ucfirst(str_replace('_', '', $name)).'Property';
        if (method_exists($this, $mutator_method_name)) {
            $this->{$mutator_method_name}($value);
            return;
        }

        $this->{$name} = $value;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function save(): bool
    {
        $data = $this->getColumnsData();

        if ($this->id) {
            return $this->update($data);
        }

        return $this->insert($data);
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        if (!$this->id) {
            return true;
        }

        $this->task = $this->sql->delete()->where([
            'id' => $this->id
        ]);

        return (bool)$this->sql
            ->prepareStatementForSqlObject($this->task)
            ->execute()
            ->getAffectedRows();
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function update(array $data): bool
    {
        $this->task = $this->sql->update()
            ->set($data)
            ->where([
                'id' => $this->id
            ]);

        return (bool)$this->sql
            ->prepareStatementForSqlObject($this->task)
            ->execute()
            ->getAffectedRows();
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function insert(array $data): bool
    {
        $this->task = $this->sql->insert()
            ->columns(array_keys($data))
            ->values($data);

        return (bool)$this->sql
            ->prepareStatementForSqlObject($this->task)
            ->execute()
            ->getGeneratedValue();
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getColumnsData(): array
    {
        $metadata = Factory::createSourceFromAdapter($this->adapter);
        $columns = array_reduce($metadata->getTable($this->getTable())->getColumns(), function ($carry, $column) {
            /** @var Column $column */
            $carry[$column->getName()] = $column->getDataType();

            return $carry;
        }, []);

        $data = [];
        foreach ($columns as $property => $type) {
            // TODO Use $type to cast values to the appropriate type.
            $data[$property] = $this->{$property} ?? null;
        }

        return $data;
    }
}
