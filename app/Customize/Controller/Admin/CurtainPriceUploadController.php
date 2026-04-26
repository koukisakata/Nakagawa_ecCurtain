<?php

namespace Customize\Controller\Admin;

use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class CurtainPriceUploadController extends AbstractController
{
    #[Route('/%eccube_admin_route%/curtain_price_upload', name: 'admin_curtain_price_upload', methods: ['GET', 'POST'])]
    #[Template('@admin/curtain_price_upload.twig')]
    public function index(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('csv_file', FileType::class, ['label' => 'CSVを選択'])
            ->getForm();

        $form->handleRequest($request);

        // ファイルの保存場所
        $savePath = $this->getParameter('kernel.project_dir') . '/curtain_price.csv';

        // --- アップロード時の保存処理 ---
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('csv_file')->getData();
            if ($file) {
                $file->move(dirname($savePath), basename($savePath));
                $this->addSuccess('価格表を更新しました', 'admin');
                return $this->redirectToRoute('admin_curtain_price_upload');
            }
        }

        // --- CSVデータの読み込みとプレビュー生成 ---
        $csvData = [];
        if (file_exists($savePath)) {
            try {
                $file = new \SplFileObject($savePath);
                $file->setFlags(
                    \SplFileObject::READ_CSV | 
                    \SplFileObject::READ_AHEAD | 
                    \SplFileObject::SKIP_EMPTY | 
                    \SplFileObject::DROP_NEW_LINE
                );

                $count = 0;
                foreach ($file as $row) {
                    if ($row === [null] || empty($row)) {
                        continue;
                    }
                    
                    mb_convert_variables('UTF-8', 'SJIS-win, UTF-8', $row);
                    $csvData[] = $row;

                    $count++;
                    if ($count >= 100) {
                        break;
                    }
                }
            } catch (\Exception $e) {
                // エラー時は何もしない（空の表になる）
            }
        }

        return [
            'form' => $form->createView(),
            'csvData' => $csvData
        ];
    }
}