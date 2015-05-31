<?php
class User_Model extends Model {
	public function getUserLists()
	{
		$sql = "select name, age from user";
		$sth = $this->_link->prepare($sql);
		$sth->execute();
		$ret = $sth->fetchAll(PDO::FETCH_ASSOC);
		return $ret;
	}
}
