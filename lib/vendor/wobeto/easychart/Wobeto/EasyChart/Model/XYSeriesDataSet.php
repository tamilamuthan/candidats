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

    use Wobeto\EasyChart\Model\XYDataSet;
    
    class XYSeriesDataSet extends DataSet {
        /**
         * List of titles
         */
        private $titleList = array();
    
        /**
         * List of XYDataSet.
         */
        private $serieList = array();
    
        /**
         * Add a new serie to the dataset.
         *
         * @param string Title (label) of the serie.
         * @param XYDataSet Serie of points to add
         */
        public function addSerie($title, XYDataSet $serie) {
            array_push($this->titleList, $title);
            array_push($this->serieList, $serie);
        }
        
        /**
         * Getter of titleList.
         *
         * @return List of titles.
         */
        public function getTitleList() {
            return $this->titleList;
        }

        /**
         * Getter of serieList.
         *
         * @return List of series.
         */
        public function getSerieList() {
            return $this->serieList;
        }
    }
?>