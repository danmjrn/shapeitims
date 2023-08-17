<?php


namespace App\Event\Domain\Invitation;


use App\Entity\Invitation;

abstract class InvitationEvent
{
    /**
     * @var Invitation
     */
    private Invitation $invitation;

    /**
     * ApplicationFilesApproved constructor.
     * @param Invitation $invitation
     */
    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * @return Invitation
     */
    public function getInvitation(): Invitation
    {
        return $this->invitation;
    }
}