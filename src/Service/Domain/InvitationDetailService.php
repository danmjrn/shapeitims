<?php


namespace App\Service\Domain;

use App\Entity\Exception\EntityNotFoundException;
use App\Entity\Exception\InvitationNotCreatedException;
use App\Entity\Invitation;
use App\Entity\InvitationDetail;
use App\Entity\InvitationGroup;
use App\Entity\Invitee;
use App\Entity\Exception\EntityNotCreatedException;

use App\Event\Domain\Invitation\InvitationAdded;
use App\Event\Domain\Invitation\InvitationRsvped;
use App\Repository\InvitationDetailRepository;
use App\Repository\InvitationRepository;
use App\Repository\InviteeRepository;

use App\Service\Domain\Entity\InvitationDataTransferObject;

use App\Service\Domain\Entity\InvitationDetailDataTransferObject;
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

class InvitationDetailService extends Service
{
    /**
     * @var InvitationDetailDataTransferObject
     */
    private InvitationDetailDataTransferObject $invitationDetailDataTransferObject;

    /**
     * @var InvitationDetailRepository
     */
    private InvitationDetailRepository $invitationDetailRepository;

    /**
     * @var InviteeRepository
     */
    private InviteeRepository $inviteeRepository;

    public function __construct
        (
            InvitationDetailDataTransferObject $invitationDetailDataTransferObject,
            InvitationDetailRepository $invitationDetailRepository,
            InviteeRepository $inviteeRepository,
            EntityManagerInterface $entityManager,
            EventDispatcherInterface $eventDispatcher,
            LoggerInterface $logger,
            RequestStack $session,
        )
    {
        $this->invitationDetailDataTransferObject = $invitationDetailDataTransferObject;
        $this->invitationDetailRepository = $invitationDetailRepository;
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
     * @param InvitationDetail $invitationDetail
     * @return InvitationDetailDataTransferObject
     */
    public function convertToDataTransferObject(InvitationDetail $invitationDetail): InvitationDetailDataTransferObject
    {
        return $this->invitationDetailDataTransferObject->fromEntity( $invitationDetail );
    }

    /**
     * @param array $invitationDetails
     * @return array
     */
    public function convertToDataTransferObjects(array $invitationDetails): array
    {
        return $this->invitationDetailDataTransferObject->convertToDataTransferObjects( $invitationDetails );
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
    public function getAllInvitationDetails( bool $returnDTO = true ): array
    {
        $invitationDetails = $this->invitationDetailRepository->findAll();

        return $returnDTO
            ? $this->convertToDataTransferObjects($invitationDetails)
            : $invitationDetails
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

    /**
     * @param string $type
     * @param bool $returnDTO
     * @return InvitationDetail|InvitationDetailDataTransferObject|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getInvitationDetailByType(string $type, bool $returnDTO = false) : InvitationDetail|InvitationDetailDataTransferObject|null
    {
        $invitationDetail = $this->invitationDetailRepository->findInvitationDetailByType( $type );

        return $returnDTO
            ? $this->invitationDetailDataTransferObject->fromEntity( $invitationDetail )
            : $invitationDetail
        ;
    }
}