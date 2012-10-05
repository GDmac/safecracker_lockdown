<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Safecracker_lockdown_ext {
	
	public $settings 		= array();
	public $description		= 'Lockdown ExpressionEngine SafeCracker rules and fields';
	public $docs_url		= '';
	public $name			= 'Safecracker Lockdown';
	public $settings_exist	= 'n';
	public $version			= '1.0';
	
	private $EE;
	
	// ----------------------------------------------------------------------

	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
	}

	// ----------------------------------------------------------------------
	
	public function safecracker_submit_entry_start( & $SC )
	{
		// start session
		if (!isset($_SESSION))
		{
			session_start();
		}

		// Is the session ready?
		if ( ! isset($_SESSION['SC_lockdown']))
		{
			show_error("SC_lockdown is in effect, but lockdown session not set", 500);
		}

		// continue
		$SC_lockdown = & $_SESSION['SC_lockdown'];


		// Does lockdown id match the hidden form element
		if ($this->EE->input->post('lockdown_id') !== @$SC_lockdown['lockdown_id'])
		{
			// reset session for next form
			$_SESSION['SC_lockdown'] = array();

			show_error("SC_lockdown is in effect, lockdown ID's don't match", 500);
		}


		// continue processing

		// ...

		// after processing reset lockdown session for next form
		$_SESSION['SC_lockdown'] = array();

	}

	// ----------------------------------------------------------------------
	
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array();
		
		$hooks = array(
			'safecracker_submit_entry_start'      => 'safecracker_submit_entry_start',
		);

		foreach ($hooks as $hook => $method)
		{
			$data = array(
				'class'		=> __CLASS__,
				'method'	=> $method,
				'hook'		=> $hook,
				'settings'	=> serialize($this->settings),
				'version'	=> $this->version,
				'enabled'	=> 'y'
			);

			$this->EE->db->insert('extensions', $data);			
		}
	}	

	// ----------------------------------------------------------------------
	
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}	
	
	// ----------------------------------------------------------------------
}

/* End of file ext.safecracker_lockdown.php */
