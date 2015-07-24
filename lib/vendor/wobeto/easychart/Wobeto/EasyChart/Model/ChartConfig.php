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
        
    class ChartConfig {
        /**
         * Use several colors for a single data set chart (as if it was a multiple data set).
         * 
         * @var Boolean
         */
        private $useMultipleColor = false;
        
        /**
         * Show caption on individual data points.
         * 
         * @var Boolean
         */
        private $showPointCaption = true;
        
        /**
         * Sort data points (only pie charts).
         * 
         * @var Boolean
         */
        private $sortDataPoint = true;
        
        /**
         * If true the chart will use several colors for a single data set chart
         * (as if it was a multiple data set).
         * 
         * @param $useMultipleColor Use several colors : boolean
         */
        public function setUseMultipleColor($useMultipleColor) {
            $this->useMultipleColor = $useMultipleColor;
        }
        
        /**
         * If true the chart will use several colors for a single data set chart
         * (as if it was a multiple data set).
         * 
         * @return $useMultipleColor Use several colors : boolean
         */
        public function getUseMultipleColor() {
            return $this->useMultipleColor;
        }
        
        /**
         * Set the option to show caption on individual data points.
         * 
         * @param $showPointCaption Show caption on individual data points : boolean
         */
        public function setShowPointCaption($showPointCaption) {
            $this->showPointCaption = $showPointCaption;
        }
        
        /**
         * Get the option to show caption on individual data points.
         * 
         * @return Show caption on individual data points : boolean
         */
        public function getShowPointCaption() {
            return $this->showPointCaption;
        }
        
        /**
         * Set the option to sort data points (only pie charts).
         * 
         * @param $sortDataPoint Sort data points : boolean
         */
        public function setSortDataPoint($sortDataPoint) {
            $this->sortDataPoint = $sortDataPoint;
        }
        
        /**
         * Get the option to sort data points (only pie charts).
         * 
         * @return Sort data points : boolean
         */
        public function getSortDataPoint() {
            return $this->sortDataPoint;
        }
    }
?>