<?php

namespace Customize\Command;

use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\Master\ProductStatusRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

class ImportProductsCommand extends Command
{
    protected static $defaultName = 'customize:import:products';
    private $entityManager;
    private $productRepository;
    private $productStatusRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        ProductStatusRepository $productStatusRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
        $this->productStatusRepository = $productStatusRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $csvPath = 'var/import/curtain_price.csv';
        $statusVisible = $this->productStatusRepository->find(ProductStatus::DISPLAY_SHOW);

        $file = new \SplFileObject($csvPath);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);

        $products = [];
        foreach ($file as $index => $row) {
            if ($index === 0) continue; // ヘッダー飛ばし

            $productCode = $row[0]; // A1001
            $seriesName = $row[2];  // オーダーカーテンA

            // 1. 商品本体(Product)の作成（無ければ作る）
            if (!isset($products[$seriesName])) {
                $Product = $this->productRepository->findOneBy(['name' => $seriesName]);
                if (!$Product) {
                    $Product = new Product();
                    $Product->setName($seriesName);
                    $Product->setStatus($statusVisible);
                    $this->entityManager->persist($Product);
                    $this->entityManager->flush();
                    $io->note("新規商品を作成しました: {$seriesName}");
                }
                $products[$seriesName] = $Product;
            }

            // 2. 規格(ProductClass)の作成
            $Product = $products[$seriesName];
            
            // 既にその品番があるかチェック
            $existingClass = $this->entityManager->getRepository(ProductClass::class)
                ->findOneBy(['code' => $productCode]);

            if (!$existingClass) {
                $ProductClass = new ProductClass();
                $ProductClass->setProduct($Product);
                $ProductClass->setCode($productCode);
                $ProductClass->setStockUnlimited(true); // 在庫無制限
                $ProductClass->setPrice01(0);           // 通常価格
                $ProductClass->setPrice02(0);           // 販売価格（マトリクスで計算するため0でOK）
                $ProductClass->setVisible(true);

                $this->entityManager->persist($ProductClass);
                $io->text("品番を登録しました: {$productCode}");
            }
        }

        $this->entityManager->flush();
        $io->success('商品および品番の基本登録が完了しました！');
        return 0;
    }
}