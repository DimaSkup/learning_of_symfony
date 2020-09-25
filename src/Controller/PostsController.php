<?php

namespace App\Controller;

use App\Entity\Post;

use App\Form\PostType;
use App\Repository\PostRepository;
use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    /**
     * @return Response
     */
    public function posts()
    {
        $posts = $this->postRepository->findAll();

        return $this->render('posts/index.html.twig', [
            'posts' => $posts,
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
    public function addPost(Request $request, Slugify $slugify)
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $post->setSlug($slugify->slugify($post->getTitle()));
            $post->setCreatedAt(new \DateTime());

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
    public function edit(Post $post, Request $request, Slugify $slugify)
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
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
