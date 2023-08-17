<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mediaType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $fileName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $fileType = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $position = null;

    /**
     * @var InvitationDetail|null
     */
    #[ORM\ManyToOne(targetEntity: InvitationDetail::class, inversedBy: 'media')]
    private ?InvitationDetail $invitationDetail;

    /**
     * @var Collection<User>|null
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'media')]
    #[ORM\JoinTable(name: 'media_user')]
    private ?Collection $users;

    const MEDIA_TYPE_CONTENT = 'content';
    const MEDIA_TYPE_GALLERY = 'gallery';
    const MEDIA_TYPE_ICON = 'icon';
    const MEDIA_TYPE_LOGO = 'logo';
    const MEDIA_TYPE_LINK = 'link';
    const MEDIA_TYPE_PROFILE = 'profile';
    const MEDIA_TYPE_SLIDER = 'slider';
    const MEDIA_TYPE_BACKGROUND = 'background';

    const FILE_NAME_IMAGE_PLACEHOLDER = 'placeholder.png';

    const FILE_TYPE_AUDIO = 'audio';
    const FILE_TYPE_DOCUMENT = 'document';
    const FILE_TYPE_IMAGE = 'image';
    const FILE_TYPE_VIDEO = 'video';


    public function getId(): ?int
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->users = new ArrayCollection();

        $this->uuid = Uuid::uuid4()->toString();
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    public function setMediaType(string $mediaType): self
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(?string $fileType): self
    {
        $this->fileType = $fileType;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Collection<User>|null
     */
    public function getUsers(): ?Collection
    {
        return $this->users;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addMedia($this);
        }

        return $this;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return InvitationDetail|null
     */
    public function getInvitationDetail(): ?InvitationDetail
    {
        return $this->invitationDetail;
    }

    /**
     * @param InvitationDetail $invitationDetail
     * @return $this
     */
    public function setInvitationDetail(InvitationDetail $invitationDetail): self
    {
        $this->invitationDetail = $invitationDetail;

        return $this;
    }


}
