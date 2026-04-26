<?php

namespace Customize\Event;

use Customize\Repository\CurtainPriceMatrixRepository;
use Eccube\Event\TemplateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CurtainPriceEvent implements EventSubscriberInterface
{
    private $curtainPriceMatrixRepository;

    public function __construct(CurtainPriceMatrixRepository $curtainPriceMatrixRepository)
    {
        $this->curtainPriceMatrixRepository = $curtainPriceMatrixRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'Product/detail.twig' => 'onProductDetail',
        ];
    }

    public function onProductDetail(TemplateEvent $event)
    {
        // 1. 現在のテンプレート引数を取得
        $parameters = $event->getParameters();
        $Product = $parameters['Product'];

        // 2. マトリクスデータの構築
        $matrixData = [];
        foreach ($Product->getProductClasses() as $ProductClass) {
            $prices = $this->curtainPriceMatrixRepository->findBy(['ProductClass' => $ProductClass]);
            foreach ($prices as $p) {
                // JSで検索しやすい多次元配列構造
                $matrixData[$ProductClass->getCode()][$p->getWidthLimit()][$p->getHeightLimit()][(string)$p->getPleats()] = $p->getPrice();
            }
        }

        // 3. 配列に新しいデータを追加して、イベントにセットし直す
        $parameters['CurtainMatrix'] = $matrixData;
        $event->setParameters($parameters);
    }
}