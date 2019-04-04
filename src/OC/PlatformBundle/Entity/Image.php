<?php

namespace OC\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Image
 *
 * @ORM\Table(name="oc_image")
 * @ORM\Entity(repositoryClass="OC\PlatformBundle\Repository\ImageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Image
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="alt", type="string", length=255)
     */
    private $alt;

    private $file;

    //Stock le nom du fichier temporaire
    private $tempFilename;
    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return Image
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set alt.
     *
     * @param string $alt
     *
     * @return Image
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt.
     *
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }

	public function getFile()
	{
		return $this->file;
	}

	//Se charge de la sauvegarde du fichier
	public function setFile(UploadedFile $file = null)
	{
		$this->file = $file;

		// On vérifie si on avait déjà un fichier pour cette entité
		if (null !== $this->url) {
			// On sauvegarde l'extension du fichier pour le supprimer plus tard
			$this->tempFilename = $this->url;
			// On réinitialise les valeurs des attributs url et alt
			$this->url = null;
			$this->alt = null;
		}
	}

	/**
	 * @ORM\PrePersist()
	 * @ORM\PreUpdate()
	 */
	public function preUpload()
	{
		if (null === $this->file) {
			return;
		}

		$this->url = $this->file->guessExtension();

		$this->alt = $this->file->getClientOriginalName();
	}

	/**
	 * @ORM\PostPersist()
	 * @ORM\PostUpdate()
	 */
	public function upload()
	{
		if (null === $this->file) {
			return;
		}

		if (null !== $this->tempFilename) {
			$oldFIle = $this->getUploadRootDir() . '/' . $this->id . '.' . $this->tempFilename;
			if (file_exists($oldFIle)) {
				unlink($oldFIle);
			}
		}

		$this->file->move(
			$this->getUploadRootDir(),
			$this->id . '.' . $this->url
		);
	}

	/**
	 * @ORM\PreRemove()
	 */
	public function preRemoveUpload()
	{
		$this->tempFilename = $this->getUploadRootDir() . '/' . $this->id . '.' . $this->url;
	}

	/**
	 * @ORM\PostRemove()
	 */
	public function removeUpload()
	{
		if (file_exists($this->tempFilename)) {
			unlink($this->tempFilename);
		}
	}

	/**
	 * @return string
	 */
	public function getUploadDir()
	{
		// On retourne le chemin relatif vers l'image pour un navigateur (relatif au répertoire /web donc)
		return 'uploads/img';
	}

	/**
	 * @return string
	 */
	protected function getUploadRootDir()
	{
		// On retourne le chemin relatif vers l'image pour notre code PHP
		return __DIR__.'/../../../../web/'.$this->getUploadDir();
	}

	/**
	 * @return string
	 */
	public function getWebPath()
	{
		return $this->getUploadDir().'/'.$this->getId().'.'.$this->getUrl();
	}
}
