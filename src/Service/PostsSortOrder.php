<?php


namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class PostsSortOrder
{
    private $displayOrder;
    private $dateSortOrder;

    public function __construct(Request $request)
    {
        $requestData = $request->attributes->all();

        $this->displayOrder = $requestData['display_order'];
        $this->dateSortOrder = $requestData['date_sort_order'];


    }


    public function sortPostSet($postsSetForSort)
    {
        $sortBy = $this->displayOrder;

        if ($sortBy === "createdAt")
        {
            $order = $this->dateSortOrder;

            if ($order === "DESC")
            {
                usort($postsSetForSort, function($post1, $post2)
                {
                    if ($post1->getCreatedAt() == $post2->getCreatedAt()) return 0;
                    return ($post1->getCreatedAt() > $post2->getCreatedAt()) ? -1 : 1;
                });
            }
            else if ($order === "ASC")
            {
                usort($postsSetForSort, function($post1, $post2)
                {
                    if ($post1->getCreatedAt() == $post2->getCreatedAt()) return 0;
                    return ($post1->getCreatedAt() < $post2->getCreatedAt()) ? -1 : 1;
                });
            }
        }
        else if ($sortBy === "username")
        {
            usort($postsSetForSort, function($post1, $post2)
            {
                if ($post1->getUsername() == $post2->getUsername()) return 0;
                return ($post1->getUsername() < $post2->getUsername()) ? -1 : 1;
            });
        }
        else if ($sortBy === "email")
        {
            usort($postsSetForSort, function($post1, $post2)
            {
                if ($post1->getEmail() == $post2->getEmail()) return 0;
                return ($post1->getEmail() < $post2->getEmail()) ? -1 : 1;
            });
        }

        return $postsSetForSort;
    }


}