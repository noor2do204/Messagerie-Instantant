<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findBySearchQuery(string $query): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where('u.name LIKE :query')
            ->setParameter('query', '%' . $query . '%');

        return $qb->getQuery()->getResult();
    }

    public function findFriendsOfUser(User $user): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->leftJoin('u.friendshipsInitiated', 'fi')
            ->leftJoin('u.friendshipsReceived', 'fr')
            ->where('fi.userTwo = :user OR fr.userOne = :user')
            ->andWhere('fi.status = :status OR fr.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'accepted')
            ->distinct();

        return $qb->getQuery()->getResult();
    }
}
