<?php


namespace App\Admin;

use App\Entity\Image;
use App\Entity\Post;
use App\Entity\User;
use Cocur\Slugify\Slugify;
use App\Service\FileHandleService;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Form\Type\AdminType;

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
    public function __construct($code, $class, $baseControllerName,
                                Slugify $slugify,
                                FileHandleService $fileHandler,
                                ParameterBagInterface $parameterBag)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->slugify = $slugify;
        $this->fileHandler = $fileHandler;
        $this->parameterBag = $parameterBag;
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




        $formMapper->get('image')
            ->addModelTransformer(new CallbackTransformer(
                function($image)
                {
                    return;
                },
                function ($imageAsSting)
                {
                    $postSubject = $this->subject;
                    $postSubject->setImageFilename(null);



                    $pathToImageDir = $this->parameterBag->get('images_directory');
                    $imageFullPath = $pathToImageDir . '/' . $postSubject->getImageFilename();

                    $request = $this->getRequest();
                    $uniqid = $request->query->get('uniqid');
                    /** @var UploadedFile $imageFile */
                    $imageFile = $request->files->get($uniqid)['image'];

                    return $imageFile;

                    //dd($this->subject->getImageFilename());


                    if (null !== $imageFile)
                    {
                        //dd($imageFile->getPathname() . '/' . $imageFile->getClientOriginalName());
                        return $imageFile;
                    }
                    else if ($this->subject->getImageFilename())
                    {
                        dd("SECOND");
                        $postImageFilename = $this->subject->getImageFilename();
                        dd($postImageFilename);
                        return $pathToImageDir.'/'.$postImageFilename;
                    }
                    else // null === $this->subject->getImage() && null === $imageFile
                    {
                        dd("THIRD");
                        return $pathToImageDir.'/default_image.png';
                    }

                    dd("END");
                }
            ));



    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->add('user.email')
            ->add('isModerated')
            ->add('created_at')
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
            /** @var UploadedFile $file */

            if ($key == "brochure" && $file !== null)
            {
                $this->fileHandler->handleBrochureFile($post, $file, 'brochures_directory');
            }
            else if ($key == "image" && $file !== null)
            {

                $this->fileHandler->handleImageFile($post, $file, 'images_directory');
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
        $files = $this->getRequest()->files;
        $uniqid = $files->keys()[0];
        $filesParams = $files->all()[$uniqid];

        if ($filesParams['brochure'] !== null)
            $this->fileHandler->handleBrochureFile($post, $filesParams['brochure'], 'images_directory');

        if ($filesParams['image'] !== null)
            $this->fileHandler->handleImageFile($post, $filesParams['image'], 'images_directory');


        // A FILES HANDLING
       /* if ($filesParams['brochure'] !== null)
            $this->fileHandler->handleBrochureFile($post, $filesParams['brochure'], 'images_directory');

        if ($filesParams['image'] !== null)
            $this->fileHandler->handleImageFile($post, $filesParams['image'], 'images_directory');
*/
        $post->setSlug($this->slugify->slugify(substr($post->getTitle(), 0, 20)));
        //$post->setIsModerated(true);
        //$post->setCreatedAt(new \DateTime());
    }

    /** @var Slugify */
    private $slugify;

    /** @var FileHandleService */
    private $fileHandler;

    /** @var ParameterBagInterface */
    private $parameterBag;
}