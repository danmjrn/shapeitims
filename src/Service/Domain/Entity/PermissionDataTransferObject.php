<?php


namespace App\Service\Domain\Entity;


use App\Entity\Exception\UnknownPermissionTypeException;
use App\Entity\Permission;
use App\Service\Domain\Exception\MissingAttributeException;
use JetBrains\PhpStorm\Pure;

class PermissionDataTransferObject extends DataTransferObject
{
    /**
     * @param Permission $permission
     * @return $this
     */
    public function fromEntity(Permission $permission): self
    {
        $permissionDTO = new static();

        $permissionDTO->id = $permission->getId();
        $permissionDTO->uuid = $permission->getUuid();
        $permissionDTO->description = $permission->getDescription();
        $permissionDTO->name = $permission->getName();
        $permissionDTO->slug = $permission->getSlug();
        $permissionDTO->type = $permission->getType();

        return $permissionDTO;
    }

    /**
     * @param array $data
     * @param Permission|null $permission
     * @return Permission
     * @throws MissingAttributeException
     * @throws UnknownPermissionTypeException
     */
    public function toEntity(array $data, Permission $permission = null): Permission
    {
        if
            (
                empty($data['name']) ||
                empty($data['permissionType'])
            )
            throw new MissingAttributeException();

        if (is_null($permission))
            $permission = new Permission();

        $permission
            ->setName
                (
                    $data['name'],
                    $data['permissionType']
                );

        if (isset($data['description']))
            $permission->setDescription($data['description']);

        return $permission;
    }
}