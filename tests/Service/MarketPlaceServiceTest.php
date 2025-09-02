<?php

namespace TicketSwap\Assessment\tests\Service;

use PHPUnit\Framework\TestCase;
use Money\Currency;
use Money\Money;
use TicketSwap\Assessment\Entity\Admin;
use TicketSwap\Assessment\Entity\Barcode;
use TicketSwap\Assessment\Entity\Buyer;
use TicketSwap\Assessment\Entity\Listing;
use TicketSwap\Assessment\Entity\ListingId;
use TicketSwap\Assessment\Entity\Marketplace;
use TicketSwap\Assessment\Entity\Seller;
use TicketSwap\Assessment\Entity\Ticket;
use TicketSwap\Assessment\Entity\TicketId;
use TicketSwap\Assessment\Exception\ListingCreationException;
use TicketSwap\Assessment\Exception\ListingNotVerifiedException;
use TicketSwap\Assessment\Exception\TicketAlreadySoldException;
use TicketSwap\Assessment\Repository\InMemoryListingRepository;
use TicketSwap\Assessment\Service\ListingService;
use TicketSwap\Assessment\Service\MarketPlaceService;
use TicketSwap\Assessment\tests\Helpers\TestEntityFactory;


class MarketPlaceServiceTest extends TestCase
{
    use TestEntityFactory;
    protected Marketplace $marketplace;
    protected InMemoryListingRepository $repository;
    protected ListingService $listingService;
    protected MarketPlaceService $marketplaceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->marketplace = new Marketplace([]);
        $this->repository = new InMemoryListingRepository();
        $this->listingService = new ListingService($this->repository);
        $this->marketplaceService = new MarketPlaceService(
            $this->marketplace,
            $this->listingService
        );
    }

    /**
     * @test
     */
    public function it_should_list_all_the_tickets_for_sale(): void
    {
        $listing = $this->createListing(seller: new Seller('Pascal'), tickets: [$this->createTicket()]);

        $marketplaceService = new MarketPlaceService(
            $this->marketplace,
            $this->listingService
        );

        $marketplaceService->setListingToSell($listing);

        $listingsForSale = $marketplaceService->getListingsForSale();

        $this->assertNotEmpty($listingsForSale);
        $this->assertCount(1, $listingsForSale);
        $this->assertSame($listing->getTickets(), $listingsForSale[0]->getTickets());
    }

    /**
     * @test
     */
    public function it_should_list_only_verified_listings_for_sale(): void
    {
        $verifiedListing = $this->createListing(
            seller: new Seller('Sarah'),
            tickets: [$this->createTicket(barcodes: [new Barcode('EAN-13', '38974312923')])],
            isVerified: true,
            verifiedBy: new Admin('AdminUser')
        );

        $unverifiedListing = $this->createListing(seller: new Seller('Tom'), tickets: [$this->createTicket()]);

        $mockedListingRepository = $this->createMock(InMemoryListingRepository::class);
        $mockedListingRepository->method('findAllVerifiedAndWithTickets')
            ->willReturn([$verifiedListing]);

        $marketplaceService = new MarketPlaceService(
            $this->marketplace,
            new ListingService($mockedListingRepository)
        );

        $marketplaceService->setListingToSell($verifiedListing);
        $marketplaceService->setListingToSell($unverifiedListing);

        $listingsForSale = $marketplaceService->getOnlyVerifiedAndWithTicketsListingsForSale();

        $this->assertCount(1, $listingsForSale);
        $this->assertCount(1, $listingsForSale);
        $this->assertSame($verifiedListing, $listingsForSale[0]);
    }


    /**
     * @test
     */
    public function it_should_be_possible_to_buy_a_ticket_from_a_verified_list(): void
    {
        $verifiedListing = $this->createListing(
            seller: new Seller('Sarah'),
            tickets: [$this->createTicket(barcodes: [new Barcode('EAN-13', '38974312923')])],
            isVerified: true,
            verifiedBy: new Admin('AdminUser')
        );

        $marketplace = new Marketplace(
            listingsForSale: [
                $verifiedListing
            ]
        );

        $mockedListingRepository = $this->createMock(InMemoryListingRepository::class);
        $mockedListingRepository->method('findAll')
            ->willReturn([$verifiedListing]);

        $marketplaceService = new MarketPlaceService(
            $marketplace,
            new ListingService($mockedListingRepository)
        );

        $boughtTicket = $marketplaceService->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B')
        );

        $this->assertInstanceOf(Ticket::class, $boughtTicket);
    }

    /**
     * @test
     */
    public function it_should_not_be_possible_to_buy_a_ticket_from_an_unverified_list(): void
    {
        $this->expectException(ListingNotVerifiedException::class);
        $this->expectExceptionMessage('Cannot buy ticket from unverified listing.');
        $listingWithTicket = $this->createListing(seller: new Seller('Pascal'), tickets: [$this->createTicket()]);

        $marketplace = new Marketplace(
            listingsForSale: [
                $listingWithTicket
            ]
        );

        $mockedListingRepository = $this->createMock(InMemoryListingRepository::class);
        $mockedListingRepository->method('findAll')
            ->willReturn([$listingWithTicket]);

        $marketplaceService = new MarketPlaceService(
            $marketplace,
            new ListingService($mockedListingRepository)
        );

        $boughtTicket = $marketplaceService->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B')
        );

        $this->assertInstanceOf(Ticket::class, $boughtTicket);
    }

    /**
     * @test
     */
    public function it_should_not_list_empty_listings_for_sale(): void
    {
        $listingWithTicket = $this->createListing(
            seller: new Seller('Pascal'),
            tickets: [
                $this->createTicket()
            ]
        );

        $listingWithoutTicket = $this->createListing(
            seller: new Seller('Pascal'),
            tickets: [
                $this->createTicket(
                    id: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401C'),
                    barcodes: [new Barcode('EAN-13', '38974312923')]
                )
            ],
            isVerified: true,
            verifiedBy: new Admin('adminUser')
        );

        $mockedListingRepository = $this->createMock(InMemoryListingRepository::class);
        $mockedListingRepository->method('findAllVerifiedAndWithTickets')
            ->willReturn([$listingWithTicket]);

        $marketplaceService = new MarketPlaceService(
            $this->marketplace,
            new ListingService($mockedListingRepository)
        );
        $marketplaceService->setListingToSell($listingWithTicket);
        $marketplaceService->setListingToSell($listingWithoutTicket);
        $marketplaceService->buyTicket(new Buyer('buyerUser'), new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401C'));

        $listingsForSale = $marketplaceService->getOnlyVerifiedAndWithTicketsListingsForSale();

        $this->assertCount(1, $listingsForSale);
        $this->assertSame($listingWithTicket, $listingsForSale[0]);
    }


    /**
     * @test
     */
    public function it_should_not_be_possible_to_buy_the_same_ticket_twice(): void
    {
        $this->expectException(TicketAlreadySoldException::class);

        $listing = $this->createListing(
            id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
            seller: new Seller('Pascal'),
            tickets:
            [
                $this->createTicket(
                    id: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                    barcodes: [new Barcode('EAN-13', '38974312923')],
                    buyer: new Buyer('Sarah')
                )
            ]
        );

        $marketplace = new Marketplace(
            listingsForSale: [
                $listing
            ]
        );

        $inMemoryListingRepository = new InMemoryListingRepository();
        $listingService = new ListingService($inMemoryListingRepository);
        $marketplaceService = new MarketPlaceService(
            $marketplace,
            $listingService
        );

        $marketplaceService->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B')
        );
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_put_a_listing_for_sale(): void
    {
        $listing = $this->createListing(
            id: new ListingId('26A7E5C4-3F59-4B3C-B5EB-6F2718BC31AD'),
            seller: new Seller('Tom'),
            tickets:
            [
                $this->createTicket()
            ]
        );

        $marketplace = new Marketplace(
            listingsForSale: []
        );

        $inMemoryListingRepository = new InMemoryListingRepository();
        $listingService = new ListingService($inMemoryListingRepository);
        $marketplaceService = new MarketPlaceService(
            $marketplace,
            $listingService
        );

        $marketplaceService->setListingToSell(
            $listing
        );

        $listingsForSale = $marketplaceService->getListingsForSale();

        $this->assertSame($listing->getSeller(), $listingsForSale[0]->getSeller());
    }

    /**
     * @test
     */
    public function it_should_not_be_possible_to_sell_a_ticket_with_a_barcode_that_is_already_for_sale(): void
    {
        $this->expectException(ListingCreationException::class);
        $this->expectExceptionMessage('Ticket with barcode EAN-13:38974312923 is already for sale.');

        $existingTicket = $this->createTicket(
            id: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
            barcodes: [new Barcode('EAN-13', '38974312923')]
        );

        $existingListing = $this->createListing(
            id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
            tickets: [$existingTicket]
        );

        $mockedListingRepository = $this->createMock(InMemoryListingRepository::class);
        $mockedListingRepository->method('findTicketByBarcode')
            ->willReturn($existingTicket);

        $marketplaceService = new MarketPlaceService(
            $this->marketplace,
            new ListingService($mockedListingRepository)
        );

        $marketplaceService->setListingToSell(
            $existingListing
        );

        $newListing = new Listing(
            id: new ListingId('26A7E5C4-3F59-4B3C-B5EB-6F2718BC31AD'),
            seller: new Seller('Tom'),
            tickets: [
                new Ticket(
                    new TicketId('45B96761-E533-4925-859F-3CA62182848E'),
                    [
                        new Barcode('EAN-13', '38974312923')
                    ]
                ),
            ],
            price: new Money(4950, new Currency('EUR')),
        );

        $marketplaceService->setListingToSell(
            $newListing
        );
    }

    /**
     * @test
     */
    public function it_should_be_possible_for_a_buyer_of_a_ticket_to_sell_it_again(): void
    {

        $originalListing = $this->createListing(
            id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
            seller: new Seller('John'),
            tickets:
            [
                $this->createTicket(
                    id: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                    barcodes: [new Barcode('EAN-13', '38974312923')]
                )
            ],
            isVerified: true,
            verifiedBy: new Admin('AdminUser')
        );

        $mockedListingRepository = $this->createMock(InMemoryListingRepository::class);
        $mockedListingRepository->method('findAll')
            ->willReturn([$originalListing]);
        $mockedListingRepository->method('findAllVerifiedAndWithTickets')
            ->willReturn([$originalListing]);

        $marketplaceService = new MarketPlaceService(
            $this->marketplace,
            new ListingService($mockedListingRepository)
        );


        $marketplaceService->setListingToSell($originalListing);

        $boughtTicket = $marketplaceService->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B')
        );

        $this->assertTrue($boughtTicket->isBought());

        $resaleListing = $this->createListing(
            id: new ListingId('26A7E5C4-3F59-4B3C-B5EB-6F2718BC31AD'),
            seller: new Seller('Sarah'),
            tickets: [
                $this->createTicket(
                    id: new TicketId('45B96761-E533-4925-859F-3CA62182848E'),
                    barcodes: [new Barcode('EAN-13', '38974312923')]
                )
            ],
            isVerified: true,
            verifiedBy: new Admin('AdminUser')
        );

        $marketplaceService->setListingToSell($resaleListing);

        $listingsForSale = $marketplaceService->getOnlyVerifiedAndWithTicketsListingsForSale();

        $this->assertGreaterThanOrEqual(1, count($listingsForSale));
    }
}