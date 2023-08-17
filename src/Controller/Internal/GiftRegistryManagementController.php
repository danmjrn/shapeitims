<?php

namespace App\Controller\Internal;

use App\Entity\InternalUser;
use App\Security\AccessControl\Traits\AccessControlTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route( '/internal/gift-registry-management' )]
class GiftRegistryManagementController extends AbstractController
{
    use AccessControlTrait;

    #[Route( path: '/add-gift-registry', name: 'add_gift_registry' )]
    public function addInvitees(): Response
    {
        return $this->render('public_directory/index.html.twig', [
            'controller_name' => 'Welcome Admin',
        ]);
    }

    #[Route( path: '/edit-gift-registry', name: 'edit_gift_registry' )]
    public function editInvitees(): Response
    {
        $this->validateUserRoleAccess($this->getUser(), 'Super Admin', InternalUser::class);

        return $this->render('public_directory/index.html.twig', [
            'controller_name' => 'This is Edit Admin',
        ]);
    }

    #[Route( path: '', name: 'gift_registry_management' )]
    public function home(): Response
    {
        return $this->render('internal/coming_soon.html.twig', [
            'feature' => 'Gift Registry Management',
        ]);
    }

    #[Route( path: '/remove-gift-registry', name: 'remove_gift_registry' )]
    public function removeInvitees(): Response
    {
        $this->validateUserRoleAccess($this->getUser(), 'Super Admin', InternalUser::class);

        return $this->render('public_directory/index.html.twig', [
            'controller_name' => 'This is Edit Admin',
        ]);
    }

    #[Route( path: '/view-gift-registry', name: 'view_gift_registry' )]
    public function viewInvitees(): Response
    {
        $this->validateUserRoleAccess($this->getUser(), 'Super Admin', InternalUser::class);

        return $this->render('public_directory/index.html.twig', [
            'controller_name' => 'This is Edit Admin',
        ]);
    }
}
