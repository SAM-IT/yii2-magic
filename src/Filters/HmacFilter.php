<?php


namespace SamIT\Yii2\Filters;


use yii\base\ActionFilter;
use yii\base\InvalidConfigException;
use yii\helpers\StringHelper;
use yii\web\Request;
use yii\web\UnauthorizedHttpException;

/**
 * Filter that checks for a valid HMAC in the URL.
 * @inheritdoc
 */
class HmacFilter extends ActionFilter
{
    public static $secret = null;

    /**
     * @var bool Whether the current request was secured with an HMAC.
     */
    private $_secure = false;

    /**
     * @var bool Set this to false to disallow URLs with added query parameters.
     */
    public $allowAdditions = true;

    /**
     * @var string The name of the URL param for the HMAC
     */
    public $hmacParam = 'hmac';
    /**
     * @return bool Whether the current request was secured with an HMAC.
     */
    public function isSecure():bool
    {
        return $this->_secure;
    }

    /**
     * Checks the query parameters for a valid HMAC.
     * @param \yii\base\Action $action
     * @return bool
     * @throws UnauthorizedHttpException
     * @throws InvalidConfigException
     */
    public function beforeAction($action)
    {
        /** @var Request $request */
        $request = $action->controller->module->get('request');
        $queryParams = $request->getQueryParams();
        if (empty($queryParams)) {
            return true;
        }
        if (!isset($queryParams[$this->hmacParam])) {
            throw new UnauthorizedHttpException("No HMAC signature found");
        }

        // Verify the HMAC.
        if (!self::verifyHMAC($queryParams)) {
            throw new UnauthorizedHttpException("Invalid HMAC signature");
        }
        $this->_secure = true;
        return true;
    }

    /**
     * @param array $params
     * @return bool
     * @throws InvalidConfigException
     */
    protected function verifyHMAC(array $params):bool
    {
        $hmac = $params[$this->hmacParam];
        $signedParams = [];
        if ($this->allowAdditions && isset($params['params']) && !empty($params['params'])) {
            $signedParams['params'] = $params['params'];
            foreach(explode(',', base64_decode($params['params'])) as $signedParam) {
                $signedParams[$signedParam] = $params[$signedParam] ?? null;
            }
        } else {
            $signedParams = $params;
            unset($signedParams[$this->hmacParam]);
        }

        $calculated = self::calculateHMAC($signedParams);
        return hash_equals($calculated, $hmac);
    }

    /**
     * This adds an HMAC to a list of query params.
     * If
     * @param array $queryParams List of query parameters
     * @param bool $allowAddition Whether to allow extra parameters to be added.
     * @throws InvalidConfigException
     * @return void
     */
    public static function signParams(array &$queryParams, $allowAddition = true): void
    {
        $params = array_keys($queryParams);
        if ($params[0] == '0') {
            unset($params[0]);
        }
        sort($params);
        if ($allowAddition) {
            $queryParams['params'] = strtr(StringHelper::base64UrlEncode(implode(',', $params)), ['=' => '']);
        }

        $queryParams['hmac'] = self::calculateHMAC($queryParams);
        array_unshift($params, implode(',', array_keys($params)));
    }

    /**
     * Calculates the HMAC for a URL.
     * @param array $params A Yii2 route array, the first element is the route the rest are params.
     * @return string The HMAC
     * @throws InvalidConfigException
     */
    public static function calculateHMAC(array $params): string
    {
        if (isset($params[0])) {
            unset($params[0]);
        }

        ksort($params);

        if (!isset(self::$secret)) {
            throw new InvalidConfigException("No secret configured");
        }

        if (self::$secret instanceof \Closure) {
            $secret = call_user_func(self::$secret);
        }

        if (empty($secret)) {
            throw new InvalidConfigException('No URL secret available to sign the request.');
        }

        return substr(hash_hmac('sha256', implode('#', $params), $secret), 1, 16);
    }


}
