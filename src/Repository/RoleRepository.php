<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 *
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository
{
    /**
     * RoleRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /**
     * @param Role $role
     * @return void
     */
    public function delete(Role $role): void
    {
        $this->getEntityManager()->remove($role);
        $this->getEntityManager()->flush();
    }

    /**
     * @return array
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function findAllRolesExcludingSuperAdmin(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.name <> :role')
            ->setParameter('role', 'Super Admin')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $name
     * @return Role|null
     */
    public function findByName(string $name): ?Role
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * @param string $slug
     * @param string $userUuid
     * @return Role|null
     * @throws NonUniqueResultException
     */
    public function findByUserUuidAndSlug(string $slug, string $userUuid): ?Role
    {
        return $this->createQueryBuilder('r')
            ->join('r.users', 'u')
            ->where('r.uuid = :slug')
            ->andWhere('u.uuid = :userUuid')
            ->setParameters
            (
                [
                    'slug' => $slug,
                    'userUuid' => $userUuid,
                ]
            )
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $name
     * @param int $userId
     * @return Role|null
     * @throws NonUniqueResultException
     */
    public function findByNameAndUserId( string $name, int $userId ): ?Role
    {
        return $this->createQueryBuilder('r')
            ->join('r.partners', 'u')
            ->where('r.name = :role')
            ->andWhere('u.id = :userId')
            ->setParameters
            (
                [
                    'role' => $name,
                    'userId' => $userId,
                ]
            )
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param array $names
     * @return Role[]
     */
    public function findByNames(array $names): array
    {
        return $this->findBy(['name' => $names]);
    }

    /**
     * @param string $slug
     * @return Role|null
     */
    public function findBySlug(string $slug): ?Role
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * @param Role $role
     * @return void
     */
    public function save(Role $role): void
    {
        $this->getEntityManager()->persist($role);
        $this->getEntityManager()->flush();
    }
}
