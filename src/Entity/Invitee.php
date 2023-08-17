<?php

namespace App\Entity;

use App\Repository\InviteeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InviteeRepository::class)]
class Invitee extends User
{
    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, unique: false, nullable: true)]
    private ?string $email = null;

    /**
     * @var InvitationGroup|null
     */
    #[ORM\OneToOne(mappedBy: 'invitee', targetEntity: InvitationGroup::class)]
    protected ?InvitationGroup $invitationGroup;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 50, unique: false, nullable: true)]
    private ?string $phoneNumber = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    protected ?string $title = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 100, unique: false, nullable: true)]
    protected ?string $inviteeFrom = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 100, unique: false, nullable: true)]
    protected ?string $inviteeLang = null;

    #[ORM\ManyToOne(targetEntity: InternalUser::class, inversedBy: 'invitees')]
    #[ORM\JoinColumn(name: 'internal_user_id', referencedColumnName: 'id', nullable: false)]
    protected ?InternalUser $internalUser;

    #[ORM\ManyToOne(inversedBy: 'invitees')]
    private ?SeatPlacement $seatPlacement = null;

    public const DEFAULT_ROLES = [
        'ROLE_INVITEE',
        'ROLE_USER'
    ];

    public const INVITEE_FROM_BOTH = 'Both';
    public const INVITEE_FROM_GROOM = 'Groom';
    public const INVITEE_FROM_BRIDE = 'Bride';

    public function __construct()
    {
        $this->isDeleted = false;
        $this->isVerified = false;

        $this->invitationGroup = new InvitationGroup();

        $this->internalUser = new InternalUser();

        parent::__construct();
    }

    /**
     * @return InternalUser|null
     */
    public function getInternalUser(): ?InternalUser
    {
        return $this->internalUser;
    }

    /**
     * @return InvitationGroup|null
     */
    public function getInvitationGroup(): ?InvitationGroup
    {
        return $this->invitationGroup;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getInviteeFrom(): ?string
    {
        return $this->inviteeFrom;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param InternalUser|null $internalUser
     */
    public function setInternalUser(?InternalUser $internalUser): void
    {
        $this->internalUser = $internalUser;
    }

    /**
     * @param InvitationGroup|null $invitationGroup
     */
    public function setInvitationGroup(?InvitationGroup $invitationGroup): void
    {
        $this->invitationGroup = $invitationGroup;
    }

    /**
     * @param string|null $inviteeFrom
     */
    public function setInviteeFrom(?string $inviteeFrom): void
    {
        $this->inviteeFrom = $inviteeFrom;
    }

    /**
     * @param string|null $phoneNumber
     * @return void
     */
    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getInviteeLang(): ?string
    {
        return $this->inviteeLang;
    }

    /**
     * @param string|null $inviteeLang
     * @return Invitee
     */
    public function setInviteeLang(?string $inviteeLang): self
    {
        $this->inviteeLang = $inviteeLang;

        return $this;
    }

    public function getSeatPlacement(): ?SeatPlacement
    {
        return $this->seatPlacement;
    }

    public function setSeatPlacement(?SeatPlacement $seatPlacement): self
    {
        $this->seatPlacement = $seatPlacement;

        return $this;
    }



}
