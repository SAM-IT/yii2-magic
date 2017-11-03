[![Latest Stable Version](https://poser.pugx.org/sam-it/yii2-magic/v/stable)](https://packagist.org/packages/sam-it/yii2-magic)
[![Total Downloads](https://poser.pugx.org/sam-it/yii2-magic/downloads)](https://packagist.org/packages/sam-it/yii2-magic)
[![Latest Unstable Version](https://poser.pugx.org/sam-it/yii2-magic/v/unstable)](https://packagist.org/packages/sam-it/yii2-magic)
[![License](https://poser.pugx.org/sam-it/yii2-magic/license)](https://packagist.org/packages/sam-it/yii2-magic)
[![Monthly Downloads](https://poser.pugx.org/sam-it/yii2-magic/d/monthly)](https://packagist.org/packages/sam-it/yii2-magic)
[![Daily Downloads](https://poser.pugx.org/sam-it/yii2-magic/d/daily)](https://packagist.org/packages/sam-it/yii2-magic)

# yii2-magic
Improvements for Yii2 that make it more "magic".

# ActionInjectionTrait
Use this trait in your controller to get dependency injection in controller actions.
````
use \SamIT\Yii2\Traits\ActionInjectionTrait;
````

# HighlightUnsafeAttributesTrait
Use this trait in your form to highlight unsafe attributes.
````
use \SamIT\Yii2\Traits\HighlightUnsafeAttributesTrait;
````

# SingleTableInheritanceTrait
Use this trait in your active record model to implement single table inheritance.
````
use \SamIT\Yii2\Traits\SingleTableInheritanceTrait;

protected static function inheritanceConfig()
{
    return [
        'map' => [
            PartnerProject::class => 'partner'
        ],
        'column' => 'type'
    ];
}
````
This trait uses a different query object. If you use your own `ActiveQuery` implementation, use `SingleTableInheritanceQueryTrait`.
