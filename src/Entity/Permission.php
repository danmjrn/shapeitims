<?php

namespace App\Entity;

use App\Entity\Exception\UnknownPermissionTypeException;
use App\Entity\Traits\SluggedTrait;
use App\Repository\PermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
class Permission
{
    use SluggedTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'uuid', unique: true, nullable: true)]
    private ?string $uuid = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $name;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, unique: false)]
    private string $type;

    /**
     * @var Collection<Role>
     */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'permissions')]
    private Collection $roles;

    const PERMISSION_CREATE_INVITATIONS = 'create-invitations';
    const PERMISSION_CREATE_ROLES_AND_PERMISSIONS = 'create-users-roles-permissions';
    const PERMISSION_CREATE_USERS = 'create-users-manage-users';

    const PERMISSION_DELETE_INVITATIONS = 'delete-invitations';

    const PERMISSION_EDIT_INVITATIONS = 'edit-invitations';
    const PERMISSION_EDIT_ROLES_AND_PERMISSIONS = 'edit-users-roles-permissions';
    const PERMISSION_EDIT_USERS = 'edit-users-manage-users';

    const PERMISSION_TYPE_CREATE = 'CREATE';
    const PERMISSION_TYPE_DELETE = 'DELETE';
    const PERMISSION_TYPE_EDIT = 'EDIT';
    const PERMISSION_TYPE_VIEW = 'VIEW';

    const PERMISSION_VIEW_INVITATIONS = 'view-invitations';
    const PERMISSION_VIEW_ROLES_AND_PERMISSIONS = 'view-users-roles-permissions';
    const PERMISSION_VIEW_USERS = 'view-users-manage-users';

    /**
     * @param string $permission
     * @param string $permissionType
     * @return string
     * @throws UnknownPermissionTypeException
     */
    public static function generatePermission(string $permission, string $permissionType): string
    {
        if
        (
            $permissionType !== static::PERMISSION_TYPE_CREATE &&
            $permissionType !== static::PERMISSION_TYPE_DELETE &&
            $permissionType !== static::PERMISSION_TYPE_EDIT &&
            $permissionType !== static::PERMISSION_TYPE_VIEW
        )
            throw new UnknownPermissionTypeException();

        return sprintf('%s | %s', $permissionType, $permission);
    }

    /**
     * Permission constructor.
     */
    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();

        $this->roles = new ArrayCollection();
    }

    /**
     * @param Role $role
     * @return $this
     */
    public function addRole(Role $role): self
    {
        if (! $this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * @param Role $role
     * @return $this
     */
    public function removeRole(Role $role): self
    {
        $this->roles->removeElement($role);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Collection
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $uuid
     * @return Permission
     */
    public function setUuid(string $uuid): Permission
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param string $name
     * @param string $permissionType
     * @return $this
     * @throws UnknownPermissionTypeException
     */
    public function setName(string $name, string $permissionType): self
    {
        $this->name = static::generatePermission($name, $permissionType);

        $this->type = $permissionType;

        return $this;
    }
}
