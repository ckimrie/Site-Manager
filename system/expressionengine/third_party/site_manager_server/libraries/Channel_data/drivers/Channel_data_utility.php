<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Data Utility
 *
 * A data utility for performing varies common task
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
 
class Channel_data_utility {
	
	/**
	 * Add a prefix to an result array or a single row.
	 * Must pass an array.
	 *
	 * @access	public
	 * @param	string	The prefix
	 * @param	array	The data to prefix
	 * @param	string	The delimiting value
	 * @return	array
	 */
	 public function add_prefix($prefix, $data, $delimeter = ':')
	 {
	 	$new_data = array();
	 	
	 	if(!empty($prefix))
	 	{
		 	foreach($data as $data_index => $data_value)
		 	{
		 		if(is_array($data_value))
		 		{
		 			$new_row = array();
		 			
		 			foreach($data_value as $inner_index => $inner_value)
		 			{
		 				$new_row[$prefix . $delimeter . $inner_index] = $inner_value;
		 			}
		 			
		 			$new_data[$data_index] = $new_row;
		 		}
		 		else
		 		{
		 			$new_data[$prefix . $delimeter . $data_index] = $data_value;
		 		}
		 	}
	 	}
	 	else
	 	{
	 		$new_data = $data;
	 	}
	 	
	 	return $new_data;	
	 }

	/**
	 * Merge an array to any nested array. Useful for merging data into arrays
	 * before they are used to parse the templates.
	 *
	 * @access	public
	 * @param	array	The array to merge
	 * @param	array	The subject and data to be returned
	 * @param	string	The starting point
	 * @param	string	The ending point
	 * @return	array
	 */
	 public function merge_array($array, $subject, $start = 0, $stop = FALSE)
	 {
	 	if($stop === FALSE)
	 	{
	 		$stop = count($subject);
	 	}

	 	for($y=$start; $y < $stop; $y++)
	 	{
	 		if(isset($subject[$y]))
	 		{
	 			$subject[$y] = array_merge($subject[$y], $array);
	 		}
	 		else
	 		{	
	 			$subject[$y] = $array;
	 		}
	 	}

	 	return $subject;
	 }

	/**
	 * Prepare an entry to be added/edited using the channel entries API
	 *
	 * @access	public
	 * @param	mixed	The channel id
	 * @param	array	The entry data
	 * @param	prefix  Delete a consistent prefix from entry data
	 * @param	string	The ending point
	 * @return	array
	 */
	public function prepare_entry($channel_id, $data, $prefix = '')
	{
		$fields = $this->EE->channel_data->get_channel_fields($channel_id);

		$post   = array(
			'title'           => $data->{($prefix.'title')},
			'url_title'       => $data->{($prefix.'url_title')},
			'entry_date'      => $data->{($prefix.'entry_date')},
			'expiration_date' => $data->{($prefix.'expiration_date')},
			'author_id'		  => $data->{($prefix.'author_id')},
			'status'          => $data->{($prefix.'status')}
		);

		foreach($fields->result() as $field)
		{
			$post_value = $this->EE->input->post($field->field_name);

			$post['field_id_'.$field->field_id] = $post_value ? $post_value : $data->{$field->field_name};
			$post['field_ft_'.$field->field_id] = $field->field_fmt;
		}

		return $post;
	}
	
	public function reindex($data, $index)
	{
		if(is_string($data))
		{
			$old_index = $index;
			$index     = $data;
			$data      = $old_index;
		}

		$array = array();
		
		foreach($data as $key => $value)
		{
			if(is_array($value))
			{
				$array[$value[$index]] = $value;
			}
			
			if(is_object($value))
			{
				$array[$value->$index] = $value;
			}
		}
		
		return $array;
	}

	/**
	 * Submits an entry using the channel entries API
	 *
	 * @access	public
	 * @param	mixed	The channel id
	 * @param	array	The entry data
	 * @return	int
	 */
	public function submit_entry($channel_id, $data)
	{
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_entries');

		$this->EE->session->userdata['group_id'] = 1;

		$this->EE->api_channel_entries->submit_new_entry($channel_id, $data);

		if(count($this->EE->api_channel_entries->errors) > 0)
		{
			return $this->EE->api_channel_entries->errors;
		}

		return $this->EE->api_channel_entries->entry_id;
	}

	/**
	 * Updates an entry using the channel entries API
	 *
	 * @access	public
	 * @param	mixed	The channel id
	 * @param	mixed	The entry id
	 * @param	array	The entry data
	 * @return	int
	 */
	public function update_entry($channel_id, $entry_id, $data)
	{
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_entries');
		
		$data['entry_id']   = $entry_id;
		$data['channel_id'] = $channel_id;

		$this->EE->session->userdata['group_id'] = 1;

		$this->EE->api_channel_entries->update_entry($entry_id, $data);

		if(count($this->EE->api_channel_entries->errors) > 0)
		{
			return $this->EE->api_channel_entries->errors;
		}

		return TRUE;
	}

}