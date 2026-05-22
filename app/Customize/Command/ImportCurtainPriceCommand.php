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

            // CSVのデータ列が不足している場合はスキップ
            if (count($row) < 4) {
                continue;
            }

            $productCode = $row[0]; // A1001など
            $ProductClass = $this->productClassRepository->findOneBy(['code' => $productCode]);

            if (!$ProductClass) {
                $io->warning("商品コードが見つかりません: {$productCode}");
                continue;
            }

            // ★ 修正箇所：既存のスペックデータがあれば取得し「更新」、なければ「新規作成」
            $matrixRepository = $this->entityManager->getRepository(CurtainPriceMatrix::class);
            $matrix = $matrixRepository->findOneBy(['ProductClass' => $ProductClass]);

            if (!$matrix) {
                $matrix = new CurtainPriceMatrix();
                $matrix->setProductClass($ProductClass);
            }

            // ★ 修正箇所：CSVの各列からスペックを取得してセット
            // [1]: 有効巾(mm), [2]: 縦リピート(mm), [3]: メーター単価(円)
            $matrix->setWidthMm((int)$row[1]);
            $matrix->setRepeatVMm((int)$row[2]);
            $matrix->setMeterUnitPrice((float)$row[3]); // 先ほど追加したカラム

            $this->entityManager->persist($matrix);

            // 1件ごとにフラッシュ（メモリ不足になるほどの行数ではないですが安全のため）
            $this->entityManager->flush();
            $this->entityManager->clear(CurtainPriceMatrix::class); // メモリ節約

            $io->text("インポート/更新完了: {$productCode} (m単価: {$row[3]}円)");
        }

        $io->success('すべての生地スペック・単価データの登録が完了しました！');
        return 0;
    }
}
