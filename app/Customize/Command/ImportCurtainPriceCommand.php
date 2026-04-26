<?php

namespace Customize\Command;

use Customize\Entity\CurtainPriceMatrix;
use Eccube\Repository\ProductClassRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

class ImportCurtainPriceCommand extends Command
{
    protected static $defaultName = 'customize:import:curtain-price';
    private $entityManager;
    private $productClassRepository;

    public function __construct(EntityManagerInterface $entityManager, ProductClassRepository $productClassRepository)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->productClassRepository = $productClassRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $csvPath = 'var/import/curtain_price.csv'; 

        if (!file_exists($csvPath)) {
            $io->error("CSVファイルが見つかりません: {$csvPath}");
            return 1;
        }

        $file = new \SplFileObject($csvPath);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);

        $header = [];
        foreach ($file as $index => $row) {
            if ($index === 0) {
                $header = $row;
                continue;
            }

            $productCode = $row[0]; // A1001など
            $ProductClass = $this->productClassRepository->findOneBy(['code' => $productCode]);

            if (!$ProductClass) {
                $io->warning("商品コードが見つかりません: {$productCode}");
                continue;
            }

            // 3列目以降（W...H..._P...）を解析して保存
            for ($i = 3; $i < count($row); $i++) {
                if (!isset($row[$i]) || $row[$i] === '') continue;

                // 正規表現で W(幅) H(丈) P(ヒダ*10) を抽出
                if (preg_match('/W(\d+)H(\d+)_P(\d+)/', $header[$i], $matches)) {
                    $matrix = new CurtainPriceMatrix();
                    $matrix->setProductClass($ProductClass);
                    $matrix->setWidthLimit((int)$matches[1]);
                    $matrix->setHeightLimit((int)$matches[2]);
                    
                    // P10 -> 1.0, P15 -> 1.5, P20 -> 2.0 に変換
                    $pleatsValue = (float)$matches[3] / 10;
                    $matrix->setPleats($pleatsValue);
                    
                    $matrix->setPrice((float)$row[$i]);

                    $this->entityManager->persist($matrix);
                }
            }
            $this->entityManager->flush();
            $this->entityManager->clear(); // メモリ節約
            $io->text("インポート完了: {$productCode}");
        }

        $io->success('すべての価格データの登録が完了しました！');
        return 0;
    }
}