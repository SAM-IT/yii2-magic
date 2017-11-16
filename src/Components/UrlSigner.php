<?php


namespace SamIT\Yii2\Components;


use SamIT\Yii2\Secret;
use yii\base\Component;
use yii\helpers\StringHelper;

class UrlSigner extends Component
{
    /**
     * @var string The name of the URL param for the HMAC
     */
    public $hmacParam = 'hmac';

    /**
     * @var string The name of the URL param for the parameters
     */
    public $paramsParam = 'params';

    /**
     * @var Secret
     */
    public $secret;

    public function init()
    {
        parent::init();
        if (!$this->secret instanceof Secret) {
            throw new \Exception("Secret must be set and an instance of SamIT\\Yii2\\Secret");
        }

        $this->secret->lock(1);
    }


    /**
     * Calculates the HMAC for a URL.
     * @param array $params A Yii2 route array, the first element is the route the rest are params.
     * @return string The HMAC
     * @throws \Exception
     */
    public function calculateHMAC(array $params, string $route): string
    {
        if (isset($params[0])) {
            unset($params[0]);
        }

        ksort($params);
        codecept_debug($params);
        codecept_debug($route);

        return substr(hash_hmac('sha256', trim($route, '/') . '|' .  implode('#', $params), $this->secret->getValue()), 1, 16);
    }

    /**
     * This adds an HMAC to a list of query params.
     * If
     * @param array $queryParams List of query parameters
     * @param bool $allowAddition Whether to allow extra parameters to be added.
     * @return void
     * @throws \Exception
     */
    public function signParams(array &$queryParams, $allowAddition = true): void
    {
        if (isset($queryParams[$this->hmacParam])) {
            throw new \Exception("HMAC param is already present");
        }
        $params = array_keys($queryParams);
        $route = $queryParams[0];
        if ($params[0] == '0') {
            unset($params[0]);
        }
        sort($params);
        if ($allowAddition) {
            $queryParams[$this->paramsParam] = strtr(StringHelper::base64UrlEncode(implode(',', $params)), ['=' => '']);
        }

        $queryParams[$this->hmacParam] = $this->calculateHMAC($queryParams, $route);
        array_unshift($params, implode(',', array_keys($params)));
    }

    /**
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public function verifyHMAC(array $params, string $route):bool
    {
        if (!isset($params[$this->hmacParam])) {
           return false;
        }
        $hmac = $params[$this->hmacParam];
        $signedParams = [];
        if (!empty($params[$this->paramsParam])) {
            $signedParams[$this->paramsParam] = $params[$this->paramsParam];
            foreach(explode(',', base64_decode($params[$this->paramsParam])) as $signedParam) {
                $signedParams[$signedParam] = $params[$signedParam] ?? null;
            }
        } else {
            $signedParams = $params;
            unset($signedParams[$this->hmacParam]);
        }
        $calculated = $this->calculateHMAC($signedParams, $route);
        return hash_equals($calculated, $hmac);
    }
}