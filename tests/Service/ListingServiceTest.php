<?php

namespace TicketSwap\Assessment\tests\Service;

use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use TicketSwap\Assessment\Entity\Admin;
use TicketSwap\Assessment\Entity\Barcode;
use TicketSwap\Assessment\Entity\Listing;
use TicketSwap\Assessment\Exception\ListingCreationException;
use TicketSwap\Assessment\Repository\InMemoryListingRepository;
use TicketSwap\Assessment\Service\ListingService;
use TicketSwap\Assessment\tests\Helpers\TestEntityFactory;

class ListingServiceTest extends TestCase
{
    use TestEntityFactory;

    /**
     * @test
     */
    public function it_should_be_possible_to_create_a_listing(): void
    {
        $inMemoryListingRepository = new InMemoryListingRepository();
        $listingService = new ListingService($inMemoryListingRepository);

        $listing = $this->createListing(
            tickets: [$this->createTicket()]
        );

        $createdListing = $listingService->createListing(
            $listing
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

        $inMemoryListingRepository = new InMemoryListingRepository();
        $listingService = new ListingService($inMemoryListingRepository);

        $listing = $this->createListing();

        $listingService->createListing(
            $listing
        );
    }

    /**
     * @test
     */
    public function it_should_not_be_possible_to_create_a_listing_with_negative_price(): void
    {
        $this->expectException(ListingCreationException::class);
        $this->expectExceptionMessage('The listing price must be greater than zero.');

        $inMemoryListingRepository = new InMemoryListingRepository();
        $listingService = new ListingService($inMemoryListingRepository);

        $listing = $this->createListing(
            tickets: [$this->createTicket()],
            price: new Money(-30, new Currency('EUR'))
        );


        $listingService->createListing(
            $listing
        );
    }

    /**
     * @test
     */
    public function it_should_not_be_possible_to_create_a_listing_with_duplicate_barcodes(): void
    {
        $this->expectException(ListingCreationException::class);
        $this->expectExceptionMessage('Duplicate barcode found in the listing: EAN-13:38974312923');

        $inMemoryListingRepository = new InMemoryListingRepository();
        $listingService = new ListingService($inMemoryListingRepository);

        $listing = $this->createListing(
            tickets:
            [
                $this->createTicket(
                    barcodes: [
                        new Barcode('EAN-13', '38974312923'),
                        new Barcode('EAN-13', '38974312923')
                    ]
                )
            ]
        );

        $listingService->createListing(
            $listing
        );
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_verify_a_listing(): void
    {
        $inMemoryListingRepository = new InMemoryListingRepository();
        $listingService = new ListingService($inMemoryListingRepository);

        $listing = $this->createListing(
            tickets: [$this->createTicket()]
        );

        $createdListing = $listingService->createListing(
            $listing
        );

        $this->assertFalse($createdListing->isVerified());

        $allVerifiedListings = $inMemoryListingRepository->findAllVerified();

        $this->assertCount(0, $allVerifiedListings);

        $createdListing->verifyListing(new Admin('AdminUser'));

        $listingService->updateListing($createdListing);

        $allVerifiedListings = $inMemoryListingRepository->findAllVerified();

        $this->assertCount(1, $allVerifiedListings);
        $this->assertTrue($allVerifiedListings[0]->isVerified());
    }

}