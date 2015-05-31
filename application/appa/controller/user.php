<?php
class User_Controller extends Controller {
	public function show()
	{
		$t = array(
			array('name'=>'yrt','age'=>23),
			array('name'=>'zx','age'=>29),
		);
		$this->assignVal('users', $t);
		return $this->render('userlist.tpl');
	}
	
	public function test()
	{
		$model = Model::getInstance('testappa.user');
		$users = $model->getUserLists();
		$this->assignVal('users', $users);
		return $this->render('userlist.tpl');
	}
}
