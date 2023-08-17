<?php


namespace App\Event\Domain\Invitation;


use App\Entity\Invitation;
use App\Entity\InvitationGroup;

class InvitationAdded extends InvitationEvent
{
    /**
     * @var array
     */
    private array $invitationGroups;

    public function __construct( array $invitationGroups, Invitation $invitation )
    {
        $this->invitationGroups = $invitationGroups;
        parent::__construct($invitation);
    }

    /**
     * @return array
     */
    public function getInvitationGroups(): array
    {
        return $this->invitationGroups;
    }
}