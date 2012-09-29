<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Data Template Parser
 *
 * A helper class to make life easier when parsing fieldtype tags
 *
 * @package		Channel Data
 * @subpackage	Libraries
 * @category	Library
 * @author		Justin Kimbrell
 * @copyright	Copyright (c) 2012, Justin Kimbrell
 * @link 		http://www.objectivehtml.com/libraries/channel_data
 * @version		0.7.0
 * @build		20120711
 */
 
class Channel_data_tmpl extends Channel_data_lib {
	
	public function __construct()
	{
		parent::__construct();
	}
		
	public function init($tagdata = FALSE)
	{
		if(!isset($this->EE->TMPL))
		{
			require_once APPPATH.'/libraries/Template.php';
		}
		
		$this->EE->load->library('typography');
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_fields');

		$fields = $this->EE->api_channel_fields->fetch_custom_channel_fields();

		$parse_object = (object) array();

		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_fields');
	}
	
	public function create_alias($tagdata = NULL)
	{
		$TMPL = $this->EE->TMPL;
		
		$this->EE->TMPL = new EE_Template();
		
		$this->EE->TMPL->template = $tagdata ? $tagdata : $TMPL->tagdata;	
		$this->EE->TMPL->template = $this->EE->TMPL->parse_globals($this->EE->TMPL->template);
		
		return $TMPL;
	}
			
	public function reset($TMPL)
	{
		$this->EE->TMPL = $TMPL;
	}
	
	public function parse_switch($tagdata, $count = 1)
	{
		// Match {switch="foo|bar"} variables			
		$switch = array();

		if (preg_match_all("/".LD."(switch\s*=.+?)".RD."/i", $tagdata, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$sparam = $this->EE->functions->assign_parameters($match[1]);

				if (isset($sparam['switch']))
				{
					$sopt = explode("|", $sparam['switch']);

					$switch[$match[1]] = $sopt;
				}
			}
		}
		
		$row = array();
		
		// Set {switch} variable values
		foreach ($switch as $key => $val)
		{
			$row[$key] = $switch[$key][($count + count($val) -1) % count($val)];
		}
		
		return $this->EE->TMPL->parse_variables_row($tagdata, $row, FALSE);	
	}
	
	public function parse_entry($entry_data, $channels = array(), $channel_fields = array(), $tagdata = FALSE, $count = 1)
	{
		$TMPL = $this->EE->channel_data->tmpl->create_alias($tagdata);
			
		if(is_string($entry_data) || is_int($entry_data))
		{
			$entry_data = $this->get_channel_entry($entry_data)->row();
		}
		
		if(is_array($entry_data))
		{
			$entry_data = (object) $entry_data;
		}		
			
		$vars = $this->EE->functions->assign_variables($this->EE->TMPL->template);
		
		foreach($vars['var_single'] as $single_var)
		{
			$params = $this->EE->functions->assign_parameters($single_var);

			$single_var_array = explode(' ', $single_var);
			
			$field_name = str_replace('', '', $single_var_array[0]);
		
			$entry = FALSE;

			if(isset($channel_fields[$field_name]))
			{
				if(is_array($channel_fields[$field_name]))
				{
						$channel_fields[$field_name] = (object) $channel_fields[$field_name];
				}
				
				$field_type = $channel_fields[$field_name]->field_type;
				$field_id   = $channel_fields[$field_name]->field_id;
				
				$data       = $entry_data->$field_name;
				
				if($this->EE->api_channel_fields->setup_handler($field_id))
				{
					$row = array_merge($channels[$entry_data->channel_id], (array) $entry_data);
					
					foreach($channel_fields as $channel_field)
					{
						$channel_field = (array) $channel_field;
							
						if(isset($row[$channel_field['field_name']]))
						{
							$row['field_id_'.$channel_field['field_id']] = $data;
							$row['field_ft_'.$channel_field['field_id']] = $channel_field['field_fmt'];
							unset($row[$channel_field['field_name']]);
						}
						
					}
					
					$this->EE->api_channel_fields->apply('_init', array(array('row' => $row)));
					// Preprocess
					$data = $this->EE->api_channel_fields->apply('pre_process', array($row['field_id_'.$field_id]));

					$entry = $this->EE->api_channel_fields->apply('replace_tag', array($data, $params, FALSE));
					
					$this->EE->TMPL->template = $this->EE->TMPL->swap_var_single($single_var, $entry, $this->EE->TMPL->template );
				}
			}
		}

		$pair_vars = array();

		foreach($vars['var_pair'] as $pair_var => $params)
		{
			$pair_var_array = explode(' ', $pair_var);
			
			$field_name = str_replace('', '', $pair_var_array[0]);
			$offset = 0;

			while (($end = strpos($this->EE->TMPL->template, LD.'/'.$field_name.RD, $offset)) !== FALSE)
			{
				if (preg_match("/".LD."{$field_name}(.*?)".RD."(.*?)".LD.'\/'.$field_name.RD."/s", $this->EE->TMPL->template, $matches, 0, $offset))
				{
					$chunk  = $matches[0];
					$params = $matches[1];
					$inner  = $matches[2];

					// We might've sandwiched a single tag - no good, check again (:sigh:)
					if ((strpos($chunk, LD.$field_name, 1) !== FALSE) && preg_match_all("/".LD."{$field_name}(.*?)".RD."/s", $chunk, $match))
					{
						// Let's start at the end
						$idx = count($match[0]) - 1;
						$tag = $match[0][$idx];
						
						// Reassign the parameter
						$params = $match[1][$idx];

						// Cut the chunk at the last opening tag (PHP5 could do this with strrpos :-( )
						while (strpos($chunk, $tag, 1) !== FALSE)
						{
							$chunk = substr($chunk, 1);
							$chunk = strstr($chunk, LD.$field_name);
							$inner = substr($chunk, strlen($tag), -strlen(LD.'/'.$field_name.RD));
						}
					}
					
					$pair_vars[$field_name] = array($inner, $this->EE->functions->assign_parameters($params), $chunk);
				}
				
				$offset = $end + 1;
			}

			foreach($pair_vars as $field_name => $pair_var)
			{																
				if(isset($channel_fields[$field_name]))
				{
					$field_type = $channel_fields[$field_name]->field_type;
					$field_id   = $channel_fields[$field_name]->field_id;

					$data       = $entry_data->$field_name;

					if($this->EE->api_channel_fields->setup_handler($field_id))
					{
						$entry = $this->EE->api_channel_fields->apply('replace_tag', array($data, $pair_var[1], $pair_var[0]));

						$this->EE->TMPL->template = str_replace($pair_var[2], $entry, $this->EE->TMPL->template);
					}
				}
			}

			$entry = FALSE;
		}
		
		$this->EE->TMPL->template = $this->EE->TMPL->parse_variables_row($this->EE->TMPL->template, (array) $entry_data);
		$this->EE->TMPL->parse($this->EE->TMPL->template);
		
		$return = $this->EE->channel_data->tmpl->parse_switch($this->EE->TMPL->template, $count);
		
		$this->EE->channel_data->tmpl->reset($TMPL);
			
		return $return;
	}
}