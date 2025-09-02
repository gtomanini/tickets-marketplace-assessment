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
        $this->marketplace->setListingForSale($listing);
    }

    /**
     * @return array<Listing>
     */
    public function getListingsForSale() : array
    {
        return $this->marketplace->getListingsForSale();
    }

    /**
     * @return array<Listing>
     */
    public function getOnlyVerifiedAndWithTicketsListingsForSale() : array
    {
        return $this->listingService->getOnlyVerifiedAndWithTicketsListings();
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
        foreach($this->marketplace->getListingsForSale() as $listing) {
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