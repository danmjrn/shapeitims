<?php


namespace App\Event\Domain\Invitee;


use App\Entity\Invitee;

abstract class InviteeEvent
{
    /**
     * @var Invitee
     */
    private Invitee $invitee;

    /**
     * ApplicationFilesApproved constructor.
     * @param Invitee $invitee
     */
    public function __construct(Invitee $invitee)
    {
        $this->invitee = $invitee;
    }

    /**
     * @return Invitee
     */
    public function getInvitee(): Invitee
    {
        return $this->invitee;
    }
}