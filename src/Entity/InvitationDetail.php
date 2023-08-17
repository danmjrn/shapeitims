<?php

namespace App\Entity;

use App\Repository\InvitationDetailRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvitationDetailRepository::class)]
class InvitationDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    private ?int $maximumDistribution = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $eventDate = null;

    #[ORM\OneToMany(mappedBy: 'invitationDetail', targetEntity: Venue::class)]
    private Collection $venues;

    #[ORM\OneToMany(mappedBy: 'invitationDetail', targetEntity: Media::class)]
    private Collection $media;

    public function __construct()
    {
        $this->venues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getMaximumDistribution(): ?int
    {
        return $this->maximumDistribution;
    }

    public function setMaximumDistribution(?int $maximumDistribution): self
    {
        $this->maximumDistribution = $maximumDistribution;

        return $this;
    }

    public function getEventDate(): ?\DateTimeInterface
    {
        return $this->eventDate;
    }

    public function setEventDate(\DateTimeInterface $eventDate): self
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    /**
     * @return Collection<int, Venue>
     */
    public function getVenues(): Collection
    {
        return $this->venues;
    }

    public function addVenue(Venue $venue): self
    {
        if (!$this->venues->contains($venue)) {
            $this->venues->add($venue);
            $venue->setInvitationDetail($this);
        }

        return $this;
    }

    public function removeVenue(Venue $venue): self
    {
        if ($this->venues->removeElement($venue)) {
            // set the owning side to null (unless already changed)
            if ($venue->getInvitationDetail() === $this) {
                $venue->setInvitationDetail(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getMedias(): Collection
    {
        return $this->media;
    }

    /**
     * @param Media $media
     * @return $this
     */
    public function addMedia(Media $media): self
    {
        if ( ! $this->media->contains( $media ) ) {
            $this->media[] = $media;
            $media->setInvitationDetail( $this );
        }

        return $this;
    }


}
