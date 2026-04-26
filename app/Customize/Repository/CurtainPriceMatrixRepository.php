<?php

namespace Customize\Repository;

use Customize\Entity\CurtainPriceMatrix;
use Eccube\Repository\AbstractRepository;
// ↓ ここを変更
use Doctrine\Persistence\ManagerRegistry;

class CurtainPriceMatrixRepository extends AbstractRepository
{
    // ↓ 引数の型を ManagerRegistry に変更
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CurtainPriceMatrix::class);
    }
}