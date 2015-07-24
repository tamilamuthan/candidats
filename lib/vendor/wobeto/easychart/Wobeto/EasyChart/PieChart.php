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

    use Wobeto\EasyChart\View\Caption\Caption;
    use Wobeto\EasyChart\View\Primitive\Padding;

    class PieChart extends Chart {
        protected $pieCenterX;
        protected $pieCenterY;

        protected $labelFormat = '%01.2f';
        protected $showValues  = false;
    
        /**
         * Constructor of a pie chart.
         *
         * @param integer width of the image
         * @param integer height of the image
         */
        public function __construct($width = 600, $height = 250) {
            parent::__construct($width, $height);
            $this->plot->setGraphPadding(new Padding(15, 10, 30, 30));
        }

        public function showValues($boolean){
            if(is_bool($boolean)){
                $this->showValues = $boolean;
            }
        }

        /**
         * Computes the layout.
         */
        protected function computeLayout() {
            $this->plot->setHasCaption(true);
            $this->plot->computeLayout();
            
            // Get the graph area
            $graphArea = $this->plot->getGraphArea();

            // Compute the coordinates of the pie
            $this->pieCenterX = $graphArea->x1 + ($graphArea->x2 - $graphArea->x1) / 2;
            $this->pieCenterY = $graphArea->y1 + ($graphArea->y2 - $graphArea->y1) / 2;

            $this->pieWidth = round(($graphArea->x2 - $graphArea->x1) * 4 / 5);
            $this->pieHeight = round(($graphArea->y2 - $graphArea->y1) * 3.7 / 5);
            $this->pieDepth = round($this->pieWidth * 0.05);
        }
        
        /**
         * Compute pie values in percentage and sort them.
         */
        protected function computePercent() {
            $this->total = 0;
            $this->percent = array();

            $pointList = $this->dataSet->getPointList();
            foreach ($pointList as $point) {
                $this->total += $point->getY();
            }

            foreach ($pointList as $point) {
                $percent = $this->total == 0 ? 0 : 100 * $point->getY() / $this->total;
                array_push($this->percent, array($percent, $point, $point->getY()));
            }

            // Sort data points
            if ($this->config->getSortDataPoint()) {
                usort($this->percent, function($v1, $v2) {
                    return $v1[0] == $v2[0] ? 0 :
                        $v1[0] > $v2[0] ? -1 :1;
                });
            }
        }

        /**
         * Renders the caption.
         */
        protected function printCaption() {
            // Create a list of labels
            $labelList = array();
            foreach($this->percent as $percent) {
                list($percent, $point) = $percent;
                $label = $point->getX();
                
                array_push($labelList, $label);
            }
            
            // Create the caption
            $caption = new Caption($this->plot);
            $caption->setLabelList($labelList);
            
            $palette = $this->plot->getPalette();
            $pieColorSet = $palette->pieColorSet;
            $caption->setColorSet($pieColorSet);

            // Render the caption
            $caption->render();
        }

        /**
         * Draw a 2D disc.
         *
         * @param integer Center coordinate (y)
         * @param array Colors for each portion
         * @param bitfield Drawing mode
         */
        protected function drawDisc($cy, $colorArray, $mode) {
            // Get graphical obects
            $img = $this->plot->getImg();

            $i = 0;
            $oldAngle = 0;
            $percentTotal = 0;

            foreach ($this->percent as $a) {
                list ($percent, $point) = $a;

                // If value is null, don't draw this arc
                if ($percent <= 0) {
                    continue;
                }
                
                $color        = $colorArray[$i % count($colorArray)];                
                $percentTotal += $percent;
                $newAngle     = $percentTotal * 360 / 100;

                // imagefilledarc doesn't like null values (#1)
                if ($newAngle - $oldAngle >= 1) {
                    imagefilledarc($img, $this->pieCenterX, $cy, $this->pieWidth, $this->pieHeight, $oldAngle, $newAngle, $color->getColor($img), $mode);
                }

                $oldAngle = $newAngle;
                $i++;
            }
        }

        /**
         * Print the percentage text.
         */
        protected function drawPercent() {
            // Get graphical obects
            $img = $this->plot->getImg();
            $palette = $this->plot->getPalette();
            $text = $this->plot->getText();
            $primitive = $this->plot->getPrimitive();
            
            $angle1       = 0;
            $percentTotal = 0;

            // var_dump($this->percent);

            foreach ($this->percent as $a) {
                list ($percent, $point, $total) = $a;

                // If value is null, the arc isn't drawn, no need to display percent
                if ($percent <= 0) {
                    continue;
                }

                $percentTotal += $percent;
                $angle2 = $percentTotal * 2 * M_PI / 100;

                $angle = $angle1 + ($angle2 - $angle1) / 2;

                if($this->showValues === true){
                    $label = sprintf($this->labelFormat, $total);
                }else{
                    $label = sprintf($this->labelFormat, $percent);    
                }

                $x = cos($angle) * ($this->pieWidth + 35) / 2 + $this->pieCenterX;
                $y = sin($angle) * ($this->pieHeight + 35) / 2 + $this->pieCenterY;

                $text->printText($img, $x, $y, $this->plot->getTextColor(), $label, $text->fontCondensed, $text->HORIZONTAL_CENTER_ALIGN | $text->VERTICAL_CENTER_ALIGN);

                $angle1 = $angle2;
            }
        }

        /**
         * Print the pie chart.
         */
        protected function printPie() {
            // Get graphical obects
            $img = $this->plot->getImg();
            $palette = $this->plot->getPalette();
            $text = $this->plot->getText();
            $primitive = $this->plot->getPrimitive();

            // Get the pie color set
            $pieColorSet = $palette->pieColorSet;
            $pieColorSet->reset();

            // Top
            $this->drawDisc($this->pieCenterY - $this->pieDepth / 2, $palette->pieColorSet->colorList, IMG_ARC_PIE);

            // Top Outline
            if ($this->config->getShowPointCaption()) {
                $this->drawPercent();
            }
        }

        /**
         * Render the chart image.
         *
         * @param string name of the file to render the image to (optional)
         */
        public function render($fileName = null) {
            $this->computePercent();
            $this->computeLayout();
            $this->createImage();
            $this->plot->printTitle();
            $this->printPie();
            $this->printCaption();

            return $this->plot->render($fileName);
        }
    }