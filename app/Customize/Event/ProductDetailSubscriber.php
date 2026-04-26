<?php

namespace Customize\Event;

use Eccube\Event\TemplateEvent;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\CategoryRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductDetailSubscriber implements EventSubscriberInterface
{
    private $productRepository;
    private $categoryRepository;

    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // 商品詳細ページのテンプレートが表示される時に実行
            'Product/detail.twig' => 'onProductDetailHtml',
        ];
    }

    public function onProductDetailHtml(TemplateEvent $event)
    {
        // カテゴリーID 7 (タッセル) を取得
        $Category = $this->categoryRepository->find(7);

        if ($Category) {
            // カテゴリーID 7 に紐づく商品一覧を取得
            $TasselProducts = $this->productRepository->getQueryBuilderBySearchData([
                'category_id' => $Category
            ])->getQuery()->getResult();

            // Twigに 'TasselProducts' という名前でデータを渡す
            $event->setParameter('TasselProducts', $TasselProducts);
        }
    }
}