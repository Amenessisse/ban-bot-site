<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BannedUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<BannedUser> */
class BannedUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BannedUser::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    function countAllUsers(): int
    {
        $qb = $this->createQueryBuilder('banned_user');

        return $qb->select($qb->expr()->count('banned_user'))->getQuery()->getSingleScalarResult();
    }
}
