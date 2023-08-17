<?php


namespace App\Service\Domain;

use App\Entity\Exception\EntityNotCreatedException;

use App\Entity\Exception\UnknownApplicationUpdateTypeException;

use App\Repository\MediaRepository;

use App\Service\Communication\ApplicationMailer;

use App\Service\Domain\Entity\MediaDataTransferObject;

use App\Service\Domain\Exception\MissingAttributeException;

use App\Service\Service;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

use Exception;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class InvitationGroupService extends Service
{


    /**
     * @param Instance $instance
     * @param Collection $linkedApisToKeep
     */
    private function determineLinkedApisToKeep(Instance &$instance, Collection $linkedApisToKeep): void
    {
        if (! $linkedApisToKeep->isEmpty())
            $this->removeLinkedApis
                (
                    $instance,
                    $this->getLinkedApisToDelete
                        (
                            new ArrayCollection($instance->getLinkedApis()->getValues()),
                            $linkedApisToKeep
                        )
                );
        else
            $this->removeLinkedApis($instance, $instance->getLinkedApis());
    }

    /**
     * @param Collection $linkedApis
     * @param Collection $linkedApisToKeep
     * @return Collection
     */
    private function getLinkedApisToDelete(Collection $linkedApis, Collection $linkedApisToKeep): Collection
    {
        return $linkedApis->filter
            (
                function (LinkedApi $linkedApi) use ($linkedApisToKeep) {
                    return ! $linkedApisToKeep->contains($linkedApi);
                }
            );
    }

    /**
     * @param Instance $instance
     * @param Collection $linkedApis
     */
    private function removeLinkedApis(Instance &$instance, Collection $linkedApis): void
    {
        /** @var LinkedApi $linkedApi */
        foreach ($linkedApis as $linkedApi) {
            $instance->removeLinkedApi($linkedApi);

            $this->removeEntity($linkedApi);
        }
    }

    /**
     * @param Instance $instance
     * @param array $data
     */
    private function setLinkedApis(Instance &$instance, array $data): void
    {
        $linkedApisToKeep = new ArrayCollection();

        if (! empty($data['linkedApiLinks']) && ! empty($data['linkedApiNames'])) {
            $numberOfLinkedApis = count($data['linkedApiNames']);

            for ($i = 0; $i < $numberOfLinkedApis; $i++) {
                $link = $data['linkedApiLinks'][$i];
                $name = $data['linkedApiNames'][$i];

                try {
                    $linkedApi = $this->linkedApiRepository->findByInstanceUuidAndApiName($instance->getUuid(), $name);

                    $linkedApi = $this->linkedApiDataTransferObject->toEntity
                        (
                            [
                                'link' => $link,
                                'name' => $name,
                                'instance' => $instance,
                            ],
                            $linkedApi
                        );

                    $linkedApisToKeep->add($linkedApi);

                    $this->persistEntity($linkedApi);

                    $instance->addLinkedApi($linkedApi);
                }
                catch (Exception $exception) {
                    continue;
                }
            }
        }

        $this->determineLinkedApisToKeep($instance, $linkedApisToKeep);
    }

    /**
     * @param Instance $instance
     * @param array $data
     * @throws MissingAttributeException
     */
    private function uploadAsset(Instance &$instance, array $data): void
    {
        $asset = $this->applicationDataTransferObject->toAsset
            (
                [
                    'repositoryLink' => $data['repositoryLink'],
                    'instance' => $instance,
                ]
            );

        $instance->addAsset($asset);
    }

    /**
     * @param array $data
     * @throws MissingAttributeException
     */
    private function validateApplicationInstance(array $data): void
    {
        if
            (
                empty($data['name']) ||
                empty($data['repositoryLink']) ||
                empty($data['account']) ||
                ! $data['account'] instanceof Provider ||
                empty($data['author']) ||
                ! $data['author'] instanceof Partner ||
                empty($data['instance']) ||
                ! $data['instance'] instanceof Instance
            )
            throw new MissingAttributeException();
    }

    public function __construct
        (
            EntityManagerInterface $entityManager,
            EventDispatcherInterface $eventDispatcher,
            LoggerInterface $logger,
            ApplicationMailer $mailer,
            MediaDataTransferObject $mediaDataTransferObject,
            MediaRepository $mediaRepository,
            SessionInterface $session,
        )
    {
        $this->mailer = $mailer;

        parent::__construct
            ();
    }

    /**
     * @param array $data
     * @return ApplicationDataTransferObject
     * @throws ApplicationWithDuplicateNameException
     * @throws ConnectionException
     * @throws EntityNotCreatedException
     * @throws MissingAttributeException
     */
    public function createApplicationInstance(array $data): ApplicationDataTransferObject
    {
        $this->validateApplicationInstance($data);

        try {
            $this->beginTransaction();

            if ($this->doesApplicationExist($data['account']->getUuid(), $data['name']))
                throw new ApplicationWithDuplicateNameException();

            $application = $this->applicationDataTransferObject->toEntity
                (
                    [
                        'name' => $data['name'],
                        'account' => $data['account']
                    ]
                );

            $this->persistEntity($application);

            $instance = $data['instance'];

            $this->uploadAsset($instance, $data);

            $this->setLinkedApis($instance, $data);

            $instance->setApplication($application);

            $this->persistEntity($instance);

            $application->addInstance($instance);

            /** @var Partner $user */
            $user = $data['author'];

            $user->addInstance($instance);

            $this->persistEntity($user);
            $this->persistEntity($application);

            $this->flush();

            if ($this->isEntityManagerOpen()) {
                $this->commitTransaction();

                $this->eventDispatcher->dispatch
                    (
                        new ApplicationFilesBeingReviewed
                            (
                                $data['account'],
                                $application,
                                $user
                            )
                    );
            }
            else {
                $this->rollBackTransaction();

                throw new EntityNotCreatedException();
            }

            return $this->applicationDataTransferObject->fromEntity($application);
        }
        catch (Exception $exception) {
            $this->rollBackTransaction();

            throw $exception;
        }
    }

    /**
     * @param string $accountUuid
     * @param string $name
     * @return bool
     */
    public function doesApplicationExist(string $accountUuid, string $name): bool
    {
        try {
            return ! is_null($this->applicationRepository->findApplicationByAccountUuidAndName($accountUuid, $name));
        }
        catch (NonUniqueResultException $nonUniqueResultException) {
            return true;
        }
        catch (Exception $exception) {
            return false;
        }
    }

//    /**
//     * @param string $searchQuery
//     * @param string $sortBy
//     * @return array
//     * @throws MissingAttributeException
//     * @throws ORMException
//     * @throws OptimisticLockException
//     */
//    public function getApplicationsBySearchQuery
//        (
//            string $searchQuery,
//            string $sortBy = FilterManager::SORT_BY_POPULAR
//        ): array
//    {
//        if
//            (
//                ! in_array
//                    (
//                        $sortBy,
//                        [
//                            FilterManager::SORT_BY_BEST_MATCH,
//                            FilterManager::SORT_BY_FEATURED,
//                            FilterManager::SORT_BY_POPULAR,
//                            FilterManager::SORT_BY_RECENT,
//                        ]
//                    )
//            )
//            $sortBy = FilterManager::SORT_BY_POPULAR;
//
//        if (strpos($searchQuery, '?sort'))
//            $searchQuery = substr($searchQuery, 0, strpos($searchQuery, '?sort'));
//
//        $applications = $this->applicationRepository->findBySearchQuery($searchQuery, $sortBy);
//
//        if (strlen($searchQuery) >= SearchQuery::MIN_NUMBER_OF_CHARACTERS_FOR_VALID_SEARCH_QUERY) {
//            $searchQueryEntity = $this->searchQueryDataTransferObject->toEntity
//                (
//                    [
//                        'query' => $searchQuery
//                    ]
//                );
//
//            $this->searchQueryRepository->save($searchQueryEntity);
//        }
//
//        return $this->convertToDataTransferObjects($applications);
//    }




    /**
     * @param Application $application
     * @param Partner $author
     * @param Message $message
     * @param string $sender
     * @throws UnknownMessageSenderTypeException
     */
    public function sendMessage(Application $application, Partner $author, Message $message, string $sender): void
    {
        if
            (
                ! in_array
                    (
                        $sender,
                        [
                            Message::SENDER_ADMIN,
                            Message::SENDER_PROVIDER
                        ]
                    )
            )
            throw new UnknownMessageSenderTypeException();

        $message
            ->setSender($sender)
            ->setApplication($application)
            ->setAuthor($author)
        ;

        $author->addMessage($message);

        $this->persistEntity($message);
        $this->saveEntity($author);

        $this->eventDispatcher->dispatch
            (
                new ApplicationMessageSent
                    (
                        $application->getAccount(),
                        $application,
                        $author,
                        $application->getCurrentInstance()->getCreator(),
                        $sender
                    )
            );
    }

    /**
     * @param array $data
     * @return ApplicationDataTransferObject
     * @throws ApplicationCannotBeListedException
     * @throws ConnectionException
     * @throws EntityNotCreatedException
     * @throws MissingAttributeException
     */
    public function updateApplicationListing(array $data): ApplicationDataTransferObject
    {
        if
            (
                empty($data['_token']) ||
                empty($data['compatibleWith']) ||
                ! is_array($data['compatibleWith']) ||
                empty($data['application']) ||
                ! $data['application'] instanceof Application
            )
            throw new MissingAttributeException();

        if (! $data['application']->canBeListed())
            throw new ApplicationCannotBeListedException();

        try {
            $this->beginTransaction();

            $application = $data['application'];

            $application->setCompatibleWith($data['compatibleWith']);

            $formToken = $data['_token'];

            if ($application->getStatus() !== Application::STATUS_LISTING_PUBLISHED)
                $application->setStatus(Application::STATUS_LISTING_IN_REVIEW);

            $mediaItemsToRemove = [];

            $this->updateListingAdditionalResources($application, $data, $formToken, $mediaItemsToRemove);

            $this->persistEntity($application);

            $this->flush();

            if ($this->isEntityManagerOpen()) {
                $this->commitTransaction();

                $this->uploadHelper->removeMediaItems($formToken, $mediaItemsToRemove);

                if (empty($data['isSuperAdmin'])) {
                    $this->eventDispatcher->dispatch
                        (
                            new ApplicationListingBeingReviewed
                                (
                                    $application->getAccount(),
                                    $application,
                                    $application->getCurrentInstance()->getCreator()
                                )
                        );
                }
            }
            else {
                $this->rollBackTransaction();

                throw new EntityNotCreatedException();
            }
        }
        catch (Exception $exception) {
            $this->rollBackTransaction();

            throw new EntityNotCreatedException();
        }

        return $this->applicationDataTransferObject->fromEntity($application);
    }
}