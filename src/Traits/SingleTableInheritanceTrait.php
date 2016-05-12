<?php

namespace SamIT\Yii2\Traits;

use SamIT\Yii2\Queries\SingleTableInheritanceQuery;

trait SingleTableInheritanceTrait
{
    /**
     * Returns an array with keys 'map' and 'column'
     * where 'map' contains a map of type => class
     * and 'column' contains the column name
     * [
     *      'map' => [
     *          \app\Car::class => 'car'
     *      ],
     *      'column' => 'type'
     * ]
     * This function should return the same map every time
     * @return array
     */
    protected static function inheritanceConfig() {
        throw new \Exception('Inheritance config must be implemented');
    }

    abstract public function setAttribute($name, $value);

    final protected static function getTypeFromClass($class)
    {
        static $cache;
        if(!isset($cache)) {
            $cache = self::inheritanceConfig()['map'];
        }

        if($class === __CLASS__) {
            return null;
        }

        if(!array_key_exists($class, $cache)
            && false !== $class = get_parent_class($class)) {
            return self::getTypeFromClass($class);
        }

        return $cache[$class];
    }

    final protected static function getClassFromType($type)
    {
        static $cache;
        if(!isset($cache)) {
            $cache = array_flip(self::inheritanceConfig()['map']);
        }

        if($type === null) {
            return __CLASS__;
        }

        return $cache[$type];
    }

    final protected static function getInheritanceColumn()
    {
        static $cache;
        if(!isset($cache)) {
            $cache = self::inheritanceConfig()['column'];
        }
        return $cache;
    }

    public function init()
    {
        $this->initSingleTableInheritance();
        parent::init();
    }

    protected function initSingleTableInheritance()
    {
        $this->setAttribute(self::getInheritanceColumn(), self::getTypeFromClass(static::class));
    }

    public static function instantiate($row)
    {
        return self::instantiateSingleTableInheritance($row);
    }

    protected static function instantiateSingleTableInheritance($row)
    {
        $class = self::getClassFromType($row[self::getInheritanceColumn()]);
        return new $class;
    }

    public static function find()
    {
        return self::findSingleTableInheritance();
    }

    protected static function findSingleTableInheritance()
    {
        return new SingleTableInheritanceQuery(static::class, ['type' => self::getTypeFromClass(static::class)]);
    }
}