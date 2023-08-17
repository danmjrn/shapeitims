<?php

namespace App\Email;

use App\Entity\InternalUser;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

class InternalTemplatedEmail extends TemplatedEmail
{
    /**
     * @var InternalUser
     */
    private InternalUser $internalUser;

    public function __construct(InternalUser $internalUser, Headers $headers = null, AbstractPart $body = null)
    {
        $this->internalUser = $internalUser;

        parent::__construct($headers, $body);
    }

    /**
     * @return InternalUser
     */
    public function getAccount(): InternalUser
    {
        return $this->internalUser;
    }
}
