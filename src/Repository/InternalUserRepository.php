<?php

namespace App\Repository;

use App\Entity\InternalUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InternalUserRepository>
 *
 * @method InternalUserRepository|null find($id, $lockMode = null, $lockVersion = null)
 * @method InternalUserRepository|null findOneBy(array $criteria, array $orderBy = null)
 * @method InternalUserRepository[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InternalUserRepository extends ServiceEntityRepository
{
    /**
     * PartnerRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct( $registry, InternalUser::class );
    }

    /**
     * @param InternalUser $internalUser
     * @return void
     */
    public function delete( InternalUser $internalUser ): void
    {
        $this->getEntityManager()->remove( $internalUser );
        $this->getEntityManager()->flush();
    }

    /**
     * @return array
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function findAllSuperAdmins(): array
    {
        return $this->createQueryBuilder('u')
            ->join('u.roles', 'r')
            ->where('r.name = :role')
            ->andWhere('u.isDeleted = :isDeleted')
            ->setParameters
            (
                [
                    'isDeleted' => false,
                    'role' => User::ROLE_SUPER_ADMIN,
                ]
            )
            ->getQuery()
            ->getResult();
    }
    /**
     * @param string $email
     * @return InternalUser|null
     */
    public function findByEmail(string $email): ?InternalUser
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * @param string $username
     * @return InternalUserRepository|null
     */
    public function findByUsername(string $username): ?InternalUserRepository
    {
        return $this->findOneBy(['username' => $username]);
    }

    /**
     * @param string $uuid
     * @return InternalUserRepository|null
     */
    public function findByUuid(string $uuid): ?InternalUserRepository
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    /**
     * @param InternalUser $internalUser
     * @return void
     */
    public function save( InternalUser $internalUser ): void
    {
        $this->getEntityManager()->persist( $internalUser );
        $this->getEntityManager()->flush();
    }
}
