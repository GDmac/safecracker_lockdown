# Safecracker Lockdown

Lockdown ExpressionEngine SafeCracker rules and fields.

Note: currently only works with safecracker error_handling="inline".
This will reload the template on any errors and the plugin will then generate a new lockdown_id.


## Installation and usage

Enable the extension and add the plugin into your safecracker tag

{exp:safecracker_lockdown}

This will add a hidden lockdown_id form element store the same lockdown ID 
into a PHP session. If both ID's don't match the form can't be submitted.
