<?php

namespace AppBundle\Entity;

/**
 * StockOption
 */
class StockOption implements \JsonSerializable
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $company;

    /**
     * @var string
     */
    private $ticker_symbol;

    /**
     * @var integer
     */
    private $quantity;

    /**
     * @var string
     */
    private $value;


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
     * Set company
     *
     * @param string $company
     *
     * @return StockOption
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set tickerSymbol
     *
     * @param string $tickerSymbol
     *
     * @return StockOption
     */
    public function setTickerSymbol($tickerSymbol)
    {
        $this->ticker_symbol = $tickerSymbol;

        return $this;
    }

    /**
     * Get tickerSymbol
     *
     * @return string
     */
    public function getTickerSymbol()
    {
        return $this->ticker_symbol;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return StockOption
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
     * Set value
     *
     * @param string $value
     *
     * @return StockOption
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->company;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $trades;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->trades = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add trade
     *
     * @param \AppBundle\Entity\Trade $trade
     *
     * @return StockOption
     */
    public function addTrade(\AppBundle\Entity\Trade $trade)
    {
        $this->trades[] = $trade;

        return $this;
    }

    /**
     * Remove trade
     *
     * @param \AppBundle\Entity\Trade $trade
     */
    public function removeTrade(\AppBundle\Entity\Trade $trade)
    {
        $this->trades->removeElement($trade);
    }

    /**
     * Get trades
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTrades()
    {
        return $this->trades;
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
            'company' => $this->company,
            'ticker_symbol' => $this->ticker_symbol,
            'quantity' => $this->quantity,
            'value' => $this->value
        ];
    }
}
