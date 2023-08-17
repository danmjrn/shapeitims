<?php


namespace App\Service\Domain\Entity;

use App\Entity\Betrothed;
use App\Entity\InternalUser;
use App\Entity\Invitee;
use App\Entity\Media;
use App\Entity\User;
use App\Service\Domain\Exception\MissingAttributeException;

class MediaDataTransferObject extends DataTransferObject
{
    /**
     * @param array $mediaItems
     * @return array
     */
    public function convertToDataTransferObjects(array $mediaItems): array
    {
        $mediaDTOs = [];

        foreach ($mediaItems as $media) {
            $mediaDTOs[] = $this->fromEntity($media);
        }

        return $mediaDTOs;
    }

    /**
     * @param Media $media
     * @return $this
     */
    public function fromEntity(Media $media): self
    {
        $mediaDTO = new static();

        $mediaDTO->uuid = $media->getUuid();
        $mediaDTO->mediaType = $media->getMediaType();
        $mediaDTO->fileName = $media->getFileName();
        $mediaDTO->fileType = $media->getFileType();
        $mediaDTO->position = $media->getPosition();

        $mediaDTO->createdAt = $media->getCreatedAt();
        $mediaDTO->updatedAt = $media->getUpdatedAt();

        if( $media->getInvitationDetail() )
            $mediaDTO->invitationDetail = $media->getInvitationDetail();

        if( $media->getUsers() ){
            foreach ($media->getUsers() as $user) {
                $mediaDTO->users[] = $this->fromUser($user);
            }
        }

        return $mediaDTO;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function fromUser( User $user ): self
    {
        $dto = new static();

        if ( $user instanceof Betrothed )
            $dto->entityType = Betrothed::class;

        if ( $user instanceof InternalUser )
            $dto->entityType = InternalUser::class;

        if ( $user instanceof Invitee )
            $dto->entityType = Invitee::class;

        $dto->firstname = $user->getFirstname();
        $dto->lastname = $user->getLastname();
        $dto->fullNames = sprintf('%s %s', $user->getFirstname(), $user->getLastname());
        $dto->email = $user->getEmail();

        return $dto;
    }

    /**
     * @param array $data
     * @param Media|null $media
     * @return Media
     * @throws MissingAttributeException
     */
    public function toEntity(array $data, Media $media = null): Media
    {
        if
        (
            empty($data['mediaType']) ||
            empty($data['fileName']) ||
            empty($data['fileType']) ||
            empty($data['position'])
        )
            throw new MissingAttributeException();

        if (is_null($media))
            $media = new Media();

        $media->setMediaType($data['mediaType']);
        $media->setFileName($data['fileName']);
        $media->setFileType($data['fileType']);
        $media->setPosition($data['position']);

        if( ! empty($data['users']) ){
            foreach ($data['users'] as $user) {
                $media->addUser($user);
            }
        }

        return $media;
    }
}
