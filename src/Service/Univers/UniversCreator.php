<?php
    namespace App\Service\Univers;

    use App\Entity\Univers;
    use App\Entity\User;
    use App\Entity\UserUnivers;
    use App\Service\FileUploader;
    use Doctrine\ORM\EntityManagerInterface;

    class UniversCreator {

        /**
         * @var FileUploader
         */
        private $fileUploder;

        /**
         * UniversCreator constructor.
         * @param FileUploader $fileUploader
         * @param EntityManagerInterface $entityManager
         */
        public function __construct(FileUploader $fileUploader, EntityManagerInterface $entityManager)
        {
            $this->fileUploder = $fileUploader;
            $this->em = $entityManager;
        }

        /**
         * @param Univers $univers
         * @param User $user
         * @param $image
         */
        public function createUnivers(Univers $univers, User $user, $image)
        {
            $univers-> setIsPrivate(true)
                    -> setCreator($user);
            if($univers->getImage() !== null){

                $nameFile = $this->fileUploder->upload($image);
                $univers -> setImage($nameFile);

            }else{
                $univers -> setImage("default.png");
            }
            //  4) save the Universe!
            $this->em->persist($univers);

            $userUnivers = new UserUnivers();
            $userUnivers->setUser($user)
                ->setNameRole('creator')
                ->setUnivers($univers);

            $this->em->persist($userUnivers);

            $this->em->flush();
        }
    }
?>