<?php


namespace App\Service\Domain;


use App\Entity\Exception\EntityCollectionNotFoundException;
use App\Entity\Exception\EntityNotFoundException;
use App\Entity\Exception\EntityNotUpdatedException;
use App\Entity\Exception\EntityWithSameUniqueIdentifierAlreadyExists;

use App\Entity\Role;

use App\Repository\PermissionRepository;
use App\Repository\RoleRepository;

use App\Service\Domain\Entity\RoleDataTransferObject;

use App\Service\Domain\Exception\MissingAttributeException;

class RoleService
{
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

    /**
     * RoleService constructor.
     * @param PermissionRepository $permissionRepository
     * @param RoleDataTransferObject $roleDataTransferObject
     * @param RoleRepository $roleRepository
     */
    public function __construct
        (
            PermissionRepository $permissionRepository,
            RoleDataTransferObject $roleDataTransferObject,
            RoleRepository $roleRepository
        )
    {
        $this->permissionRepository = $permissionRepository;
        $this->roleDataTransferObject = $roleDataTransferObject;
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param array $data
     * @return RoleDataTransferObject
     * @throws EntityWithSameUniqueIdentifierAlreadyExists
     * @throws Exception\MissingAttributeException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addRole(array $data): RoleDataTransferObject
    {
        $role = (isset($data['role']) && $data['role'] instanceof Role)  ? $data['role'] : null;

        $role = $this->roleDataTransferObject->toEntity($data, $role);

        if (! is_null($this->roleRepository->findByName($role->getName())))
            throw new EntityWithSameUniqueIdentifierAlreadyExists();

        $this->roleRepository->save($role);

        return $this->roleDataTransferObject->fromEntity($role);
    }

    /**
     * @param array $data
     * @return RoleDataTransferObject
     * @throws EntityNotFoundException
     * @throws MissingAttributeException
     * @throws \App\Security\AccessControl\Exception\RoleAlreadyHasPermissionAssignedException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function assignPermissionsToRole(array $data): RoleDataTransferObject
    {
        if (empty($data['role'])) throw new MissingAttributeException();

        $role = $this->roleRepository->find($data['role']);

        if (empty($role)) throw new EntityNotFoundException();

        $role->clearPermissions();

        if (! empty($data['create-marketplace-template'])) {
            $permission = $this->permissionRepository->findBySlug('create-marketplace-template');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-marketplace-template'])) {
            $permission = $this->permissionRepository->findBySlug('edit-marketplace-template');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-marketplace-template'])) {
            $permission = $this->permissionRepository->findBySlug('delete-marketplace-template');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-marketplace-template'])) {
            $permission = $this->permissionRepository->findBySlug('view-marketplace-template');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-marketplace-categories-entities'])) {
            $permission = $this->permissionRepository->findBySlug('create-marketplace-categories-entities');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-marketplace-categories-entities'])) {
            $permission = $this->permissionRepository->findBySlug('edit-marketplace-categories-entities');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-marketplace-categories-entities'])) {
            $permission = $this->permissionRepository->findBySlug('delete-marketplace-categories-entities');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-marketplace-categories-entities'])) {
            $permission = $this->permissionRepository->findBySlug('view-marketplace-categories-entities');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-filters'])) {
            $permission = $this->permissionRepository->findBySlug('create-filters');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-filters'])) {
            $permission = $this->permissionRepository->findBySlug('edit-filters');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-filters'])) {
            $permission = $this->permissionRepository->findBySlug('delete-filters');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-filters'])) {
            $permission = $this->permissionRepository->findBySlug('view-filters');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-service-packages'])) {
            $permission = $this->permissionRepository->findBySlug('create-service-packages');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-service-packages'])) {
            $permission = $this->permissionRepository->findBySlug('edit-service-packages');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-service-packages'])) {
            $permission = $this->permissionRepository->findBySlug('delete-service-packages');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-service-packages'])) {
            $permission = $this->permissionRepository->findBySlug('view-service-packages');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-service-package-categories'])) {
            $permission = $this->permissionRepository->findBySlug('create-service-package-categories');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-service-package-categories'])) {
            $permission = $this->permissionRepository->findBySlug('edit-service-package-categories');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-service-package-categories'])) {
            $permission = $this->permissionRepository->findBySlug('delete-service-package-categories');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-service-package-categories'])) {
            $permission = $this->permissionRepository->findBySlug('view-service-package-categories');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-projects'])) {
            $permission = $this->permissionRepository->findBySlug('create-projects');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-projects'])) {
            $permission = $this->permissionRepository->findBySlug('edit-projects');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-projects'])) {
            $permission = $this->permissionRepository->findBySlug('delete-projects');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-projects'])) {
            $permission = $this->permissionRepository->findBySlug('view-projects');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-ratings-reviews'])) {
            $permission = $this->permissionRepository->findBySlug('create-ratings-reviews');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-ratings-reviews'])) {
            $permission = $this->permissionRepository->findBySlug('edit-ratings-reviews');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-ratings-reviews'])) {
            $permission = $this->permissionRepository->findBySlug('delete-ratings-reviews');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-ratings-reviews'])) {
            $permission = $this->permissionRepository->findBySlug('view-ratings-reviews');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-conversations'])) {
            $permission = $this->permissionRepository->findBySlug('create-conversations');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-conversations'])) {
            $permission = $this->permissionRepository->findBySlug('edit-conversations');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-conversations'])) {
            $permission = $this->permissionRepository->findBySlug('delete-conversations');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-conversations'])) {
            $permission = $this->permissionRepository->findBySlug('view-conversations');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-briefs'])) {
            $permission = $this->permissionRepository->findBySlug('create-briefs');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-briefs'])) {
            $permission = $this->permissionRepository->findBySlug('edit-briefs');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-briefs'])) {
            $permission = $this->permissionRepository->findBySlug('delete-briefs');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-briefs'])) {
            $permission = $this->permissionRepository->findBySlug('view-briefs');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-customers'])) {
            $permission = $this->permissionRepository->findBySlug('create-customers');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-customers'])) {
            $permission = $this->permissionRepository->findBySlug('edit-customers');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-customers'])) {
            $permission = $this->permissionRepository->findBySlug('delete-customers');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-customers'])) {
            $permission = $this->permissionRepository->findBySlug('view-customers');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-marketplace-company-profile'])) {
            $permission = $this->permissionRepository->findBySlug('create-marketplace-company-profile');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-marketplace-company-profile'])) {
            $permission = $this->permissionRepository->findBySlug('edit-marketplace-company-profile');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-marketplace-company-profile'])) {
            $permission = $this->permissionRepository->findBySlug('delete-marketplace-company-profile');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-marketplace-company-profile'])) {
            $permission = $this->permissionRepository->findBySlug('view-marketplace-company-profile');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-service-provider-profile'])) {
            $permission = $this->permissionRepository->findBySlug('create-service-provider-profile');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-service-provider-profile'])) {
            $permission = $this->permissionRepository->findBySlug('edit-service-provider-profile');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-service-provider-profile'])) {
            $permission = $this->permissionRepository->findBySlug('delete-service-provider-profile');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-service-provider-profile'])) {
            $permission = $this->permissionRepository->findBySlug('view-service-provider-profile');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-users'])) {
            $permission = $this->permissionRepository->findBySlug('create-users');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-users'])) {
            $permission = $this->permissionRepository->findBySlug('edit-users');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-users'])) {
            $permission = $this->permissionRepository->findBySlug('delete-users');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-users'])) {
            $permission = $this->permissionRepository->findBySlug('view-users');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-domains'])) {
            $permission = $this->permissionRepository->findBySlug('create-domains');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-domains'])) {
            $permission = $this->permissionRepository->findBySlug('edit-domains');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-domains'])) {
            $permission = $this->permissionRepository->findBySlug('delete-domains');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-domains'])) {
            $permission = $this->permissionRepository->findBySlug('view-domains');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-domain-email'])) {
            $permission = $this->permissionRepository->findBySlug('create-domain-email');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-domain-email'])) {
            $permission = $this->permissionRepository->findBySlug('edit-domain-email');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-domain-email'])) {
            $permission = $this->permissionRepository->findBySlug('delete-domain-email');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-domain-email'])) {
            $permission = $this->permissionRepository->findBySlug('view-domain-email');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-domain-customer-portal'])) {
            $permission = $this->permissionRepository->findBySlug('create-domain-customer-portal');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-domain-customer-portal'])) {
            $permission = $this->permissionRepository->findBySlug('edit-domain-customer-portal');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-domain-customer-portal'])) {
            $permission = $this->permissionRepository->findBySlug('delete-domain-customer-portal');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-domain-customer-portal'])) {
            $permission = $this->permissionRepository->findBySlug('view-domain-customer-portal');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-domain-marketplace'])) {
            $permission = $this->permissionRepository->findBySlug('create-domain-marketplace');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-domain-marketplace'])) {
            $permission = $this->permissionRepository->findBySlug('edit-domain-marketplace');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-domain-marketplace'])) {
            $permission = $this->permissionRepository->findBySlug('delete-domain-marketplace');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-domain-marketplace'])) {
            $permission = $this->permissionRepository->findBySlug('view-domain-marketplace');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-integrations'])) {
            $permission = $this->permissionRepository->findBySlug('create-integrations');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-integrations'])) {
            $permission = $this->permissionRepository->findBySlug('edit-integrations');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-integrations'])) {
            $permission = $this->permissionRepository->findBySlug('delete-integrations');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-integrations'])) {
            $permission = $this->permissionRepository->findBySlug('view-integrations');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['create-payment-infrastructure'])) {
            $permission = $this->permissionRepository->findBySlug('create-payment-infrastructure');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['edit-payment-infrastructure'])) {
            $permission = $this->permissionRepository->findBySlug('edit-payment-infrastructure');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['delete-payment-infrastructure'])) {
            $permission = $this->permissionRepository->findBySlug('delete-payment-infrastructure');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        if (! empty($data['view-payment-infrastructure'])) {
            $permission = $this->permissionRepository->findBySlug('view-payment-infrastructure');

            if (! empty($permission)) {
                if (! $role->hasPermissionAssigned($permission))
                    $role->assignPermission($permission);
            }
        }

        $this->roleRepository->save($role);
        /*
         * create-marketplace-template
         * edit-marketplace-template
         * delete-marketplace-template
         * view-marketplace-template
         * create-marketplace-categories-entities
         * edit-marketplace-categories-entities
         * delete-marketplace-categories-entities
         * view-marketplace-categories-entities
         * create-filters
         * edit-filters
         * delete-filters
         * view-filters
         * create-service-packages
         * edit-service-packages
         * delete-service-packages
         * view-service-packages
         * create-service-package-categories
         * edit-service-package-categories
         * delete-service-package-categories
         * view-service-package-categories
         * create-projects
         * edit-projects
         * delete-projects
         * view-projects
         * create-ratings-reviews
         * edit-ratings-reviews
         * delete-ratings-reviews
         * view-ratings-reviews
         * create-conversations
         * edit-conversations
         * delete-conversations
         * view-conversations
         * create-briefs
         * edit-briefs
         * delete-briefs
         * view-briefs
         * create-customers
         * edit-customers
         * delete-customers
         * view-customers
         * create-marketplace-company-profile
         * edit-marketplace-company-profile
         * delete-marketplace-company-profile
         * view-marketplace-company-profile
         * create-service-provider-profile
         * edit-service-provider-profile
         * delete-service-provider-profile
         * view-service-provider-profile
         * create-users
         * edit-users
         * delete-users
         * view-users
         * create-domains
         * edit-domains
         * delete-domains
         * view-domains
         * create-domain-email
         * edit-domain-email
         * delete-domain-email
         * view-domain-email
         * create-domain-customer-portal
         * edit-domain-customer-portal
         * delete-domain-customer-portal
         * view-domain-customer-portal
         * create-domain-marketplace
         * edit-domain-marketplace
         * delete-domain-marketplace
         * view-domain-marketplace
         * create-integrations
         * edit-integrations
         * delete-integrations
         * view-integrations
         * create-payment-infrastructure
         * edit-payment-infrastructure
         * delete-payment-infrastructure
         * view-payment-infrastructure
        */

        return $this->roleDataTransferObject->fromEntity($role);
    }

    /**
     * @param Role $role
     * @return RoleDataTransferObject
     */
    public function convertToDataTransferObject(Role $role): RoleDataTransferObject
    {
        return $this->roleDataTransferObject->fromEntity($role);
    }

    /**
     * @return array
     */
    public function getAllRoles(): array
    {
        $roles = $this->roleRepository->findAll();

        $rolesDTO = [];

        foreach ($roles as $role) {
            $rolesDTO[] = $this->roleDataTransferObject->fromEntity($role);
        }

        return $rolesDTO;
    }

    /**
     * @param int $roleId
     * @return RoleDataTransferObject
     * @throws EntityNotFoundException
     */
    public function getRole(int $roleId): RoleDataTransferObject
    {
        $role = $this->roleRepository->find($roleId);

        if (is_null($role)) throw new EntityNotFoundException();

        return $this->roleDataTransferObject->fromEntity($role);
    }

    /**
     * @param Role $role
     * @param int $roleId
     * @throws EntityNotFoundException
     */
    public function loadById(Role &$role, int $roleId): void
    {
        $role = $this->roleRepository->find($roleId);

        if (is_null($role)) throw new EntityNotFoundException();
    }

    /**
     * @param array $data
     * @return RoleDataTransferObject
     * @throws EntityNotUpdatedException
     * @throws Exception\MissingAttributeException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateRole(array $data): RoleDataTransferObject
    {
        if
            (
                empty($data['role']) ||
                ! $data['role'] instanceof Role ||
                empty($data['role']->getId())
            )
            throw new EntityNotUpdatedException();

        $role = $data['role'];

        $this->roleRepository->save($role);

        return $this->roleDataTransferObject->fromEntity($role);
    }
}
