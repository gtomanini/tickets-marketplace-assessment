<?php

namespace TicketsMarketplace\Assessment\Entity;

use Money\Money;

final class Listing
{
    /**
     * @param array<Ticket> $tickets
     */
    public function __construct(
        private ListingId $id,
        private Seller $seller,
        private array $tickets,
        private Money $price,
        private bool $isVerified = false,
        private ?Admin $verifiedBy = null
    ) {
    }

    public function getId() : ListingId
    {
        return $this->id;
    }

    public function getSeller() : Seller
    {
        return $this->seller;
    }

    /**
     * @return array<Ticket>
     */
    public function getTickets(?bool $forSale = null) : array
    {
        if (true === $forSale) {
            $forSaleTickets = [];
            foreach ($this->tickets as $ticket) {
                if (!$ticket->isBought()) {
                    $forSaleTickets[] = $ticket;
                }
            }

            return $forSaleTickets;
        } else if (false === $forSale) {
            $notForSaleTickets = [];
            foreach ($this->tickets as $ticket) {
                if ($ticket->isBought()) {
                    $notForSaleTickets[] = $ticket;
                }
            }

            return $notForSaleTickets;
        } else {
            return $this->tickets;
        }
    }

    public function getPrice() : Money
    {
        return $this->price;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function getVerifiedBy(): ?Admin
    {
        return $this->verifiedBy;
    }

    public function verifyListing(Admin $admin): void
    {
        $this->isVerified = true;
        $this->verifiedBy = $admin;
    }
}
