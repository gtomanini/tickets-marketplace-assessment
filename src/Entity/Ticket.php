<?php

namespace TicketSwap\Assessment\Entity;

use TicketSwap\Assessment\Exception\TicketHasNoBarcodeException;

final class Ticket
{
    private array $barcodes = [];
    public function __construct(private TicketId $id, array $barcodes, private ?Buyer $buyer = null)
    {
        $this->barcodes[] = $barcodes;
    }

    public function getId() : TicketId
    {
        return $this->id;
    }

    public function getBarcodes(): array
    {
        return $this->barcodes;
    }

    public function getBuyer() : Buyer
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
