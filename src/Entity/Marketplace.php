<?php

namespace TicketSwap\Assessment\Entity;

final class Marketplace
{
    /**
     * @param array<Listing> $listingsForSale
     */
    public function __construct(private array $listingsForSale = [])
    {
    }

    /**
     * @return array<Listing>
     */
    public function getListingsForSale() : array
    {
        return $this->listingsForSale;
    }

    /**
     * @param Listing $listing
     * @return void
     */
    public function setListingForSale(Listing $listing) : void
    {
       $this->listingsForSale[] = $listing;
    }
}
