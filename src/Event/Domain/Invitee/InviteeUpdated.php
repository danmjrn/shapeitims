<?php


namespace App\Event\Domain\Invitee;

use App\Entity\InternalUser;
use App\Entity\Invitee;

class InviteeUpdated extends InviteeEvent
{
    /**
     * @var InternalUser|null
     */
    private ?InternalUser $internalUser;

    public function __construct( InternalUser $internalUser, Invitee $invitee )
    {
        $this->internalUser = $internalUser;

        parent::__construct($invitee);
    }

    /**
     * @return InternalUser|null
     */
    public function getInternalUser(): ?InternalUser
    {
        return $this->internalUser;
    }
}