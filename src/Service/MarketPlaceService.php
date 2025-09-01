<?php

namespace TicketSwap\Assessment\Service;

use TicketSwap\Assessment\Entity\Buyer;
use TicketSwap\Assessment\Entity\Listing;
use TicketSwap\Assessment\Entity\Marketplace;
use TicketSwap\Assessment\Entity\Ticket;
use TicketSwap\Assessment\Entity\TicketId;
use TicketSwap\Assessment\Exception\ListingNotVerifiedException;
use TicketSwap\Assessment\Exception\TicketAlreadySoldException;

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
        $this->listingService->createListing($listing);
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
     * @return array<Listing>
     * @return null if no listings are available for sale
     */
    public function getVerifiedListingsForSale() : ?array
    {
        $allListings = $this->listingService->getAllVerifiedListings();

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
     * @throws ListingNotVerifiedException if the listing is not verified
     */
    public function buyTicket(Buyer $buyer, TicketId $ticketId) : Ticket
    {
        foreach($this->listingService->findAll() as $listing) {
            foreach($listing->getTickets() as $ticket) {
                if ($ticket->getId()->equals($ticketId) && !$ticket->isBought()) {
                    if($listing->isVerified() === false) {
                        throw new ListingNotVerifiedException('Cannot buy ticket from unverified listing.');
                    }
                   return $ticket->buyTicket($buyer); 
                
                }
            }
        }
        throw new TicketAlreadySoldException();
    }

}