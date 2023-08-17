<?php


namespace App\Service\Domain\Entity;


use App\Entity\InvitationGroup;
use App\Entity\Invitee;
use App\Service\Domain\Exception\MissingAttributeException;

class InvitationGroupDataTransferObject extends DataTransferObject
{
    /**
     * @param InvitationGroup $invitationGroup
     * @return $this
     */
    public function fromEntity( InvitationGroup $invitationGroup ): self
    {
        $invitationGroupDTO = new static();

        $invitationGroupDTO->id = $invitationGroup->getId();
        $invitationGroupDTO->invitationAlias = $invitationGroup->getInvitationAlias();
        $invitationGroupDTO->type = $invitationGroup->getType();

        if( $invitationGroup->getInvitee()->getUuid() )
            $invitationGroupDTO->uuid = $invitationGroup->getInvitee()->getUuid();

        return $invitationGroupDTO;
    }

    /**
     * @param string $invitationType
     * @param string $randomAlias
     * @param InvitationGroup|null $invitationGroup
     * @return InvitationGroup
     * @throws MissingAttributeException
     */
    public function toEntity(string $invitationType, string $randomAlias, InvitationGroup $invitationGroup = null): InvitationGroup
    {
        if
            (
                empty( $invitationType ) ||
                empty( $randomAlias )
            )
            throw new MissingAttributeException();

        if (is_null($invitationGroup))
            $invitationGroup = new InvitationGroup();

        $invitationGroup
            ->setInvitationAlias( $randomAlias )
            ->setType( $invitationType )
            ->setInvitee(new Invitee())
        ;

        return $invitationGroup;
    }
}