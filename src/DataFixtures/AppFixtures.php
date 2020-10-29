<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;

use App\Entity\Person;

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

        $this->fakePostsCount = 54;
        $this->fakeUsersCount = 5;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadPosts($manager);
        //$this->loadPersons($manager);

        $manager->flush();
    }

    public function loadPosts(ObjectManager $manager)
    {
        for ($i = 1; $i <= $this->fakePostsCount; $i++)
        {
            $user = $this->userRepository->findOneBy(['email' => rand(1, $this->fakeUsersCount).'@gmail.com']);
            $post = new Post();
            $post->setTitle($this->faker->text(100))
                 ->setSlug($this->slug->slugify($post->getTitle()))
                 ->setBody($this->faker->text(1000))
                 ->setCreatedAt($this->faker->dateTime)
                 ->setUser($user)
                 ->setIsModerated(true)
                 ->setEmail($user->getEmail())
                 ->setUsername($user->getUsername());

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
                 ->setUsername($this->faker->text(10))
                 ->setOAuthType('legasy')
                 ->setLastLoginTime(new DateTime('now'));

            $manager->persist($user);
        }

        $manager->flush();
    }

    public function loadPersons(ObjectManager $manager)
    {
        for ($i = 0; $i < 10000; $i++)
        {
            $person = new Person;

            $person
                ->setAge(random_int(18, 30))
                ->setGender((random_int(1, 100) % 2 == 0) ? 'male' : 'female')
                ->setPositionId((random_int(1, 10) % 5 !== 0) ? random_int(1, 3) : null);

            $manager->persist($person);
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
