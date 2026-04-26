<?php

namespace Customize\Controller\Admin;

use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class CurtainPriceUploadController extends AbstractController
{
    /**
     * @Route("/%eccube_admin_route%/curtain_price_upload", name: "admin_curtain_price_upload", methods={"GET", "POST"})
     * @Template("@admin/curtain_price_upload.twig")
     */
    public function index(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('csv_file', FileType::class, ['label' => 'CSVを選択'])
            ->getForm();

        $form->handleRequest($request);

        // ファイルの保存場所（プロジェクトルート直下のcurtain_price.csv）
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
                // CSVとして読み込む設定
                $file->setFlags(
                    \SplFileObject::READ_CSV | 
                    \SplFileObject::READ_AHEAD | 
                    \SplFileObject::SKIP_EMPTY | 
                    \SplFileObject::DROP_NEW_LINE
                );

                $count = 0;
                foreach ($file as $row) {
                    // 空行の判定（[null] や空配列をスキップ）
                    if ($row === [null] || empty($row)) {
                        continue;
                    }
                    
                    // 文字コード変換（Shift-JIS/Windows-31JからUTF-8へ）
                    // これを入れないとExcelで作ったCSVが文字化けします
                    mb_convert_variables('UTF-8', 'SJIS-win, UTF-8', $row);
                    
                    $csvData[] = $row;

                    // プレビュー表示は先頭100件までに制限（ブラウザの負荷軽減）
                    $count++;
                    if ($count >= 100) {
                        break;
                    }
                }
            } catch (\Exception $e) {
                // ファイル読み込み失敗時はログに出力するなどの処理
            }
        }

        // --- ここでテンプレートにデータを渡しています ---
        return [
            'form' => $form->createView(),
            'csvData' => $csvData // 👈 これがTwig側の {% if csvData %} で使われます
        ];
    }
}