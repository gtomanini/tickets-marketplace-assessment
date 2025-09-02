<?php

namespace TicketSwap\Assessment\Interface;

use TicketSwap\Assessment\Entity\Barcode;
use TicketSwap\Assessment\Entity\Listing;
use TicketSwap\Assessment\Entity\Ticket;

interface ListingRepositoryInterface
{

    /**
     * @param Listing $listing listing to be saved
     * @return void
     */
    public function save(Listing $listing): void;
    
    
    /**
     * @param \TicketSwap\Assessment\Entity\Listing $listing listing to be updated
     * @return void
     */
    public function update(Listing $listing) : void;

    /**
     * @return array<Listing> all listings
     */
    public function findAll(): array;

    /**
     * @param Barcode $barcode barcode to search for
     * @return Ticket|null returns the listing if found, null otherwise
     */
    public function findTicketByBarcode(Barcode $barcode): ?Ticket;

    /**
     * @return array<Listing> all verified listings
     */
    public function findAllVerified(): array;

    /**
     * @return array<Listing> all verified and with available tickets
    */
    public function findAllVerifiedAndWithTickets() : array;
}