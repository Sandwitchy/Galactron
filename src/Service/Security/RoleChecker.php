<?php


namespace App\Service\Security;


use App\Entity\Univers;
use App\Entity\User;

class RoleChecker
{
    /**
     * @param User $user
     * @param Univers $univers
     */
    public function check(User $user, Univers $univers){
        if($user !== null){
            return [($univers->getCreator() === $user)? $isCreator = true :  $isCreator = false,$this->checkIfRedactor($user,$univers)];
        }else{
            return [false,false];
        }
    }

    /**
     * @param User $user
     * @param Univers $universe
     * @return bool
     */
    private function checkIfRedactor(User $user, Univers $universe){
        $userUnivers = $user->getUserUnivers();

        foreach($userUnivers as $uU){
            if($uU->getUnivers() == $universe){
                if($uU->getNameRole() === 'redactor'){
                    return true;
                }
            }
        }
        return false;
    }
}