<?php

namespace TicketsMarketplace\Assessment\Entity;

use TicketsMarketplace\Assessment\Exception\TicketHasNoBarcodeException;

final class Ticket
{
    /** @var Barcode[] */
    private array $barcodes = [];
    
    /** 
     * @param Barcode[] $barcodes
    */
    public function __construct(
        private TicketId $id, 

        array $barcodes, 
        private ?Buyer $buyer = null)
    {
        $this->barcodes = $barcodes;
    }

    public function getId() : TicketId
    {
        return $this->id;
    }

    /** @return Barcode[] */
    public function getBarcodes(): array
    {
        return $this->barcodes;
    }

    public function getBuyer() : ?Buyer
    {
        return $this->buyer;
    }

    public function isBought() : bool
    {
        return $this->buyer !== null;
    }

    public function buyTicket(Buyer $buyer) : self
    {
        $this->buyer = $buyer;

        return $this;
    }
}
