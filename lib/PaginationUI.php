<?php
/**************************************************************************
 * Naanal PHP Framework, Simple, Efficient and Developer Friendly
 * Ver 3.0, Copyright (C) <2010>  <Tamil Amuthan. R>
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
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ************************************************************************/

class PaginationUI extends Pagination
{
	public function __construct($totalItems,$currentPage=1,$itemsPerPage=20)
	{
		parent::__construct($totalItems,$currentPage,$itemsPerPage);
	}
	public function getUIPrevLink($href,$caption="prev",$param="")
	{
		$prevlink=$this->getPrevLink();
		if(empty($prevlink)) return $caption;
		$where="&amp;".$this->getPrevLink();
		$link="<a {$param} class='pagination-prev' href='{$href}{$where}'>{$caption}</a>";
		return $link;
	}
	public function getUINextLink($href,$caption="next",$param="")
	{
		$nextlink=$this->getNextLink();
		if(empty($nextlink)) return $caption;
		$where="&amp;".$this->getNextLink();
		$link="<a {$param} class='pagination-next' href='{$href}{$where}'>{$caption}</a>";
		return $link;
	}
	public function getUIFirstLink($href,$caption="first",$param="")
	{
		$where="&amp;".$this->getFirstLink(); 
		$link="<a {$param} class='pagination-first' href='{$href}{$where}'>{$caption}</a>";
		return $link;
	}
	public function getUILastLink($href,$caption="last",$param="")
	{
		$where="&amp;".$this->getLastLink();
		$link="<a {$param} class='pagination-last' href='{$href}{$where}'>{$caption}</a>";
		return $link;
	}
}
?>