<?php


namespace App\Service\Domain\Entity;

use App\Entity\InvitationDetail;
use App\Entity\Invitee;
use App\Entity\SeatPlacement;
use App\Entity\Venue;
use App\Entity\Media;
use App\Service\Domain\Exception\MissingAttributeException;

class SeatPlacementDataTransferObject extends DataTransferObject
{
    /**
     * @param array $seatPlacements
     * @return array
     */
    public function convertToDataTransferObjects(array $seatPlacements): array
    {
        $seatPlacementDTOs = [];

        foreach ($seatPlacements as $seatPlacement) {
            $seatPlacementDTOs[] = $this->fromEntity($seatPlacement);
        }

        return $seatPlacementDTOs;
    }

    /**
     * @param SeatPlacement $seatPlacement
     * @return $this
     */
    public function fromEntity(SeatPlacement $seatPlacement): self
    {
        $dto = new static();

        $dto->tableNumber = $seatPlacement->getTableNumber();

        foreach ($seatPlacement->getInvitees() as $invitee ) {
            $dto->invitees[] = $this->fromInvitee($invitee);
        }

        return $dto;
    }

    /**
     * @param Invitee $user
     * @return $this
     */
    public function fromInvitee( Invitee $user ): self
    {
        $dto = new static();

        $dto->entityType = Invitee::class;

        $dto->firstname = $user->getFirstname();
        $dto->lastname = $user->getLastname();
        $dto->fullNames = sprintf('%s %s', $user->getFirstname(), $user->getLastname());
        $dto->title = $user->getTitle();

        return $dto;
    }

    /**
     * @param array $data
     * @param SeatPlacement|null $seatPlacement
     * @return SeatPlacement
     * @throws MissingAttributeException
     */
    public function toEntity(array $data, SeatPlacement $seatPlacement = null): SeatPlacement
    {
        if (
            empty($data['tableNumber']) ||
            empty($data['invitees'])
        )
            throw new MissingAttributeException();

        if (is_null($seatPlacement))
            $seatPlacement = new SeatPlacement();

        $seatPlacement->setTableNumber($data['tableNumber']);

        foreach ($data['invitees'] as $invitee) {
            $seatPlacement->addInvitee($invitee);
        }

        return $seatPlacement;
    }
}
