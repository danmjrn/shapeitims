<?php

namespace App\Service\Utility;


use App\Entity\Betrothed;
use App\Entity\Exception\UnknownPermissionTypeException;

use App\Entity\InternalUser;
use App\Entity\InvitationDetail;
use App\Entity\Permission;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\BetrothedRepository;
use App\Repository\InternalUserRepository;
use App\Repository\InvitationDetailRepository;
use App\Repository\PermissionRepository;
use App\Repository\RoleRepository;
use App\Service\Domain\Entity\PermissionDataTransferObject;
use App\Service\Domain\Entity\RoleDataTransferObject;
use App\Service\Domain\Exception\MissingAttributeException;
use App\Service\Service;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ImportUtility extends Service
{

    /**
     * @var BetrothedRepository
     */
    private BetrothedRepository $betrothedRepository;

    /**
     * @var InternalUserRepository
     */
    private InternalUserRepository $internalUserRepository;

    /**
     * @var InvitationDetailRepository
     */
    private InvitationDetailRepository $invitationDetailRepository;

    /**
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $userPasswordHasher;

    /**
     * @var PermissionDataTransferObject
     */
    private PermissionDataTransferObject $permissionDataTransferObject;

    /**
     * @var PermissionRepository
     */
    private PermissionRepository $permissionRepository;

    /**
     * @var RoleDataTransferObject
     */
    private RoleDataTransferObject $roleDataTransferObject;

    /**
     * @var RoleRepository
     */
    private RoleRepository $roleRepository;

    private const INITIAL_USERS = [
        [
            'email' => 'danieljrnkulu@shapeit.solutions',
            'firstName' => 'Daniel Jr',
            'lastName' => 'Nkulu',
            'roleName' => Role::ROLE_SUPER_ADMIN,
            'password' => 'TempPass1234!',
            'userType' => User::USER_TYPE_INTERNAL,
        ],
        [
            'email' => 'dodi@shapeit.solutions',
            'firstName' => 'Dodi',
            'lastName' => 'Nkulu',
            'roleName' => Role::ROLE_SUPER_ADMIN,
            'password' => 'P@ssword1',
            'userType' => User::USER_TYPE_INTERNAL,
        ],
        [
            'email' => 'codi@shapeit.solutions',
            'firstName' => 'Codi',
            'lastName' => 'Nkulu',
            'roleName' => Role::ROLE_BETROTHED,
            'password' => 'P@ssword1',
            'userType' => User::USER_TYPE_BETROTHED,
        ],
        [
            'email' => 'raissa@shapeit.solutions',
            'firstName' => 'Raissa',
            'lastName' => 'Elongo',
            'roleName' => Role::ROLE_BETROTHED,
            'password' => 'P@ssword1',
            'userType' => User::USER_TYPE_BETROTHED,
        ],
        [
            'email' => 'viewer@shapeit.solutions',
            'firstName' => 'Internal',
            'lastName' => 'Viewer',
            'roleName' => Role::ROLE_INTERNAL_VIEWER,
            'password' => 'P@ssword1',
            'userType' => User::USER_TYPE_INTERNAL,
        ],
    ];
    private const INITIAL_INVITATION_DETAILS = [
        [
            'content' => 'This is White Wedding of Codi & Raissa',
            'type' => InvitationDetail::INVITATION_DETAIL_WW_TYPE,
            'maximumDistribution' => 400,
            'eventDate' => '2023-10-21',
        ],
        [
            'content' => 'This is Traditional of Codi & Raissa',
            'type' => InvitationDetail::INVITATION_DETAIL_TW_TYPE,
            'maximumDistribution' => 200,
            'eventDate' => '2023-10-14',
        ],
    ];

    /**
     * @throws NonUniqueResultException
     */
    private function createUsers(array $users): void
    {
        $betrothedUsers = [];

        foreach ($users as $userData) {
            $user = $this->createUser(
                $userData['email'],
                $userData['firstName'],
                $userData['lastName'],
                $userData['roleName'],
                $userData['password'],
                $userData['userType']
            );

            if ($user instanceof Betrothed)
                $betrothedUsers[] = $user;
        }

        if (count($betrothedUsers) === 2)
            $this->linkBetrothedUsers(...$betrothedUsers);
    }

    /**
     * @param array $invitationDetailsArray
     * @return array
     * @throws \Exception
     */
    private function createInvitationDetails(array $invitationDetailsArray): array
    {
        $invitationDetails = [];

        foreach ($invitationDetailsArray as $invitationDetail) {
            $invitationDetail = $this->createInvitationDetail(
                $invitationDetail['content'],
                $invitationDetail['type'],
                $invitationDetail['maximumDistribution'],
                $invitationDetail['eventDate']
            );

            $invitationDetails[] = $invitationDetail;
        }

        return $invitationDetails;
    }

    /**
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $roleName
     * @param string $password
     * @param string $userType
     * @return InternalUser|Betrothed
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    private function createUser(
        string $email,
        string $firstName,
        string $lastName,
        string $roleName,
        string $password,
        string $userType = User::USER_TYPE_INTERNAL
    ): InternalUser|Betrothed {

        switch ($userType) {
            case User::USER_TYPE_INTERNAL: {
                $user = $this->internalUserRepository->findByEmail($email);
                if (! $user) {
                    $user = new InternalUser();
                    $this->setUserAttributes($user, $password, $email, $firstName, $lastName, $roleName);
                }
                break;
            }
            case User::USER_TYPE_BETROTHED: {
                $user = $this->betrothedRepository->findByEmail($email);
                if (! $user) {
                    $user = new Betrothed();
                    $this->setUserAttributes($user, $password, $email, $firstName, $lastName, $roleName);
                }
                break;
            }
            default: throw new \Exception("Valid user type not defined");
        }

        return $user;
    }

    /**
     * @param string $content
     * @param string $type
     * @param int $maximumDistribution
     * @param string $eventDate
     * @return InvitationDetail
     * @throws \Exception
     */
    private function createInvitationDetail(
        string $content,
        string $type,
        int $maximumDistribution,
        string $eventDate,
    ): InvitationDetail {

        $invitationDetail = $this->invitationDetailRepository->findByContent($content);

        if (! $invitationDetail) {
            $invitationDetail = new InvitationDetail();

            $this->setInvitationDetailAttributes(
                $invitationDetail,
                $content,
                $type,
                $maximumDistribution,
                new \DateTime($eventDate),
            );
        }

        return $invitationDetail;
    }

    /**
     * @param Betrothed $user1
     * @param Betrothed $user2
     * @return void
     */
    private function linkBetrothedUsers(Betrothed $user1, Betrothed $user2): void
    {
        $user1->setBetrothed($user2);
        $user2->setBetrothed($user1);
        $this->persistEntity($user1);
        $this->persistEntity($user2);
    }

    /**
     * @param Betrothed $user
     * @param string $password
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $roleName
     * @return void
     */
    private function setUserAttributes(User $user, string $password, string $email, string $firstName, string $lastName, string $roleName): void
    {
        $hashedPassword = $this->userPasswordHasher->hashPassword($user, $password);

        $user
            ->setEmail($email)
            ->setFirstname($firstName)
            ->setIsDeleted(false)
            ->setIsVerified(true)
            ->setLastname($lastName)
            ->setPassword($hashedPassword)
            ->setUsername($email);

        $role = $this->roleRepository->findByName($roleName);

        if ($role) {
            $user->assignRole($role);
        }

        $this->persistEntity($user);
    }

    /**
     * @param InvitationDetail $invitationDetail
     * @param string $content
     * @param string $type
     * @param int $maximumDistribution
     * @param \DateTimeInterface $eventDate
     * @return void
     */
    private function setInvitationDetailAttributes(InvitationDetail &$invitationDetail, string $content, string $type, int $maximumDistribution, \DateTimeInterface $eventDate): void
    {
        $invitationDetail
            ->setContent($content)
            ->setType($type)
            ->setMaximumDistribution($maximumDistribution)
            ->setEventDate($eventDate)
        ;

        $this->persistEntity($invitationDetail);
    }

    public function __construct
        (
            EntityManagerInterface $entityManager,
            EventDispatcherInterface $eventDispatcher,
            LoggerInterface $logger,
            RequestStack $session,
            BetrothedRepository $betrothedRepository,
            InternalUserRepository $internalUserRepository,
            InvitationDetailRepository $invitationDetailRepository,
            UserPasswordHasherInterface $userPasswordHasher,
            PermissionDataTransferObject $permissionDataTransferObject,
            PermissionRepository $permissionRepository,
            RoleDataTransferObject $roleDataTransferObject,
            RoleRepository $roleRepository
        )
    {
        parent::__construct($entityManager, $eventDispatcher, $logger, $session);

        $this->betrothedRepository = $betrothedRepository;
        $this->internalUserRepository = $internalUserRepository;
        $this->invitationDetailRepository = $invitationDetailRepository;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->permissionDataTransferObject = $permissionDataTransferObject;
        $this->permissionRepository = $permissionRepository;
        $this->roleDataTransferObject = $roleDataTransferObject;
        $this->roleRepository = $roleRepository;
    }

    /**
     * @throws \Exception
     */
    public function createUserAccounts(): void
    {
        $this->createUsers(static::INITIAL_USERS);

        $this->flush();
    }

    /**
     * @return void
     * @throws MissingAttributeException
     * @throws UnknownPermissionTypeException
     */
    public function importRoles(): void
    {
        $permissions =
            [
                'Invitation Management' =>
                    [
                        'Super Admin' =>
                            [
                                Permission::PERMISSION_TYPE_CREATE,
                                Permission::PERMISSION_TYPE_DELETE,
                                Permission::PERMISSION_TYPE_EDIT,
                                Permission::PERMISSION_TYPE_VIEW,
                            ],
                        'Betrothed' =>
                            [
                                Permission::PERMISSION_TYPE_CREATE,
                                Permission::PERMISSION_TYPE_DELETE,
                                Permission::PERMISSION_TYPE_EDIT,
                                Permission::PERMISSION_TYPE_VIEW,
                            ],
                        'Internal Admin' =>
                            [
                                Permission::PERMISSION_TYPE_CREATE,
                                Permission::PERMISSION_TYPE_DELETE,
                                Permission::PERMISSION_TYPE_EDIT,
                                Permission::PERMISSION_TYPE_VIEW,
                            ],
                        'Internal Viewer' =>
                            [
                                Permission::PERMISSION_TYPE_VIEW,
                            ],
                        'Invitee' =>
                            [
                                Permission::PERMISSION_TYPE_EDIT,
                                Permission::PERMISSION_TYPE_VIEW,
                            ]
                    ],
                'Invitees' =>
                    [
                        'Super Admin' =>
                            [
                                Permission::PERMISSION_TYPE_CREATE,
                                Permission::PERMISSION_TYPE_DELETE,
                                Permission::PERMISSION_TYPE_EDIT,
                                Permission::PERMISSION_TYPE_VIEW,
                            ],
                        'Betrothed' =>
                            [
                                Permission::PERMISSION_TYPE_CREATE,
                                Permission::PERMISSION_TYPE_DELETE,
                                Permission::PERMISSION_TYPE_EDIT,
                                Permission::PERMISSION_TYPE_VIEW,
                            ],
                        'Internal Admin' =>
                            [
                                Permission::PERMISSION_TYPE_VIEW,
                                Permission::PERMISSION_TYPE_CREATE,
                                Permission::PERMISSION_TYPE_DELETE,
                                Permission::PERMISSION_TYPE_EDIT,
                                Permission::PERMISSION_TYPE_VIEW,
                            ],
                    ],
                'Settings - Account Profile' =>
                    [
                        'Super Admin' =>
                            [
                                Permission::PERMISSION_TYPE_CREATE,
                                Permission::PERMISSION_TYPE_DELETE,
                                Permission::PERMISSION_TYPE_EDIT,
                                Permission::PERMISSION_TYPE_VIEW,
                            ],
                        'Betrothed' =>
                            [
                                Permission::PERMISSION_TYPE_CREATE,
                                Permission::PERMISSION_TYPE_DELETE,
                                Permission::PERMISSION_TYPE_EDIT,
                            ],
                        'Internal Admin' =>
                            [
                                Permission::PERMISSION_TYPE_CREATE,
                                Permission::PERMISSION_TYPE_EDIT,
                                Permission::PERMISSION_TYPE_VIEW,
                            ],
                    ],
                'Users - Manage Users' =>
                    [
                        'Super Admin' =>
                            [
                                Permission::PERMISSION_TYPE_CREATE,
                                Permission::PERMISSION_TYPE_DELETE,
                                Permission::PERMISSION_TYPE_EDIT,
                                Permission::PERMISSION_TYPE_VIEW,
                            ],
                        'Internal Admin' =>
                            [
                                Permission::PERMISSION_TYPE_CREATE,
                                Permission::PERMISSION_TYPE_EDIT,
                                Permission::PERMISSION_TYPE_VIEW,
                            ],
                        'Betrothed' =>
                            [
                                Permission::PERMISSION_TYPE_CREATE,
                                Permission::PERMISSION_TYPE_DELETE,
                                Permission::PERMISSION_TYPE_EDIT,
                                Permission::PERMISSION_TYPE_VIEW,
                            ],
                    ],
                'Users - Roles & Permissions' =>
                    [
                        'Super Admin' =>
                            [
                                Permission::PERMISSION_TYPE_CREATE,
                                Permission::PERMISSION_TYPE_DELETE,
                                Permission::PERMISSION_TYPE_EDIT,
                                Permission::PERMISSION_TYPE_VIEW,
                            ],
                    ],
            ];

        /* format [ roleName => roleDescription ]*/
        $roles =
            [
                'Super Admin' => 'System Super User',
                'Betrothed' => 'System Betrothed',
                'Internal Admin' => 'System Admin',
                'Internal Viewer' => 'System Viewer',
                'Invitee' => 'Guest Invited',
            ];

        foreach ($roles as $role => $description) {
            $roleEntity = $this->roleDataTransferObject->toEntity(['name' => $role, 'description' => $description], new Role());

            if (is_null($this->roleRepository->findByName($role))) {
                $this->persistEntity($roleEntity);

                foreach ($permissions as $permissionName => $roleNames) {
                    foreach ($roleNames as $roleName => $permissionTypes) {
                        foreach ($permissionTypes as $permissionType) {
                            if ($roleName === $role) {
                                $permissionEntity = $this->permissionRepository->findByName
                                    (
                                        Permission::generatePermission
                                            (
                                                $permissionName,
                                                $permissionType
                                            )
                                    );

                                if ( is_null( $permissionEntity ) ) {
                                    $permissionEntity = $this->permissionDataTransferObject->toEntity
                                        (
                                            [
                                                'name' => $permissionName,
                                                'permissionType' => $permissionType
                                            ]
                                        );

                                    $this->persistEntity($permissionEntity);
                                }

                                $roleEntity->assignPermission($permissionEntity);
                            }
                        }
                    }
                }

                $this->persistEntity($roleEntity);

                $this->flush();
            }
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function initializeInvitationDetails(): void
    {
        $invitationDetails = $this->createInvitationDetails(static::INITIAL_INVITATION_DETAILS);

//        dd($invitationDetails);
        $this->flush();
    }
}