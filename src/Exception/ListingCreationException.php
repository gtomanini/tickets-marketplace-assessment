<?php

namespace TicketsMarketplace\Assessment\Exception;

use DomainException;
final class ListingCreationException extends DomainException
{
    public static function withReason(string $reason): self
    {
        return new self('Could not create listing: ' . $reason);
    }
}