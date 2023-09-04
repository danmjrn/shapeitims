<?php

namespace App\Entity;

use App\Repository\InvitationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: InvitationRepository::class)]
class Invitation
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\Column(length: 255, unique: true, nullable: false)]
    private ?string $alias = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'uuid', unique: true, nullable: true)]
    private ?string $uuid = null;

    /**
     * @var Collection<InvitationGroup>
     */
    #[ORM\OneToMany(mappedBy: 'invitation', targetEntity: InvitationGroup::class)]
    private Collection $invitationGroups;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $rsvp = null;

    #[ORM\Column(nullable: true)]
    private ?int $timesOpened;

    /**
     * @var int|null
     * this variable holds the number
     * of people the invitation is for.
     */
    #[ORM\Column(nullable: true)]
    private ?int $invitationFor;

    /**
     * @var bool|null
     * used for invitations for single people
     */
    #[ORM\Column]
    private ?bool $hasPlusOne = false;

    #[ORM\ManyToMany(targetEntity: InvitationDetail::class, inversedBy: 'invitations')]
    #[ORM\JoinColumn(name: 'invitation_alias', referencedColumnName: 'alias', nullable: true)]
    private Collection $invitationDetails;

    public const ANY = 'ANY';
    public const YES = 'Will be attending';
    public const NO = 'Will not be attending';
    public const UNSURE = 'Unsure';

    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
        $this->timesOpened = 0;

        $this->invitationGroups = new ArrayCollection();
        $this->invitationDetails = new ArrayCollection();
    }

    /**
     * @param InvitationGroup $invitationGroup
     * @return $this
     */
    public function addInvitationGroup( InvitationGroup $invitationGroup ): self
    {
        if (! $this->invitationGroups->contains($invitationGroup)) {
            $this->invitationGroups->add($invitationGroup);

            $invitationGroup->setInvitation( $this );
        }

        return $this;
    }

    /**
     * @param InvitationGroup $invitationGroup
     * @return bool
     */
    public function hasInvitationGroupAdded( InvitationGroup $invitationGroup ): bool
    {
        if (property_exists(static::class, 'invitationGroups'))
            return $this->invitationGroups->contains($invitationGroup);
        else
            return false;
    }

    /**
     * @param InvitationGroup $invitationGroup
     * @return $this
     */
    public function removeInvitationGroup( InvitationGroup $invitationGroup ): self
    {
        $this->invitationGroups->removeElement($invitationGroup);

        return $this;
    }

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

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @return Collection
     */
    public function getInvitationGroups(): Collection
    {
        return $this->invitationGroups;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getRsvp(): ?string
    {
        return $this->rsvp;
    }

    public function getTimesOpened(): ?int
    {
        return $this->timesOpened;
    }

    public function setRsvp(?string $rsvp): self
    {
        $this->rsvp = $rsvp;

        return $this;
    }

    /**
     * @param string|null $uuid
     */
    public function setUuid(?string $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function setTimesOpened(?int $timesOpened): self
    {
        $this->timesOpened = $timesOpened;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getInvitationFor(): ?int
    {
        return $this->invitationFor;
    }

    /**
     * @param int|null $invitationFor
     */
    public function setInvitationFor(?int $invitationFor): void
    {
        $this->invitationFor = $invitationFor;
    }

    /**
     * @return bool|null
     */
    public function getHasPlusOne(): ?bool
    {
        return $this->hasPlusOne;
    }

    /**
     * @param bool|null $hasPlusOne
     */
    public function setHasPlusOne(?bool $hasPlusOne): void
    {
        $this->hasPlusOne = $hasPlusOne;
    }

    /**
     * @return Collection
     */
    public function getInvitationDetails(): Collection
    {
        return $this->invitationDetails;
    }

    /**
     * @param InvitationDetail|null $invitationDetail
     * @return $this
     */
    public function addInvitationDetail(?InvitationDetail $invitationDetail): self
    {
        if (!$this->invitationDetails->contains($invitationDetail)) {
            $this->invitationDetails->add($invitationDetail);
            $invitationDetail?->addInvitation($this);
        }

        return $this;
    }


}
