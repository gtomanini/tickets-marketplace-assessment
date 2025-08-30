<?php
declare(strict_types=1);

namespace TicketSwap\Assessment\Repository;

use TicketSwap\Assessment\Entity\Barcode;
use TicketSwap\Assessment\Entity\Listing;
use TicketSwap\Assessment\Entity\Ticket;

class ListingRepository
{
    private array $listings = [];

    /**
     * @param Listing $listing listing to be saved
     * @return void
     */
    public function save(Listing $listing): void
    {
        $this->listings[] = $listing;
    }

    /**
     * @return array<Listing> all listings
     */
    public function findAll(): array
    {
        return $this->listings;
    }

    /**
     * @param string $barcode barcode to search for
     * @return Ticket|null returns the listing if found, null otherwise
     */
    public function findTicketByBarcode(Barcode $barcode): ?Ticket
    {
        foreach ($this->listings as $listing) {
            foreach ($listing->getTickets() as $ticket) {
                if ($ticket->getBarcode() === $barcode) {
                    return $ticket;
                }
            }
        }

        return null;
    }
    
}