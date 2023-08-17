<?php


namespace App\Security\AccessControl;


use App\Entity\InternalUser;
use App\Entity\Invitee;

use App\Entity\Exception\EntityNotFoundException;
use App\Entity\Exception\EntityNotUpdatedException;

use App\Repository\InternalUserRepository;
use App\Repository\InviteeRepository;
use App\Repository\PermissionRepository;
use App\Repository\RoleRepository;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AccessControl
{
    /**
     * @var InternalUserRepository
     */
    private InternalUserRepository $internalUserRepository;

    /**
     * @var InviteeRepository
     */
    private InviteeRepository $inviteeRepository;

    /**
     * @var PermissionRepository
     */
    private PermissionRepository $permissionRepository;

    /**
     * @var RoleRepository
     */
    private RoleRepository $roleRepository;

    /**
     * @var RequestStack
     */
    private RequestStack $session;

    /**
     * @var TokenStorageInterface
     */
    private TokenStorageInterface $tokenStorage;

    public const USER_TYPE_INVITEE = 'invitee';
    public const USER_TYPE_INTERNAL = 'internal';

    /**
     * @param int $userId
     * @param string $userType
     * @return UserInterface|null
     */
    private function getUserFromRepository
        (
            int $userId,
            string $userType = self::USER_TYPE_INTERNAL
        ): ?UserInterface
    {
        if ($userType === self::USER_TYPE_INTERNAL)
            $user = $this->internalUserRepository->find($userId);
        elseif ($userType === self::USER_TYPE_INVITEE)
            $user = $this->inviteeRepository->find($userId);
        else
            $user = null;

        return $user;
    }

    public function __construct
        (
            InternalUserRepository $internalUserRepository,
            InviteeRepository $inviteeRepository,
            PermissionRepository $permissionRepository,
            RoleRepository $roleRepository,
            RequestStack $session,
            TokenStorageInterface $tokenStorage
        )
    {
        $this->internalUserRepository = $internalUserRepository;
        $this->inviteeRepository = $inviteeRepository;
        $this->permissionRepository = $permissionRepository;
        $this->roleRepository = $roleRepository;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param int $permissionId
     * @param int $roleId
     * @throws EntityNotFoundException
     * @throws EntityNotUpdatedException
     */
    public function assignPermissionToRole(int $permissionId, int $roleId): void
    {
        $permission = $this->permissionRepository->find($permissionId);

        $role = $this->roleRepository->find($roleId);

        if (is_null($permission) || is_null($role)) throw new EntityNotFoundException();

        $role->assignPermission($permission);

        try {
            $this->roleRepository->save($role);
        }
        catch (\Exception $exception) {
            throw new EntityNotUpdatedException();
        }
    }

    /**
     * @param int $roleId
     * @param int $userId
     * @param string $userType
     * @throws EntityNotFoundException
     * @throws EntityNotUpdatedException
     */
    public function assignRoleToUser(int $roleId, int $userId, string $userType = self::USER_TYPE_INTERNAL): void
    {
        $role = $this->roleRepository->find($roleId);

        $user = $this->getUserFromRepository($userId, $userType);

        if (is_null($role) || is_null($user)) throw new EntityNotFoundException();

        try {
            if ($user instanceof InternalUser) {
                $user->assignRole($role);

                $this->internalUserRepository->save($user);
            }
            elseif ($user instanceof Invitee) {
                $user->assignRole($role);

                $this->inviteeRepository->save($user);
            }
        }
        catch (\Exception $exception) {
            throw new EntityNotUpdatedException();
        }
    }

    /**
     * @return UserInterface|null
     */
    public function getAuthenticatedUser(): ?UserInterface
    {
        $authenticatedUser = null;

        return $this->tokenStorage->getToken()->getUser();
    }

    /**
     * @param string $slug
     * @param string $userUuid
     * @return bool
     */
    public function hasPermission(string $slug, string $userUuid): bool
    {
        try {
            return ! empty( $this->permissionRepository->findByUserUuidAndSlug($userUuid, $slug) );
        }
        catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param array $slugs
     * @param string $userUuid
     * @return bool
     */
    public function hasPermissions(array $slugs, string $userUuid): bool
    {
        try {
            return ! empty($this->permissionRepository->findByUserUuidAndSlugs($userUuid, $slugs));
        }
        catch (\Exception) {
            return false;
        }
    }

    /**
     * @param string $role
     * @param int $userId
     * @return bool
     */
    public function hasRole(string $role, int $userId): bool
    {
        try {
            return ! is_null($this->roleRepository->findByNameAndUserId($role, $userId));
        }
        catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param int $permissionId
     * @param int $roleId
     * @throws EntityNotFoundException
     * @throws EntityNotUpdatedException
     */
    public function revokePermissionFromRole(int $permissionId, int $roleId): void
    {
        $permission = $this->permissionRepository->find($permissionId);

        $role = $this->roleRepository->find($roleId);

        if (is_null($permission) || is_null($role)) throw new EntityNotFoundException();

        $role->revokePermission($permission);

        try {
            $this->roleRepository->save($role);
        }
        catch (\Exception $exception) {
            throw new EntityNotUpdatedException();
        }
    }

    /**
     * @param int $roleId
     * @param int $userId
     * @param string $userType
     * @throws EntityNotFoundException
     * @throws EntityNotUpdatedException
     */
    public function revokeRoleFromUser(int $roleId, int $userId, string $userType = self::USER_TYPE_INTERNAL): void
    {
        $role = $this->roleRepository->find($roleId);

        $user = $this->getUserFromRepository($userId, $userType);

        if (is_null($role) || is_null($user)) throw new EntityNotFoundException();

        try {
            if ($user instanceof InternalUser) {
                $user->revokeRole($role);

                $this->internalUserRepository->save($user);
            }
            elseif ($user instanceof Invitee) {
                $user->revokeRole($role);

                $this->inviteeRepository->save($user);
            }
        }
        catch (\Exception $exception) {
            throw new EntityNotUpdatedException();
        }
    }
}