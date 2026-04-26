<?php

namespace Customize\Event;

use Eccube\Event\CartEvent;
use Eccube\Event\EccubeEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;

class CurtainCartListener implements EventSubscriberInterface
{
    private $requestStack;
    private $entityManager;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // カートへの追加処理が終わった直後に実行
            EccubeEvents::FRONT_CART_ADD_COMPLETE => 'onCartAddComplete',
        ];
    }

    public function onCartAddComplete(CartEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) return;

        // 【修正】queryだけでなく、すべてのソースからパラメータを取得
        $width = $request->get('width');
        $height = $request->get('height');
        $pleats = $request->get('pleats');
        $memory = $request->get('shape_memory');

        // 値が届いている場合のみ実行
        if ($width || $height) {
            $Cart = $event->getCart();
            $CartItems = $Cart->getCartItems();
            
            // カート内の全商品をチェックし、今回追加された（サイズがまだ空の）商品を探してセット
            foreach ($CartItems as $Item) {
                if ($Item->getWidth() === null) {
                    $Item->setWidth(intval($width));
                    $Item->setHeight(intval($height));
                    $Item->setPleats($pleats);
                    $Item->setShapeMemory($memory);
                    
                    // 1つ見つけたら保存して終了
                    $this->entityManager->persist($Item);
                    break; 
                }
            }
            $this->entityManager->flush();
        }
    }
}