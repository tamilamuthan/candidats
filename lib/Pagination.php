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

class Pagination
{
	public $paginationVar="pv";
	protected $currentPage=null;
	protected $numPage=null;
	protected $totalItems=null;
	protected $itemsPerPage=20;
	
	public function __construct($totalItems,$currentPage=1,$itemsPerPage=20)
	{
		$this->currentPage=$currentPage;
		$this->itemsPerPage=$itemsPerPage;
		$this->totalItems=$totalItems;
		$this->numPage=ceil($this->totalItems/$this->itemsPerPage);
	}
	public function getNumPage()
	{
		return $this->numPage;
	}
	public function getPrev()
	{
		if($this->currentPage==1) return false;
		return $this->currentPage-1;					
	}
	public function getNext()
	{
		if($this->currentPage==$this->numPage) return false;
		return $this->currentPage+1;					
	}
	public function getFirst()
	{
		return 1;					
	}
	public function getLast()
	{
		return $this->numPage;					
	}
	public function getCurrent()
	{
		return $this->currentPage;					
	}
	public function getNextLink()
	{
		$next=$this->getNext();
		if(empty($next)) return null;
		else return $this->paginationVar."=".$this->getNext();
	}
	public function getPrevLink()
	{
		$prev=$this->getPrev();
		if(empty($prev)) return null;
		else return $this->paginationVar."=".$this->getPrev();
	}
	public function getFirstLink()
	{
		return $this->paginationVar."=".$this->getFirst();
	}
	public function getLastLink()
	{
		return $this->paginationVar."=".$this->getLast();
	}
	public function getNextHidden()
	{
		return "<input type='hidden' name='".$this->paginationVar."' value='".$this->getNext()."' />";
	}
	public function getPrevHidden()
	{
		return "<input type='hidden' name='".$this->paginationVar."' value='".$this->getPrev()."' />";
	}
	public function getFirstHidden()
	{
		return "<input type='hidden' name='".$this->paginationVar."' value='".$this->getFirst()."' />";
	}
	public function getLastHidden()
	{
		return "<input type='hidden' name='".$this->paginationVar."' value='".$this->getLast()."' />";
	}
}
?>