<?php

namespace Eccube\Event;

use Eccube\Event\EventArgs;
use Eccube\Event\EccubeEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CurtainCartSubscriber implements EventSubscriberInterface
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // カートに商品が追加された直後のイベント
            EccubeEvents::FRONT_PRODUCT_CART_ADD_COMPLETE => 'onCartAddComplete',
        ];
    }

    public function onCartAddComplete(EventArgs $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        // Controllerで新しく作成されたCartItemを取得
        // ※実際にはCartServiceから最新のItemを取得するロジックが必要になる場合があります
        
        // 簡単な実装例：直近のCartItemにリクエスト値をセット
        // 本来は追加された特定のItemを特定する処理が望ましいです
    }
}