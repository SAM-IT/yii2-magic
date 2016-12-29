<?php


namespace SamIT\Yii2\Components;

/**
 * Yii2 often uses strings to pass around HTML content.
 * Sometimes you don't want to generate this string until you actually use it.
 * This class helps.
 * Class LazyString
 * @package SamIT\Yii2\Components
 */
class LazyString
{
    private $closure;
    private $value;
    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    function __toString()
    {
        if (!isset($this->value)) {
            // Recursively execute the closure.
            $this->value = $this->closure;
            while ($this->value instanceof \Closure) {
                $this->value = call_user_func($this->value);
            }

            $this->value = (string) $this->value;
            unset($closure);
        }
        return $this->value;
    }


}