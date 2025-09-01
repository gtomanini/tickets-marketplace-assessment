<?php
declare(strict_types=1);

namespace TicketSwap\Assessment\Service;

use TicketSwap\Assessment\Entity\Listing;
use TicketSwap\Assessment\Entity\Seller;
use TicketSwap\Assessment\Exception\ListingCreationException;
use Money\Money;
use TicketSwap\Assessment\Entity\Admin;
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
        return $this->filterOnlyListingWithTickets($this->listingRepository->findAll());
    }

    /**
     * Retrieves all verified listings.
     *
     * @return array<Listing> an array of all verified listings
     */
    public function getAllVerifiedListings(): array
    {
        return $this->filterOnlyListingWithTickets($this->listingRepository->findAllVerified());
    }

    /**
     * Updates an existing listing.
     *
     * @param Listing $listing the listing to update
     * @return void
     */
    public function updateListing(Listing $listing): void
    {
        $this->listingRepository->update($listing);

    }

    /**
     * Creates a new Listing instance for a seller.
     *
     * @param Listing $listing the listing to be created
     * @return Listing the created listing
     * @throws ListingCreationException if business rules for creation are not met.
     */
    public function createListing(Listing $listing): Listing
    {
        if (empty($listing->getTickets())) {
            throw ListingCreationException::withReason('A listing cannot be created without tickets.');
        }

        if ($listing->getPrice()->isNegative() || $listing->getPrice()->isZero()) {
            throw ListingCreationException::withReason('The listing price must be greater than zero.');
        }

        $this->checkForDuplicateBarcodeOnListing($listing->getTickets());
        $this->checkForDuplicatedBarcodeOnMarketplace($listing->getTickets(), $listing->getSeller());

        try {
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

    /**
     * Verifies a listing by an admin.
     *
     * @param Listing $listing the listing to be verified
     * @param Admin $admin the admin performing the verification
     * @return void
     */
    public function verifyListing(Listing $listing, Admin $admin): void
    {
        $listing->verifyListing($admin);
    }

    /**
     * @param array<Listing> $listings list of listings to filter
     * @return array<Listing> only listings with tickets
     */
    private function filterOnlyListingWithTickets(array $listings): array
    {
        return array_values(array_filter(
            $listings,
            function (Listing $listing): bool {
                return count($listing->getTickets()) > 0;
            }
        ));
    }
}