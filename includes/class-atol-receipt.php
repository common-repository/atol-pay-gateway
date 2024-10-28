<?php
if (!defined('ABSPATH')) {
    exit;
}


class AtolReceipt
{
    const TYPE_SELL = 'sell';
    const DEFAULT_PROVIDER_ID = 100;

    /**
     * @var array $positions
     */
    public $positions = [];

    /**
     * @var string $buyer
     */
    private $buyer;

    /**
     * @var int $providerId
     */
    private $providerId;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var int $sno
     */
    private $sno;


    /**
     * @param string $buyer
     * @return AtolReceipt
     */
    public function setBuyer($buyer)
    {
        $this->buyer = $buyer;
        return $this;
    }

    /**
     * @param string $type
     * @return AtolReceipt
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param int $sno
     * @return AtolReceipt
     */
    public function setSno($sno)
    {
        $this->sno = $sno;
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
     * @param AtolReceiptItem $position
     * @return void
     */
    public function attachPosition($position)
    {
        $this->positions[] = $position;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $positions = [];

        foreach ($this->positions as $item) {
            $positions[] = $item->toArray();
        }

        $options = [
            'positions' => $positions,
            'type' => self::TYPE_SELL,
            'providerId' => self::DEFAULT_PROVIDER_ID,
            'sno' => $this->sno
        ];

        if ($this->buyer) {
            $options['buyer'] = [
                'email' => $this->buyer
            ];
        }

        return $options;
    }
}