<?php

namespace TicketSwap\Assessment\Service;

use TicketSwap\Assessment\Entity\Buyer;
use TicketSwap\Assessment\Entity\Listing;
use TicketSwap\Assessment\Entity\Marketplace;
use TicketSwap\Assessment\Entity\Ticket;
use TicketSwap\Assessment\Entity\TicketId;
use TicketSwap\Assessment\Exception\TicketAlreadySoldException;
use TicketSwap\Assessment\Repository\ListingRepository;

final class MarketPlaceService
{
    public function __construct(private Marketplace $marketplace, private ListingService $listingService)
    {
    }

    /**
     * @param Listing $listing
     * @return void
     */
    public function setListingToSell(Listing $listing): void
    {
        foreach ($listing->getTickets() as $ticket) {
            // TODO implement to check if barcode is already for sale
        }

        $this->listingService->createListing($listing->getSeller(), $listing->getTickets(), $listing->getPrice());
    }

    /**
     * @return array<Listing>
     * @return null if no listings are available for sale
     */
    public function getListingsForSale() : ?array
    {
        $allListings = $this->listingService->findAll();

        $filteredListings = array_filter(
            $allListings,
            function (Listing $listing): bool {
                return count($listing->getTickets()) > 0;
            }
        );

        if (empty($filteredListings)) {
            return null;
        }

        return array_values($filteredListings);
    }

    /**
     * @param Buyer $buyer
     * @param TicketId $ticketId
     * @return Ticket if ticket is available for purchase
     * @throws TicketAlreadySoldException if the ticket is already sold
     */
    public function buyTicket(Buyer $buyer, TicketId $ticketId) : Ticket
    {
        foreach($this->listingService->findAll() as $listing) {
            foreach($listing->getTickets() as $ticket) {
                if ($ticket->getId()->equals($ticketId) && !$ticket->isBought()) {
                   return $ticket->buyTicket($buyer); 
                
                }
            }
        }
        throw new TicketAlreadySoldException();
    }

}