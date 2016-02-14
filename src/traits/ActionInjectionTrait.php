<?php

namespace SamIT\Yii2\Traits;

use yii\base\InlineAction;
use yii\base\InvalidConfigException;

/**
 * Trait that implements dependency injection for controller actions.
 * It should be bound only to subclasses of controller.
 */
trait ActionInjectionTrait
{
    /**
     * @var array the parameters bound to the current action.
     */
    public $actionParams;

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
        $this->actionParams = $actionParams;
        return $args;
    }
}