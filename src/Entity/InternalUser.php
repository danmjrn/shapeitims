<?php

namespace App\Entity;

use App\Repository\InternalUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Asserts;

#[ORM\Entity(repositoryClass: InternalUserRepository::class)]
class InternalUser extends User
{
    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    private ?string $email = null;

    /**
     * @var Collection<Invitee>
     */
    #[ORM\OneToMany(mappedBy: 'internalUser', targetEntity: Invitee::class)]
    private Collection $invitees;

    public const DEFAULT_ROLES = [
        'ROLE_INTERNAL_ADMIN',
        'ROLE_USER'
    ];

    public function __construct()
    {
        $this->isDeleted = false;
        $this->isVerified = false;

        $this->invitees = new ArrayCollection();

        parent::__construct();
    }

    /**
     * @param Invitee $invitee
     * @return $this
     */
    public function addInvitee( Invitee $invitee ): self
    {
        if (! $this->invitees->contains($invitee)) {
            $this->invitees->add($invitee);

            $invitee->setInternalUser( $this );
        }

        return $this;
    }

    /**
     * @param Invitee $invitee
     * @return bool
     */
    public function hasInviteeAdded( Invitee $invitee ): bool
    {
        if (property_exists(static::class, 'invitees'))
            return $this->invitees->contains($invitee);
        else
            return false;
    }

    /**
     * @param Invitee $invitee
     * @return $this
     */
    public function removeInvitee( Invitee $invitee ): self
    {
        $this->invitees->removeElement($invitee);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return Collection
     */
    public function getInvitees(): Collection
    {
        return $this->invitees;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
