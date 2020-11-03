<?php


namespace App\Admin;

use App\Entity\Post;
use App\Entity\User;

use Cocur\Slugify\Slugify;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

final class PostAdmin extends AbstractAdmin
{
    public function __construct($code, $class, $baseControllerName, Slugify $slugify)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->slugify = $slugify;
    }

    public function toString($object)
    {
        return $object instanceof Post
            ? $object->getTitle()
            : 'Post object'; // shown in the breadcrumb on the create view
     }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Content', ['class' => 'post_admin_content'])
                ->add('title', TextType::class)
                ->add('body', TextareaType::class)
            ->end()
            ->with('Meta data', ['class' => 'post_admin_meta_data'])
                ->add('user', ModelType::class, [
                    'class' => User::class,
                    'property' => 'email',
            ])
        ->end()
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {

    }

    public function prePersist($post)
    {
        $post->setSlug($this->slugify->slugify(substr($post->getTitle(), 0, 20)));
        $post->setIsModerated(true);

        $post->setImageFilename(null);
        $post->setBrochureFilename(null);
        $post->setCreatedAt(new \DateTime());

        $post->setEmail($post->getUser()->getEmail());
        $post->setUsername($post->getUser()->getUsername());
    }

    /** @var Slugify */
    private $slugify;
}