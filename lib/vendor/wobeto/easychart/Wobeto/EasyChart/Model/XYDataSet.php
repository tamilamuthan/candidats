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

    use Wobeto\EasyChart\Model\Point;
    
    class XYDataSet extends DataSet {
        private $pointList = array();
    
        /**
         * Add a new point to the dataset.
         *
         * @param Point Point to add to the dataset
         */
        
        public function addPoint(Point $point) {
            array_push($this->pointList, $point);
        }

        /**
         * Getter of pointList.
         *
         * @return List of points.
         */
        public function getPointList() {
            return $this->pointList;
        }
    }
?>