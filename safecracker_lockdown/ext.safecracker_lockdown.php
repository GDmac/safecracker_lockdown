<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Safecracker_lockdown_ext {
	
	public $settings 		= array();
	public $description		= 'Lockdown ExpressionEngine SafeCracker rules and fields';
	public $docs_url		= '';
	public $name			= 'Safecracker Lockdown';
	public $settings_exist	= 'n';
	public $version			= '1.2';
	
	private $EE;

	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
	}


	/* ----------------------------------------------------------------------
	 * Inject any rules before safecracker even runs
	 * They have to be encrypted, safecrackster will decrypt them *sigh*
	 *
	 */

	public function hook_sessions_end( &$sess )
	{
		if (REQ !== 'ACTION') return;

		// bye-bye user submitted rules
		if (isset($_POST['rules'])) show_error('Please use safecracker_lockdown to set any rules.', 500);

		// do we have lockdown?
		if ($this->EE->input->post('lockdown_id') != false)
		{

			$this->EE->load->library('safecracker_lockdown_lib');

			if ( ! $this->EE->safecracker_lockdown_lib->verify_lockdown_id())
			{
				show_error('Safecracker lockdown id not found', 500);
			}

			$lockdown_id = $this->EE->input->post('lockdown_id');

			$this->restore_lockdown_rules($lockdown_id);
	
			// $this->restore_hidden_fields();
		}

	}

	// ----------------------------------------------------------------------
	public function restore_lockdown_rules($lockdown_id)
	{
		// any rules to inject?
		if ( empty($_SESSION['SC_lockdown'][$lockdown_id]['rules']) ) return;

		foreach ($_SESSION['SC_lockdown'][$lockdown_id]['rules'] as $field_name => $field_rules)
		{
			$_POST['rules'][$field_name] = $field_rules;
		}

	}

	// ----------------------------------------------------------------------
	public function restore_hidden_fields($lockdown_id)
	{
		// any values to restore
		if ( empty($_SESSION['SC_lockdown'][$lockdown_id]['hidden_fields']) ) return;

		foreach ($_SESSION['SC_lockdown'][$lockdown_id]['hidden_fields'] as $field_name => $value)
		{
			$_POST[$field_name] = $value;
		}
	}



	/* ----------------------------------------------------------------------
	 * Create or verify the lockdown session. Fetch any rules and field values
	 * from safecracker tagdata, and stuff them into the lockdown session.
	 * And, insert the lockdown_id in the form
	 *
	 */

	public function hook_safecracker_entry_form_tagdata_end( $tagdata, &$SC )
	{
		$this->EE->load->library('safecracker_lockdown_lib');

		// create a fresh lockdown session when there are no errors
		// if there are errors, then a lockdown session should already exist

		if (empty($SC->field_errors) && empty($SC->errors))
		{
			// this would be a good place to do garbage collection on the sessions
			$this->EE->safecracker_lockdown_lib->garbage_collection();

			$lockdown_id = $this->EE->safecracker_lockdown_lib->create_lockdown_session();
	
		}
		else
		{
			// errors 
			// lockdown_id is stored in session cache on submit_entry_start

			$lockdown_id = $this->EE->session->cache('SC_lockdown', 'active_id');


			// exit if there is no existing lockdown session
			if ( ! isset($_SESSION['SC_lockdown'][$lockdown_id]))
			{
				show_error('Form with errors but without a lockdown id',500);
			}

			// we're keeping the lockdown id, but clear out data
			$this->EE->safecracker_lockdown_lib->reset_lockdown_session($lockdown_id);
		}

var_dump($lockdown_id, $_SESSION['SC_lockdown']);


		// Add lockdown ID to the form
		$tagdata = str_replace('</form>', form_hidden('lockdown_id', $lockdown_id). '</form>', $tagdata);


		// fetch safecracker_lockdown tag-pair(s)
		if ( ! preg_match_all('#{safecracker_lockdown}(.*?)\{/safecracker_lockdown}?#ms', $tagdata, $matches) )
		{
			return $tagdata;
		}
		
		foreach($matches[1] as $i => $lockdowntag)
		{
			// remove safecracker_lockdown tag-pair from template tagdata
			$tagdata = str_replace($matches[0][$i], '', $tagdata);

			// any conditionals? hopefully they are already prepped
			if (strpos($lockdowntag, '{if')!== false)
			{
				$lockdowntag = $this->EE->TMPL->advanced_conditionals($lockdowntag);
			}

			// fetch rules and store to session
			if (preg_match_all('#{rules:([\w]+)=(\042|\047)([^\2].*?)\2}#', $lockdowntag, $rule_matches))
			{
				foreach ($rule_matches[1] as $j => $field)
				{
					$rules = $rule_matches[3][$j];

					$_SESSION['SC_lockdown'][$lockdown_id]['rules'][$field] = $this->EE->safecracker->encrypt_input($rules);

				}
			}
		}

		return $tagdata;
	}


	/* ----------------------------------------------------------------------
	 * The form has been submit
	 *
	 */
	
	public function hook_safecracker_submit_entry_start( &$SC )
	{
		// start PHP session
		if (!isset($_SESSION))
		{
			session_start();
		}

		// verify session

		if ( ! $this->EE->safecracker_lockdown_lib->verify_lockdown_id())
		{
			show_error('Safecracker lockdown id not set or not found', 500);
		}

		$lockdown_id = $this->EE->input->post('lockdown_id', true);


		// Keep lockdown id for submit entry end. The class is 
		// instantiated for every individual hook! 
		// so we need to store it in session rather than in $this *sigh*

		$this->EE->session->set_cache('SC_lockdown', 'active_id', $lockdown_id);


		// handy, allow extra rules on default fields, title etc., like min_length[4]
		if ( ! empty($_SESSION['SC_lockdown'][$lockdown_id]['rules']))
		{
			foreach ($_SESSION['SC_lockdown'][$lockdown_id]['rules'] as $field_name => $field_rules)
			{
				if (isset($SC->default_fields[$field_name]))
				{
					// stick them on the default rules
					$SC->default_fields[$field_name]['rules'] .= $SC->decrypt_input($field_rules);
				}
			}
		}

	}


	/* ----------------------------------------------------------------------
	 * Reset lockdown session on success
	 *
	 */
	
	public function hook_safecracker_submit_entry_end( &$SC )
	{
		// reset our stuffs
		if (empty($SC->field_errors) && empty($SC->errors))
		{
			// pop lockdown id, hello there
			$lockdown_id = $this->EE->session->cache('SC_lockdown', 'active_id');

			// and bye
			$this->EE->safecracker_lockdown_lib->delete_lockdown_session($lockdown_id);
		}

	}


	/* ----------------------------------------------------------------------
	 *
	 */
	
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array();
		
		$hooks = array(
			'sessions_end'                       => 'hook_sessions_end',
			'safecracker_entry_form_tagdata_end' => 'hook_safecracker_entry_form_tagdata_end',
			'safecracker_submit_entry_start'     => 'hook_safecracker_submit_entry_start',
			'safecracker_submit_entry_end'       => 'hook_safecracker_submit_entry_end',
		);

		foreach ($hooks as $hook => $method)
		{
			$data = array(
				'class'		=> __CLASS__,
				'method'	=> $method,
				'hook'		=> $hook,
				'settings'	=> serialize($this->settings),
				'version'	=> $this->version,
				'enabled'	=> 'y',
				'priority'	=> 20, // thank you SC for claiming the last spot
			);

			$this->EE->db->insert('extensions', $data);			
		}
	}	

	/* ----------------------------------------------------------------------
	 *
	 */
	
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	/* ----------------------------------------------------------------------
	 *
	 */

	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}	

}

/* End of file ext.safecracker_lockdown.php */
