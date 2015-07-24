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

    namespace Wobeto\EasyChart\View\Caption;

    use Wobeto\EasyChart\View\Plot\Plot;
    use Wobeto\EasyChart\View\Color\ColorSet;

    class Caption {
        protected $labelBoxWidth = 15;
        protected $labelBoxHeight = 15;
    
        // Plot
        protected $plot;
        
        // Label list
        protected $labelList;
        
        // Color set
        protected $colorSet;

        public function __construct(Plot $plot){
            $this->plot = $plot;
        }
        
        /**
         * Render the caption.
         */
        public function render() {
            // Get graphical obects
            $img         = $this->plot->getImg();
            $palette     = $this->plot->getPalette();
            $text        = $this->plot->getText();
            $primitive   = $this->plot->getPrimitive();
            
            // Get the caption area
            $captionArea = $this->plot->getCaptionArea();
            
            // Get the pie color set
            $colorSet    = $this->colorSet;
            $colorSet->reset();
            
            $i = 0;
            foreach ($this->labelList as $label) {
                // Get the next color
                $color = $colorSet->currentColor();
                $colorSet->next();

                $boxX1 = $captionArea->x1;
                $boxX2 = $boxX1 + $this->labelBoxWidth;
                $boxY1 = $captionArea->y1 + 5 + $i * ($this->labelBoxHeight + 5);
                $boxY2 = $boxY1 + $this->labelBoxHeight;

                $primitive->outlinedBox($boxX1, $boxY1, $boxX2, $boxY2, $palette->axisColor[0], $palette->axisColor[1]);
                imagefilledrectangle($img, $boxX1, $boxY1, $boxX2, $boxY2, $color->getColor($img));

                $text->printText($img, $boxX2 + 5, $boxY1 + $this->labelBoxHeight / 2, $this->plot->getTextColor(), $label, $text->fontCondensed, $text->VERTICAL_CENTER_ALIGN);

                $i++;
            }
        }
        
        /**
         * Sets the label list.
         *
         * @param Array label list
         */
        public function setLabelList($labelList) {
            $this->labelList = $labelList;
        }        
        
        /**
         * Sets the color set.
         *
         * @param Array Color set
         */
        public function setColorSet(ColorSet $colorSet) {
            $this->colorSet = $colorSet;
        }
    }