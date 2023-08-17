<?php


namespace App\Security\AccessControl\Traits;


use App\Entity\InternalUser;
use App\Entity\Invitee;
use App\Entity\User;

use App\Security\AccessControl\AccessControl;

use Symfony\Component\Security\Core\User\UserInterface;

trait AccessControlTrait
{
    /**
     * @var AccessControl
     */
    private AccessControl $accessControl;

    /**
     * @required
     * @param AccessControl $accessControl
     */
    public function setAccessControl(AccessControl $accessControl): void
    {
        $this->accessControl = $accessControl;
    }

    /**
     * @param string $slug
     * @param string $userUuid
     * @return bool
     */
    public function hasPermission(string $slug, string $userUuid): bool
    {
        return $this->accessControl->hasPermission($slug, $userUuid);
    }

    /**
     * @param array $slugs
     * @param string $userUuid
     * @return bool
     */
    public function hasPermissions(array $slugs, string $userUuid): bool
    {
        return $this->accessControl->hasPermissions($slugs, $userUuid);
    }

    /**
     * @param string $role
     * @param int $userId
     * @return bool
     */
    public function hasRole(string $role, int $userId): bool
    {
        return $this->accessControl->hasRole($role, $userId);
    }

    /**
     * @param UserInterface $user
     * @param string $userType
     * @return bool
     */
    public function isUser(UserInterface $user, string $userType): bool
    {
        return ($user instanceof $userType);
    }

    /**
     * @param UserInterface $user
     * @param string $permission
     * @param string $userType
     */
    public function validateUserPermissionAccess
        (
            UserInterface $user,
            string $permission,
            string $userType = InternalUser::class
        ): void
    {
        if (! $this->isUser($user, $userType))
            throw $this->createAccessDeniedException();

        if (! $this->hasPermission($permission, $user->getUuid()))
            throw $this->createAccessDeniedException();
    }

    /**
     * @param UserInterface $user
     * @param array $slugs
     * @param string $userType
     */
    public function validateUserPermissionsAccess
        (
            UserInterface $user,
            array $slugs,
            string $userType = User::class
        ): void
    {

        if( $user instanceof  User ) {
            if ( ! $this->isUser( $user, $userType ) )
                throw $this->createAccessDeniedException();

            if ( ! $this->hasPermissions( $slugs, $user->getUuid() ) )
                throw $this->createAccessDeniedException();
        }
    }

    /**
     * @param UserInterface $user
     * @param string $role
     * @param string $userType
     */
    public function validateUserRoleAccess(UserInterface $user, string $role, string $userType = Invitee::class): void
    {
        if (! $this->isUser($user, $userType))
            throw $this->createAccessDeniedException();

        if ( $user instanceof  Invitee || $user instanceof InternalUser )
            if (! $this->hasRole($role, $user->getId()))
                throw $this->createAccessDeniedException();
    }
}