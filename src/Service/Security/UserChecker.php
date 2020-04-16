<?php


namespace App\Service\Security;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserChecker
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param $userId
     * @return object|null
     */
    public function checkUserById($userId){
        $user = $this   ->em
                        ->getRepository(User::class)
                        ->find($userId);
        if($user !== null){
            return $user;
        }
        return false;
    }
}