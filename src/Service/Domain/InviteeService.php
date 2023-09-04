<?php


namespace App\Service\Domain;

use App\Entity\Exception\EntityNotCreatedException;

use App\Entity\Exception\EntityNotFoundException;
use App\Entity\Exception\InvitationNotCreatedException;
use App\Entity\Exception\UnknownApplicationUpdateTypeException;

use App\Entity\Exception\UnknownUserTypeException;
use App\Entity\InternalUser;
use App\Entity\Invitation;
use App\Entity\InvitationDetail;
use App\Entity\InvitationGroup;
use App\Entity\Invitee;
use App\Event\Domain\Invitation\InvitationAdded;
use App\Event\Domain\Invitee\InviteeAdded;
use App\Event\Domain\Invitee\InviteeUpdated;
use App\Repository\InternalUserRepository;
use App\Repository\InvitationGroupRepository;
use App\Repository\InvitationRepository;
use App\Repository\InviteeRepository;

use App\Service\Communication\ApplicationMailer;


use App\Service\Domain\Entity\InvitationDataTransferObject;
use App\Service\Domain\Entity\InvitationGroupDataTransferObject;

use App\Service\Domain\Entity\UserDataTransferObject;
use App\Service\Domain\Exception\InvitationDetailEmptyException;
use App\Service\Domain\Exception\InviteeWithDuplicateUsernameException;
use App\Service\Domain\Exception\MissingAttributeException;

use App\Service\Service;

use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

use Exception;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class InviteeService extends Service
{
    private InvitationDetailService $invitationDetailService;

    /**
     * @var InvitationService
     */
    private InvitationService $invitationService;

    /**
     * @var UserDataTransferObject
     */
    private UserDataTransferObject $inviteeDataTransferObject;

    /**
     * @var InviteeRepository
     */
    private InviteeRepository $inviteeRepository;

    /**
     * @var InternalUserRepository
     */
    private InternalUserRepository $internalUserRepository;

    /**
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $userPasswordHasher;

    /**
     * @param array $data
     * @return bool
     * @throws MissingAttributeException
     */
    private function validateInvitee(array $data): bool
    {
//        if(empty($data['firstname'])) $data['firstname'] = " ";
//
//        if(empty($data['lastname'])) $data['lastname'] = " ";


        if
            (
                empty($data['username']) ||
                empty($data['firstname']) ||
                empty($data['lastname']) ||
                empty($data['title']) ||
                empty($data['invitationType'])||
                ! $data['author'] instanceof InternalUser ||
                empty($data['author'])
            )
            throw new MissingAttributeException();

        return true;
    }

    public function __construct
        (
            EntityManagerInterface $entityManager,
            EventDispatcherInterface $eventDispatcher,
            InvitationDetailService $invitationDetailService,
            InvitationService $invitationService,
            InviteeRepository $inviteeRepository,
            InternalUserRepository $internalUserRepository,
            LoggerInterface $logger,
            RequestStack $session,
            UserDataTransferObject $inviteeDataTransferObject,
            UserPasswordHasherInterface $userPasswordHasher
    )
    {
        $this->invitationDetailService = $invitationDetailService;
        $this->invitationService = $invitationService;
        $this->inviteeDataTransferObject = $inviteeDataTransferObject;
        $this->inviteeRepository = $inviteeRepository;
        $this->internalUserRepository = $internalUserRepository;
        $this->userPasswordHasher = $userPasswordHasher;

        parent::__construct
            (
                $entityManager,
                $eventDispatcher,
                $logger,
                $session,

            );
    }

    /**
     * @param array $data
     * @param string $randomAlias
     * @param string $hashedPassword
     * @param array $invitationGroups
     * @return void
     * @throws ConnectionException
     * @throws EntityNotCreatedException
     * @throws MissingAttributeException
     * @throws \App\Entity\Exception\UnknownUserTypeException
     * @throws \Doctrine\DBAL\Exception
     */
    private function addInvitee(array &$data, string &$randomAlias, string &$hashedPassword, array &$invitationGroups): void
    {
        $invitationGroupDataTransferObject = new InvitationGroupDataTransferObject();

        $invitationGroup = $invitationGroupDataTransferObject->toEntity( $data['invitationType'], $randomAlias );

        $invitee = new Invitee();

        $invitee = $this->inviteeDataTransferObject->toEntity
        (
            array_merge
            (
                $data,
                [
                    'password' => $hashedPassword,
                ]
            ),
            $invitee
        );

        /** @var Invitee $invitee */
        $invitee->setInvitationGroup($invitationGroup);

        $invitationGroup->setUuid($invitee->getUuid());
        $invitationGroup->setInvitee($invitee);

        $invitationGroups[] = $invitationGroup;
        $this->persistEntity($invitationGroup);

        /** @var InternalUser $user */
        $user = $data['author'];

        $user->addInvitee($invitee);

        $invitee->setInternalUser($user);

        $this->persistEntity($user);

        $this->persistEntity($invitee);

        $this->flush();

        if ($this->isEntityManagerOpen()) {
            $this->commitTransaction();

            $this->eventDispatcher->dispatch
            (
                new InviteeAdded
                (
                    $data['author'],
                    $invitee,
                )
            );
        } else {
            $this->rollBackTransaction();

            throw new EntityNotCreatedException();
        }

//                $invitees[] = $this->inviteeDataTransferObject->fromEntity($invitee);
    }

    /**
     * @param array $invitees
     * @return array
     * @throws \App\Entity\Exception\UnknownUserTypeException
     */
    public function convertToDataTransferObjects(array $invitees): array
    {
        return $this->inviteeDataTransferObject->convertToDataTransferObjects($invitees);
    }

    /**
     * @param array $allData
     * @return void
     * @throws ConnectionException
     * @throws EntityNotCreatedException
     * @throws InvitationNotCreatedException
     * @throws InviteeWithDuplicateUsernameException
     * @throws MissingAttributeException
     * @throws \Doctrine\DBAL\Exception
     * @throws UnknownUserTypeException
     * @throws NonUniqueResultException
     */
    public function createInvitee( array $allData ): void
    {
        $combinedUsername = $this->generateCombinedUsername( $allData );
        $randomAlias = InvitationGroup::generateRandomAlias( $combinedUsername );
        $hashedPassword = $this->userPasswordHasher->hashPassword( new Invitee(), "Password@1" );
        $invitationGroups = [];

        foreach ( $allData as $data ){
            try {
                $this->validateInvitee($data);

                $this->beginTransaction();

                if ($this->doesInviteeExist($data['username']))
                    throw new InviteeWithDuplicateUsernameException();

                $this->addInvitee($data, $randomAlias, $hashedPassword, $invitationGroups);

            } catch (Exception $exception) {
                $this->rollBackTransaction();

                throw $exception;
            }
        }

        $this->linkToInvitationDetail($allData[0]['invitationDetailType'], $randomAlias, $invitationGroups);

    }

    /**
     * @param array $allData
     * @return void
     * @throws ConnectionException
     * @throws EntityNotCreatedException
     * @throws InvitationNotCreatedException
     * @throws MissingAttributeException
     * @throws \Doctrine\DBAL\Exception
     * @throws UnknownUserTypeException
     * @throws InvitationDetailEmptyException
     * @throws NonUniqueResultException
     */
    public function createInviteeViaImport( array $allData ): void
    {
        $this->doesInvitationDetailExist();

        $this->updateDuplicateUsernameFromImport($allData);
        $combinedUsername = $this->generateCombinedUsername( $allData );
        $randomAlias = InvitationGroup::generateRandomAlias( $combinedUsername );
        $hashedPassword = $this->userPasswordHasher->hashPassword( new Invitee(), "Password@1" );
        $invitationGroups = [];

        foreach ( $allData as $data ){
            try {
                $this->validateInvitee($data);

                $this->beginTransaction();

                if ($this->doesInviteeExist($data['username']))
                    continue;

                $this->addInvitee($data, $randomAlias, $hashedPassword, $invitationGroups);

            } catch (Exception $exception) {
                $this->rollBackTransaction();

                throw $exception;
            }
        }

        if ( ! empty($invitationGroups) ) {
            $this->linkToInvitationDetail($allData[0]['invitationDetailType'], $randomAlias, $invitationGroups);
        }

    }

    /**
     * @param string $username
     * @return bool
     */
    public function doesInviteeExist(string $username): bool
    {
        try {
            return ! is_null( $this->inviteeRepository->findInviteeByUsername( $username ) );
        }
        catch (NonUniqueResultException $nonUniqueResultException) {
            return true;
        }
        catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param array $allData
     * @return string
     * @throws MissingAttributeException
     */
    public function generateCombinedUsername( array $allData ): string
    {
        $combinedUsername = '';

        foreach ( $allData as $data )
        {
            $this->validateInvitee($data);

            $combinedUsername .= $data['username'];
        }
        return $combinedUsername;
    }

    /**
     * @param array $allData
     * @return void
     */
    public function updateDuplicateUsernameFromImport( array &$allData ): void {
        // update Duplicate Username from $allData if count is 2
        $duplicateUsername = [];
        foreach ( $allData as $data ) {
            if ( ! empty($data['username']) ) {
                $duplicateUsername[] = $data['username'];
            }
        }
        if ( count($duplicateUsername) === 2 && ($duplicateUsername[0] === $duplicateUsername[1]) ) {
            $allData[0]['username'] = $duplicateUsername[0] . 'a';
            $allData[1]['username'] = $duplicateUsername[1] . 'b';
        }

        if ( count($duplicateUsername) === 3
            && ($duplicateUsername[0] === $duplicateUsername[1]
                || $duplicateUsername[0] === $duplicateUsername[2])
        ) {
            $allData[0]['username'] = $duplicateUsername[0] . 'a';
            $allData[1]['username'] = $duplicateUsername[1] . 'b';
            $allData[2]['username'] = $duplicateUsername[2] . 'c';
        }
    }

    /**
     * @param bool $returnDTO
     * @return array
     * @throws \App\Entity\Exception\UnknownUserTypeException
     */
    public function getAllInvitees( bool $returnDTO = true ): array
    {
        $invitees = $this->inviteeRepository->findAll();

        return $returnDTO
            ? $this->convertToDataTransferObjects($invitees)
            : $invitees
        ;
    }

    /**
     * @param string $string
     * @param bool $returnDataTransferableObject
     * @return array
     * @throws \App\Entity\Exception\UnknownUserTypeException
     */
    public function getInviteesByInvitationAlias(string $string, bool $returnDataTransferableObject = true ): array
    {
        $invitees = $this->inviteeRepository->findInviteesByInvitationAlias($string);

        return $returnDataTransferableObject
            ? $this->inviteeDataTransferObject->convertToDataTransferObjects( $invitees )
            : $invitees;
    }

    public function findInvitationsByInvitationFrom( string $inviteeFrom = Invitee::INVITEE_FROM_BOTH ): array
    {
        return $this->inviteeRepository->findInvitationsByInvitationFrom( $inviteeFrom );
    }

    /**
     * @param array $data
     * @return UserDataTransferObject
     * @throws ConnectionException
     * @throws EntityNotCreatedException
     * @throws EntityNotFoundException
     * @throws MissingAttributeException
     * @throws \App\Entity\Exception\UnknownUserTypeException
     * @throws \Doctrine\DBAL\Exception
     */
    public function updateInvitee(array $data): UserDataTransferObject
    {
        if
            (
                empty($data['userUuid']) ||
                empty($data['invitee']) ||
                ! $data['invitee'] instanceof Invitee
            )
            throw new MissingAttributeException();

        try {
            $this->beginTransaction();

            $invitee = $data['invitee'];

            $user = $this->internalUserRepository->findByUuid($data['userUuid']);

            if (is_null($user)) throw new EntityNotFoundException();

//            $oldInvitee = $invitee;

            if ( ! empty($data['firstname']) )
                $invitee->setFirstname( $data['firstname'] );

            if ( ! empty($data['lastname']) )
                $invitee->setLastname( $data['lastname'] );

            if ( ! empty($data['title']) )
                $invitee->setTitle( $data['title'] );

            if ( ! empty($data['phoneNumber']) )
                $invitee->setPhoneNumber( $data['phoneNumber'] );

            $this->persistEntity($invitee);

            $this->saveEntity($invitee);

            $this->commitOrRollbackTransaction();

            $this->eventDispatcher->dispatch
            (
                new InviteeUpdated
                (
                    $invitee->getInternalUser(),
                    $invitee,
                )
            );

            return $this->inviteeDataTransferObject->fromEntity($invitee);
        }
        catch (Exception $exception) {
            $this->rollBackTransaction();

            throw $exception;
        }
    }

    /**
     * @return void
     * @throws InvitationDetailEmptyException
     */
    private function doesInvitationDetailExist(): void
    {
        if (count($this->invitationDetailService->getAllInvitationDetails()) < 1)
            throw new InvitationDetailEmptyException();
    }

    /**
     * @param string $invitationDetailType
     * @param string $randomAlias
     * @param array $invitationGroups
     * @return void
     * @throws ConnectionException
     * @throws InvitationNotCreatedException
     * @throws MissingAttributeException
     * @throws NonUniqueResultException
     * @throws \Doctrine\DBAL\Exception
     */
    private function linkToInvitationDetail(string $invitationDetailType, string $alias, array $invitationGroups): void
    {
        $invitation = new Invitation();

        switch (strtolower($invitationDetailType)) {
            case 'w':
            {
                $invitationDetailTypeConstant = InvitationDetail::INVITATION_DETAIL_WW_TYPE;
                $aliasSuffix = '-w';
                break;
            }
            case 't':
            {
                $invitationDetailTypeConstant = InvitationDetail::INVITATION_DETAIL_TW_TYPE;
                $aliasSuffix = '-t';
                break;
            }
            default:
            {
                $invitationDetailTypeConstant = InvitationDetail::INVITATION_DETAIL_WW_TYPE;
                $invitation->addInvitationDetail(
                    $this->invitationDetailService
                        ->getInvitationDetailByType(InvitationDetail::INVITATION_DETAIL_TW_TYPE)
                );
                $aliasSuffix = '-b';
                break;
            }
        }

        $invitation->addInvitationDetail(
            $this->invitationDetailService
                ->getInvitationDetailByType($invitationDetailTypeConstant)
        );
        $this->invitationService->createInvitation($invitation, $alias . $aliasSuffix, $invitationGroups);

    }

    /**
     * @param $username
     * @return Invitee|null
     * @throws NonUniqueResultException
     */
    public function getInviteeByUsername($username): ?Invitee
    {
        return $this->inviteeRepository->findInviteeByUsername($username);
    }
}