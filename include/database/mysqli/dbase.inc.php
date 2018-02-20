<?php
/*************************
  Coppermine Photo Gallery
  ************************
  Copyright (c) 2003-2016 Coppermine Dev Team
  v1.0 originally written by Gregory Demar

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License version 3
  as published by the Free Software Foundation.

  ********************************************
  Coppermine version: 1.6.03
  $HeadURL$
**********************************************/

/** MySQLi database implementation **/

class CPG_Dbase
{
	public $db_type = 'MySQLi';
	protected $dbobj = null;
	protected $connected = false;
	protected $errnum = 0;
	protected $error = '';

	public function __construct ($cfg)
	{
		$obj = new mysqli($cfg['dbserver'], $cfg['dbuser'], $cfg['dbpass'], $cfg['dbname']);

		if ($obj) {
			$this->dbobj = $obj;
			if (!mysqli_connect_error()) {
				$this->connected = true;
				if (!empty($cfg['dbcharset'])) {
					$obj->real_query("SET NAMES '{$cfg['dbcharset']}'");
				}
			}
		}
		$this->errnum = mysqli_connect_errno();
		$this->error = mysqli_connect_error();
	}

	public function query ($sql)
	{
		$rslt = $this->dbobj->query($sql);
		if ($rslt === true) return true;
		if ($rslt) {
			return new CPG_DbaseResult($rslt);
		} else {
			return false;
		}
	}

	public function execute ()
	{
		// not currently used
	}

	public function isConnected ()
	{
		return $this->connected;
	}

	public function getError ($code=false, $last=false)
	{
		if (!$last) {
			$this->errnum = $this->dbobj->errno;
			$this->error = $this->dbobj->error;
		}
		if ($code) {
			return $this->errnum;
		} else {
			return $this->errnum . ' : ' . $this->error;
		}
	}

	public function escapeStr ($str)
	{
		return $this->dbobj->real_escape_string($str);
	}

	public function insertId ()
	{
		return $this->dbobj->insert_id;
	}

	public function affectedRows ()
	{
		return $this->dbobj->affected_rows;
	}

}

class CPG_DbaseResult
{
	protected $robj = null;

	public function __construct ($rslt)
	{
		$this->robj = $rslt;
	}

	public function fetchRow ($free=false)
	{
		$dat = $this->robj->fetch_row();
		if ($free) $this->free();
		return $dat;
	}

	public function fetchAssoc ($free=false)
	{
		$dat = $this->robj->fetch_assoc();
		if ($free) $this->free();
		return $dat;
	}

	public function fetchAssocAll ($free=false)
	{
		// not currently used
	}

	public function fetchArray ($free=false)
	{
		$dat = $this->robj->fetch_array();
		if ($free) $this->free();
		return $dat;
	}

	public function result ($row=0, $fld=0, $free=false)
	{
		$return = null;
		if ($this->robj->data_seek($row)) {
			$row = $this->robj->fetch_array();
			$return = $row[$fld];
		}
		if ($free) $this->free();
		return $return;
	}

	public function numRows ($free=false)
	{
		$num = $this->robj->num_rows;
		if ($free) $this->free();
		return $num;
	}

	public function free ()
	{
		if (is_object($this->robj)) $this->robj->free();
		$this->robj = null;
	}

}
