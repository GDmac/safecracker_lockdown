# Safecracker Lockdown

Lockdown ExpressionEngine SafeCracker.

Note: currently only tested with safecracker error_handling="inline".
(This reloads the template on any errors and re-generates the form.)


## Installation

Enable the extension, this will add a hidden lockdown_id 
to the safecracker form. The extension stores the rules
in a PHP session for secure transfer between requests.

## Usage

Add the a tag-pair inside the safecracker tag to set any rules.
Additionally you can set rules to some default fields, like title.

	{safecracker_lockdown}

		{!-- Rules --}
		{rules:agenda_body="required|min_length[50]"}

		{!-- Rules on default fields --}
		{rules:title="min_length[4]"}

	{safecracker_lockdown}



## Changelog

2012-10-07 
Version 1.2
- Move lockdown session to library
- Garbage collection of session

Version 1.1.1
- use EE session cache for inter-hook data

Version 1.1
- Working Beta version
- removed plugin

2012-10-05 Version 1.0
- Initial Idea and initial Commit



## Author(s)
- GDmac
