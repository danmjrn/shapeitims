<?php


namespace App\Event\Domain\Invitation;


use App\Entity\Invitation;
use App\Entity\InvitationGroup;

class InvitationRsvped extends InvitationEvent
{
    public function __construct( Invitation $invitation )
    {
        parent::__construct($invitation);
    }
}