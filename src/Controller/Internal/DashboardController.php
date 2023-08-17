<?php

namespace App\Controller\Internal;

use App\Entity\InternalUser;
use App\Entity\Invitation;
use App\Security\AccessControl\Traits\AccessControlTrait;
use App\Service\Domain\InvitationService;
use App\Service\Domain\InviteeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route( '/internal' )]
class DashboardController extends AbstractController
{
    use AccessControlTrait;

    /**
     * @var InvitationService
     */
    private InvitationService $invitationService;

    /**
     * @var InviteeService
     */
    private InviteeService $inviteeService;

    public function __construct
        (
            InvitationService $invitationService,
            InviteeService $inviteeService,
        )
    {
        $this->invitationService = $invitationService;
        $this->inviteeService = $inviteeService;
    }

    /**
     * @return Response
     * @throws \App\Entity\Exception\UnknownUserTypeException
     */
    #[Route( path: '/', name: 'admin_dashboard' )]
    public function home(): Response
    {
        $invitees = $this->inviteeService->getAllInvitees();

        $invitations = $this->invitationService->getAllInvitations();

        $rsvped = $this->invitationService->getInvitationsByRsvp();

        $totalInvitees = count($invitees);
        $totalInvitations = count($invitations);
        $totalRsvped = count($rsvped);

        return $this->render('internal/dashboard.html.twig', [
            'totalInvitations' => $totalInvitations,
            'totalInvitees' => $totalInvitees,
            'totalRsvped' => $totalRsvped,
        ]);
    }

    #[Route( path: '/edit', name: 'admin_dashboard_edit' )]
    public function edit(): Response
    {
        $this->validateUserRoleAccess($this->getUser(), 'Super Admin', InternalUser::class);

        return $this->render('public_directory/home.html.twig', [
            'controller_name' => 'This is Edit Admin',
        ]);
    }


}
