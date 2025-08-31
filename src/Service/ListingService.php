<?php
declare(strict_types=1);

namespace TicketSwap\Assessment\Service;

use TicketSwap\Assessment\Entity\Listing;
use TicketSwap\Assessment\Entity\Seller;
use TicketSwap\Assessment\Entity\ListingId;
use TicketSwap\Assessment\Exception\ListingCreationException;
use Money\Money;
use Ramsey\Uuid\Uuid;
use TicketSwap\Assessment\Entity\Barcode;
use TicketSwap\Assessment\Entity\Buyer;
use TicketSwap\Assessment\Repository\ListingRepository;

final class ListingService {
    
    public function __construct(private ListingRepository $listingRepository) 
    {
    }

    /**
     * Retrieves all listings.
     *
     * @return array<Listing> an array of all listings
     */
    public function findAll(): array
    {
        return $this->listingRepository->findAll();
    }

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

        $this->checkForDuplicateBarcodeOnListing($tickets);
        $this->checkForDuplicatedBarcodeOnMarketplace($tickets, $seller);

        try {
            $listing = new Listing(
                id: new ListingId(Uuid::uuid4()->toString()),
                seller: $seller,
                tickets: $tickets, 
                price: $price
            );
            $this->listingRepository->save($listing);
        } catch (\InvalidArgumentException $e) {
            throw ListingCreationException::withReason('The listing composition is invalid: ' . $e->getMessage());
        }

        return $listing;
    }

    /**
     * Search for duplicate barcodes in the provided tickets array.
     *
     * @param array $tickets
     * @throws ListingCreationException if duplicates are found.
     */
    private function checkForDuplicateBarcodeOnListing(array $tickets): void
    {
        $barcodes = [];
        foreach ($tickets as $ticket) {
            $barcodeValue = $ticket->getBarcode();

            if (in_array($barcodeValue, $barcodes)) {
                throw ListingCreationException::withReason(
                    sprintf('Duplicate barcode found in the listing: %s', $barcodeValue)
                );
            }

            $barcodes[] = $barcodeValue;
        }
    }


    /**     
     * Check if any of the tickets' barcodes are already listed in the marketplace.
     *
     * @param array $tickets
     * @throws ListingCreationException if any ticket's barcode is already for sale.
     */
    private function checkForDuplicatedBarcodeOnMarketplace(array $tickets, Seller $seller): void
    {
        foreach ($tickets as $ticket) {
            if ($this->isBarcodeAlreadyForSale($ticket->getBarcode())) {
                $createdTicket = $this->listingRepository->findTicketByBarcode($ticket->getBarcode());
                if( $createdTicket->isBought() && $this->isSellerTheLastBuyer($seller, $createdTicket->getBuyer()) ) {
                    continue;
                }
                throw ListingCreationException::withReason(
                    sprintf('Ticket with barcode %s is already for sale.', $ticket->getBarcode())
                );
            }
        }
    }

    /**
     * Check if a barcode is already listed for sale in the marketplace.
     *
     * @param string $barcode
     * @return bool true if the barcode is already for sale, false otherwise.
     */
    private function isBarcodeAlreadyForSale(Barcode $barcode): bool
    {
        $existingTicket = $this->listingRepository->findTicketByBarcode($barcode);
        return $existingTicket !== null;
    }

    /**
     * Check if the Seller of the ticket is the last Buyer
     * @param Seller $seller 
     * @param Buyer $buyer
     * @return bool
     */
    private function isSellerTheLastBuyer(Seller $seller, Buyer $buyer): bool
    {
        if ($buyer === null) {
            return false;
        }
        return $seller == $buyer;
    }
}