<?php
/**
 * Created by PhpStorm.
 * User: mthga
 * Date: 08/10/2018
 * Time: 18:36
 */

namespace App\Model;


class PlayBack
{
    private $userId;

    private $userName;

    private $userPhoto;

    private $track;

    private $repeat;

    private $shuffle;

    private $playing;

    private $context;

    private $positionMs;

    private $positionStr;

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param mixed $userName
     */
    public function setUserName($userName): void
    {
        $this->userName = $userName;
    }

    /**
     * @return mixed
     */
    public function getUserPhoto()
    {
        return $this->userPhoto;
    }

    /**
     * @param mixed $userPhoto
     */
    public function setUserPhoto($userPhoto): void
    {
        $this->userPhoto = $userPhoto;
    }

    /**
     * @return Track
     */
    public function getTrack()
    {
        return $this->track;
    }

    /**
     * @param Track $track
     */
    public function setTrack(Track $track): void
    {
        $this->track = $track;
    }

    /**
     * @return mixed
     */
    public function getRepeat()
    {
        return $this->repeat;
    }

    /**
     * @param mixed $repeat
     */
    public function setRepeat($repeat): void
    {
        $this->repeat = $repeat;
    }

    /**
     * @return mixed
     */
    public function getShuffle()
    {
        return $this->shuffle;
    }

    /**
     * @param mixed $shuffle
     */
    public function setShuffle($shuffle): void
    {
        $this->shuffle = $shuffle;
    }

    /**
     * @return mixed
     */
    public function getPlaying()
    {
        return $this->playing;
    }

    /**
     * @param mixed $playing
     */
    public function setPlaying($playing): void
    {
        $this->playing = $playing;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param Context $context
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return mixed
     */
    public function getPositionMs()
    {
        return $this->positionMs;
    }

    /**
     * @param mixed $positionMs
     */
    public function setPositionMs($positionMs): void
    {
        $this->positionMs = $positionMs;
    }

    /**
     * @return mixed
     */
    public function getPositionStr()
    {
        $input = $this->positionMs;

        $uSec = $input % 1000;
        $input = floor($input / 1000);

        $seconds = $input % 60;
        $input = floor($input / 60);

        $minutes = $input % 60;
        $input = floor($input / 60);


        return $minutes . ':' . $seconds;
    }

    /**
     * @param mixed $positionStr
     */
    public function setPositionStr($positionStr): void
    {
        $this->positionStr = $positionStr;
    }




}