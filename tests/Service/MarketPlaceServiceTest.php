<?php

namespace TicketSwap\Assessment\tests\Service;

use PHPUnit\Framework\TestCase;
use Money\Currency;
use Money\Money;
use TicketSwap\Assessment\Entity\Barcode;
use TicketSwap\Assessment\Entity\Buyer;
use TicketSwap\Assessment\Entity\Listing;
use TicketSwap\Assessment\Entity\ListingId;
use TicketSwap\Assessment\Entity\Marketplace;
use TicketSwap\Assessment\Entity\Seller;
use TicketSwap\Assessment\Entity\Ticket;
use TicketSwap\Assessment\Entity\TicketId;
use TicketSwap\Assessment\Exception\ListingCreationException;
use TicketSwap\Assessment\Exception\TicketAlreadySoldException;
use TicketSwap\Assessment\Repository\ListingRepository;
use TicketSwap\Assessment\Service\ListingService;
use TicketSwap\Assessment\Service\MarketPlaceService;

class MarketPlaceServiceTest extends TestCase
{

    /**
     * @test
     */
    public function it_should_list_all_the_tickets_for_sale()
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
        
        $marketplace = new Marketplace(listingsForSale: []);

        $marketplaceService = new MarketPlaceService(
            $marketplace, new ListingService(new ListingRepository())
        );

        $marketplaceService->setListingToSell($listing);

        $listingsForSale = $marketplaceService->getListingsForSale();

        $this->assertSame($listing->getTickets(), $listingsForSale[0]->getTickets());
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_buy_a_ticket()
    {
        $listingWithTicket = new Listing(
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

        $marketplace = new Marketplace(
            listingsForSale: [
                $listingWithTicket
            ]
        );

        $mockedListingRepository = $this->createMock(ListingRepository::class);
        $mockedListingRepository->method('findAll')
            ->willReturn([$listingWithTicket]);

        $marketplaceService = new MarketPlaceService(
            $marketplace, new ListingService($mockedListingRepository)
        );

        $boughtTicket = $marketplaceService->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B')
        );

        $this->assertNotNull($boughtTicket);
        $this->assertSame('EAN-13:38974312923', (string) $boughtTicket->getBarcode());
    }

    /**
     * @test
     */
    public function it_should_not_list_empty_listings_for_sale() : void 
    {
        $listingWithTicket = new Listing(
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

        $listingWithoutTicket = new Listing(
                    id: new ListingId('26A7E5C4-3F59-4B3C-B5EB-6F2718BC31AD'),
                    seller: new Seller('Tom'),
                    tickets: [],
                    price: new Money(4950, new Currency('EUR')),
        );

        $marketplace = new Marketplace(
            listingsForSale: []
        );
        
        $mockedListingRepository = $this->createMock(ListingRepository::class);
        $mockedListingRepository->method('findAll')
            ->willReturn([$listingWithTicket, $listingWithoutTicket]);

        $marketplaceService = new MarketPlaceService(
            $marketplace, new ListingService($mockedListingRepository)
        );
        
        $listingsForSale = $marketplaceService->getListingsForSale();

        $this->assertNotNull($listingsForSale);
        $this->assertCount(1, $listingsForSale);
        $this->assertSame($listingWithTicket, $listingsForSale[0]);
    }


    /**
     * @test
     */
    public function it_should_not_be_possible_to_buy_the_same_ticket_twice()
    {
        $this->expectException(TicketAlreadySoldException::class);

        $listing = new Listing(
                    id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
                    seller: new Seller('Pascal'),
                    tickets: [
                        new Ticket(
                            new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                            new Barcode('EAN-13', '38974312923'),
                            new Buyer('Sarah')
                        ),
                    ],
                    price: new Money(4950, new Currency('EUR')),
                );

        $marketplace = new Marketplace(
            listingsForSale: [
                $listing
            ]
        );


        $marketplaceService = new MarketPlaceService(
            $marketplace,
            new ListingService(new ListingRepository())
        );

        $marketplaceService->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B')
        );
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_put_a_listing_for_sale()
    {
        $listing = new Listing(
                id: new ListingId('26A7E5C4-3F59-4B3C-B5EB-6F2718BC31AD'),
                seller: new Seller('Tom'),
                tickets: [
                    new Ticket(
                        new TicketId('45B96761-E533-4925-859F-3CA62182848E'),
                        new Barcode('EAN-13', '893759834')
                    ),
                ],
                price: new Money(4950, new Currency('EUR')),
            );

        $marketplace = new Marketplace(
            listingsForSale: []
        );

        $marketplaceService = new MarketPlaceService(
            $marketplace, new ListingService(new ListingRepository())
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
    public function it_should_not_be_possible_to_sell_a_ticket_with_a_barcode_that_is_already_for_sale()
    {
        $this->expectException(ListingCreationException::class);
        $this->expectExceptionMessage('Ticket with barcode EAN-13:38974312923 is already for sale.');

        $existingTicket = new Ticket(
                        new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                        new Barcode('EAN-13', '38974312923')
            );

        $existingListing = new Listing(
                id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
                seller: new Seller('Pascal'),
                tickets: [
                    $existingTicket
                ],
                price: new Money(4950, new Currency('EUR')),
            );

        $mockedListingRepository = $this->createMock(ListingRepository::class);
        $mockedListingRepository->method('findTicketByBarcode')
            ->willReturn($existingTicket);

        $marketplace = new Marketplace(
            listingsForSale: []
        );

        $marketplaceService = new MarketPlaceService(
            $marketplace, new ListingService($mockedListingRepository)
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
                        new Barcode('EAN-13', '38974312923')
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
    public function it_should_be_possible_for_a_buyer_of_a_ticket_to_sell_it_again()
    {
        $repository = new ListingRepository();
        $marketplace = new Marketplace(listingsForSale: []);
        $marketplaceService = new MarketPlaceService(
            $marketplace, new ListingService($repository)
        );

        $originalListing = new Listing(
                id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
                seller: new Seller('John'),
                tickets: [
                    new Ticket(
                        new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                        new Barcode('EAN-13', '38974312923')
                    ),
                ],
                price: new Money(4950, new Currency('EUR')),
            );

        $marketplaceService->setListingToSell($originalListing);

        $boughtTicket = $marketplaceService->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B')
        );

        $this->assertTrue($boughtTicket->isBought());

        $resaleListing = new Listing(
                id: new ListingId('26A7E5C4-3F59-4B3C-B5EB-6F2718BC31AD'),
                seller: new Seller('Sarah'),
                tickets: [
                    new Ticket(
                        new TicketId('45B96761-E533-4925-859F-3CA62182848E'),
                        new Barcode('EAN-13', '38974312923')
                    ),
                ],
                price: new Money(5500, new Currency('EUR')),
            );

        $marketplaceService->setListingToSell($resaleListing);

        $listingsForSale = $marketplaceService->getListingsForSale();

        $this->assertNotNull($listingsForSale);
        $this->assertGreaterThanOrEqual(1, count($listingsForSale));
    }


}