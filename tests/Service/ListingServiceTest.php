<?php

namespace TicketSwap\Assessment\tests\Service;

use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use TicketSwap\Assessment\Entity\Barcode;
use TicketSwap\Assessment\Entity\Listing;
use TicketSwap\Assessment\Entity\Seller;
use TicketSwap\Assessment\Entity\Ticket;
use TicketSwap\Assessment\Entity\TicketId;
use TicketSwap\Assessment\Exception\ListingCreationException;
use TicketSwap\Assessment\Service\ListingService;

class ListingServiceTest extends TestCase
{

    /**
     * @test
    */
    public function it_should_be_possible_to_create_a_listing(): void
    {
        $listingService = new ListingService();
        
        $createdListing = $listingService->createListing(
            seller: new Seller('Pascal'),
            tickets: [
                new Ticket(
                    new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                    new Barcode('EAN-13', '38974312923')
                ),
            ],
            price: new Money(4950, new Currency('EUR'))
        );
        
        $this->assertInstanceOf(Listing::class, $createdListing);
    }

    /**
     * @test
    */
    public function it_should_not_be_possible_to_create_a_listing_with_no_tickets(): void
    {
        $this->expectException(ListingCreationException::class);
        $this->expectExceptionMessage('A listing cannot be created without tickets.');

        $listingService = new ListingService();

        $listingService->createListing(
            seller: new Seller('Pascal'),
            tickets: [],
            price: new Money(4950, new Currency('EUR'))
        );
    }

    /**
     * @test
    */
    public function it_should_not_be_possible_to_create_a_listing_with_negative_price(): void
    {
        $this->expectException(ListingCreationException::class);
        $this->expectExceptionMessage('The listing price must be greater than zero.');

        $listingService = new ListingService();

        $listingService->createListing(
            seller: new Seller('Pascal'),
            tickets: [
                new Ticket(
                    new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                    new Barcode('EAN-13', '38974312923')
                ),
            ],
            price: new Money(-30, new Currency('EUR'))
        );
    }   

    /**
     * @test
    */
    public function it_should_not_be_possible_to_create_a_listing_with_duplicate_barcodes(): void
    {
        $this->expectException(ListingCreationException::class);
        $this->expectExceptionMessage('Duplicate barcode found in the listing: EAN-13:38974312923');

        $listingService = new ListingService();

        $listingService->createListing(
            seller: new Seller('Pascal'),
            tickets: [
                new Ticket(
                    new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                    new Barcode('EAN-13', '38974312923')
                ),
                new Ticket(
                    new TicketId('B47CBE2D-9F80-47D9-A9CC-894CE82AA6BA'),
                    new Barcode('EAN-13', '38974312923')
                ),
            ],
            price: new Money(300, new Currency('EUR'))
        );
    }
}