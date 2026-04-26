<?php

namespace Customize\Event;

use Eccube\Event\EventArgs;
use Eccube\Event\EccubeEvents;
use Eccube\Service\CartService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CurtainCartSubscriber implements EventSubscriberInterface
{
    private $requestStack;
    private $cartService;

    public function __construct(RequestStack $requestStack, CartService $cartService)
    {
        $this->requestStack = $requestStack;
        $this->cartService = $cartService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // カート追加時
            EccubeEvents::FRONT_PRODUCT_CART_ADD_COMPLETE => 'onCartAddComplete',
            // カート画面表示時（警備員のチェック後に割り込む）
            EccubeEvents::FRONT_CART_INDEX_INITIALIZE => 'onCartIndexInitialize',
        ];
    }

    public function onCartAddComplete(EventArgs $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        $width = $request->query->get('curtain_width');
        $height = $request->query->get('curtain_height');
        $pleats = $request->query->get('curtain_pleats');
        $memory = $request->query->get('curtain_memory');
        $price = $request->query->get('curtain_price'); 
        $open = $request->query->get('curtain_open');
        $hook = $request->query->get('curtain_hook');
        $tassel = $request->query->get('curtain_tassel');
        log_info('カーテンデータ受信確認', ['width' => $width, 'price' => $price]);

        if ($width && $height) {
            $form = $event->getArgument('form');
            $addCartData = $form->getData();
            $productClassId = isset($addCartData['product_class_id']) ? $addCartData['product_class_id'] : $addCartData['ProductClass']->getId();

            $carts = $this->cartService->getCarts();
            foreach ($carts as $cart) {
                foreach ($cart->getCartItems() as $cartItem) {
                    if ($cartItem->getProductClass()->getId() == $productClassId) {
                        $cartItem->setCurtainWidth($width);
                        $cartItem->setCurtainHeight($height);
                        $cartItem->setCurtainPleats($pleats);
                        $cartItem->setCurtainMemory($memory);
                        $cartItem->setCurtainOpen($open);
                        $cartItem->setCurtainHook($hook);
                        $cartItem->setCurtainTassel($tassel);

                        if ($price) {
                            $cartItem->setCurtainPrice($price);
                            $cartItem->setPrice($price);
                        }
                    }
                }
            }
            
            $this->cartService->save();
        }
    }
    public function onCartIndexInitialize(EventArgs $event)
    {
        $carts = $this->cartService->getCarts();
        foreach ($carts as $cart) {
            $totalPrice = 0; // カートごとの合計を計算する変数

            foreach ($cart->getCartItems() as $cartItem) {
                $customPrice = $cartItem->getCurtainPrice();
                if ($customPrice > 0) {
                    // 単価をカスタム価格で上書き
                    $cartItem->setPrice($customPrice);
                    // このアイテムの小計を計算して足す
                    $totalPrice += ($customPrice * $cartItem->getQuantity());
                } else {
                    // カーテン以外の商品がある場合は、その商品の小計を足す
                    $totalPrice += $cartItem->getTotalPrice();
                }
            }
            
            // 重要：カート全体の合計金額（表示用）を、計算した値で上書きする
            // ※EC-CUBEの内部データに反映させます
            if ($totalPrice > 0) {
                // Cartエンティティの内部変数を無理やり書き換えることはできないため、
                // Twig側で表示が合うように工夫します。
            }
        }
    }
}