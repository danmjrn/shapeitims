<?php

namespace App\Security\Validation;

use App\Entity\User;
use App\Entity\VerifiableEmailLink;

use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    /**
     * @var VerifyEmailHelperInterface
     */
    private VerifyEmailHelperInterface $verifyEmailHelper;

    const VERIFY_EMAIL_ROUTE_NAME = 'app_verify_email';

    public function __construct(VerifyEmailHelperInterface $verifyEmailHelper)
    {
        $this->verifyEmailHelper = $verifyEmailHelper;
    }

    /**
     * @param User $user
     * @return VerifiableEmailLink
     */
    public function generateVerifiableEmailLink(User $user): VerifiableEmailLink
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature
            (
                static::VERIFY_EMAIL_ROUTE_NAME,
                $user->getId(),
                $user->getEmail()
            );

        return new VerifiableEmailLink
            (
                $signatureComponents->getExpiresAt(),
                $signatureComponents->getSignedUrl()
            );
    }

    /**
     * @param User $user
     * @param string $url
     * @throws VerifyEmailExceptionInterface
     */
    public function validateEmailConfirmation(User &$user, string $url): void
    {
        $this->verifyEmailHelper->validateEmailConfirmation
            (
                $url,
                $user->getId(),
                $user->getEmail()
            );

        $user->setIsVerified(true);
    }
}
