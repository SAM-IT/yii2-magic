<?php

namespace SamIT\Yii2\Traits;

trait SingleTableInheritanceQueryTrait
{
    public $type;

    public function prepare($builder)
    {
        $modelClass = $this->modelClass;
        $column = $modelClass::getInheritanceColumn();
        if ($this->type !== null) {
            $this->andFilterWhere([$column => $this->type]);
        }
        return parent::prepare($builder);
    }
}