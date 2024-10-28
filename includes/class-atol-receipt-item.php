<?php
if (!defined('ABSPATH')) {
    exit;
}


class AtolReceiptItem
{
    /**
     * @var string $name
     */
    private  $name;

    /**
     * @var int $price
     */
    private$price;

    /**
     * @var int|null $quantity
     */
    private$quantity;

    /**
     * @var int|null $measure
     */
    private $measure;

    /**
     * @var int|null $paymentMethod
     */
    private $paymentMethod;

    /**
     * @var int|null $paymentSubject
     */
    private $paymentSubject;

    /**
     * @var int|null $tax
     */
    private $tax;


    const DEFAULT_PAYMENT_METHOD = 3;
    const DEFAULT_PAYMENT_SUBJECT = 0;

    const DEFAULT_SHIPMENT_PAYMENT_SUBJECT = 3;
    const DEFAULT_SHIPMENT_TAX = 5;

    /**
     * @param string $name
     * @return AtolReceiptItem
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param int $price
     * @return AtolReceiptItem
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @param int|null $quantity
     * @return AtolReceiptItem
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @param int|null $measure
     * @return AtolReceiptItem
     */
    public function setMeasure($measure)
    {
        $this->measure = $measure;
        return $this;
    }

    /**
     * @param int|null $paymentMethod
     * @return AtolReceiptItem
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * @param int|null $paymentSubject
     * @return AtolReceiptItem
     */
    public function setPaymentSubject($paymentSubject)
    {
        $this->paymentSubject = $paymentSubject;
        return $this;
    }

    /**
     * @param int|null $tax
     * @return AtolReceiptItem
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
        return $this;
    }

    /**
     * @return self
     */
    public static function make()
    {
        return new self();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            "name" => $this->name,
            "price" => $this->price,
            "quantity" => $this->quantity * 1000,
            "measure" => 0, // поштучно
            "paymentMethod" => $this->paymentMethod ?: self::DEFAULT_PAYMENT_METHOD,
            "paymentSubject" => $this->paymentSubject ?: self::DEFAULT_PAYMENT_SUBJECT,
            "tax" => (int)$this->tax ?: 0
        ];
    }

}