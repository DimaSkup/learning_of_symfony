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

        $this->pageNumber = intval($requestData['page']);
        $this->displayOrder = $requestData['display_order'];
        $this->dateSortOrder = $requestData['date_sort_order'];
        $this->resultsPerPage = intval($requestData['results_per_page']);
        $this->postRepository = $postRepository;
    }

    public function getPostsSet()
    {
        $posts = $this->postRepository->findAllPaginated($this->countOfPages, $this->pageNumber, $this->resultsPerPage);
        $posts = $this->sortPostSetBy($posts, $this->displayOrder, $this->dateSortOrder);
        return $posts;
    }

    public function getPageNumberList()
    {
        if ($this->isBetween($this->pageNumber, 1, 3))
            return range(1, 5);
        else
            return [
                '1' => $this->pageNumber - 2,
                '2' => $this->pageNumber - 1,
                '3' => $this->pageNumber,
                '4' => $this->pageNumber + 1,
                '5' => $this->pageNumber + 2,
            ];
    }

    /**
     * @return int
     */
    public function getCountOfPages(): int
    {
        return $this->countOfPages;
    }

    public function sortPostSetBy($postSetForSort, $sortBy, $order)
    {

        if ($sortBy === "createdAt")
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

    // checking if the number n is in the range between a and b
    private function isBetween($n, $a, $b)
    {
        return ($n-$a)*($n-$b) <= 0;
    }


    private $postRepository;
    private $displayOrder;
    private $dateSortOrder;
    private $countOfPages;
    private $pageNumber;
    private $resultsPerPage;

}