<?php
declare(strict_types=1);

namespace TicketSwap\Assessment\Repository;

use TicketSwap\Assessment\Entity\Listing;

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
    
}