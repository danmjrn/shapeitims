<?php

namespace App\Entity;

use App\Repository\InvitationGroupRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvitationGroupRepository::class)]
class InvitationGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\Column(length: 255, unique: false, nullable: true)]
    private ?string $invitationAlias = null;

    #[ORM\ManyToOne(targetEntity: Invitation::class, inversedBy: 'invitationGroups')]
    #[ORM\JoinColumn(name: 'invitation_alias', referencedColumnName: 'alias', nullable: true)]
    private ?Invitation $invitation = null;

    #[ORM\OneToOne(inversedBy: 'invitationGroup', targetEntity: Invitee::class)]
    #[ORM\JoinColumn(name: 'invitee_id', referencedColumnName: 'id', nullable: false)]
    private ?Invitee $invitee = null;

    public const INVITATION_TYPE_COUPLE = 2;
    public const INVITATION_TYPE_GROUP = 3;
    public const INVITATION_TYPE_SINGLE = 1;

    /**
     * @param string $usernames
     * @param int $length
     * @return string
     */
    public static function generateRandomAlias(string $usernames, int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' . $usernames;
        $charactersLength = strlen($characters);
        $randomAlias = '';

        for ($i = 0; $i < $length; $i++) {
            $randomAlias .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomAlias;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Invitation|null
     */
    public function getInvitation(): ?Invitation
    {
        return $this->invitation;
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
    public function getInvitationAlias(): ?string
    {
        return $this->invitationAlias;
    }

    /**
     * @return Invitee|null
     */
    public function getInvitee(): ?Invitee
    {
        return $this->invitee;
    }

    /**
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param string|null $uuid
     */
    public function setUuid(?string $uuid): void
    {
        $this->uuid = $uuid;
    }

    /**
     * @param Invitation|null $invitation
     */
    public function setInvitation(?Invitation $invitation): void
    {
        $this->invitation = $invitation;
    }

    /**
     * @param string $invitationAlias
     * @return $this
     */
    public function setInvitationAlias(string $invitationAlias): self
    {
        $this->invitationAlias = $invitationAlias;

        return $this;
    }

    /**
     * @param Invitee|null $invitee
     */
    public function setInvitee(?Invitee $invitee): void
    {
        $this->invitee = $invitee;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }
}
