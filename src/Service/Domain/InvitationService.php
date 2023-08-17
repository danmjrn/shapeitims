<?php


namespace App\Service\Domain;

use App\Entity\Exception\EntityNotFoundException;
use App\Entity\Exception\InvitationNotCreatedException;
use App\Entity\Invitation;
use App\Entity\InvitationGroup;
use App\Entity\Invitee;
use App\Entity\Exception\EntityNotCreatedException;

use App\Event\Domain\Invitation\InvitationAdded;
use App\Event\Domain\Invitation\InvitationRsvped;
use App\Repository\InvitationRepository;
use App\Repository\InviteeRepository;

use App\Service\Domain\Entity\InvitationDataTransferObject;

use App\Service\Domain\Exception\InvitationNotRsvpedException;
use App\Service\Domain\Exception\InvitationRsvpNotUpdatedException;
use App\Service\Domain\Exception\InvitationTimesOpenedNotUpdatedException;
use App\Service\Domain\Exception\MissingAttributeException;
use App\Service\Service;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;

use Exception;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\RequestStack;

class InvitationService extends Service
{
    /**
     * @var InvitationDataTransferObject
     */
    private InvitationDataTransferObject $invitationDataTransferObject;

    /**
     * @var InvitationRepository
     */
    private InvitationRepository $invitationRepository;

    /**
     * @var InviteeRepository
     */
    private InviteeRepository $inviteeRepository;

    public function __construct
        (
            InvitationDataTransferObject $invitationDataTransferObject,
            InvitationRepository $invitationRepository,
            InviteeRepository $inviteeRepository,
            EntityManagerInterface $entityManager,
            EventDispatcherInterface $eventDispatcher,
            LoggerInterface $logger,
//            ApplicationMailer $mailer,
            RequestStack $session,
        )
    {
        $this->invitationDataTransferObject = $invitationDataTransferObject;
        $this->invitationRepository = $invitationRepository;
        $this->inviteeRepository = $inviteeRepository;

        parent::__construct
            (
                $entityManager,
                $eventDispatcher,
                $logger,
                $session,
            );
    }

    /**
     * @param Invitation $invitation
     * @return InvitationDataTransferObject
     */
    public function convertToDataTransferObject(Invitation $invitation): InvitationDataTransferObject
    {
        return $this->invitationDataTransferObject->fromEntity( $invitation );
    }

    /**
     * @param array $invitations
     * @return array
     */
    public function convertToDataTransferObjects(array $invitations): array
    {
        return $this->invitationDataTransferObject->convertToDataTransferObjects( $invitations );
    }

    /**
     * @param string $alias
     * @param array $invitationGroups
     * @return void
     * @throws ConnectionException
     * @throws InvitationNotCreatedException
     * @throws MissingAttributeException
     * @throws \Doctrine\DBAL\Exception
     */
    public function createInvitation(string $alias, array $invitationGroups ): void
    {
        if ( ! empty($alias) )
            try {
                $this->beginTransaction();

                $invitationDataTransferObject = new InvitationDataTransferObject();

                $invitation = $invitationDataTransferObject->toEntity( [ 'alias' => $alias ] );

                foreach ( $invitationGroups as $invitationGroup) {
                    if ($invitationGroup instanceof InvitationGroup ){
                        $invitationGroup->setInvitation( $invitation );

                        $invitation->addInvitationGroup( $invitationGroup );

                        $this->persistEntity($invitation);

                        $this->persistEntity($invitationGroup);
                    }
                }

                $this->flush();

                if ($this->isEntityManagerOpen()) {
                    $this->commitTransaction();

                    $this->eventDispatcher->dispatch
                    (
                        new InvitationAdded
                        (
                            $invitationGroups,
                            $invitation,
                        )
                    );
                } else {
                    $this->rollBackTransaction();

                    throw new InvitationNotCreatedException();
                }

            } catch (Exception $exception) {
                $this->rollBackTransaction();

                throw $exception;
            }
        else
            throw new InvitationNotCreatedException();
    }

    /**
     * @param bool $returnDTO
     * @return array
     */
    public function getAllInvitations( bool $returnDTO = true ): array
    {
        $invitations = $this->invitationRepository->findAll();

        return $returnDTO
            ? $this->convertToDataTransferObjects($invitations)
            : $invitations
        ;
    }

    /**
     * @param string $uuid
     * @param bool $returnDTO
     * @return InvitationDataTransferObject|Invitation
     */
    public function getInvitationByUuid( string $uuid, bool $returnDTO = true ): InvitationDataTransferObject|Invitation
    {
        $invitation = $this->invitationRepository->findInvitationByUuid( $uuid );

        return $returnDTO
            ? $this->convertToDataTransferObject($invitation)
            : $invitation
        ;
    }

    /**
     * @param string $rsvp
     * @param bool $returnDTO
     * @return InvitationDataTransferObject|array
     */
    public function getInvitationsByRsvp( string $rsvp = Invitation::ANY, bool $returnDTO = false ): InvitationDataTransferObject|array
    {
        $invitations = $this->invitationRepository->findInvitationsByRsvp( $rsvp );

        return $returnDTO
            ? $this->convertToDataTransferObjects($invitations)
            : $invitations
        ;
    }

    public function getInvitationsByInvitationFrom( string $invitationFrom = Invitee::INVITEE_FROM_BOTH, bool $returnDTO = false ): InvitationDataTransferObject|array
    {
        $invitations = $this->invitationRepository->findInvitationsByInvitationFrom( $invitationFrom );

        return $returnDTO
            ? $this->convertToDataTransferObjects($invitations)
            : $invitations
        ;
    }

    public function updateInvitation(array $data, Invitation $invitation ): void
    {

    }

    /**
     * @param string $rsvp
     * @param Invitation $invitation
     * @return bool|null
     * @throws ConnectionException
     * @throws InvitationRsvpNotUpdatedException
     * @throws \Doctrine\DBAL\Exception
     */
    public function updateInvitationRsvp(string $rsvp, Invitation $invitation): ?bool
    {
        if
        (
            empty($rsvp)
        )
            return false;

        if ($this->invitationDataTransferObject->updateRsvp($invitation, $rsvp)) {
            try {
                $this->beginTransaction();

                $this->persistEntity($invitation);

                $this->flush();

                $this->commitOrRollbackTransaction();

                $this->eventDispatcher->dispatch
                (
                    new InvitationRsvped
                    (
                        $invitation
                    )
                );

                return true;
            } catch (Exception $exception) {
                $this->rollBackTransaction();

                throw new InvitationRsvpNotUpdatedException();
            }
        }

        return false;
    }

    /**
     * @param Invitation $invitation
     * @return InvitationDataTransferObject|null
     * @throws ConnectionException
     * @throws InvitationTimesOpenedNotUpdatedException
     * @throws \Doctrine\DBAL\Exception
     */
    public function updateInvitationTimesOpened(Invitation $invitation): ?InvitationDataTransferObject
            {
                try {
                    $this->beginTransaction();

                    $this->invitationDataTransferObject->updateTimesOpened($invitation);

            $this->persistEntity($invitation);

            $this->flush();

            $this->commitOrRollbackTransaction();

            $this->eventDispatcher->dispatch
            (
                new InvitationRsvped
                (
                    $invitation
                )
            );

            return $this->invitationDataTransferObject->fromEntity($invitation);
        }
        catch (Exception $exception) {
            $this->rollBackTransaction();

            throw new InvitationTimesOpenedNotUpdatedException();
        }
    }
}