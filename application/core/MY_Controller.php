<?php
class MY_Controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->output->set_header('Content-Type: text/html; charset=utf-8');
	}
}
?>