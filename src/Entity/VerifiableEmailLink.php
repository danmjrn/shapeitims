<?php


namespace App\Entity;


class VerifiableEmailLink
{
    /**
     * @var \DateTimeInterface
     */
    private \DateTimeInterface $expiresAt;

    /**
     * @var string
     */
    private string $signedUrl;

    /**
     * VerifiableEmailLink constructor.
     * @param \DateTimeInterface $expiresAt
     * @param string $signedUrl
     */
    public function __construct(\DateTimeInterface $expiresAt, string $signedUrl)
    {
        $this->expiresAt = $expiresAt;
        $this->signedUrl = $signedUrl;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }

    /**
     * @return string
     */
    public function getSignedUrl(): string
    {
        return $this->signedUrl;
    }
}