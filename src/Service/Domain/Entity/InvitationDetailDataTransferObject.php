<?php


namespace App\Service\Domain\Entity;

use App\Entity\InvitationDetail;
use App\Entity\Venue;
use App\Entity\Media;

class InvitationDetailDataTransferObject extends DataTransferObject
{

    /**
     * @param array $invitationDetails
     * @return array
     */
    public function convertToDataTransferObjects(array $invitationDetails): array
    {
        $invitationDetailDTOs = [];

        foreach ($invitationDetails as $invitationDetail) {
            $invitationDetailDTOs[] = $this->fromEntity($invitationDetail);
        }

        return $invitationDetailDTOs;
    }

    /**
     * @param InvitationDetail $invitationDetail
     * @return $this
     */
    public function fromEntity(InvitationDetail $invitationDetail): self
    {
        $dto = new static();

        $dto->id = $invitationDetail->getId();
        $dto->content = $invitationDetail->getContent();
        $dto->type = $invitationDetail->getType();
        $dto->maximumDistribution = $invitationDetail->getMaximumDistribution();
        $dto->eventDate = $invitationDetail->getEventDate();

        foreach ($invitationDetail->getVenues() as $venue) {
            $dto->venues[] = $this->fromVenue($venue);
        }

        foreach ($invitationDetail->getMedias() as $media) {
            $dto->media[] = $this->fromMedia($media);
        }

        return $dto;
    }

    /**
     * @param Venue $venue
     * @return array
     */
    private function fromVenue(Venue $venue): array
    {
        // Create and return Venue DTO here
        // You can follow a similar pattern to the fromEntity method
        // to create a VenueDataTransferObject and populate its properties
        // based on the properties of the $venue entity.
    }

    /**
     * @param Media $media
     * @return array
     */
    private function fromMedia(Media $media): array
    {
        // Create and return Media DTO here
        // Similar to the fromVenue method, create a MediaDataTransferObject
        // and populate its properties based on the $media entity.
    }

    /**
     * @param array $data
     * @param InvitationDetail|null $invitationDetail
     * @return InvitationDetail
     */
    public function toEntity(array $data, InvitationDetail $invitationDetail = null): InvitationDetail
    {
        if (is_null($invitationDetail)) {
            $invitationDetail = new InvitationDetail();
        }

        // Populate the properties of $invitationDetail based on $data array
        // Similar to the toEntity method in InvitationDataTransferObject

        return $invitationDetail;
    }
}
