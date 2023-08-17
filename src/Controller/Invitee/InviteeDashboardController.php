<?php

namespace App\Controller\Invitee;

use App\Entity\InternalUser;
use App\Security\AccessControl\Traits\AccessControlTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route( '/invitee' )]
class InviteeDashboardController extends AbstractController
{
    #[Route( path: '/', name: 'invitee_dashboard' )]
    public function home(): Response
    {
        return $this->render('public_directory/index.html.twig', [
            'controller_name' => 'Welcome Invitee',
        ]);
    }

    #[Route( path: '/view_invite', name: 'invitee_dashboard_edit' )]
    public function edit(): Response
    {
        $this->validateUserRoleAccess($this->getUser(), 'Super Admin', InternalUser::class);

        return $this->render('public_directory/index.html.twig', [
            'controller_name' => 'This is Edit Invitee',
        ]);
    }
}
