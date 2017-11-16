<?php


namespace SamIT\Yii2\Filters;


use SamIT\Yii2\Components\UrlSigner;
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
    /**
     * @var UrlSigner
     */
    public $signer;

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws \Exception
     */
    public function beforeAction($action)
    {
        /** @var Request $request */
        $request = $action->controller->module->get('request');
        return $this->signer->verifyHMAC($request->queryParams);
    }


}
