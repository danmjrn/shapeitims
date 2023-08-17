<?php


namespace App\Event\Domain\User;


use App\Entity\User;
use App\Entity\VerifiableEmailLink;

final class UserConfirmationEmailResent
{
    /**
     * @var User
     */
    private User $user;

    /**
     * @var VerifiableEmailLink
     */
    private VerifiableEmailLink $verifiableEmailLink;

    /**
     * SendUserConfirmationEmail constructor.
     * @param User $user
     * @param VerifiableEmailLink $verifiableEmailLink
     */
    public function __construct(User $user, VerifiableEmailLink $verifiableEmailLink)
    {
        $this->user = $user;
        $this->verifiableEmailLink = $verifiableEmailLink;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return VerifiableEmailLink
     */
    public function getVerifiableEmailLink(): VerifiableEmailLink
    {
        return $this->verifiableEmailLink;
    }
}