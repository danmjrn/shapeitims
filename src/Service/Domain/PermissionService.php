<?php


namespace App\Service\Domain;


use App\Entity\Exception\EntityNotFoundException;
use App\Repository\PermissionRepository;

use App\Service\Domain\Entity\PermissionDataTransferObject;

class PermissionService
{
    /**
     * @var PermissionDataTransferObject
     */
    private PermissionDataTransferObject $permissionDataTransferObject;

    /**
     * @var PermissionRepository
     */
    private PermissionRepository $permissionRepository;

    /**
     * PermissionService constructor.
     * @param PermissionDataTransferObject $permissionDataTransferObject
     * @param PermissionRepository $permissionRepository
     */
    public function __construct
        (
            PermissionDataTransferObject $permissionDataTransferObject,
            PermissionRepository $permissionRepository
        )
    {
        $this->permissionDataTransferObject = $permissionDataTransferObject;
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * @return array
     */
    public function getAllPermissions(): array
    {
        $permissions = $this->permissionRepository->findAll();

        $permissionsDTO = [];

        foreach ($permissions as $permission) {
            $permissionsDTO[] = $this->permissionDataTransferObject->fromEntity($permission);
        }

        return $permissionsDTO;
    }

    /**
     * @param int $permissionId
     * @return PermissionDataTransferObject
     * @throws EntityNotFoundException
     */
    public function getPermission(int $permissionId): PermissionDataTransferObject
    {
        $permission = $this->permissionRepository->find($permissionId);

        if (is_null($permission)) throw new EntityNotFoundException();

        return $this->permissionDataTransferObject->fromEntity($permission);
    }
}