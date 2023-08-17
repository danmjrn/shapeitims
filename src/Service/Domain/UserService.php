<?php


namespace App\Service\Domain;


use App\Entity\Invitee;
use App\Entity\InternalUser;
use App\Entity\Permission;
use App\Entity\User;

use App\Event\Domain\User\UserConfirmationEmailResent;

use App\Repository\InviteeRepository;
use App\Repository\InternalUserRepository;
use App\Repository\PermissionRepository;
use App\Repository\RoleRepository;

use App\Security\Validation\EmailVerifier;

use App\Service\Domain\Exception\MissingAttributeException;

use App\Service\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class UserService extends Service
{
    /**
     * @var InviteeRepository
     */
    private InviteeRepository $inviteeRepository;

    /**
     * @var EmailVerifier
     */
    private EmailVerifier $emailVerifier;

    /**
     * @var InternalUserRepository
     */
    private InternalUserRepository $internalUserRepository;

    /**
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $userPasswordHasher;

    /**
     * @var PermissionRepository
     */
    private PermissionRepository $permissionRepository;

    /**
     * @var ResetPasswordHelperInterface
     */
    private ResetPasswordHelperInterface $resetPasswordHelper;

    /**
     * @var RoleRepository
     */
    private RoleRepository $roleRepository;

    /**
     * @var VerifyEmailHelperInterface
     */
    private VerifyEmailHelperInterface $verifyEmailHelper;

    public function __construct
        (
            EmailVerifier $emailVerifier,
            EntityManagerInterface $entityManager,
            EventDispatcherInterface $eventDispatcher,
            InviteeRepository $inviteeRepository,
            InternalUserRepository $internalUserRepository,
            UserPasswordHasherInterface $userPasswordHasher,
            PermissionRepository $permissionRepository,
            ResetPasswordHelperInterface $resetPasswordHelper,
            RoleRepository $roleRepository,
            VerifyEmailHelperInterface $verifyEmailHelper
        )
    {
        $this->emailVerifier = $emailVerifier;
        $this->inviteeRepository = $inviteeRepository;
        $this->internalUserRepository = $internalUserRepository;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->permissionRepository = $permissionRepository;
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->roleRepository = $roleRepository;
        $this->verifyEmailHelper = $verifyEmailHelper;

        parent::__construct( $entityManager, $eventDispatcher );
    }

    /**
     * @param User $user
     */
    public function deleteUser(User $user): void
    {
        $user->deleteUser();

        $this->saveEntity($user);
    }

    /**
     * @param InternalUser $internalUser
     * @param string $email
     * @return bool
     * @throws NonUniqueResultException
     */
    public function isAuthorizedToAddUserToAccount( InternalUser $internalUser, string $email ): bool
    {
        return
            (
                (
                    $internalUser->getEmail() === $email &&
                    $this->permissionRepository->findByInternalUserUuidAndSlug
                        (
                        $internalUser->getUuid(),
                            Permission::PERMISSION_CREATE_USERS
                        )
                ) ||
                ! empty
                    (
                        $this->permissionRepository->findByInternalUserUuidAndSlug
                            (
                            $internalUser->getUuid(),
                                Permission::PERMISSION_CREATE_USERS
                            )
                    )
            );
    }

    /**
     * @param User $requesterUser
     * @param User $targetedUser
     * @return bool
     * @throws NonUniqueResultException
     */
    public function isAuthorizedToEditUser( User $requesterUser, User $targetedUser ): bool
    {
        return
            (
                (
                    $requesterUser->getUuid() === $targetedUser->getUuid() &&
                    $this->permissionRepository->findByInternalUserUuidAndSlug
                        (
                        $requesterUser->getUuid(),
                            Permission::PERMISSION_EDIT_USERS
                        )
                ) ||
                ! empty
                    (
                        $this->permissionRepository->findByInternalUserUuidAndSlug
                            (
                            $requesterUser->getUuid(),
                                Permission::PERMISSION_EDIT_USERS
                            )
                    )
            );
    }

    /**
     * @param InternalUser $requester
     * @param InternalUser $targetedUser
     * @return bool
     * @throws NonUniqueResultException
     */
    public function isAuthorizedToViewUser( InternalUser $requester, InternalUser $targetedUser ): bool
    {
        return
            (
                (
                    $requester->getEmail() === $targetedUser->getEmail() &&
                    $this->permissionRepository->findByInternalUserUuidAndSlug
                        (
                            $requester->getUuid(),
                            Permission::PERMISSION_VIEW_USERS
                        )
                ) ||
                ! empty
                    (
                        $this->permissionRepository->findByInternalUserUuidAndSlug
                            (
                                $requester->getUuid(),
                                Permission::PERMISSION_VIEW_USERS
                            )
                    )
            );
    }

    /**
     * @param User $user
     */
    public function sendVerificationEmail(UserInterface $user): void
    {
        $this->eventDispatcher->dispatch
            (
                new UserConfirmationEmailResent
                    (
                        $user,
                        $this->emailVerifier->generateVerifiableEmailLink($user)
                    )
            );
    }

    /**
     * @param User $user
     */
    public function signInUser(User $user): void
    {
        $user->signInUser();

        $this->saveEntity($user);
    }

    /**
     * @param array $details
     * @throws MissingAttributeException
     */
    public function updateUserPassword(array $details): void
    {
        if
            (
                empty($details['password']) ||
                empty($details['token']) ||
                empty($details['user']) ||
                ! $details['user'] instanceof User
            )
            throw new MissingAttributeException();

        $user = $details['user'];
        // A password reset token should be used only once, remove it.
        $this->resetPasswordHelper->removeResetRequest($details['token']);
        // Encode the plain password, and set it.
        $user
            ->setPassword
                (
                    $this->userPasswordHasher->hashPassword
                        (
                            $user,
                            $details['password']
                        )
                )
            ->verifyUser();

        $this->saveEntity($user);
    }

    /**
     * @param string $token
     * @return object
     * @throws ResetPasswordExceptionInterface
     */
    public function validateTokenAndFetchUser(string $token): object
    {
        return $this->resetPasswordHelper->validateTokenAndFetchUser($token);
    }

    /**
     * @param UserInterface $user
     * @param string $url
     * @throws VerifyEmailExceptionInterface
     */
    public function verifyUserAccount(UserInterface $user, string $url): void
    {
        $this->emailVerifier->validateEmailConfirmation($user, $url);

        $this->saveEntity($user);
    }
}