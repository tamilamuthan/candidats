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
    
    namespace Wobeto\EasyChart\View\Primitive;

    class Padding {
        /**
         * Top padding.
         */
        public $top;
        
        /**
         * Right padding.
         */
        public $right;
        
        /**
         * Bottom padding.
         */
        public $bottom;
    
        /**
         * Left padding.
         */
        public $left;

        /**
         * Creates a new padding.
         *
         * @param integer Top padding
         * @param integer Right padding
         * @param integer Bottom padding
         * @param integer Left padding
         */
        public function __construct($top, $right = null, $bottom = null, $left = null) {
            $this->top = $top;
            if ($right == null) {
                $this->right  = $top;
                $this->bottom = $top;
                $this->left   = $top;
            } else {
                $this->right  = $right;
                $this->bottom = $bottom;
                $this->left   = $left;
            }
        }
    }