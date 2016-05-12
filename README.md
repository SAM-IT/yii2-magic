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
