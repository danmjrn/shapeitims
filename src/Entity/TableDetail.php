<?php

namespace App\Entity;

use App\Repository\TableDetailRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TableDetailRepository::class)]
class TableDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $capacity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $alias = null;

    #[ORM\Column]
    private ?int $number = null;

    #[ORM\Column]
    private ?int $order = null;

    #[ORM\ManyToOne(inversedBy: 'tableDetails')]
    private ?Venue $venue = null;

    #[ORM\OneToOne(mappedBy: 'tableDetail', cascade: ['persist', 'remove'])]
    private ?SeatPlacement $seatPlacement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(?string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getVenue(): ?Venue
    {
        return $this->venue;
    }

    public function setVenue(?Venue $venue): self
    {
        $this->venue = $venue;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getOrder(): ?int
    {
        return $this->order;
    }

    /**
     * @param int|null $order
     * @return TableDetail
     */
    public function setOrder(?int $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getSeatPlacement(): ?SeatPlacement
    {
        return $this->seatPlacement;
    }

    public function setSeatPlacement(?SeatPlacement $seatPlacement): self
    {
        // unset the owning side of the relation if necessary
        if ($seatPlacement === null && $this->seatPlacement !== null) {
            $this->seatPlacement->setTableDetail(null);
        }

        // set the owning side of the relation if necessary
        if ($seatPlacement !== null && $seatPlacement->getTableDetail() !== $this) {
            $seatPlacement->setTableDetail($this);
        }

        $this->seatPlacement = $seatPlacement;

        return $this;
    }
}
