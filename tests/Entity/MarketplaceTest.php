<?php

namespace TicketSwap\Assessment\tests\Entity;

use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use TicketSwap\Assessment\Entity\Barcode;
use TicketSwap\Assessment\Entity\Listing;
use TicketSwap\Assessment\Entity\ListingId;
use TicketSwap\Assessment\Entity\Marketplace;
use TicketSwap\Assessment\Entity\Seller;
use TicketSwap\Assessment\Entity\Ticket;
use TicketSwap\Assessment\Entity\TicketId;

class MarketplaceTest extends TestCase
{
    /** 
     * @test 
    */
    public function it_should_be_possible_to_create_empty_marketplace(): void
    {
        $marketplace = new Marketplace();

        $this->assertEmpty($marketplace->getListingsForSale());
    }

    /** 
     * @test 
    */
    public function it_should_be_possible_to_create_a_marketplace_with_one_listing(): void
    {
        $listing = new Listing(
                                id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
                                seller: new Seller('Pascal'),
                                tickets: [
                                    new Ticket(
                                        new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                                        new Barcode('EAN-13', '38974312923')
                                    ),
                                ],
                                price: new Money(4950, new Currency('EUR')),
                            );

        $marketplace = new Marketplace([$listing]);

        $this->assertCount(1, $marketplace->getListingsForSale());
        $this->assertSame($listing, $marketplace->getListingsForSale()[0]);
    }

    /** 
     * @test 
    */
    public function it_should_be_possible_to_add_a_listing_for_sale(): void
    {
        $marketplace = new Marketplace();

        $listing = new Listing(
                        id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
                        seller: new Seller('Pascal'),
                        tickets: [
                            new Ticket(
                                new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                                new Barcode('EAN-13', '38974312923')
                            ),
                        ],
                        price: new Money(4950, new Currency('EUR')),
                    );

        $marketplace->setListingForSale($listing);

        $this->assertCount(1, $marketplace->getListingsForSale());
        $this->assertSame($listing, $marketplace->getListingsForSale()[0]);
    }
}
