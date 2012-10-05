<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


$plugin_info = array(
	'pi_name'		=> 'Safecracker Lockdown',
	'pi_version'	=> '1.0',
	'pi_author'		=> 'GDmac',
	'pi_author_url'	=> 'http://github.com/GDmac',
	'pi_description'=> 'Lockdown ExpressionEngine SafeCracker rules and fields',
	'pi_usage'		=> Safecracker_lockdown::usage()
);


// ----------------------------------------------------------------

class Safecracker_lockdown {

	public $return_data;
    
	// ----------------------------------------------------------------
	public function __construct()
	{
		$this->EE =& get_instance();
		$tagdata = $this->EE->TMPL->tagdata;

		// start session
		if (!isset($_SESSION))
		{
			session_start();
		}

		// initialize session var and set lockdown_id
		$SC_lockdown = & $_SESSION['SC_lockdown'];
		$SC_lockdown = array();

		$SC_lockdown['lockdown_id'] = md5(uniqid('SC', true));

		$this->return_data = '<input type="hidden" name="lockdown_id" value="'.$SC_lockdown['lockdown_id'].'" />' . PHP_EOL;

	}
	
	// ----------------------------------------------------------------

	public static function usage()
	{
		return "Lockdown ExpressionEngine SafeCracker rules and fields";
	}
}


/* End of file pi.safecracker_lockdown.php */
