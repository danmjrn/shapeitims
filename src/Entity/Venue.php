<?php

namespace App\Entity;

use App\Repository\VenueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VenueRepository::class)]
class Venue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $address = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $mapLink = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'venues')]
    private ?InvitationDetail $invitationDetail = null;

    #[ORM\OneToMany(mappedBy: 'venue', targetEntity: TableDetail::class)]
    private Collection $tableDetails;

    public function __construct()
    {
        $this->tableDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getMapLink(): ?string
    {
        return $this->mapLink;
    }

    public function setMapLink(?string $mapLink): self
    {
        $this->mapLink = $mapLink;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getInvitationDetail(): ?InvitationDetail
    {
        return $this->invitationDetail;
    }

    public function setInvitationDetail(?InvitationDetail $invitationDetail): self
    {
        $this->invitationDetail = $invitationDetail;

        return $this;
    }

    /**
     * @return Collection<int, TableDetail>
     */
    public function getTableDetails(): Collection
    {
        return $this->tableDetails;
    }

    public function addTableDetail(TableDetail $tableDetail): self
    {
        if (!$this->tableDetails->contains($tableDetail)) {
            $this->tableDetails->add($tableDetail);
            $tableDetail->setVenue($this);
        }

        return $this;
    }

    public function removeTableDetail(TableDetail $tableDetail): self
    {
        if ($this->tableDetails->removeElement($tableDetail)) {
            // set the owning side to null (unless already changed)
            if ($tableDetail->getVenue() === $this) {
                $tableDetail->setVenue(null);
            }
        }

        return $this;
    }
}
