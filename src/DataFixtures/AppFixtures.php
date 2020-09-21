<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use Cocur\Slugify\Slugify;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function __construct(Slugify $slugify)
    {
        $this->faker = Factory::create();
        $this->slug = $slugify;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadPosts($manager);

        $manager->flush();
    }

    public function loadPosts(ObjectManager $manager)
    {
        for ($i = 1; $i < 20; $i++)
        {
            $post = new Post();
            $post->setTitle($this->faker->text(100));
            $post->setSlug($this->slug->slugify($post->getTitle()));
            $post->setBody($this->faker->text(1000));
            $post->setCreatedAt($this->faker->dateTime);

            $manager->persist($post);
        }

        $manager->flush();
    }

    private $faker;
    private $slug;
}
