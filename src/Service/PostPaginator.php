<?php


namespace App\Service;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;


class PostPaginator
{
    public function __construct(PostRepository $postRepository, Request $request)
    {
        $this->countOfPages = 0;
        $requestData = $request->attributes->all();
       // $request->getParameter('page_number');

        $this->pageNumber = intval($requestData['page_number']);
        $this->sortBy = $requestData['display_order'];
        $this->resultsPerPage = intval($requestData['results_per_page']);
        $this->postRepository = $postRepository;
    }

    public function getPostsSet()
    {
        return $this->postRepository->findAllPaginated($this->countOfPages, $this->pageNumber, $this->resultsPerPage);
    }

    public function getPageNumberList()
    {
        return [
            '1' => $this->pageNumber,
        ];
    }

    public function sortPostSetBy($postSetForSort, $sortBy, $order = "ASC")
    {
        if ($sortBy === "created_at")
        {
            if ($order === "DESC")
            {
                usort($postSetForSort, function($post1, $post2)
                {
                    if ($post1->getCreatedAt() == $post2->getCreatedAt()) return 0;
                    return ($post1->getCreatedAt() > $post2->getCreatedAt()) ? -1 : 1;
                });
            }
            else if ($order === "ASC")
            {
                usort($postSetForSort, function($post1, $post2)
                {
                    if ($post1->getCreatedAt() == $post2->getCreatedAt()) return 0;
                    return ($post1->getCreatedAt() < $post2->getCreatedAt()) ? -1 : 1;
                });
            }
        }
        else if ($sortBy === "username")
        {
            usort($postSetForSort, function($post1, $post2)
            {
                if ($post1->getUsername() == $post2->getUsername()) return 0;
                return ($post1->getUsername() < $post2->getUsername()) ? -1 : 1;
            });
        }
        else if ($sortBy === "email")
        {
            usort($postSetForSort, function($post1, $post2)
            {
                if ($post1->getEmail() == $post2->getEmail()) return 0;
                return ($post1->getEmail() < $post2->getEmail()) ? -1 : 1;
            });
        }

        return $postSetForSort;
    }


    private $postRepository;
    private $sortBy;
    private $countOfPages;
    private $pageNumber;
    private $resultsPerPage;
}