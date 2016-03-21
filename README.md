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

Currently this depends in on an unstable version of Yii, but when 2.0.7 is released there will be a tagged release of this library pointing to it.