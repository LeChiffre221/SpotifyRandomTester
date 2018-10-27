<?php
/**
 * Created by PhpStorm.
 * User: mthga
 * Date: 08/10/2018
 * Time: 18:39
 */

namespace App\Model;


class Track
{
    private $name;

    private $uri;

    private $image;

    private $durationMs;

    private $durationStr;

    private $artist = [];

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param mixed $uri
     */
    public function setUri($uri): void
    {
        $this->uri = $uri;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image): void
    {
        $this->image = $image;
    }

    /**
     * @return array
     */
    public function getArtist(): array
    {
        return $this->artist;
    }

    /**
     * @return mixed
     */
    public function getDurationMs()
    {
        return $this->durationMs;
    }

    /**
     * @param mixed $durationMs
     */
    public function setDurationMs($durationMs): void
    {
        $this->durationMs = $durationMs;
    }



    /**
     * @param array $artists
     */
    public function setArtist(array $artist): void
    {
        $this->artist = $artist;
    }

    public function addArtist(Artist $artist){
        $this->artist[] = $artist;
    }

    /**
     * @return mixed
     */
    public function getDurationStr()
    {
        $input = $this->durationMs;

        $uSec = $input % 1000;
        $input = floor($input / 1000);

        $seconds = $input % 60;
        $input = floor($input / 60);

        $minutes = $input % 60;
        $input = floor($input / 60);


        return $minutes . ':' . $seconds;
    }

    /**
     * @param mixed $durationStr
     */
    public function setDurationStr($durationStr): void
    {
        $this->durationStr = $durationStr;
    }




}