<?php


namespace App\Service;


use App\Entity\Post;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileHandleService
{
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    /**
     * @param Post $post
     * @param UploadedFile $brochureFile
     * @param string $wayToPlace
     */
    public function handleBrochureFile($post, $brochureFile, $wayToPlace)
    {
        $newBrochureFilename = $this->getNewFilename($brochureFile);
        $this->handleFile($brochureFile, $newBrochureFilename, $wayToPlace);
        if ($post->getBrochureFilename())
            @unlink($this->parameterBag->get($wayToPlace).'/'.$post->getBrochureFilename());
        $post->setBrochureFilename($newBrochureFilename);
    }

    /**
     * @param Post $post
     * @param UploadedFile $imageFile
     * @param string $wayToPlace
     */
    public function handleImageFile($post, $brochureFile, $wayToPlace)
    {
        $newBrochureFilename = $this->getNewFilename($brochureFile);
        $this->handleFile($brochureFile, $newBrochureFilename, $wayToPlace);
        if ($post->getImageFilename())
            @unlink($this->parameterBag->get($wayToPlace).'/'.$post->getImageFilename());
        $post->setImageFilename($newBrochureFilename);
    }

    /**
     * @param UploadedFile $brochureFile
     * @param string $wayToPlace
     * @return void
     */
    private function handleFile($uploadedFile, $newFilename, $wayToPlace): void
    {
        try
        {
            $uploadedFile->move(
                $this->parameterBag->get($wayToPlace),
                $newFilename
            );
        }
        catch (FileException $e)
        {
            throw new FileException("Error: the file can't be uploaded");
        }
    }

    /**
     * @param $uploadedFile $uploadedFile
     * @return string $newFilename
     */
    private function getNewFilename($uploadedFile): string
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9] remove; Lower()', $originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

        return $newFilename;
    }

}