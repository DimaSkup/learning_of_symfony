<?php


namespace App\Admin;

use App\Entity\Post;
use App\Entity\User;
use Cocur\Slugify\Slugify;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\File;

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
                ->add('brochure', FileType::class, [
                    'label' => 'Brochure (PDF file)',
                    'mapped' => false,
                    'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '1024k',
                            'mimeTypes' => [
                                'application/pdf',
                                'application/x-pdf',
                            ],
                            'mimeTypesMessage' => 'Please, upload a valid PDF document',
                        ])
                    ],
                ])
                ->add('image', FileType::class, [
                    'label' => 'Image (JPG|PNG|GIF file)',
                    'mapped' => false,
                    'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '1024k',
                            'mimeTypesMessage' => 'Please, upload a valid Image (JPG|PNG|GIF) file',
                        ])
                    ]
                ])
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
        $listMapper
            ->addIdentifier('title')
            ->add('user.email')
            ->add('isModerated')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title')
            ->add('user', null, [], EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ]);
    }

    public function prePersist($post)
    {
        $files = $this->getRequest()->files;
        $uniqid = $files->keys()[0];
        $filesParams = $files->all()[$uniqid];


        // A FILES HANDLING
        foreach ($filesParams as $key => $file)
        {
            dd($file);
        }


        $post->setSlug($this->slugify->slugify(substr($post->getTitle(), 0, 20)));
        $post->setIsModerated(true);

        //$post->setImageFilename(null);
        //$post->setBrochureFilename(null);
        $post->setCreatedAt(new \DateTime());

        dd($post);
    }

    /** @var Slugify */
    private $slugify;
}