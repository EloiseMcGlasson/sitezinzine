<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class TagsMp3
{

    private ?string $titre = null;
    private ?string $artiste = null;
    private ?string $album = null;
    private ?string $comment = null;
    private ?string $genre = null;
    private ?int $recordingTime = null;
    private ?string $language = null;
    private ?string $publisher = null;
    private ?int $year = null;

    #[Assert\File(
        extensions: ['png'],
        extensionsMessage: 'Please upload a valid png logo file',
    )]
    private ?File $logo = null;



    /**
     * Get the value of titre
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set the value of titre
     *
     * @return  self
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get the value of artiste
     */
    public function getArtiste()
    {
        return $this->artiste;
    }

    /**
     * Set the value of artiste
     *
     * @return  self
     */
    public function setArtiste($artiste)
    {
        $this->artiste = $artiste;

        return $this;
    }

    /**
     * Get the value of album
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * Set the value of album
     *
     * @return  self
     */
    public function setAlbum($album)
    {
        $this->album = $album;

        return $this;
    }

    /**
     * Get the value of comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set the value of comment
     *
     * @return  self
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get the value of genre
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set the value of genre
     *
     * @return  self
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * Get the value of recordingTime
     */
    public function getRecordingTime()
    {
        return $this->recordingTime;
    }

    /**
     * Set the value of recordingTime
     *
     * @return  self
     */
    public function setRecordingTime($recordingTime)
    {
        $this->recordingTime = $recordingTime;

        return $this;
    }

    /**
     * Get the value of language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set the value of language
     *
     * @return  self
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get the value of publisher
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * Set the value of publisher
     *
     * @return  self
     */
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * Get the value of year
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set the value of year
     *
     * @return  self
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get the value of logo
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set the value of logo
     *
     * @return  self
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }
}
