<?php


namespace App\Service\Domain\Entity;


use App\Entity\TableDetail;
use App\Entity\Venue;

class VenueDataTransferObject extends DataTransferObject
{
    /**
     * @param array $venues
     * @return array
     */
    public function convertToDataTransferObjects(array $venues): array
    {
        $venueDTOs = [];

        foreach ($venues as $venue) {
            $venueDTOs[] = $this->fromEntity($venue);
        }

        return $venueDTOs;
    }

    /**
     * @param Venue $venue
     * @return $this
     */
    public function fromEntity(Venue $venue): self
    {
        $dto = new static();

        $dto->name = $venue->getName();
        $dto->address = $venue->getAddress();
        $dto->mapLink = $venue->getMapLink();
        $dto->description = $venue->getDescription();

        foreach ($venue->getTableDetails() as $tableDetail) {
            $dto->tableDetails[] = $this->fromTableDetail($tableDetail);
        }

        return $dto;
    }

    /**
     * @param TableDetail $tableDetail
     * @return array
     */
    public function fromTableDetail(TableDetail $tableDetail): array
    {
        return [
            'capacity' => $tableDetail->getCapacity(),
            'alias' => $tableDetail->getAlias(),
            'number' => $tableDetail->getNumber(),
            // Add other properties from TableDetail entity as needed
        ];
    }

    /**
     * @param Venue $venue
     * @return Venue
     */
    public function toEntity(Venue $venue): Venue
    {
        // Implement the logic to update the Venue entity based on the DTO properties
        $venue->setName($this->name);
        $venue->setAddress($this->address);
        $venue->setMapLink($this->mapLink);
        $venue->setDescription($this->description);

        // Update the related TableDetail entities if needed

        return $venue;
    }
}
