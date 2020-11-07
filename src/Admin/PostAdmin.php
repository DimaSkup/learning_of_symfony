<?php


namespace App\Admin;

use App\Entity\Post;
use App\Entity\User;
use Cocur\Slugify\Slugify;
use App\Service\FileHandleService;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\CallbackTransformer;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;


final class PostAdmin extends AbstractAdmin
{
    public function __construct($code, $class, $baseControllerName, Slugify $slugify, FileHandleService $fileHandleService)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->slugify = $slugify;
        $this->fileHandleService = $fileHandleService;
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

        $dataCollector = $formMapper->getFormBuilder()->get('title')->getAttribute('data_collector/passed_options');
        $fieldDescription = $dataCollector["sonata_field_description"];
        $postSubject = $fieldDescription->getAdmin()->getSubject();

        $container = $this->getConfigurationPool()->getContainer();


        $imageFilename =
        dd($postSubject->getBrochureFilename());
        //dd($formMapper->get('image'));

        /*
        $formMapper->get('image')
            ->addModelTransformer(new CallbackTransformer(
                function($image)
                {
                    return;
                },
                function ($image)
                {
                    dd($image);


                }
            ));
        */

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
        $fileHandler = $this->fileHandleService;
        $files = $this->getRequest()->files;
        $uniqid = $files->keys()[0];
        $filesParams = $files->all()[$uniqid];

        // A FILES HANDLING
        foreach ($filesParams as $key => $file)
        {
            /** @var UploadedFile $file */

            if ($key == "brochure")
            {
                $fileHandler->handleBrochureFile($post, $file, 'brochures_directory');
            }
            else if ($key == "image")
            {
                $fileHandler->handleImageFile($post, $file, 'images_directory');
            }
            else
            {
                return new Response("Error! Unknown type of the file!");
            }
        }

        $post->setSlug($this->slugify->slugify(substr($post->getTitle(), 0, 20)));
        $post->setIsModerated(true);
        $post->setCreatedAt(new \DateTime());
    }

    public function preUpdate($post)
    {
        dd("KEK");
        $fileHandler = $this->fileHandleService;
        $files = $this->getRequest()->files;
        $uniqid = $files->keys()[0];
        $filesParams = $files->all()[$uniqid];

        // A FILES HANDLING
        foreach ($filesParams as $key => $file)
        {
            /** @var UploadedFile $file */

            if ($key == "brochure")
            {
                $post->setBrochureFilename(null);
                $fileHandler->handleBrochureFile($post, $file, 'brochures_directory');
            }
            else if ($key == "image")
            {
                $post->setImageFilename(null);
                $fileHandler->handleImageFile($post, $file, 'images_directory');
            }
            else
            {
                return new Response("Error! Unknown type of the file!");
            }
        }

        $post->setSlug($this->slugify->slugify(substr($post->getTitle(), 0, 20)));
        //$post->setIsModerated(true);
        //$post->setCreatedAt(new \DateTime());
    }

    /** @var Slugify */
    private $slugify;

    /** @var FileHandleService */
    private $fileHandleService;

    /** @var ParameterBagInterface */
    $parameterBag;
}