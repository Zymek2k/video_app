<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }


    public function load(ObjectManager $manager): void
    {
        foreach($this->getUserData() as [$name, $lastName, $email, $password, $apiKey, $roles])
        {
            $user = new User();
            $user->setName($name);
            $user->setLastName($lastName);
            $user->setEmail($email);
            $user->setPassword($this->passwordHasher->hashPassword($user,$password));
            $user->setVimeoApiKey($apiKey);
            $user->setRoles($roles);

            $manager->persist($user);
        }
        $manager->flush();
    }

    public function getUserData(): array
    {
        return [
            ['John', 'Wayne', 'jw@jawuz.com', 'passw', 'hjd8dehdh', ['ROLE_ADMIN']],
            ['John', 'Bravo', 'jb@jawuz.com', 'passw', null, ['ROLE_ADMIN']],
            ['John', 'Delta', 'jd@jawuz.com', 'passw', null, ['ROLE_USER']],
        ];
    }
}
