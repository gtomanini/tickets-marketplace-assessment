<?php
declare(strict_types=1);

namespace TicketSwap\Assessment\Service;

use TicketSwap\Assessment\Entity\Listing;
use TicketSwap\Assessment\Entity\Seller;
use TicketSwap\Assessment\Entity\ListingId;
use TicketSwap\Assessment\Exception\ListingCreationException;
use Money\Money;
use Ramsey\Uuid\Uuid;

final class ListingService {
    public function __construct() 
    {}

    /**
     * Creates a new Listing instance for a seller.
     *
     * @param Seller $seller the seller that is creating the listing
     * @param array $tickets the tickets to be included in the listing
     * @param Money $price the price for the listing
     * @return Listing the created listing
     * @throws ListingCreationException if business rules for creation are not met.
     */
    public function createListing(Seller $seller, array $tickets, Money $price): Listing
    {
        if (empty($tickets)) {
            throw ListingCreationException::withReason('A listing cannot be created without tickets.');
        }

        if ($price->isNegative() || $price->isZero()) {
            throw ListingCreationException::withReason('The listing price must be greater than zero.');
        }

        try {
            $listing = new Listing(
                id: new ListingId(Uuid::uuid4()->toString()),
                seller: $seller,
                tickets: $tickets, 
                price: $price
            );
        } catch (\InvalidArgumentException $e) {
            throw ListingCreationException::withReason('The listing composition is invalid: ' . $e->getMessage());
        }

        return $listing;
    }

}