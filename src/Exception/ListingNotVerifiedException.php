<?php

namespace TicketsMarketplace\Assessment\Exception;

use DomainException;
use TicketsMarketplace\Assessment\Entity\Listing;

final class ListingNotVerifiedException extends DomainException
{
    public static function withListing(Listing $listing): self
    {
        return new self(
            sprintf(
                'Listing (%s) has not been verified and cannot be purchased',
                (string) $listing->getId()
            )
        );
    }
}