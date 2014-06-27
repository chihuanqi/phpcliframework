<?php
/***************************************************************************
 * 
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file fopen.php
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
			return new DirIterator($filelocation);	
		}
		if (is_file($filelocation)) {
			return new FileIterator($filelocation);
		}
		return false;
	}
}

class FileIterator implements Iterator
{
    private  $fp;
    private  $line_num;
    private  $line;

    public function __construct($filename) 
    {
		$fp = @fopen($filename, 'r');
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
		$this->line_num++;
        return $this->line != false;
    }

}

