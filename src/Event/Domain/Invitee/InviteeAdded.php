<?php


namespace App\Event\Domain\Invitee;

use App\Entity\InternalUser;
use App\Entity\Invitee;

class InviteeAdded extends InviteeEvent
{
    /**
     * @var InternalUser
     */
    private InternalUser $internalUser;

    public function __construct( InternalUser $internalUser, Invitee $invitee )
    {
        $this->internalUser = $internalUser;

        parent::__construct($invitee);
    }

    /**
     * @return InternalUser
     */
    public function getInternalUser(): InternalUser
    {
        return $this->internalUser;
    }
}