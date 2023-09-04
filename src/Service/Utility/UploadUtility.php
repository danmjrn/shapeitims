<?php
//
//namespace App\Service\Utility;
//
//use App\Entity\Invitee;
//use App\Entity\User;
//use App\Service\Domain\Entity\UserDataTransferObject;
//use App\Service\Domain\Exception\MissingAttributeException;
//use App\Service\Domain\InviteeService;
//use App\Service\Service;
//use Doctrine\ORM\EntityManagerInterface;
//use Psr\EventDispatcher\EventDispatcherInterface;
//use Psr\Log\LoggerInterface;
//use Symfony\Component\HttpFoundation\File\UploadedFile;
//use Symfony\Component\HttpFoundation\RequestStack;
//use Symfony\Component\Serializer\Encoder\CsvEncoder;
//use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
//use Symfony\Component\Serializer\Serializer;
//
//class UploadUtility extends Service
//{
//
//    /**
//     * @var InviteeService
//     */
//    private InviteeService $inviteeService;
//
//    /**
//     * @var string
//     */
//    private string $uploadsDir;
//
//    /**
//     * @var UserDataTransferObject
//     */
//    private UserDataTransferObject $userDataTransferObject;
//    private const IMPORTED_INVITEES_DIR = '/imports/invitees/';
//    private const EXPORTED_INVITEES_DIR = '/exports/invitees/';
//
//    /**
//     * @param array $groupedAllData
//     * @param array $allData
//     * @param array $invitee
//     * @return void
//     */
//    private function assignToGroup( array &$groupedAllData, array $allData, array $invitee )
//    {
//        $newGroup = [];
//            foreach ($allData as $person){
//                    if (
//                        $person['userGroup'] === $invitee['userGroup'] &&
//                        $person['invitationType'] === $invitee['invitationType']
//                    ) {
//                        if (!$this->personExistsInGroup($newGroup, $person))
//                            $newGroup[] = $person;
//
//                        if (!$this->personExistsInGroup($newGroup, $invitee))
//                            $newGroup[] = $invitee;
//                    }
//            }
//
//        if ( ! $this->groupExists($groupedAllData, $newGroup) )
//            $groupedAllData[] = $newGroup;
//    }
//
//    /**
//     * @param array $group
//     * @param array $person
//     * @return bool
//     */
//    private function personExistsInGroup( array $group, array $person ): bool
//    {
//        foreach ( $group as $invitee )
//            if ( $person === $invitee )
//                return true;
//
//        return false;
//    }
//
//    /**
//     * @param array $allGroups
//     * @param array $group
//     * @return bool
//     */
//    private function groupExists( array $allGroups, array $group ): bool
//    {
//        foreach ( $allGroups as $allGroup )
//            foreach ( $allGroup as $person )
//                foreach ( $group as $invitee )
//                    if ( $person === $invitee )
//                        return true;
//
//        return false;
//    }
//
//
//    public function __construct
//        (
//            EntityManagerInterface $entityManager,
//            EventDispatcherInterface $eventDispatcher,
//            InviteeService $inviteeService,
//            LoggerInterface $logger,
//            RequestStack $session,
//            $uploadsDir,
//            UserDataTransferObject $userDataTransferObject
//        )
//    {
//        $this->inviteeService = $inviteeService;
//        $this->uploadsDir = $uploadsDir;
//        $this->userDataTransferObject = $userDataTransferObject;
//
//        parent::__construct($entityManager, $eventDispatcher, $logger, $session);
//    }
//
//    /**
//     * @param UploadedFile $uploadedFile
//     * @return UploadedFile
//     */
//    public function uploadFile(UploadedFile $uploadedFile): UploadedFile
//    {
//        $uploadDir = $this->uploadsDir.static::IMPORTED_INVITEES_DIR;
////        $date = new \DateTime('now');
////        $dateString = $date->format('Y-m-d');
//        $fileName = 'invitees' . '.' . $uploadedFile->guessExtension();
//
//        $uploadedFile->move(
//            $uploadDir,
//            $fileName
//        );
//
//        return $uploadedFile;
//    }
//
//    /**
//     * @param User $user
//     * @return void
//     * @throws MissingAttributeException
//     * @throws \App\Entity\Exception\EntityNotCreatedException
//     * @throws \App\Entity\Exception\InvitationNotCreatedException
//     * @throws \App\Entity\Exception\UnknownUserTypeException
//     * @throws \Doctrine\DBAL\ConnectionException
//     * @throws \Doctrine\DBAL\Exception
//     */
//    public function importInvitees(User $user): void
//    {
//        $uploadDir = $this->uploadsDir.static::IMPORTED_INVITEES_DIR;
//        $fileName = 'invitees.txt';
//        $inputInvitees = $uploadDir . $fileName;
//
//        $decoder = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
//
//        $xlRows = $decoder->decode(file_get_contents($inputInvitees), 'csv');
//
//        $allData = [];
//
//        foreach ($xlRows as $row) {
//            $dataPerson = [];
//
//            foreach ($row as $invitee) {
//                $valueHolder = $invitee;
//                $delimiter = ";";
//
//                $delimiterPos = strpos($valueHolder, $delimiter);
//                $firstname = substr($valueHolder, 0, $delimiterPos);
//                $valueHolder = substr($valueHolder, $delimiterPos + 1);
//
//                $delimiterPos = strpos($valueHolder, $delimiter);
//                $lastname = substr($valueHolder, 0, $delimiterPos);
//                $valueHolder = substr($valueHolder, $delimiterPos + 1);
//
//                $delimiterPos = strpos($valueHolder, $delimiter);
//                $title = substr($valueHolder, 0, $delimiterPos);
//                $valueHolder = substr($valueHolder, $delimiterPos + 1);
//
//                $delimiterPos = strpos($valueHolder, $delimiter);
//                $invitationGroup = substr($valueHolder, 0, $delimiterPos);
//                $valueHolder = substr($valueHolder, $delimiterPos + 1);
//
//                $delimiterPos = strpos($valueHolder, $delimiter);
//                $inviteeFrom = substr($valueHolder, 0, $delimiterPos);
//                $valueHolder = substr($valueHolder, $delimiterPos + 1);
//
//                $inviteeLang = $valueHolder;
//
//                $dataPerson['username'] = str_replace(' ', '', strtolower($firstname.'.'.$lastname));
//
//                $dataPerson['author'] = $user;
//
//                $dataPerson['firstname'] = $firstname;
//
//                $dataPerson['lastname'] = $lastname;
//
//                $dataPerson['title'] = $title;
//
//                $dataPerson['inviteeLang'] = strtolower($inviteeLang);
//
//                if (str_contains(strtolower($invitationGroup), 's')) {
//                    $dataPerson['invitationType'] = 1;
//                    $dataPerson['userGroup'] = null;
//                }
//
//                if (str_contains(strtolower($invitationGroup), 'c')){
//                    $dataPerson['invitationType'] = 2;
//                    $dataPerson['userGroup'] = substr($invitationGroup, strpos($invitationGroup, 'e')+1);
//                }
//
//                if (str_contains(strtolower($invitationGroup), 'm')){
//                    $dataPerson['invitationType'] = 4;
//                    $dataPerson['userGroup'] = substr($invitationGroup, strpos($invitationGroup, 'd')+1);
//                }
//
//
//                if (str_contains(strtolower($invitationGroup), 'f')) {
//                    $dataPerson['invitationType'] = 3;
//                    $dataPerson['userGroup'] = substr($invitationGroup, strpos($invitationGroup, 'm')+1);
//                }
//
//                $dataPerson['phoneNumber'] = null;
//
//                $dataPerson['email'] = null;
//
//                switch (strtolower($inviteeFrom)){
//                    case 'g':
//                    {
//                        $dataPerson['invFrom'] = Invitee::INVITEE_FROM_GROOM;
//                        break;
//                    }
//                    default: {
//                        $dataPerson['invFrom'] = Invitee::INVITEE_FROM_BRIDE;
//                        break;
//                    }
//                }
//            }
//            $allData[] = $dataPerson;
//        }
//
//        $groupedAllData = [];
//        foreach ($allData as $key => $datum){
//            $newGroup = [];
//            if($datum['invitationType'] !== 1)
//                $this->assignToGroup($groupedAllData, $allData, $datum);
//            else
//                $newGroup[] = $datum;
//
//            if (! empty( $newGroup ) )
//                $groupedAllData[] = $newGroup;
//        }
//
//        foreach ( $groupedAllData as $group )
//            $this->inviteeService->createInviteeViaImport($group);
//    }
//}


namespace App\Service\Utility;

use App\Entity\Exception\EntityNotCreatedException;
use App\Entity\Exception\InvitationNotCreatedException;
use App\Entity\Exception\UnknownUserTypeException;
use App\Entity\Invitation;
use App\Entity\Invitee;
use App\Entity\User;
use App\Service\Domain\Entity\UserDataTransferObject;
use App\Service\Domain\Exception\InvitationDetailEmptyException;
use App\Service\Domain\Exception\MissingAttributeException;
use App\Service\Domain\InviteeService;
use App\Service\Service;
use App\Service\Utility\Exception\InvalidUsernameException;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UploadUtility extends Service
{
    private InviteeService $inviteeService;
    private string $uploadsDir;
    private UserDataTransferObject $userDataTransferObject;
    private const IMPORTED_INVITEES_DIR = '/imports/invitees/';
    private const EXPORTED_INVITEES_DIR = '/exports/invitees/';

    private Serializer $serializer;

    public function __construct(
        EntityManagerInterface   $entityManager,
        EventDispatcherInterface $eventDispatcher,
        InviteeService           $inviteeService,
        LoggerInterface          $logger,
        RequestStack             $session,
                                 $uploadsDir,
        UserDataTransferObject   $userDataTransferObject
    )
    {
        $this->inviteeService = $inviteeService;
        $this->uploadsDir = $uploadsDir;
        $this->userDataTransferObject = $userDataTransferObject;
        $this->serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);

        parent::__construct($entityManager, $eventDispatcher, $logger, $session);
    }

    private function assignToGroup(array &$groupedAllData, array $allData, array $invitee): void
    {
        $newGroup = [];

        foreach ($allData as $person) {
            if (
                $person['userGroup'] === $invitee['userGroup'] &&
                $person['invitationType'] === $invitee['invitationType']
            ) {
                if (!$this->personExistsInGroup($newGroup, $person)) {
                    $newGroup[] = $person;
                }

                if (!$this->personExistsInGroup($newGroup, $invitee)) {
                    $newGroup[] = $invitee;
                }
            }
        }

        if (!$this->groupExists($groupedAllData, $newGroup)) {
            $groupedAllData[] = $newGroup;
        }
    }

    private function personExistsInGroup(array $group, array $person): bool
    {
        return in_array($person, $group, true);
    }

    private function groupExists(array $allGroups, array $group): bool
    {
        foreach ($allGroups as $allGroup) {
            if ($this->groupsAreEqual($allGroup, $group)) {
                return true;
            }
        }
        return false;
    }

    private function groupsAreEqual(array $group1, array $group2): bool
    {
        sort($group1);
        sort($group2);
        return $group1 === $group2;
    }

    public function uploadFile(UploadedFile $uploadedFile): UploadedFile
    {
        $uploadDir = $this->uploadsDir . static::IMPORTED_INVITEES_DIR;
        $fileName = 'invitees' . '.' . $uploadedFile->guessExtension();

        $uploadedFile->move(
            $uploadDir,
            $fileName
        );

        return $uploadedFile;
    }

    private function processInvitationGroup(string $invitationGroup): array
    {
        $result = [];

        if (str_contains(strtolower($invitationGroup), 'sing')) {
            $result['invitationType'] = 1;
            $result['userGroup'] = null;
        } elseif (str_contains(strtolower($invitationGroup), 'coup')) {
            $result['invitationType'] = 2;
            $result['userGroup'] = substr($invitationGroup, strpos($invitationGroup, 'e') + 1);
        } elseif (str_contains(strtolower($invitationGroup), 'marr')) {
            $result['invitationType'] = 4;
            $result['userGroup'] = substr($invitationGroup, strpos($invitationGroup, 'd') + 1);
        } elseif (str_contains(strtolower($invitationGroup), 'fa')) {
            $result['invitationType'] = 3;
            $result['userGroup'] = substr($invitationGroup, strpos($invitationGroup, 'y') + 1);
        }

        return $result;
    }

    private function determineInviteeFrom(string $inviteeFrom): string
    {
        return ((strtolower($inviteeFrom) === 'groom')
            ? Invitee::INVITEE_FROM_GROOM
            : (strtolower($inviteeFrom) === 'bride'))
                ? Invitee::INVITEE_FROM_BRIDE
                : Invitee::INVITEE_FROM_BOTH
        ;
    }

    /**
     * @throws ConnectionException
     * @throws InvitationNotCreatedException
     * @throws UnknownUserTypeException
     * @throws EntityNotCreatedException
     * @throws MissingAttributeException
     * @throws Exception
     * @throws InvalidUsernameException
     * @throws NonUniqueResultException
     */
    public function importInvitees(User $user): void
    {
        $uploadDir = $this->uploadsDir . static::IMPORTED_INVITEES_DIR;
        $fileName = 'invitees.txt';
        $inputInvitees = $uploadDir . $fileName;

        $xlRows = $this->serializer->decode(file_get_contents($inputInvitees), 'csv');

        $allData = [];

        foreach ($xlRows as $row) {
            foreach ($row as $invitee) {
                $dataPerson = $this->extractPersonData($invitee, $user);
                $allData[] = $dataPerson;
            }
        }

        $groupedAllData = [];
        foreach ($allData as $datum) {
            if ($datum['invitationType'] !== 1) {
                $this->assignToGroup($groupedAllData, $allData, $datum);
            } else {
                $groupedAllData[] = [$datum];
            }
        }

        foreach ($groupedAllData as $group) {
            try {
                $this->inviteeService->createInviteeViaImport($group);
            } catch (
                EntityNotCreatedException|
                InvitationNotCreatedException|
                UnknownUserTypeException|
                InvitationDetailEmptyException|
                MissingAttributeException|
                ConnectionException|
                Exception|
                NonUniqueResultException $e)
            {
//                $this->logger->error($e->getMessage());
                dd($e->getMessage());
            }
        }
    }

    /**
     * @param string $invitee
     * @param User|null $user
     * @return array
     * @throws InvalidUsernameException
     * @throws NonUniqueResultException
     */
    private function extractPersonData(string $invitee, User $user = null): array
    {
        list($firstname, $lastname, $title, $invitationGroup, $inviteeFrom, $invitationDetailType) = explode(";", $invitee);

        if ($firstname != '_' && $lastname != '_')
            $username = str_replace(
                ' ',
                '',
                strtolower($firstname . '.' . $lastname . $this->extractNumbersFromString(
                    $invitationGroup, false)
                )
            );
        elseif ($firstname != '_' && $lastname == '_')
            $username = str_replace(
                ' ',
                '',
                strtolower($firstname . $this->extractNumbersFromString(
                        $invitationGroup)
                )
            );
        elseif ($firstname == '_' && $lastname != '_')
            $username = str_replace(
                ' ',
                '',
                strtolower($lastname . $this->extractNumbersFromString(
                        $invitationGroup)
                )
            );
        else
            throw new InvalidUsernameException("$firstname & $lastname missing.");

        $dataPerson = [
            'username' => $username,
            'author' => $user,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'title' => $title,
            'invitationDetailType' => strtolower($invitationDetailType),
            'phoneNumber' => null,
            'email' => null,
            'inviteeLang' => 'en',
        ];

        $invitationData = $this->processInvitationGroup($invitationGroup);
        $dataPerson['invitationType'] = $invitationData['invitationType'];
        $dataPerson['userGroup'] = $invitationData['userGroup'];

        $dataPerson['invFrom'] = $this->determineInviteeFrom($inviteeFrom);

        return $dataPerson;
    }

    /**
     * @param string $stringWithNumbers
     * @param bool $withString
     * @return string
     */
    function extractNumbersFromString(string $stringWithNumbers, bool $withString = true): string {
        $pattern = '/(\d+)$/';

        if (preg_match($pattern, $stringWithNumbers, $matches)) {
            return $withString ? substr($stringWithNumbers, 0, 3).$matches[1] : $matches[1];
        } else {
            return '';
        }
    }
}
