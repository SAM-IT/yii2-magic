<?php

namespace SamIT\Yii2\Traits;

trait SingleTableInheritanceQueryTrait
{
    public $type;

    public function prepare($builder)
    {
        if ($this->type !== null) {
            $this->andFilterWhere(['type' => $this->type]);
        }
        return parent::prepare($builder);
    }
}