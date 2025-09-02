<?php

namespace TicketSwap\Assessment\tests\Helpers;

use TicketSwap\Assessment\Entity\Admin;
use TicketSwap\Assessment\Entity\Barcode;
use TicketSwap\Assessment\Entity\Buyer;
use TicketSwap\Assessment\Entity\Listing;
use TicketSwap\Assessment\Entity\ListingId;
use TicketSwap\Assessment\Entity\Seller;
use TicketSwap\Assessment\Entity\Ticket;
use TicketSwap\Assessment\Entity\TicketId;

use Money\Money;
use Money\Currency;
use Ramsey\Uuid\Uuid;

trait TestEntityFactory
{
    /**
     * @param array<Ticket> $tickets
     */
    protected function createListing(
        ?ListingId $id = null,
        ?Seller $seller = null,
        ?array $tickets = [],
        ?Money $price = null,
        ?bool $isVerified = false,
        ?Admin $verifiedBy = null
    ): Listing {
        return new Listing(
            id: $id ?? new ListingId(Uuid::uuid4()->toString()),
            seller: $seller ?? new Seller('Sarah'),
            tickets: $tickets,
            price: $price ?? new Money(5500, new Currency('EUR')),
            isVerified: $isVerified,
            verifiedBy: $verifiedBy
        );
    }

    /**
     * @param array<Barcode> $barcodes
     */
    protected function createTicket(
        ?TicketId $id = null,
        ?array $barcodes = [],
        ?Buyer $buyer = null
    ): Ticket {
        return new Ticket(
            id: $id ?? new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
            barcodes: $barcodes ?? [new Barcode('EAN-13', '38974312923')],
            buyer: $buyer
        );
    }
}
