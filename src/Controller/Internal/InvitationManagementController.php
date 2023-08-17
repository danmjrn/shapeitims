<?php

namespace App\Controller\Internal;

use App\Entity\InternalUser;
use App\Entity\InvitationGroup;
use App\Entity\Invitee;
use App\Entity\Permission;
use App\Form\InternalForm\ImportInviteesType;
use App\Form\InternalForm\InviteesType;
use App\Security\AccessControl\Traits\AccessControlTrait;
use App\Service\Domain\InvitationService;
use App\Service\Domain\InviteeService;
use App\Service\Utility\UploadUtility;
use Dompdf\Dompdf;
use Dompdf\Options;
use Mpdf\Mpdf;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route( '/internal/invitation-management' )]
class InvitationManagementController extends AbstractController
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

    /**
     * @var UploadUtility
     */
    private UploadUtility $uploadUtility;

    public function __construct
        (
            InvitationService $invitationService,
            InviteeService $inviteeService,
            UploadUtility $uploadUtility
        )
    {
        $this->invitationService = $invitationService;
        $this->inviteeService = $inviteeService;
        $this->uploadUtility = $uploadUtility;
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route( path: '/add-invitees', name: 'add_invitees', methods: ['GET'] )]
    public function addInvitees( Request $request ): Response
    {
        /** @var InternalUser $user */
        $user = $this->getUser();

        $this->validateUserPermissionsAccess(
            $user,
            [
                Permission::PERMISSION_CREATE_USERS,
                Permission::PERMISSION_CREATE_INVITATIONS,
            ]
        );

        return $this->render
        (
            'internal/invitation_management/add.html.twig'
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/create-invitation', name: 'create_invitation', methods: ['GET'])]
    public function createInvitation(Request $request): JsonResponse
    {
        /** @var InternalUser $user */
        $user = $this->getUser();

        $this->validateUserPermissionsAccess(
            $user,
            [
                Permission::PERMISSION_CREATE_USERS,
                Permission::PERMISSION_CREATE_INVITATIONS,
            ]
        );

        try {
            $allData = [];
            $requestData = $request->query->all();

            foreach ( $requestData as $persons)
                foreach ($persons as $person) {
                    $dataPerson = [];

                    foreach ($person as $personDetail) {
                        $dataPerson['username'] = str_replace(' ', '', strtolower($person[0]['val'].'.'.$person[1]['val']));

                        $dataPerson['author'] = $user;

                        if (str_contains($personDetail['name'], 'firstName'))
                            $dataPerson['firstname'] = $personDetail['val'];

                        if (str_contains($personDetail['name'], 'lastName'))
                            $dataPerson['lastname'] = $personDetail['val'];

                        if (str_contains($personDetail['name'], 'title'))
                            $dataPerson['title'] = $personDetail['val'];

                        if (str_contains($personDetail['name'], 'invitationType'))
                            $dataPerson['invitationType'] = (int)$personDetail['val'];

                        if (str_contains($personDetail['name'], 'phoneNumber') )
                            $dataPerson['phoneNumber'] = $personDetail['val'];

                        if (str_contains($personDetail['name'], 'email') )
                            $dataPerson['email'] = $personDetail['val'];

                        if (str_contains($personDetail['name'], 'invFrom') )
                            $dataPerson['invFrom'] = $personDetail['val'];

                        if (str_contains($personDetail['name'], 'inviteeLang') )
                            $dataPerson['inviteeLang'] = $personDetail['val'];

                    }
                    $allData[] = $dataPerson;
                }

        try {
            $this->inviteeService->createInvitee($allData);
        }catch (\Exception $exception){
            return new JsonResponse([
                'message'=> $exception->getTrace(),
                'success' => false
            ]);
        }

            $response = [
                'message' => 'The invitee/s was/were added successfully',
                'success' => true,
            ];

            return new JsonResponse($response);
        }
        catch (\Exception $exception) {
            $response = [
                'message' => 'There was an error with your request',
                'success' => false,
            ];

            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Mpdf\MpdfException
     */
    #[Route( path: '/export-invitations/', name: 'export_invitations', methods: ['POST|GET'] )]
    public function exportInvitations( Request $request ): Response {
        /** @var InternalUser $user */
        $user = $this->getUser();

        $this->validateUserPermissionsAccess(
            $user,
            [
                Permission::PERMISSION_CREATE_USERS,
                Permission::PERMISSION_CREATE_INVITATIONS,
            ]
        );

        $invitations = $this->invitationService->getInvitationsByInvitationFrom(Invitee::INVITEE_FROM_BOTH, true);

        $content = $this->render('internal/invitation_management/export.html.twig', ['invitations' => $invitations])->getContent();

//        $options = new Options();
//
//        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf();

//        $dompdf->setOptions($options);

        $dompdf->loadHtml($content);

        $dompdf->setPaper('a3', 'landscape');

        $dompdf->render();

        $date = new \DateTime('now');

        $dateToShow = $date->format('Y/m/d H:i');

        $dompdf->stream(
            "Invitations-Links-". $dateToShow,
            [
                'Attachment' => false
            ]
        );

//        dd($invitations);
        return  $this->redirectToRoute('invitation_management');
    }

    #[Route( path: '/edit-invitees', name: 'edit_invitees' )]
    public function editInvitees(): Response
    {
        $this->validateUserRoleAccess($this->getUser(), 'Super Admin', InternalUser::class);

        return $this->render('public_directory/index.html.twig', [
            'controller_name' => 'This is Edit Admin',
        ]);
    }

    /**
     * @return Response
     * @throws \App\Entity\Exception\UnknownUserTypeException
     */
    #[Route( path: '', name: 'invitation_management' )]
    public function home(): Response
    {
        $invitees = $this->inviteeService->getAllInvitees();

        $invitations = $this->invitationService->getAllInvitations();

        return $this->render('internal/invitation_management/home.html.twig', [
            'invitees' => $invitees,
            'invitations' => $invitations,
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \App\Entity\Exception\EntityNotCreatedException
     * @throws \App\Entity\Exception\InvitationNotCreatedException
     * @throws \App\Entity\Exception\UnknownUserTypeException
     * @throws \App\Service\Domain\Exception\InviteeWithDuplicateUsernameException
     * @throws \App\Service\Domain\Exception\MissingAttributeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    #[Route( path: '/import-invitees', name: 'import_invitees', methods: ['POST|GET'] )]
    public function importInvitees( Request $request ): Response
    {
        /** @var InternalUser $user */
        $user = $this->getUser();

        $this->validateUserPermissionsAccess(
            $user,
            [
                Permission::PERMISSION_CREATE_USERS,
                Permission::PERMISSION_CREATE_INVITATIONS,
            ]
        );

        $importInviteesForm = $this->createForm(ImportInviteesType::class);

        $importInviteesForm->handleRequest($request);

        if($importInviteesForm->isSubmitted() && $importInviteesForm->isValid()){
            $inviteeFiles = $request->files->get('import_invitees')['inviteeImportForm'];

            if($this->uploadUtility->uploadFile($inviteeFiles) !== null)
                $this->uploadUtility->importInvitees($user);
        }

        return $this->render
        (
            'internal/invitation_management/import.html.twig', [
                'importInviteesForm' => $importInviteesForm->createView(),
            ]
        );
    }

    #[Route( path: '/remove-invitees', name: 'remove_invitees' )]
    public function removeInvitees(): Response
    {
        $this->validateUserRoleAccess($this->getUser(), 'Super Admin', InternalUser::class);

        return $this->render('public_directory/index.html.twig', [
            'controller_name' => 'This is Edit Admin',
        ]);
    }

    #[Route( path: '/view-invitees', name: 'view_invitees' )]
    public function viewInvitees(): Response
    {
        $this->validateUserRoleAccess($this->getUser(), 'Super Admin', InternalUser::class);

        return $this->render('public_directory/index.html.twig', [
            'controller_name' => 'This is Edit Admin',
        ]);
    }
}
