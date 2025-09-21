<?php

namespace TicketsMarketplace\Assessment\tests\Helpers;

use TicketsMarketplace\Assessment\Entity\Admin;
use TicketsMarketplace\Assessment\Entity\Barcode;
use TicketsMarketplace\Assessment\Entity\Buyer;
use TicketsMarketplace\Assessment\Entity\Listing;
use TicketsMarketplace\Assessment\Entity\ListingId;
use TicketsMarketplace\Assessment\Entity\Seller;
use TicketsMarketplace\Assessment\Entity\Ticket;
use TicketsMarketplace\Assessment\Entity\TicketId;

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
