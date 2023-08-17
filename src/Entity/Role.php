<?php

namespace App\Entity;

use App\Entity\Traits\SluggedTrait;
use App\Repository\RoleRepository;
//use App\Security\AccessControl\Exception\RoleAlreadyHasPermissionAssignedException;
//use App\Security\AccessControl\Exception\RoleDoesNotHavePermissionAssignedException;
//use App\Security\AccessControl\Exception\UserDoesNotHavePermissionAssignedException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    use SluggedTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $uuid;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $name;

    /**
     * @var Collection<User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'roles')]
    private Collection $users;

    /**
     * @var Collection<Permission>
     */
    #[ORM\ManyToMany(targetEntity: Permission::class, mappedBy: 'roles', cascade: ['persist', 'remove'])]
    private Collection $permissions;

    public const ROLE_INTERNAL_ADMIN = 'Internal Admin';
    public const ROLE_INTERNAL_VIEWER = 'Internal Viewer';
    public const ROLE_SUPER_ADMIN = 'Super Admin';
    public const ROLE_INVITEE = 'Invitee';
    public const ROLE_BETROTHED = 'Betrothed';

    /**
     * @param string $name
     * @return string|null
     */
    public static function generateUnprocessedName(string $name): ?string
    {
        return ucwords
        (
            strtolower
            (
                str_replace
                (
                    ['ROLE_', '_'],
                    ['', ' '],
                    $name
                )
            )
        );
    }

    /**
     * Role constructor.
     */
    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();

        $this->users = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    /**
     * @param User $user
     * @return $this
     */
    public function addUser( User $user ): self
    {
        if ( $user instanceof InternalUser ) {

            if ( ! $this->users->contains( $user ) ) {
                $this->users->add($user);

                $user->assignRole($this);
            }
        }

        if ( $user instanceof Invitee ) {

            if ( ! $this->users->contains( $user ) ) {
                $this->users->add($user);

                $user->assignRole($this);
            }
        }

        if ( $user instanceof Betrothed ) {

            if ( ! $this->users->contains( $user ) ) {
                $this->users->add($user);

                $user->assignRole($this);
            }
        }

        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function removeUser( User $user ): self
    {
        if ( $user instanceof InternalUser ) {
            if ( $this->users->removeElement( $user ) )
                $user->revokeRole( $this );
        }

        if ( $user instanceof Invitee ) {
            if ( $this->users->removeElement( $user ) )
                $user->revokeRole($this);
        }

        if ( $user instanceof Betrothed ) {
            if ( $this->users->removeElement( $user ) )
                $user->revokeRole($this);
        }

        return $this;
    }

    /**
     * @param Permission $permission
     * @return $this
//     * @throws RoleAlreadyHasPermissionAssignedException
     */
    public function assignPermission( Permission $permission ): self
    {
        if ( ! $this->permissions->contains( $permission ) ) {
            $this->permissions->add( $permission );

            $permission->addRole( $this );
        }

        return $this;
    }

    /**
     * @param Permission $permission
     * @return $this
//     * @throws RoleDoesNotHavePermissionAssignedException
     */
    public function revokePermission( Permission $permission ): self
    {
        if ($this->permissions->removeElement( $permission ))
            $permission->removeRole( $this );

        return $this;
    }

    /**
     * @return $this
//     * @throws RoleDoesNotHavePermissionAssignedException
     */
    public function clearPermissions(): self
    {
        /** @var Permission $permission */
        foreach ( $this->getPermissions() as $permission ) {
            $permission->removeRole( $this );
        }

        $this->permissions = new ArrayCollection();

        return $this;
    }

    /**
     * @return string|null
     */
    public function generateProcessedName(): ?string
    {
        if (empty($this->name)) return null;

        return sprintf
        (
            'ROLE_%s',
            strtoupper
            (
                str_replace
                (
                    ' ',
                    '_',
                    $this->name
                )
            )
        );
    }

    /**
     * @param Permission $permission
     * @return bool
     */
    public function hasPermissionAssigned( Permission $permission ): bool
    {
        return $this->permissions->contains( $permission );
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
     * @param string $uuid
     * @return Role
     */
    public function setUuid(string $uuid): Role
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    /**
     * @return Collection
     */
    public function getInternalUsers(): Collection
    {
        return $this->internalUsers;
    }
}
