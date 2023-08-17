<?php


namespace App\Service\Domain\Entity;


use App\Entity\Betrothed;
use App\Entity\Exception\UnknownUserTypeException;

use App\Entity\InternalUser;
use App\Entity\InvitationGroup;
use App\Entity\Invitee;
use App\Entity\User;
use App\Service\Domain\Exception\MissingAttributeException;
use JetBrains\PhpStorm\Pure;


class UserDataTransferObject extends DataTransferObject
{
    /**
     * @var RoleDataTransferObject
     */
    private RoleDataTransferObject $roleDataTransferObject;

    /**
     * @return $this
     */
    #[Pure]
    private function createDataTransferObject(): self
    {
        return new static($this->roleDataTransferObject);
    }

    /**
     * UserDataTransferObject constructor.
     * @param RoleDataTransferObject $roleDataTransferObject
     */
    public function __construct(RoleDataTransferObject $roleDataTransferObject)
    {
        $this->roleDataTransferObject = $roleDataTransferObject;
    }

    /**
     * @param array $invitees
     * @return array
     * @throws UnknownUserTypeException
     */
    public function convertToDataTransferObjects(array $invitees): array
    {
        $inviteesDTO = [];

        foreach ($invitees as $invitee) {
            $inviteesDTO[] = $this->fromEntity( $invitee );
        }

        return $inviteesDTO;
    }

    /**
     * @param User $user
     * @return $this
     * @throws UnknownUserTypeException
     */
    public function fromEntity( User $user ): self
    {
        if ( $user instanceof Betrothed )
            return $this->fromBetrothed($user);
        if ( $user instanceof InternalUser )
            return $this->fromInternalUser($user);
        if ( $user instanceof Invitee )
            return $this->fromInvitee($user);
        else
            throw new UnknownUserTypeException();
    }

    /**
     * @param Betrothed $user
     * @return $this
     */
    public function fromBetrothed( Betrothed $user ): self
    {
        $dto = $this->createDataTransferObject();

        $dto->entityType = Betrothed::class;

        $dto->id = $user->getId();
        $dto->uuid = $user->getUuid();
        $dto->email = $user->getEmail();
        $dto->firstname = $user->getFirstname();
        $dto->isVerified = $user->isVerified();
        $dto->lastLoggedInAt = $user->getLastLoggedInAt();
        $dto->lastname = $user->getLastname();
        $dto->fullNames = sprintf('%s %s', $user->getFirstname(), $user->getLastname());
        $dto->username = $user->getUsername();
        $dto->betrothed = $user->getBetrothed();

        foreach ($user->getRolesCollection() as $role)
            $dto->roles[] = $this->roleDataTransferObject->fromEntity($role);

        return $dto;
    }

    /**
     * @param InternalUser $user
     * @return $this
     */
    public function fromInternalUser( InternalUser $user ): self
    {
        $dto = $this->createDataTransferObject();

        $dto->entityType = InternalUser::class;

        $dto->id = $user->getId();
        $dto->uuid = $user->getUuid();
        $dto->email = $user->getEmail();
        $dto->firstname = $user->getFirstname();
        $dto->isVerified = $user->isVerified();
        $dto->lastLoggedInAt = $user->getLastLoggedInAt();
        $dto->lastname = $user->getLastname();
        $dto->fullNames = sprintf('%s %s', $user->getFirstname(), $user->getLastname());
        $dto->username = $user->getUsername();

        foreach ($user->getRolesCollection() as $role)
            $dto->roles[] = $this->roleDataTransferObject->fromEntity($role);

        return $dto;
    }

    /**
     * @param Invitee $user
     * @return $this
     */
    public function fromInvitee( Invitee $user ): self
    {
        $dto = $this->createDataTransferObject();

        $dto->entityType = Invitee::class;

        $dto->id = $user->getId();
        $dto->uuid = $user->getUuid();
        $dto->email = $user->getEmail();
        $dto->firstname = $user->getFirstname();
        $dto->isVerified = $user->isVerified();
        $dto->lastLoggedInAt = $user->getLastLoggedInAt();
        $dto->lastname = $user->getLastname();
        $dto->phoneNumber = $user->getPhoneNumber();
        $dto->fullNames = sprintf('%s %s', $user->getFirstname(), $user->getLastname());
        $dto->title = $user->getTitle();
        $dto->username = $user->getUsername();
        $dto->invitationGroup = $user->getInvitationGroup();
        $dto->invitationFrom = $user->getInviteeFrom();
        $dto->languageCode = $user->getInviteeLang();


        if ($user->getInternalUser()) {
            $dto->internalUserId = $user->getInternalUser()->getId();
            $dto->internalUserUuid = $user->getInternalUser()->getUuid();
            $dto->internalUserFullName = $user->getInternalUser()->getFullName();
        }

        if ($user->getInvitationGroup()->getInvitation()){
            $dto->invitationAliasId = $user->getInvitationGroup()->getInvitation()->getAlias();
            $dto->invitationRsvp = $user->getInvitationGroup()->getInvitation()->getRsvp();
            $dto->invitationTimesOpened = $user->getInvitationGroup()->getInvitation()->getTimesOpened();
        }

        foreach ($user->getRolesCollection() as $role)
            $dto->roles[] = $this->roleDataTransferObject->fromEntity($role);

        return $dto;
    }


    /**
     * @param string $slug
     * @return bool
     */
    public function hasPermission(string $slug): bool
    {
        if (! isset($this->roles)) return false;

        $rolesDTO = (array) $this->roles;

        foreach ($rolesDTO as $roleDTO) {
            if (! $roleDTO instanceof RoleDataTransferObject) return false;

            if (! isset($roleDTO->permissions)) return false;

            foreach ($roleDTO->permissions as $permissionDTO) {
                if (! $permissionDTO instanceof PermissionDataTransferObject) return false;

                if ($permissionDTO->slug === $slug) return true;
            }
        }

        return false;
    }

    /**
     * @param string $slug
     * @return bool
     */
    public function hasRole(string $slug): bool
    {
        if (! isset($this->roles)) return false;

        $rolesDTO = (array) $this->roles;

        foreach ($rolesDTO as $roleDTO) {
            if (! $roleDTO instanceof RoleDataTransferObject) return false;

            if ($roleDTO->slug === $slug) return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @param Invitee|null $invitee
     * @return Invitee
     * @throws MissingAttributeException
     */
    public function toInvitee(array $data, Invitee $invitee = null ): Invitee
    {
//        if(empty($data['firstname'])) $data['firstname'] = " ";
//
//        if(empty($data['lastname'])) $data['lastname'] = " ";

        if
            (
                empty($data['username']) ||
                empty($data['firstname']) ||
                empty($data['lastname']) ||
                empty($data['password']) ||
                empty($data['title'])
            )
            throw new MissingAttributeException();

        if ( is_null( $invitee ) )
            $invitee = new Invitee();

        $invitee
            ->setFirstname($data['firstname'])
            ->setIsVerified(false)
            ->setLastname($data['lastname'])
            ->setPassword($data['password'])
            ->setUsername($data['username'])
            ->setTitle($data['title'])
        ;

        if (! empty($data['email']))
            $invitee->setEmail($data['email']);

        if (! empty($data['invFrom']))
            $invitee->setInviteeFrom($data['invFrom']);

        if (! empty($data['phoneNumber']))
            $invitee->setPhoneNumber($data['phoneNumber']);

        if (! empty($data['inviteeLang']))
            $invitee->setInviteeLang($data['inviteeLang']);

        return $invitee;
    }

    /**
     * @param array $data
     * @param InternalUser|null $internalUser
     * @return InternalUser
     * @throws MissingAttributeException
     */
    public function toInternalUser(array $data, InternalUser $internalUser = null ): InternalUser
    {
        if
            (
                empty($data['email']) ||
                empty($data['firstname']) ||
                empty($data['lastname']) ||
                empty($data['password'])
            )
            throw new MissingAttributeException();

        if ( is_null( $internalUser ) )
            $internalUser = new InternalUser();

        $internalUser
            ->setEmail($data['email'])
            ->setFirstname($data['firstname'])
            ->setIsVerified(false)
            ->setLastname($data['lastname'])
            ->setPassword($data['password'])
            ->setUsername($data['email'])
        ;

        return $internalUser;
    }

    /**
     * @param array $data
     * @param User|null $user
     * @return User
     * @throws MissingAttributeException
     * @throws UnknownUserTypeException
     */
    public function toEntity(array $data, User $user = null): User
    {
        if ( $user instanceof InternalUser )
            return $this->toInternalUser($data, $user);
        if ( $user instanceof Invitee )
            return $this->toInvitee($data, $user);
        else
            throw new UnknownUserTypeException();
    }
}