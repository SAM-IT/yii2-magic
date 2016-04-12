<?php

namespace SamIT\Yii2\Traits;

use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;
use yii\web\View;

/**
 * Trait that adds unsafe class to attributes that are not safe in a configuration.
 * If that still is the desired behaviour, 'allowUnsafe' can be added
 */
trait HighlightUnsafeAttributesTrait
{
    public function init()
    {
        parent::init();
        $this->initHighlight();
    }

    protected function initHighlight()
    {
        if(YII_DEBUG) {
            $safeAttributes = [];
            if(isset($this->model)) {
                if ($this->model instanceof Model) {
                    $safeAttributes = array_flip($this->model->safeAttributes());
                } else {
                    throw new InvalidConfigException('Model must be instance of ' . Model::class);
                }
            }
            
            if(!(isset($this->attributes) && is_array($this->attributes))) {
                throw new InvalidConfigException('Attributes must be an array');
            }

            $this->getView()->registerCss(<<<CSS
.unsafe:not(.unsafe-ok) {
    border: 3px solid red;
}
CSS
            );

            foreach ($this->attributes as $attribute => &$config) {
                if (!isset($safeAttributes[$attribute]) && !(isset($config['allowUnsafe']) && $config['allowUnsafe'])) {
                    if (!isset($config['options'])) {
                        $config['options'] = [];
                    }
                    Html::addCssClass($config['options'], 'unsafe');
                }

            }
        }
    }

    /**
     * @return View
     */
    abstract public function getView();
}