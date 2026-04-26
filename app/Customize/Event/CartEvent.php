<?php

namespace Customize\Event;

use Customize\Repository\CurtainPriceMatrixRepository;
use Eccube\Event\CartEvent as EccubeCartEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CartEvent implements EventSubscriberInterface
{
    private $requestStack;
    private $matrixRepository;

    public function __construct(RequestStack $requestStack, CurtainPriceMatrixRepository $matrixRepository)
    {
        $this->requestStack = $requestStack;
        $this->matrixRepository = $matrixRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // カート投入時、およびカート表示時の初期化
            'cart.add.set_item' => 'onCartUpdateItem',
            'cart.item.initialize' => 'onCartUpdateItem',
        ];
    }

    public function onCartUpdateItem(EccubeCartEvent $event)
    {
        $CartItem = $event->getItem();
        $request = $this->requestStack->getCurrentRequest();

        // 1. リクエストから値を抽出（GET/POSTの両方から、考えられる全ての名前で探す）
        if ($request) {
            // 幅、丈、ヒダ、形態安定の各パラメータを取得
            $width = $request->get('width');
            $height = $request->get('height');
            $pleats = $request->get('pleats');
            $shapeMemory = $request->get('shape_memory');

            // 【安全策】メソッドが存在するか確認してからセット（500エラー防止）
            if ($width !== null && method_exists($CartItem, 'setWidth')) {
                $CartItem->setWidth((int)$width);
                $CartItem->setHeight((int)$height);
                $CartItem->setPleats((string)$pleats);
            }
        }

        // 2. CartItem（データベース）に保存されている値を取得
        // カート画面表示時はリクエストがないため、こちらがメインになります
        $ProductClass = $CartItem->getProductClass();
        $w = (method_exists($CartItem, 'getWidth')) ? $CartItem->getWidth() : null;
        $h = (method_exists($CartItem, 'getHeight')) ? $CartItem->getHeight() : null;
        $p = (method_exists($CartItem, 'getPleats')) ? $CartItem->getPleats() : null;

        // 計算に必要な情報が揃っているか確認
        if ($ProductClass && $w && $h && $p) {
            // サイズ上限（40cm刻み）の算出
            $wLimit = ceil($w / 40) * 40;
            $hLimit = ceil($h / 40) * 40;

            // マトリクスリポジトリから価格を検索
            $Matrix = $this->matrixRepository->findOneBy([
                'ProductClass' => $ProductClass,
                'width_limit' => $wLimit,
                'height_limit' => $hLimit,
                'pleats' => (string)$p // 文字列として検索
            ]);

            if ($Matrix) {
                $price = $Matrix->getPrice();

                // 形態安定加工 (+10%) の判定
                // リクエストがある場合はそれを優先、ない場合はJSの計算結果に準ずる
                $sm = $request ? $request->get('shape_memory') : null;
                if ($sm === 'yes') {
                    $price = floor($price * 1.1);
                }

                // カート内の単価を強制的に書き換え
                $CartItem->setPrice($price);
            }
        }
    }
}