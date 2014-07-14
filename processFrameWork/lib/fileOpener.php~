<?php
/***************************************************************************
 * 
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file fileOpener.php
 * @author suqian(com@baidu.com)
 * @date 2014/06/26 14:58:25
 * @brief 
 *  
 **/
class fileOpener
{
	public function open($filelocation)
	{
		if(is_dir($filelocation)) {
			return new DirectoryIterator($filelocation);	
		}
		if (is_file($filelocation)) {
			return new FileIterator($filelocation);
		}
		return false;
	}

	public function awkopen($filelocation, $delimiter="\t")
	{
		if(is_file($filelocation))
		{
			return new fileIteratorArr($filelocation, $delimiter);
		}
		return false;
	}
}

class fileIterator implements Iterator
{
    protected  $fp;
    protected  $line_num;
    protected  $line;

    public function __construct($filename) 
    {
		$fp = fopen($filename, 'r');
        if (!$fp) {
            throw new Exception("file ".$filename." is not exists");
        }
		$this->fp = $fp;
    }

    public function key()
	{
        return $this->line_num;
    }

    public function current()
	{
        return $this->line;
    }

    public function rewind()
	{
        rewind($this->fp);
    }

    public function next()
	{
    }

    public function valid()
	{
		$this->line = fgets($this->fp);
		if ($this->line !==false) {
			$this->line_num++;
			return true;
		}
		return false;
    }
}

class fileIteratorArr extends fileIterator
{
	protected $delimiter = "\t";

	public function __construct($filename, $delimiter="\t", $dotrim=true) 
	{
		parent::__construct($filename);
		$this->delimiter = $delimiter;
		$this->dotrim    = $dotrim;
	}

	public function current()
	{
		$line = parent::current();
		$line_arr = explode($this->delimiter, $line);
		if($this->dotrim) {
			array_walk($line_arr, 'trim');
		}
		array_unshift($line_arr, $line);
		return $line_arr;
	}
}

