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
    
    namespace Wobeto\EasyChart;

    use Wobeto\EasyChart\Model\ChartConfig;
    use Wobeto\EasyChart\View\Plot\Plot;
    use Wobeto\EasyChart\Model\XYDataSet;
    use Wobeto\EasyChart\Model\XYSeriesDataSet;

    abstract class Chart {
        /**
         * The chart configuration.
         */
        protected $config;
    
        /**
         * The data set.
         */
        protected $dataSet;
        /**
         * Format label values
         */
        protected $labelFormat;
    
        /**
         * Plot (holds graphical attributes).
         */
        protected $plot;

        protected $vertical_axis = array();

        /**
         * Abstract constructor of Chart.
         *
         * @param integer width of the image
         * @param integer height of the image
         */
        protected function __construct($width, $height) {
            // Initialize the configuration
            $this->config = new ChartConfig();
            
            // Creates the plot
            $this->plot = new Plot($width, $height);
        }

        /**
         * Checks the data model before rendering the graph.
         */
        protected function checkDataModel() {
            // Check if a dataset was defined
            if (!$this->dataSet) {
                throw new Exception("No dataset defined", 1);
                
            }
            
            // Maybe no points are defined, but that's ok. This will yield and empty graph with default boundaries.
        }
        
        /**
         * Create the image.
         */
        protected function createImage() {
            $this->plot->createImage();
        }

        /**
         * Sets the data set.
         *
         * @param dataSet The data set
         */
        public function setDataSet($dataSet) {
            if($dataSet instanceof XYDataSet OR $dataSet instanceof XYSeriesDataSet){
                $this->dataSet = $dataSet;    
            }
        }

        public function setLabelFormat($format){
            if(is_string($format)){
                $this->labelFormat = $format;    
            }
        }
        
        /**
         * Return the chart configuration.
         *
         * @return configuration : ChartConfig
         */
        public function getConfig() {
            return $this->config;
        }
        
        /**
         * Return the plot.
         *
         * @return plot
         */
        public function getPlot() {
            return $this->plot;
        }
        
        /**
         * Sets the title.
         *
         * @param string New title
         */
        public function setTitle($title) {
            $this->plot->setTitle($title);
        }
    }