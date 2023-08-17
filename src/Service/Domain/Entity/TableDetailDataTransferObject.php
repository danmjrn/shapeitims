<?php


namespace App\Service\Domain\Entity;

use App\Entity\Invitee;
use App\Entity\TableDetail;
use App\Service\Domain\Exception\MissingAttributeException;

class TableDetailDataTransferObject extends DataTransferObject
{
    /**
     * @param array $tableDetails
     * @return array
     */
    public function convertToDataTransferObjects(array $tableDetails): array
    {
        $tableDetailDTOs = [];

        foreach ($tableDetails as $tableDetail) {
            $tableDetailDTOs[] = $this->fromEntity($tableDetail);
        }

        return $tableDetailDTOs;
    }

    /**
     * @param TableDetail $tableDetail
     * @return $this
     */
    public function fromEntity(TableDetail $tableDetail): self
    {
        $dto = new static();

        $dto->capacity = $tableDetail->getCapacity();
        $dto->alias = $tableDetail->getAlias();
        $dto->number = $tableDetail->getNumber();
        $dto->order = $tableDetail->getOrder();
        $dto->venueName = $tableDetail->getVenue()->getName();

        if ($tableDetail->getSeatPlacement())
            if( $tableDetail->getSeatPlacement()->getInvitees() )
                foreach ($tableDetail->getSeatPlacement()->getInvitees() as $invitee ) {
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
     * @param TableDetail|null $tableDetail
     * @return TableDetail
     * @throws MissingAttributeException
     */
    public function toEntity(array $data, TableDetail $tableDetail = null): TableDetail
    {
        if
        (
            empty($data['capacity']) ||
            empty($data['alias']) ||
            empty($data['number'])
        )
            throw new MissingAttributeException();

        if (is_null($tableDetail))
            $tableDetail = new TableDetail();

        $tableDetail->setCapacity($data['capacity']);
        $tableDetail->setAlias($data['alias']);
        $tableDetail->setNumber($data['number']);
        $tableDetail->setOrder($data['order']);

        return $tableDetail;
    }
}




