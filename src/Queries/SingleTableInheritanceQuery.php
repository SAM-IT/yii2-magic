<?php

namespace SamIT\Yii2\Queries;

use SamIT\Yii2\Traits\SingleTableInheritanceQueryTrait;
use yii\db\ActiveQuery;

class SingleTableInheritanceQuery extends ActiveQuery
{
    use SingleTableInheritanceQueryTrait;
}