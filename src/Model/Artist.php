<?php
/**
 * Created by PhpStorm.
 * User: mthga
 * Date: 08/10/2018
 * Time: 18:39
 */

namespace App\Model;


class Artist
{
    private $name;

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


}