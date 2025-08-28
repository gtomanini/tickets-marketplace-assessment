<?php

namespace TicketSwap\Assessment\Service;

use TicketSwap\Assessment\Entity\Buyer;
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
    public function getListingsForSale() : array
    {
        return $this->marketplace->getListingsForSale();
    }

    /**
     * @return Ticket
     */
    public function buyTicket(Buyer $buyer, TicketId $ticketId) : Ticket
    {
        foreach($this->marketplace->getListingsForSale() as $listing) {
            foreach($listing->getTickets() as $ticket) {
                if ($ticket->getId()->equals($ticketId)) {
                   return $ticket->buyTicket($buyer); 
                }
            }
        }
        throw new \InvalidArgumentException(sprintf("Ticket with ID %s not found for sale.", (string) $ticketId));
    }

}