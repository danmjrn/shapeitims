<?php


namespace App\Service\Domain\Entity;


use App\Entity\Invitation;
use App\Entity\Invitee;
use App\Service\Domain\Exception\MissingAttributeException;

class InvitationDataTransferObject extends DataTransferObject
{
    /**
     * @param array $invitations
     * @return array
     */
    public function convertToDataTransferObjects(array $invitations): array
    {
        $invitationsDTO = [];

        foreach ($invitations as $invitation) {
            $invitationsDTO[] = $this->fromEntity( $invitation );
        }

        return $invitationsDTO;
    }

    /**
     * @param Invitation $invitation
     * @return $this
     */
    public function fromEntity( Invitation $invitation ): self
    {
        $invitationDTO = new static();

        $invitationDTO->uuid = $invitation->getUuid();
        $invitationDTO->alias = $invitation->getAlias();
        $invitationDTO->rsvp = $invitation->getRsvp();
        $invitationDTO->timesOpened = $invitation->getTimesOpened();
        $coupleAddressTo = '';
        foreach ( $invitation->getInvitationGroups() as $invitationGroup )
            if ( $invitationGroup->getInvitee() ) {
                /** @var Invitee $invitee */
                $invitee = $invitationGroup->getInvitee();

                $invitationDTO->invitees[] = $this->fromInvitee( $invitee );



                switch ( count( $invitation->getInvitationGroups() ) ) {
                    case 1: {
                        $invitationDTO->addressedTo = $invitee->getTitle() . ' '
                            . $invitee->getFirstname() . ' ' . $invitee->getLastname();
                        break;
                    }
                    case 2:
                    case 4: {
                                $coupleAddressTo .= $invitee->getTitle() . ' ' . $invitee->getFirstname() . ' '
                                    .$invitee->getLastname() . ' & ';
                        $invitationDTO->addressedTo = 'Couple ' . $coupleAddressTo;
                        break;
                    }
                    default: {
                        $invitationDTO->addressedTo = 'Family/Group ' . $invitee->getLastname();
                        break;
                    }
                }

                $invitationDTO->invitationFrom = $invitee->getInviteeFrom();
                $invitationDTO->invitationLang = $invitee->getInviteeLang();
            }

        return $invitationDTO;
    }

    public function fromInvitee( Invitee $user ): self
    {
        $dto = new static();

        $dto->entityType = Invitee::class;

        $dto->firstname = $user->getFirstname();
        $dto->lastname = $user->getLastname();
        $dto->fullNames = sprintf('%s %s', $user->getFirstname(), $user->getLastname());
        $dto->title = $user->getTitle();
        $dto->inviteeLang = $user->getInviteeLang();

        return $dto;
    }

    /**
     * @param array $data
     * @param Invitation|null $invitation
     * @return Invitation
     * @throws MissingAttributeException
     */
    public function toEntity(array $data, Invitation $invitation = null): Invitation
    {
        if
            (
                empty($data['alias'])
            )
            throw new MissingAttributeException();

        if (is_null($invitation))
            $invitation = new Invitation();

        $invitation
            ->setAlias($data['alias'])
        ;

        return $invitation;
    }

    /**
     * @param Invitation $invitation
     * @param string $rsvp
     * @return bool
     */
    public function updateRsvp(Invitation &$invitation, string $rsvp): bool
    {
        if ($invitation->getRsvp() === $rsvp)
            return false;

        $invitation->setRsvp($rsvp);

        return true;
    }

    /**
     * @param Invitation $invitation
     * @return void
     */
    public function updateTimesOpened(Invitation &$invitation): void
    {
        $invitation->setTimesOpened($invitation->getTimesOpened() + 1);
    }
}