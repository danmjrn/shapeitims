<?php


namespace App\Service\Domain\Entity;


use App\Entity\Role;

use App\Service\Domain\Exception\MissingAttributeException;

class RoleDataTransferObject extends DataTransferObject
{
    /**
     * @var PermissionDataTransferObject
     */
    private PermissionDataTransferObject $permissionDataTransferObject;

    /**
     * RoleDataTransferObject constructor.
     * @param PermissionDataTransferObject $permissionDataTransferObject
     */
    public function __construct(PermissionDataTransferObject $permissionDataTransferObject)
    {
        $this->permissionDataTransferObject = $permissionDataTransferObject;
    }

    /**
     * @param Role $role
     * @return $this
     */
    public function fromEntity(Role $role): self
    {
        $roleDTO = new static($this->permissionDataTransferObject);

        $roleDTO->id = $role->getId();
        $roleDTO->uuid = $role->getUuid();
        $roleDTO->description = $role->getDescription();
        $roleDTO->name = $role->getName();
        $roleDTO->slug = $role->getSlug();

        foreach ($role->getPermissions() as $permission)
            $roleDTO->permissions[] = $this->permissionDataTransferObject->fromEntity($permission);

        return $roleDTO;
    }

    /**
     * @param string $slug
     * @return bool
     */
    public function hasPermission(string $slug): bool
    {
        if (! isset($this->permissions)) return false;

        $permissionsDTO = (array) $this->permissions;

        foreach ($permissionsDTO as $permissionDTO) {
            if (! $permissionDTO instanceof PermissionDataTransferObject) return false;

            if ($permissionDTO->slug === $slug) return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @param Role|null $role
     * @return Role
     * @throws MissingAttributeException
     */
    public function toEntity(array $data, Role $role = null): Role
    {
        if (empty($data['name']))
            throw new MissingAttributeException();

        if (is_null($role))
            $role = new Role();

        $role
            ->setName($data['name']);

        if (! empty($data['description']))
            $role->setDescription($data['description']);

        return $role;
    }
}
