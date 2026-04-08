import { test, expect } from '@playwright/test';

const adminRoute = process.env.ECCUBE_ADMIN_ROUTE || 'admin';
const pageTitle = '.c-pageTitle';
const searchResultMsg = '#search_form > div.c-outsideBlock__contents.mb-5 > span';
const searchResultList = '#page_admin_product table tbody';
const searchBtn = '#search_form .c-outsideBlock__contents button';
const noResultMsg = '.c-contentsArea .c-contentsArea__cols div.text-center.h5';

async function goProductList(page: import('@playwright/test').Page) {
  await page.goto(`/${adminRoute}/product`);
  await page.waitForLoadState('load');
}

async function searchProduct(page: import('@playwright/test').Page, keyword: string = '') {
  await page.locator('#admin_search_product_id').fill(keyword);
  await page.locator(searchBtn).click();
  await page.waitForLoadState('load');
}

test.describe('Admin Product (EA03)', () => {

  test('product_商品検索', async ({ page }) => {
    await goProductList(page);
    await searchProduct(page, 'ジェラート');
    await expect(page.locator(searchResultMsg)).toContainText('検索結果：1件が該当しました');
    await expect(page.locator(searchResultList)).toContainText('彩のジェラートCUBE');

    // 空検索 → 全件表示
    await goProductList(page);
    await searchProduct(page, '');
    await expect(page.locator(searchResultMsg)).toContainText('検索結果：2件が該当しました');

    // 結果0件
    await goProductList(page);
    await searchProduct(page, 'gege@gege.com');
    await expect(page.locator(searchResultMsg)).toContainText('検索結果：0件が該当しました');
  });

  test('product_商品検索結果無', async ({ page }) => {
    await goProductList(page);
    await searchProduct(page, 'お箸');
    await expect(page.locator(noResultMsg)).toContainText('検索条件に合致するデータが見つかりませんでした');
  });

  test('product_規格確認のポップアップ表示', async ({ page }) => {
    await goProductList(page);
    await searchProduct(page, '');

    // 規格あり商品 (ex-product-1) の規格確認ボタンをクリック
    await page.locator('#ex-product-1 td:nth-child(7) button').click();
    await expect(page.locator('#productClassesModal')).toBeVisible({ timeout: 5_000 });

    // キャンセル
    await page.locator('#productClassesModal [data-bs-dismiss="modal"]:visible').last().click();
    await page.waitForTimeout(500);
    await expect(page.locator('#productClassesModal')).not.toBeVisible();
  });

  test('product_ポップアップから規格編集画面に遷移', async ({ page }) => {
    await goProductList(page);
    await searchProduct(page, '');

    // 規格確認ポップアップを開く
    await page.locator('#ex-product-1 td:nth-child(7) button').click();
    await expect(page.locator('#productClassesModal')).toBeVisible({ timeout: 5_000 });

    // 規格編集リンクをクリック
    await page.locator('#productClassesModal a[href*="class"]').click();
    await page.waitForLoadState('load');
    await expect(page.locator(pageTitle)).toContainText('商品規格登録');
  });
});
