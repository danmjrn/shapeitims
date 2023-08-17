<?php

namespace App\Entity;

use App\Repository\SeatPlacementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeatPlacementRepository::class)]
class SeatPlacement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $tableNumber = null;

    #[ORM\OneToOne(inversedBy: 'seatPlacement', cascade: ['persist', 'remove'])]
    private ?TableDetail $tableDetail = null;

    /**
     * @var Collection<Invitee>
     */
    #[ORM\OneToMany(mappedBy: 'seatPlacement', targetEntity: Invitee::class)]
    private Collection $invitees;

    public function __construct()
    {
        $this->invitees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTableNumber(): ?int
    {
        return $this->tableNumber;
    }

    public function setTableNumber(?int $tableNumber): self
    {
        $this->tableNumber = $tableNumber;

        return $this;
    }

    public function getTableDetail(): ?TableDetail
    {
        return $this->tableDetail;
    }

    public function setTableDetail(?TableDetail $tableDetail): self
    {
        $this->tableDetail = $tableDetail;

        return $this;
    }

    /**
     * @return Collection<Invitee>
     */
    public function getInvitees(): Collection
    {
        return $this->invitees;
    }

    /**
     * @param Invitee $invitee
     * @return $this
     */
    public function addInvitee(Invitee $invitee): self
    {
        if (!$this->invitees->contains($invitee)) {
            $this->invitees->add($invitee);
            $invitee->setSeatPlacement($this);
        }

        return $this;
    }

    /**
     * @param Invitee $invitee
     * @return $this
     */
    public function removeInvitee(Invitee $invitee): self
    {
        if ($this->invitees->removeElement($invitee)) {
            // set the owning side to null (unless already changed)
            if ($invitee->getSeatPlacement() === $this) {
                $invitee->setSeatPlacement(null);
            }
        }

        return $this;
    }
}
