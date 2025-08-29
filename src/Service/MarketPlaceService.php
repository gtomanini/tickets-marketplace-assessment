<?php

namespace TicketSwap\Assessment\Service;

use TicketSwap\Assessment\Entity\Buyer;
use TicketSwap\Assessment\Entity\Listing;
use TicketSwap\Assessment\Entity\Marketplace;
use TicketSwap\Assessment\Entity\Ticket;
use TicketSwap\Assessment\Entity\TicketId;

final class MarketPlaceService
{
    public function __construct(private Marketplace $marketplace)
    {
    }

    /**
     * @return array<Listing>
     */
    public function getListingsForSale() : ?array
    {
        $allListings = $this->marketplace->getListings();

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
     * @return Ticket
     */
    public function buyTicket(Buyer $buyer, TicketId $ticketId) : Ticket
    {
        foreach($this->marketplace->getListings() as $listing) {
            foreach($listing->getTickets() as $ticket) {
                if ($ticket->getId()->equals($ticketId)) {
                   return $ticket->buyTicket($buyer); 
                }
            }
        }
        throw new \InvalidArgumentException(sprintf("Ticket with ID %s not found for sale.", (string) $ticketId));
    }

}