<?php

namespace App\Admin;

use App\Entity\Post;


use App\Entity\User;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Component\Form\Extension\Core\Type\TextType;

final class PostAdmin extends AbstractAdmin
{

    public function __construct($code, $class, $baseControllerName, Slugify $slugify)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->slugify = $slugify;

    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Content', ['class' => 'col-md-9'])
                ->add('email', EmailType::class)
                ->add('title', TextType::class)
                ->add('body', TextareaType::class)
                ->add('imageFilename', FileType::class, [
                    'required' => false,
                    'label' => 'Image (JPG|PNG|GIF file)',
                ])
                ->add('is_moderated', BooleanType::class)
            ->end();
    }


    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('title');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('title');
    }


    public function prePersist($post)
    {
        // get the entity manager
        $container = $this->getConfigurationPool()->getContainer();
        $this->em = $container->get('doctrine');
        // get request's data
        $request = $this->getRequest();
        $uniqid = $request->query->get('uniqid');
        $uniqidDataArray = $request->request->get($uniqid);

        // Image upload handling
        $imageFile = $request->files->get($uniqid)['imageFilename'];
        //dd($imageFile);

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $uniqidDataArray['email']]);
        $post->setUser($user);
        $post->setIsModerated(true);
        $post->setSlug($this->slugify->slugify($uniqidDataArray['title']));
        $post->setCreatedAt(new \DateTime());
    }

    /**
     * @var Slugify
     */
    private $slugify;

    /**
     * @var EntityManager
     */
    private $em;
}
