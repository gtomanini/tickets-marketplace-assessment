<?php

namespace TicketsMarketplace\Assessment\Repository;

use TicketsMarketplace\Assessment\Entity\Barcode;
use TicketsMarketplace\Assessment\Entity\Listing;
use TicketsMarketplace\Assessment\Entity\Ticket;
use TicketsMarketplace\Assessment\Interface\ListingRepositoryInterface;

class InMemoryListingRepository implements ListingRepositoryInterface
{
    /** @var Listing[] */
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
     * @param Listing $listing listing to be updated
     * @return void
     */
    public function update(Listing $listing): void
    {
        foreach ($this->listings as $key => $existingListing) {
            if ($existingListing->getId() === $listing->getId()) {
                $this->listings[$key] = $listing;
                return;
            }
        }
    }

    /**
     * @return array<Listing> all verified listings
     */
    public function findAllVerified(): array
    {
        $verifiedListings = array_filter(
            $this->listings,
            function (Listing $listing): bool {
                return $listing->isVerified();
            }
        );

        return array_values($verifiedListings);
    }

    /**
     * @param Barcode $barcode barcode to search for
     * @return Ticket|null returns the listing if found, null otherwise
     */
    public function findTicketByBarcode(Barcode $barcode): ?Ticket
    {
        foreach ($this->listings as $listing) {
            foreach ($listing->getTickets() as $ticket) {
                foreach($ticket->getBarcodes() as $ticketBarcode) { 
                    if ($ticketBarcode === $barcode) {
                        return $ticket;
                    }
                }
                
            }
        }

        return null;
    }

    /**
     * @return array<Listing> all verified and with available tickets
    */
    public function findAllVerifiedAndWithTickets(): array
    {
        $verifiedListings = array_filter(
            $this->listings,
            function (Listing $listing): bool {
                return $listing->isVerified() && count($listing->getTickets()) > 0;
            }
        );

        return array_values($verifiedListings);
    }
}