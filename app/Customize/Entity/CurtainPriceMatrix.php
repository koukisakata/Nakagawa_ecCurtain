<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Eccube\Entity\ProductClass;

/**
 * @ORM\Table(name="dtb_curtain_price_matrix")
 * @ORM\Entity(repositoryClass="Customize\Repository\CurtainPriceMatrixRepository")
 */
class CurtainPriceMatrix extends AbstractEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\ProductClass")
     * @ORM\JoinColumn(name="product_class_id", referencedColumnName="id", nullable=false)
     */
    private $ProductClass;

    /**
     * @ORM\Column(type="integer")
     */
    private $width_limit;

    /**
     * @ORM\Column(type="integer")
     */
    private $height_limit;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2)
     */
    private $price;

    /**
     * @ORM\Column(type="decimal", precision=3, scale=1)
     */
    private $pleats; // 1.0, 1.5, 2.0 などを保存

    // Getter & Setter は後ほどコマンドで自動生成するか、手動で追加します
    public function getId() { return $this->id; }
    public function getProductClass() { return $this->ProductClass; }
    public function setProductClass(ProductClass $ProductClass) { $this->ProductClass = $ProductClass; return $this; }
    public function getWidthLimit() { return $this->width_limit; }
    public function setWidthLimit($width_limit) { $this->width_limit = $width_limit; return $this; }
    public function getHeightLimit() { return $this->height_limit; }
    public function setHeightLimit($height_limit) { $this->height_limit = $height_limit; return $this; }
    public function getPrice() { return $this->price; }
    public function setPrice($price) { $this->price = $price; return $this; }
    public function getPleats(){ return $this->pleats; }
    public function setPleats($pleats) { $this->pleats = $pleats; return $this; }
}