<?php

namespace App\Controller\Public;

use App\Entity\InvitationGroup;
use App\Service\Domain\Exception\InvitationTimesOpenedNotUpdatedException;
use App\Service\Domain\InvitationService;
use App\Service\Domain\InviteeService;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublicDirectoryController extends AbstractController
{
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
            InviteeService $inviteeService
        )
    {
        $this->invitationService = $invitationService;
        $this->inviteeService = $inviteeService;
    }


    #[Route('/', name: 'app_public_directory')]
    public function home(): Response
    {
        return $this->render('public_directory/coming_soon.html.twig', [
            'eventDate' => '01 April 2023 12:30:00',
        ]);
    }

    /**
     * @param string $uuid
     * @return Response
     * @throws ConnectionException
     * @throws Exception
     * @throws InvitationTimesOpenedNotUpdatedException
     */
    #[Route('/invitation-letter/{uuid<[a-zA-Z0-9-]+>}', name: 'invitation_letter', methods: ['GET'])]
    public function invitationView(string $uuid): Response
    {
        $invitation = $this->invitationService->getInvitationByUuid($uuid, true);

        $rsvpValueFromEntity = $invitation['rsvp'];

        $addressedTo = $invitation['addressedTo'];

        if (str_contains($addressedTo, 'Couple'))
            $addressedTo = substr($addressedTo, 0, strpos($addressedTo, '&', strpos($addressedTo, '&') + 1) -1);

        $this->invitationService->updateInvitationTimesOpened($this->invitationService->getInvitationByUuid($uuid, false));

        return $this->render('public_directory/invitation_letter/view_invitation.html.twig', [
            'addressedTo' => $addressedTo,
            'invitationLang' => $invitation['invitationLang'],
            'invitationUuid' => $uuid,
            'rsvpValueFromEntity' => $rsvpValueFromEntity,
        ]);
    }

    /**
     * @param Request $request
     * @param $uuid
     * @return JsonResponse
     */
    #[Route('/rsvp-invitation/{uuid<[a-zA-Z0-9-]+>}', name: 'rsvp_invitation', methods: ['GET'])]
    public function rsvpInvitation(Request $request, $uuid): JsonResponse
    {
        try {
            $rsvpValue = '';
            if($request->query->has('rsvpValue'))
                $rsvpValue = $request->query->get('rsvpValue');

            $invitation = $this->invitationService->updateInvitationRsvp(
                $rsvpValue,
                $this->invitationService->getInvitationByUuid($uuid, false)
            );

            $response = [
                'invitation' => $invitation,
                'success' => true,
            ];

            return new JsonResponse($response);
        }
        catch (\Exception $exception) {
            $response = [
                'message' => 'There was an error rsvping your request',
                'success' => false,
            ];

            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }
    }
}
