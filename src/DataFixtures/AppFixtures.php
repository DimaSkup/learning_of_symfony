<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use Cocur\Slugify\Slugify;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    public function __construct(Slugify $slugify,
                                UserRepository $userRepository,
                                UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->faker = Factory::create();
        $this->slug = $slugify;

        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;

        $this->fakePostsCount = 200;
        $this->fakeUsersCount = 5;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadPosts($manager);

        $manager->flush();
    }

    public function loadPosts(ObjectManager $manager)
    {
        for ($i = 1; $i < $this->fakePostsCount; $i++)
        {
            $post = new Post();
            $post->setTitle($this->faker->text(100))
                 ->setSlug($this->slug->slugify($post->getTitle()))
                 ->setBody($this->faker->text(1000))
                 ->setCreatedAt($this->faker->dateTime)
                 ->setUser($this->userRepository->findOneBy(['email' => rand(1, $this->fakeUsersCount).'@gmail.com']))
                 ->setIsModerated(true);


            $manager->persist($post);
        }

        $manager->flush();
    }

    public function loadUsers(ObjectManager $manager)
    {
        # creating regular users
        for ($i = 1; $i <= $this->fakeUsersCount; $i++)
        {
            $user = new User();

            // Encode the plain password
            $encodedPassword = $this->passwordEncoder->encodePassword(
                $user,
                '12345'
            );

            $user->setEmail($i."@gmail.com")
                 ->setPassword($encodedPassword)
                 ->setRoles([User::ROLE_USER])
                 ->setEnabled(true)
                 ->setUsername('user'. $i)
                 ->setOAuthType('legasy')
                 ->setLastLoginTime(new DateTime('now'));

            $manager->persist($user);
        }

        $manager->flush();
    }

    private $faker;
    private $slug;
    private $userRepository;
    private $passwordEncoder;
    private $fakePostsCount;
    private $fakeUsersCount;
}
