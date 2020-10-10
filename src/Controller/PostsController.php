<?php

namespace App\Controller;

use App\Entity\Post;

use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\FileHandleService;

use App\Service\PostPaginator;

class PostsController extends AbstractController
{
    /**
     * PostsController constructor.
     * @param PostRepository $postRepository
     */
    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function emptyPath()
    {
        return $this->redirectToRoute("blog_posts", ['_locale' => 'en']);
    }

    /**
     * @return Response
     */
    public function posts(Request $request)
    {
        //dd($request->attributes);
        //$posts = $this->postRepository->findAll();
        //$posts = $this->getPaginator($request);

        $postPaginator = new PostPaginator($this->postRepository, $request);
        $posts = $postPaginator->getPostsSet();
        $pageNumberList = $postPaginator->getPageNumberList();
        $countOfPages = $postPaginator->getCountOfPages();

        // Setting user's data for the each post
        foreach($posts as $post)
        {
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $post->getUser()->getId()]);
            $post->setUser($user);
        }

        return $this->render('posts/index.html.twig', [
            'posts' => $posts,
            'pageNumList' => $pageNumberList,
            'countOfPages' => $countOfPages,
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function search(Request $request)
    {
        $query = $request->query->get('q');
        $posts = $this->postRepository->searchByQuery($query);

        return $this->render('posts/query_post.html.twig', [
            'posts' => $posts,
            'text' => "Word",
        ]);
    }

    /**
     * @param Request $request
     * @param Slugify $slugify
     *
     * @return Response
     */
    public function new(Request $request, Slugify $slugify, ParameterBagInterface $parameterBag)
    {
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $fileHandler = new FileHandleService($parameterBag);

            // ReCaptcha handling
            if (!$_POST['g-recaptcha-response'])
                exit('Please, fill the ReCaptcha');

            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $key = '6Lft7qwZAAAAAMTUH3WFuGV18ekY3y3U4_VP3fvB';
            $query = $url.'?secret='.$key.'&response='.$_POST['g-recaptcha-response'].'&remoteip='.$_SERVER['REMOTE_ADDR'];
            $data = json_decode(file_get_contents($query));

            if ($data->success == false)
                exit("Captcha was inputted incorrectly. Please, try again");



            // Handling of the PDF document
            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('brochure')->getData();

            if ($brochureFile)
            {
                $fileHandler->handleBrochureFile($post, $brochureFile, 'brochures_directory');
            }

            // Handling of the Image (JPG|PNG|GIF) file
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile)
            {
                $fileHandler->handleBrochureFile($post, $imageFile, 'images_directory');
            }

            $post->setSlug($slugify->slugify($post->getTitle()));
            $post->setCreatedAt(new \DateTime());
            $post->setUser($this->getUser());


            $post->setIsModerated(true);





            // Save the data in the Database
            $em = $this->getDoctrine()->getManager();

            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute("blog_posts");
        }

        return $this->render('posts/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Post $post
     * @param Request $request
     * @param Slugify $slugify
     * @return RedirectResponse|Response
     */
    public function edit(Post $post, Request $request, Slugify $slugify, ParameterBagInterface $parameterBag)
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $fileHandler = new FileHandleService($parameterBag);

            // Handling of the PDF document
            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('brochure')->getData();

            if ($brochureFile)
            {
                $fileHandler->handleBrochureFile($post, $brochureFile, 'brochures_directory');
            }

            // Handling of the Image (JPG|PNG|GIF) file
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile)
            {
                $fileHandler->handleImageFile($post, $imageFile, 'images_directory');
            }

            // Save the data in the Database
            $em = $this->getDoctrine()->getManager();

            $post->setSlug($slugify->slugify($post->getTitle()));
            $em->flush();

            return $this->redirectToRoute('blog_show', [
                'slug' => $post->getSlug()
            ]);
        }

        return $this->render('posts/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Post $post
     * @return RedirectResponse
     */
    public function delete(Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('blog_posts');
    }

    /**
     * @param Post $post
     * @return Response
     */
    public function post(Post $post)
    {
        return $this->render('posts/show.html.twig', [
            'post' => $post
        ]);
    }

    /** @var PostRepository $postRepository */
    private $postRepository;
}
