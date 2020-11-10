<?php


namespace App\Service;

use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;


class PostsPaginator
{
    private $pageNumber;
    private $resultsPerPage;
    private $postRepository;
    private $nextPage;

    public function __construct(PostRepository $postRepository, Request $request)
    {
        $requestAttrParamBag = $request->attributes;

        $this->pageNumber = intval($requestAttrParamBag->get('page'));
        $this->resultsPerPage = intval($requestAttrParamBag->get('results_per_page'));
        $this->postRepository = $postRepository;
        $this->nextPage = false;
    }

    public function getPostsSet()
    {
        $posts = $this->postRepository->findAllPaginated($this->pageNumber, $this->resultsPerPage);
        $this->setNextPageExists($posts);

        return $posts;
    }

    public function nextPage(): bool
    {
        return $this->nextPage;
    }

    private function setNextPageExists($posts)
    {
        if (count($posts) === $this->resultsPerPage + 1)
        {
            unset($posts[$this->resultsPerPage]);       // we need only $numberOfPostsPerPage - 1 posts to display
            $this->nextPage = true;     // set flag "nextPage" to point that the next page is exists

            return;
        }

        $this->nextPage = false;
    }

    /*public function getPageNumberList()
    {
        if (1 <= $this->pageNumber && $this->pageNumber <= 3)
            return range(1, 5);
        else
            return [
                '1' => $this->pageNumber - 2,
                '2' => $this->pageNumber - 1,
                '3' => $this->pageNumber,
                '4' => $this->pageNumber + 1,
                '5' => $this->pageNumber + 2,
            ];
    }*/

    /**
     * @return int
     */
    public function getCountOfPages(): int
    {
        return $this->countOfPages;
    }
}