<?php

namespace CoreUHC\events;

use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;

class BorderListener{

    private $x;
    private $z;

    private $radius;

    private $maxX;
    private $maxZ;

    private $minX;
    private $minZ;

    private $safeBlocks;
    private $unsafeBlocks;

    public function __construct($x, $z, $radius){

        $this->x = $x;
        $this->z = $z;

        $this->maxX = $x + $radius;
        $this->minX = $x - $radius;

        $this->maxZ = $z + $radius;
        $this->minZ = $z - $radius;

        $this->radius = $radius;

        $this->safeBlocks = [
            0, 6, 8, 9, 27, 30, 31, 32, 37,
            38, 39, 40, 50, 59, 63, 64, 65,
            66, 68, 71, 78, 83, 104, 105, 106,
            141, 142, 171, 244
        ];

        $this->unsafeBlocks = [10, 11, 51, 81];
    }

    public function getX(){
        return $this->x;
    }

    public function getZ(){
        return $this->z;
    }

    public function setX($x){
        $this->x = $x;
        $this->maxX = $x + $this->radius;
        $this->minX = $x - $this->radius;
    }

    public function setZ($z){
        $this->z = $z;
        $this->maxZ = $z + $this->radius;
        $this->minZ = $z - $this->radius;
    }

    public function setRadiusX($radius){
        $this->radius = $radius;
        $this->maxX = $this->x + $radius;
        $this->minX = $this->x - $radius;
    }

    public function setRadiusZ($radius){
        $this->radius = $radius;
        $this->maxZ = $this->z + $radius;
        $this->minZ = $this->z - $radius;
    }

    public function changeBorder($x, $z, $radius){
        $this->x = $x;
        $this->z = $z;

        $this->maxX = $x + $radius;
        $this->minX = $x - $radius;

        $this->maxZ = $z + $radius;
        $this->minZ = $z - $radius;

        $this->radius = $radius;
    }

    public function insideBorder($x, $z){
        return !($x < $this->minX or $x > $this->maxX or $z < $this->minZ or $z > $this->maxZ);
    }

    public function correctPosition($location){

        $knockback = 2.0;

        $x = $location->getX();
        $z = $location->getZ();
        $y = $location->getY();

        if($x <= $this->minX){
            $x = $this->minX + $knockback;
        }
        elseif($x >= $this->maxX){
            $x = $this->maxX - $knockback;
        }

        if($z <= $this->minZ){
            $z = $this->minZ + $knockback;
        }
        elseif($z >= $this->maxZ){
            $z = $this->maxZ - $knockback;
        }

        $y = $this->findSafeY($location->getLevel(), $x, $y, $z);

        if($y < 10){
            $y =  70;
        }
        return new \pocketmine\math\Vector3($x, $y, $z);
    }

    private function findSafeY(Level $level, $x, $y, $z){

        $top = $level->getHeightMap($x, $z) - 2;
        $bottom = 1;

        for($y1 = $y, $y2 = $y; ($y1 > $bottom) or ($y2 < $top); $y1--, $y2++){

            if($y1 > $bottom){
                if($this->isSafe($level, $x, $y1, $z)) return $y1;
            }

            if($y2 < $top and $y2 != $y1){
                if($this->isSafe($level, $x, $y2, $z)) return $y2;
            }

        }

        return -1;

    }

    private function isSafe(Level $level, $x, $y, $z){

        $safe = in_array($level->getBlockIdAt($x, $y, $z), $this->safeBlocks) && in_array($level->getBlockIdAt($x, $y + 1, $z), $this->safeBlocks);

        if(!$safe) return $safe;

        $below = $level->getBlockIdAt($x, $y - 1, $z);

        return ($safe and (!in_array($below, $this->safeBlocks) or $below === 8 or $below === 9) and !in_array($below, $this->unsafeBlocks));

    }

}