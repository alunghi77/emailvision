<?php

class Controller_Sdk extends Controller_Template
{

	public function action_index()
	{
		$view = View::forge('layout');

		$view->content = View::forge('sdk/index');

		return $view;
	}

}



class EmailVisionn {

	

}
