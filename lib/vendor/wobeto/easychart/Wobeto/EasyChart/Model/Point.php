<?php

    /* EasyChart - PHP chart library based in Libchart
     * Copyright (C) 2015 Fernando Wobeto (fernandowobeto@gmail.com)
     * 
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or
     * (at your option) any later version.
     * 
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     */

    namespace Wobeto\EasyChart\Model;
    
    class Point {
        private $x;
        private $y;
    
        /**
         * Creates a new sampling point of coordinates (x, y)
         *
         * @param integer x coordinate (label)
         * @param integer y coordinate (value)
         */
        public function __construct($x, $y){
            $this->x = $x;
            $this->y = $y;            
        }

        /**
         * Gets the x coordinate (label).
         *
         * @return integer x coordinate (label)
         */
        public function getX() {
            return $this->x;
        }

        /**
         * Gets the y coordinate (value).
         *
         * @return integer y coordinate (value)
         */
        public function getY() {
            return $this->y;
        }
    }
?>