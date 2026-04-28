<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\CartItem")
 */
trait CartItemTrait
{
    /** @ORM\Column(type="decimal", precision=10, scale=2, nullable=true) */
    private $curtain_width;
    /** @ORM\Column(type="decimal", precision=10, scale=2, nullable=true) */
    private $curtain_height;
    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $curtain_pleats;
    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $curtain_memory;
    /**
     * @ORM\Column(type="decimal", precision=12, scale=2, nullable=true)
     */
    private $curtain_price;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $curtain_open;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $curtain_hook;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $curtain_tassel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $custom_curtain;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $custom_curtain_color;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $custom_style_position;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $custom_combination_color;

    public function getCurtainWidth() { return $this->curtain_width; }
    public function setCurtainWidth($curtain_width) { $this->curtain_width = $curtain_width; return $this; }
    public function getCurtainHeight() { return $this->curtain_height; }
    public function setCurtainHeight($curtain_height) { $this->curtain_height = $curtain_height; return $this; }
    public function getCurtainPleats() { return $this->curtain_pleats; }
    public function setCurtainPleats($curtain_pleats) { $this->curtain_pleats = $curtain_pleats; return $this; }
    public function getCurtainMemory() { return $this->curtain_memory; }
    public function setCurtainMemory($curtain_memory) { $this->curtain_memory = $curtain_memory; return $this; }

    public function getCurtainPrice() { return $this->curtain_price; }
    public function setCurtainPrice($curtain_price) { $this->curtain_price = $curtain_price; return $this; }

    public function getCurtainOpen() { return $this->curtain_open; }
    public function setCurtainOpen($curtain_open) { $this->curtain_open = $curtain_open; return $this; }

    public function getCurtainHook() { return $this->curtain_hook; }
    public function setCurtainHook($curtain_hook) { $this->curtain_hook = $curtain_hook; return $this; }

    public function getCurtainTassel() { return $this->curtain_tassel; }

    public function setCurtainTassel($curtain_tassel) { $this->curtain_tassel = $curtain_tassel; return $this; }

    public function getCustomCurtain() { return $this->custom_curtain; }
    public function setCustomCurtain($custom_curtain) { $this->custom_curtain = $custom_curtain; return $this; }

    public function getCustomCurtainColor() { return $this->custom_curtain_color; }
    public function setCustomCurtainColor($custom_curtain_color) { $this->custom_curtain_color = $custom_curtain_color; return $this; }

    public function getCustomStylePosition() { return $this->custom_style_position; }
    public function setCustomStylePosition($custom_style_position) { $this->custom_style_position = $custom_style_position; return $this; }

    public function getCustomCombinationColor() { return $this->custom_combination_color; }
    public function setCustomCombinationColor($custom_combination_color) { $this->custom_combination_color = $custom_combination_color; return $this; }
}
