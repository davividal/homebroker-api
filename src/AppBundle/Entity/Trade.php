<?php

namespace AppBundle\Entity;

/**
 * Trade
 */
class Trade implements \JsonSerializable
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $user_id;

    /**
     * @var integer
     */
    private $stock_option_id;

    /**
     * @var integer
     */
    private $quantity;

    /**
     * @var \AppBundle\Entity\User
     */
    private $user;

    /**
     * @var \AppBundle\Entity\StockOption
     */
    private $stock_option;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return Trade
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set stockOptionId
     *
     * @param integer $stockOptionId
     *
     * @return Trade
     */
    public function setStockOptionId($stockOptionId)
    {
        $this->stock_option_id = $stockOptionId;

        return $this;
    }

    /**
     * Get stockOptionId
     *
     * @return integer
     */
    public function getStockOptionId()
    {
        return $this->stock_option_id;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return Trade
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Trade
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set stockOption
     *
     * @param \AppBundle\Entity\StockOption $stockOption
     *
     * @return Trade
     */
    public function setStockOption(\AppBundle\Entity\StockOption $stockOption = null)
    {
        $this->stock_option = $stockOption;

        return $this;
    }

    /**
     * Get stockOption
     *
     * @return \AppBundle\Entity\StockOption
     */
    public function getStockOption()
    {
        return $this->stock_option;
    }

    public function __toString()
    {
        return $this->getStockOption()->getTickerSymbol();
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'stock_option' => $this->stock_option,
            'user' => $this->user,
            'quantity' => $this->quantity
        ];
    }
}
