<?php

namespace SamIT\Yii2\Traits;

use yii\base\InlineAction;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;

/**
 * Trait that implements dependency injection for controller actions.
 * It should be bound only to subclasses of controller.
 */
trait ActionInjectionTrait
{
    /**
     * @see https://github.com/yiisoft/yii2/issues/9476
     * @inheritdoc
     */
    public function bindActionParams($action, $params)
    {

        if ($action instanceof InlineAction) {
            $callable = [$this, $action->actionMethod];
        } else {
            $callable = [$action, 'run'];
        }

        $actionParams = [];
        try {
            $args = \Yii::$container->resolveCallableDependencies($callable, $params);
        } catch (InvalidConfigException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        foreach ((new \ReflectionMethod($callable[0], $callable[1]))->getParameters() as $i => $param) {
            $actionParams[$param->getName()] = $args[$i];
        }

        if (property_exists($this, 'actionParams')) {
            $this->actionParams = $actionParams;
        }
        
        // Dont put injected  params in requestedParams, this breaks the debugger.
        foreach($actionParams as $key => $value) {
            if (is_object($value)) {
                if ($this->module->has($key, true)
                    && $value === $this->module->get($key)
                ) {
                    $value = "Component: $key";
                } else {
                    $value = "DI: " .get_class($value);
                }
            }
            \Yii::$app->requestedParams[$key] = $value;
        }
        return $args;
    }
}