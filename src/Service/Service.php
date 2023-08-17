<?php


namespace App\Service;


use App\Entity\Exception\EntityNotCreatedException;

use App\Service\Communication\Mailer;

use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\RequestStack;

abstract class Service
{
    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var Mailer
     */
    protected Mailer $mailer;

    /**
     * @var RequestStack
     */
    protected RequestStack $session;

    /**
     * Begins db transaction
     * @throws \Doctrine\DBAL\Exception
     */
    protected function beginTransaction(): void
    {
        $this->entityManager
            ->getConnection()
            ->beginTransaction();
    }

    /**
     * @throws EntityNotCreatedException
     * @throws ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    protected function commitOrRollbackTransaction(): void
    {
        if ($this->isEntityManagerOpen())
            $this->commitTransaction();
        else {
            $this->rollBackTransaction();

            throw new EntityNotCreatedException();
        }
    }

    /**
     * @throws ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    protected function commitTransaction(): void
    {
        $this->entityManager
            ->getConnection()
            ->commit();
    }

    /**
     * @param object $entity
     * @return bool
     */
    protected function contains(object $entity): bool
    {
        return $this->entityManager->contains($entity);
    }

    /**
     *
     */
    protected function flush(): void
    {
        $this->entityManager->flush();
    }

    /**
     * @return bool
     */
    protected function isEntityManagerOpen(): bool
    {
        return $this->entityManager->isOpen();
    }

    /**
     * @param object $entity
     */
    protected function mergeEntity(object $entity): void
    {
        $this->entityManager->merge($entity);
    }

    /**
     * @param object $entity
     */
    protected function persistEntity(object $entity): void
    {
        $this->entityManager->persist($entity);
    }

    /**
     * @param object $entity
     */
    protected function removeEntity(object $entity): void
    {
        $this->entityManager->remove($entity);
    }

    /**
     * @throws ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    protected function rollBackTransaction(): void
    {
        $connection = $this->entityManager->getConnection();

        if ($connection->isTransactionActive())
            $connection->rollBack();
    }

    /**
     * @param object $entity
     */
    protected function saveEntity(object $entity): void
    {
        $this->entityManager->persist($entity);

        $this->entityManager->flush();
    }

    /**
     * Service constructor.
     * @param EntityManagerInterface $entityManager
     * @param EventDispatcherInterface|null $eventDispatcher
     * @param LoggerInterface|null $logger
//     * @param \App\Service\Communication\Mailer|null $mailer
     * @param RequestStack|null $session
     */
    public function __construct
        (
            EntityManagerInterface $entityManager,
            EventDispatcherInterface $eventDispatcher = null,
            LoggerInterface $logger = null,
//            Mailer $mailer = null,
            RequestStack $session = null
        )
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
//        $this->mailer = $mailer;
        $this->session = $session;
    }
}