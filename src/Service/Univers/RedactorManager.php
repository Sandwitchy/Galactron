<?php


namespace App\Service\Univers;


use App\Entity\Univers;
use App\Entity\User;
use App\Service\MessageSystemService;
use App\Service\Security\UserChecker;
use Doctrine\ORM\EntityManagerInterface;

class RedactorManager
{
    public function __construct(UserChecker $checker,MessageSystemService $messSystem)
    {
        $this->checker = $checker;
        $this->messSystem = $messSystem;
    }

    /**
     * @param $userId
     * @param User $userAdmin
     * @param Univers $univers
     */
    public function promote($userId, User $userAdmin, Univers $univers){
        if($user = $this->checker->checkUserById($userId)){
            $this->messSystem->sendPromoteRedactor($user,$userAdmin,$univers);
        }
    }

    /**
     * @param Univers $univers
     */
    public function fetchRedactor(Univers $univers){
        $users = $univers->getUserUnivers();
        $redactors = array();
        foreach($users as $use){
            if($use->getNameRole() === 'redactor'){
                $redactors[] = $use->getUser();
            }
        }
        return $redactors;
    }
}