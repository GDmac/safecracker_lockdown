<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Safecracker_lockdown_lib {

	private $timeout = 900;

	public function __construct()
	{
		$this->EE =& get_instance();

		// start PHP session
		if (!isset($_SESSION))
		{
			session_start();
		}
	}
	
	// ----------------------------------------------------------------
	public function verify_lockdown_id()
	{
		$lockdown_id = $this->EE->input->post('lockdown_id');

		return isset($_SESSION['SC_lockdown'][$lockdown_id]);
	}


	// ----------------------------------------------------------------
	public function create_lockdown_session($identifier = false)
	{
		if ($identifier)
		{
			$lockdown_id = md5($identifier);
		}
		else
		{
			$lockdown_id = md5(uniqid('SC', true));
		}

		$_SESSION['SC_lockdown'][$lockdown_id] = array(
			'time' => time(),
		);

		return $lockdown_id;
	}

	// ----------------------------------------------------------------
	public function reset_lockdown_session($lockdown_id)
	{
		if (isset($_SESSION['SC_lockdown'][$lockdown_id]))
		{
			$_SESSION['SC_lockdown'][$lockdown_id] = array(
				'time' => time(),
			);

			return true;
		}

		return false;
	}

	// ----------------------------------------------------------------
	public function delete_lockdown_session($lockdown_id)
	{
		if (isset($_SESSION['SC_lockdown'][$lockdown_id]))
		{
			unset($_SESSION['SC_lockdown'][$lockdown_id]);
		}

	}

	// ----------------------------------------------------------------
	public function garbage_collection()
	{
		foreach ($_SESSION['SC_lockdown'] as $lockdown_id => $value)
		{
			if (isset($_SESSION['SC_lockdown'][$lockdown_id]['time']) 
				&& $_SESSION['SC_lockdown'][$lockdown_id]['time'] < (time() - $this->timeout))
			{
				unset($_SESSION['SC_lockdown'][$lockdown_id]);
			}
		}
	}

}


/* End of file safecracker_lockdown_lib.php */
