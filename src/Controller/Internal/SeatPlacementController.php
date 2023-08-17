<?php

namespace App\Controller\Internal;

use App\Entity\InternalUser;
use App\Security\AccessControl\Traits\AccessControlTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route( '/internal/seat-placement' )]
class SeatPlacementController extends AbstractController
{
    use AccessControlTrait;

    #[Route( path: '/add-seats', name: 'add_seats' )]
    public function addInvitees(): Response
    {
        return $this->render('public_directory/index.html.twig', [
            'controller_name' => 'Welcome Admin',
        ]);
    }

    #[Route( path: '/edit-seats', name: 'edit_seats' )]
    public function editInvitees(): Response
    {
        $this->validateUserRoleAccess($this->getUser(), 'Super Admin', InternalUser::class);

        return $this->render('public_directory/index.html.twig', [
            'controller_name' => 'This is Edit Admin',
        ]);
    }

    #[Route( path: '', name: 'seat_placement' )]
    public function home(): Response
    {
        return $this->render('internal/coming_soon.html.twig', [
            'feature' => 'Seat Placement',
        ]);
    }

    #[Route( path: '/remove-seats', name: 'remove_seats' )]
    public function removeInvitees(): Response
    {
        $this->validateUserRoleAccess($this->getUser(), 'Super Admin', InternalUser::class);

        return $this->render('public_directory/index.html.twig', [
            'controller_name' => 'This is Edit Admin',
        ]);
    }

    #[Route( path: '/view-seats', name: 'view_seats' )]
    public function viewInvitees(): Response
    {
        $this->validateUserRoleAccess($this->getUser(), 'Super Admin', InternalUser::class);

        return $this->render('public_directory/index.html.twig', [
            'controller_name' => 'This is Edit Admin',
        ]);
    }
}
