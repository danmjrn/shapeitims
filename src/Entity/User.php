<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JetBrains\PhpStorm\Pure;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Asserts;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'betrothed' => Betrothed::class,
    'internal' => InternalUser::class,
    'invitee' => Invitee::class
//    'external' => ExternalUser::class
])]
#[UniqueEntity('username')]
abstract class User implements UserInterface, EquatableInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableEntity;

    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'uuid', unique: true, nullable: true)]
    protected ?string $uuid = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100)]
    #[Asserts\Length(min: 3, max: 100)]
    protected ?string $firstname = null;

    /**
     * @var bool|null
     */
    #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => 0])]
    protected ?bool $isDeleted = false;

    #[ORM\Column(type: 'boolean')]
    protected bool $isVerified = false;

    /**
     * @var DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTime $lastLoggedInAt = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Asserts\Length(min: 3, max: 100)]
    protected ?string $lastname = null;

    /**
     * @var string|null The hashed password
     */
    #[ORM\Column(type: 'string')]
    protected ?string $password = null;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Asserts\NotBlank()]
    protected string $username = '';

    /**
     * @var Collection<Role>
     */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users')]
    protected Collection $roles;

    public const DEFAULT_ROLES = [
        'ROLE_USER'
    ];

    /**
     * @var Collection<Media>|null
     */
    #[ORM\ManyToMany(targetEntity: Media::class, mappedBy: 'users')]
    protected ?Collection $media;

    const ROLE_SUPER_ADMIN = 'Super Admin';

    const USER_TYPE_INTERNAL = 'internal';
    const USER_TYPE_BETROTHED = 'betrothed';
    const USER_TYPE_INVITEE = 'invitee';


    /**
     * @return string
     */
    public static function generateRandomPassword(): string
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

        $pass = array();

        $alphaLength = strlen($alphabet) - 1;

        for ($i = 0; $i < 64; $i++) {
            $n = rand(0, $alphaLength);

            $pass[] = $alphabet[$n];
        }

        return implode($pass);
    }

    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();

        $this->media = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    /**
     * @return string
     */
    #[Pure] public function __toString(): string
    {
        return $this->getFullName();
    }

    /**
     * @param Role $role
     * @return $this
     */
    public function assignRole(Role $role): self
    {
        if (! $this->roles->contains($role)) {
            $this->roles->add($role);

            $role->addUser($this);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function deleteUser(): self
    {
        $this->isDeleted = true;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    #[Pure]
    public function isEqualTo( UserInterface $user ): bool
    {
        return $this->username === $user->getUserIdentifier();
    }

    /**
     * @return bool|null
     */
    public function isDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    /**
     * @return bool|null
     */
    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    /**
     * @param Role $role
     * @return bool
     */
    public function hasRoleAssigned(Role $role): bool
    {
        if (property_exists(static::class, 'roles'))
            return $this->roles->contains($role);
        else
            return false;
    }

    /**
     * @param Role $role
     * @return $this
     */
    public function revokeRole(Role $role): self
    {
        if ( $this->roles->removeElement($role) )
            $role->removeUser($this);

        return $this;
    }

    /**
     * @return $this
     */
    public function signInUser(): self
    {
        $this->lastLoggedInAt = new \DateTime();

        return $this;
    }

    /**
     * @return $this
     */
    public function verifyUser(): self
    {
        $this->isVerified = true;

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
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return sprintf('%s %s', $this->firstname, $this->lastname);
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getLastLoggedInAt(): ?DateTimeInterface
    {
        return $this->lastLoggedInAt;
    }

    /**
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = [];

        if (property_exists(static::class, 'roles')) {
            $rolesArray = $this->roles;

            /** @var Role $role */
            foreach ($rolesArray as $role) {
                $roles[] = $role->generateProcessedName();
            }
        }

        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @return Collection
     */
    public function getRolesCollection(): Collection
    {
        return $this->roles;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @param string|null $uuid
     * @return $this
     */
    public function setUuid(?string $uuid): User
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return $this
     */
    public function setDefaultRoles(): self
    {
        $defaultRoles = new ArrayCollection(static::DEFAULT_ROLES);
        $this->roles = $defaultRoles;

        return $this;
    }

    /**
     * @param string $firstname
     * @return $this
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @param bool $isDeleted
     * @return $this
     */
    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * @param bool $isVerified
     * @return $this
     */
    public function setIsVerified(bool $isVerified = false): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @param DateTimeInterface|null $lastLoggedInAt
     * @return $this
     */
    public function setLastLoggedInAt(?DateTimeInterface $lastLoggedInAt): self
    {
        $this->lastLoggedInAt = $lastLoggedInAt;

        return $this;
    }

    /**
     * @param string $lastname
     * @return $this
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param array|null $roles
     * @return $this
     */
    public function setRoles(?array $roles): self
    {
        $this->roles = new ArrayCollection($roles);

        return $this;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param Media $media
     * @return $this
     */
    public function addMedia(Media $media): self
    {
        if (!$this->media->contains($media)) {
            $this->media[] = $media;
        }

        return $this;
    }
}
