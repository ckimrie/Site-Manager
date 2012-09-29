<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Data Library
 *
 * This class is the core library to interact with ExpressionEngine's
 * channel data. This class has been abstracted out of the loading
 * logic to make it easier manager and more modular.
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

if(!class_exists('Channel_data_lib'))
{
	class Channel_data_lib {

		// A list of escaped conditional operators

		private $conditionals = array('\!\=', '\<\=', '\>\=', '\<', '\>', '\=', 'like', 'LIKE');

		// A list of common ambitious fields

		private $ambigious_fields = array(
			'entry_id',
			'site_id',
			'channel_id'
		);

		// A list of the reserved SQL terms

		private $reserved_terms = array(
			'select', 'like', 'or_like', 'or_where', 'where', 'where_in',
			'order_by', 'sort', 'limit', 'offset', 'join', 'having', 'group_by'
		);

		/**
		 * Construct
		 *
		 * Gets the instance variable
		 *
		 * @param	array	Additional parameters used to instatiate the object
		 * @return	void
		 */

		public function __construct($params = array())
		{
			$this->EE =& get_instance();
		}

		/**
		 * Gets a specified table using polymorphic parameters
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get($table, $select = array(), $where = array(), $order_by = FALSE, $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			$this->convert_params($select, $where, $order_by, $sort, $limit, $offset);

			return $this->EE->db->get($table);
		}

		/**
		 * Gets a single action_id from a class and method
		 *
		 * @access	public
		 * @param	string A valid class name
		 * @param	string A method within the class
		 * @return	int
		 */

		public function get_action_id($class, $method)
		{
			$return = $this->get_actions(array('*'), array(
				'class' 	=> ucfirst($class),
				'method' 	=> $method
			));

			if($return->num_rows() == 0)
				return FALSE;

			return (int) $return->row('action_id');
		}

		/**
		 * Gets records from the actions table using polymorphic
		 * parameters.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_actions($select = array(), $where = array(), $order_by = FALSE, $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('actions', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Get a single category by specifying a category id.
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	string
		 */

		public function get_category($category_id, $select = array('*'))
		{
			return $this->get_categories($select, array('cat_id' => $category_id));
		}

		/**
		 * Get categories using on a series of polymorphic parameters that
		 * returns an active record object.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_categories($select = array(), $where = array(), $order_by = 'cat_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('categories', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Get categories using on a series of polymorphic parameters that
		 * returns an active record object.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_category_entries($select = array(), $where = array(), $order_by = 'cat_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			$fields 		= $this->get_category_fields()->result();
			$field_array	= array();
			$field_select 	= array();

			//Default fields to select

			$default_select = array('categories.*');

			// If the parameter is polymorphic, then the variables are extracted

			if($this->is_polymorphic($select) && $polymorphic = $select)
			{
				extract($select);

				foreach($this->reserved_terms as $term)
				{
					if(!isset($polymorphic[$term]) && isset($$term) || isset($polymorphic[$term]))
					{
                        $var_term = $$term;

						if($term == 'select' && !isset($var_term['select']))
							$$term = $default_select;
						else
							$$term = isset($polymorphic[$term]) ? $polymorphic[$term] : $$term;
					}
				}
			}

			// Selects the appropriate field name and converts where converts
			// where parameters to their corresponding m_field_id's
			foreach($fields as $field)
			{
				if(is_array($select))
					$select[] = 'field_id_'.$field->field_id.' as \''.$field->field_name.'\'';

				foreach($where as $index => $value)
				{
					$index = $this->check_ambiguity($index);

					if($field->field_name == $index)
					{
						unset($where[$index]);
						$where['field_id_'.$field->field_id] = $value;
					}
				}
			}

			// Joins the channel_data table

			$this->EE->db->join('category_field_data', 'categories.cat_id = category_field_data.cat_id');

			$params = array(
				'select' 	=> $select,
				'where' 	=> $where,
				'order_by' 	=> $order_by,
				'sort' 		=> $sort,
				'limit' 	=> $limit,
				'offset'	=> $offset
			);

			// Converts the params into active record methods

			$this->convert_params($params, FALSE, FALSE, FALSE, FALSE, FALSE, TRUE);

			return $this->get('categories', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Get category field by specifying a field id.
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	object
		 */

		public function get_category_field($field_id, $select = array('*'))
		{
			return $this->get_category_fields($select, array('field_id' => $field_id));
		}

		/**
		 * Get category fields using on a series of polymorphic parameters
		 * that returns an active record object.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_category_fields($select = array(), $where = array(), $order_by = 'field_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('category_fields', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Get category field data using on a series of polymorphic parameters
		 * that returns an active record object.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_category_field_data($select = array(), $where = array(), $order_by = 'cat_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('category_field_data', $select, $where, $order_by, $sort, $limit, $offset);

		}

		/**
		 * Get category groups using on a series of polymorphic parameters
		 * that returns an active record object.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_category_group($group_id, $select = array('*'))
		{
			return $this->get_category_groups($select, array('group_id' => $group_id));
		}

		/**
		 * Get category groups using on a series of polymorphic parameters
		 * that returns an active record object.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_category_by_group($group_id, $select = array('*'))
		{
			return $this->get_categories($select, array('group_id' => $group_id));
		}

		/**
		 * Get category groups by using on a series of polymorphic parameters
		 * that returns an active record object.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_category_groups($select = array(), $where = array(), $order_by = 'group_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('category_groups', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Get the category posts by an entry id.
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	object
		 */

		public function get_category_post($entry_id, $select = array('*'))
		{
			return $this->get_category_posts($select, array('entry_id' => $entry_id));
		}

		/**
		 * Get the category posts using a series of polymorphic parameters
		 * that returns an active record object.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_category_posts($select = array(), $where = array(), $order_by = 'entry_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('category_posts', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Get category groups by using on a series of polymorphic parameters
		 * that returns an active record object.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_channel_categories($channel_id, $select = array(), $where = array(), $order_by = 'group_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			$channel 	= $this->get_channel($channel_id);
			$new_where 	= array('group_id' => $channel->row('cat_group'));

			if($this->is_polymorphic($select))
			{
				if(!isset($select['where']))
				{
					$select['where'] = array();
				}

				$select['where'] = array_merge($select['where'], $new_where);
			}
			else
			{
				if(!is_array($where))
				{
					$where = array();
				}

				$where = array_merge($where, $new_where);
			}

			return $this->get('categories', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Gets a channel by specifying a channel_id
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	object
		 */

		public function get_channel($channel_id, $select = array('*'))
		{
			return $this->get_channels($select, array('channel_id' => $channel_id));
		}

		/**
		 * Get channel fields using a series of polymorphic parameters
		 * that returns an active record object.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_channels($select = array(), $where = array(), $order_by = 'channel_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('channels', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Gets a channel by specifying a channel_name
		 *
		 * @access	public
		 * @param	string	A string containing a channel name
		 * @param	mixed	An array or string of fields to select. Default: '*'
		 * @return	object
		 */

		public function get_channel_by_name($channel_name, $select = array('*'))
		{
			return $this->get_channels($select, array('channel_name' => $channel_name));
		}

		/**
		 * Get custom field from a specified field_id.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_channel_field($field_id, $select = array('*'))
		{
			return $this->get_fields($select, array('field_id' => $field_id));
		}

		/**
		 * Gets the custom fields by the group_id. This somehwat mimics the
		 * the native get_channel_fields method.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_channel_fields($channel_id = false, $select = array('*'), $where = array(), $order_by = 'field_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			if($channel_id !== FALSE)
			{
				$channel = $this->get_channel($channel_id)->row();

				if(isset($channel->field_group))
				{
					$group_id = array('group_id' => $channel->field_group);
					$where = array_merge($where, $group_id);
				}
			}

			return $this->get('channel_fields', $select, $where, $order_by, $sort, $limit, $offset);

		}

		/**
		 * An alias to get_channel_fields and get_custom_fields.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_fields($select = array('*'), $where = array(), $order_by = 'field_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('channel_fields', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * An alias to get_channel_field and get_custom_field.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_field($field_id, $select = array('*'))
		{
			return $this->get_channel_field($field_id, $select);
		}

		/**
		 * Get a custom field by specifying a field_name.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_field_by_name($field_name, $select = array('*'))
		{
			return $this->get_fields(array(
				'select' 	=> $select,
				'where'		=> array(
					'field_name' => $field_name
				)
			));
		}

		/**
		 * Get a custom field by specifying a field_name.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_fields_by_group($group_id = FALSE, $select = array('*'), $where = array(), $order_by = 'field_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{

			if($this->is_polymorphic($select))
			{
				$select['where']['group_id'] = $group_id;
			}
			else
			{
				$where['group_id'] = $group_id;
			}

			return $this->get_fields($select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Alias to the get_fields_by_group method
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_field_group($group_id, $select = array('*'), $where = array(), $order_by = 'group_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			if($this->is_polymorphic($select))
			{
				$select['where']['group_id'] = $group_id;
			}
			else
			{
				$where['group_id'] = $group_id;
			}

			return $this->get_field_groups($select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Gets all the field groups
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_field_groups($select = array('*'), $where = array(), $order_by = 'group_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('field_groups', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Get channel member groups by either a group_id or channel_id
		 *
		 * @access	public
		 * @param	int
		 * @param	int
		 * @return	string
		 */

		public function get_channel_member_group($group_id = FALSE, $channel_id = FALSE)
		{
			$where = array();

			if($group_id !== FALSE)
				$where['group_id'] = $group_id;

			if($channel_id !== FALSE)
				$where['channel_id'] = $channel_id;

			return $this->get('channel_member_groups', array(), $where, 'group_id', 'DESC', FALSE, 0);
		}

		/**
		 * Get the channel member group using on a series of polymorphic parameters
		 * that returns an active record object.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_channel_member_groups($channel_id, $select = array(), $where = array(), $order_by = 'channel_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('channel_member_groups', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Gets statuses by specifying a channel id. Polymorphic parameters are
		 * still allowed.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_channel_statuses($channel_id, $select = array('*'), $where = array(), $order_by = 'status_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			$channel = $this->get_channel($channel_id)->row();

			if(isset($channe->status_group))
			{
				if($this->is_polymorphic($select))
				{
					$select['where']['group_id'] = $channel->status_group;
				}
				else
				{
					$where['group_id'] = $channel->status_group;
				}
			}

			return $this->get('statuses', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Get channel title using an entry id
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	object
		 */

		public function get_channel_title($entry_id, $select = array('*'))
		{
			return $this->get_channel_titles($select, array('entry_id' => $entry_id));
		}

		/**
		 * Get channel titles using on a series of polymorphic parameters
		 * that returns an active record object.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_channel_titles($select = array(), $where = array(), $order_by = 'channel_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('channel_titles', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Get channel data using on a series of polymorphic parameters
		 * that returns an active record object.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_channel_data($select = array(), $where = array(), $order_by = 'channel_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('channel_data', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Gets channel entries using a series of polymorphic parameters
		 * that returns an active record object.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_entries($select = array('channel_data.entry_id', 'channel_data.channel_id', 'channel_titles.author_id', 'channel_titles.title', 'channel_titles.url_title', 'channel_titles.entry_date', 'channel_titles.expiration_date', 'status'), $where = array(), $order_by = 'channel_titles.channel_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get_channel_entries(FALSE, $select, $where = array(), $order_by, $sort, $limit, $offset);
		}

		/**
		 * Gets a single channel entry
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_entry($entry_id, $select = array('channel_data.entry_id', 'channel_data.channel_id', 'channel_titles.author_id', 'channel_titles.title', 'channel_titles.url_title', 'channel_titles.entry_date', 'channel_titles.expiration_date', 'status'))
		{
			return $this->get_channel_entry($entry_id, $select);
		}

		/**
		 * Get a single entry specifying an entry id
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	mixed
		 */

		public function get_channel_entry($entry_id, $select = array('channel_data.entry_id', 'channel_data.channel_id', 'channel_titles.author_id', 'channel_titles.title', 'channel_titles.url_title', 'channel_titles.entry_date', 'channel_titles.expiration_date', 'status'))
		{
			$entry = $this->get_channel_title($entry_id);

			if($entry->num_rows() == 1)
			{
				$entry->row();

				return $this->get_channel_entries($entry->row('channel_id'), $select, array('channel_data.entry_id' => $entry_id));
			}

			return FALSE;
		}

		/**
		 * Get entries by specifying a channel id. Polymorphic paramerts are
		 * also accepted. The channel id is required.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */
		public function get_channel_entries($channel_id, $select = array(), $where = array(), $order_by = 'channel_titles.channel_id', $sort = 'DESC', $limit = FALSE, $offset = 0, $debug = FALSE)
		{

			$default_select = array('channel_data.entry_id', 'channel_data.channel_id', 'channel_titles.author_id', 'channel_titles.title', 'channel_titles.url_title', 'channel_titles.entry_date', 'channel_titles.expiration_date', 'status');
			$default_select = ($select == array()) ? $default_select : (isset($select['select']) ? $select['select'] : $default_select);

			// If the parameter is polymorphic, then the variables are extracted

			if($this->is_polymorphic($select) && $polymorphic = $select)
			{
				extract($select);

				foreach($this->reserved_terms as $term)
				{
                	if(!isset($polymorphic[$term]) && isset($$term) || isset($polymorphic[$term]))
                	{
                        $var_term = $$term;

						if($term == 'select' && !isset($var_term['select']))
							$$term = $default_select;
						else
							$$term = isset($polymorphic[$term]) ? $polymorphic[$term] : $$term;
					}
				}
			}
			
			if(count($select) == 0)
			{
				$select = $default_select;
			}

			// If the channel_id is not false then only the specified channel fields are
			// appended to the query. Otherwise, all fields are appended.

			$where_array = array('channel_data.channel_id' => $channel_id);

			if($channel_id !== FALSE)
			{
				$fields	 = $this->get_channel_fields($channel_id)->result();

				if(is_array($where))
					$where_array = array_merge($where_array, $where);
				else
					$where_array = array();
			}
			else
			{
				$fields  = $this->get_fields()->result();
				$select	 = array();
			}

			$where = $where_array;

			// Selects the appropriate field name and converts where converts
			// where parameters to their corresponding field_id's
			$field_array = array();

			foreach($fields as $field)
			{
				if(is_array($select))
				{
					$select[] = 'field_id_'.$field->field_id.' as \''.$field->field_name.'\'';
				}

				$field_array[$field->field_name] = $field;
			}

			$where_array = array();

			foreach($where as $index => $value)
			{
				$where_index = $this->check_ambiguity($index);

				if(isset($field_array[$index]))
				{
					unset($where_array[$index]);
					$where_array['field_id_'.$field_array[$index]->field_id] = $value;
				}
				else
				{
					if($debug)
					{
						$debug_where[$index] = $field->field_name;
					}
					//var_dump($where_index);echo '<br><br>';
					$where_array[$index] = $value;
				}
			}

			if($debug)
			{
				var_dump($where_array);exit();
			}


			$where = $where_array;

			// Joins the channel_data table

			$this->EE->db->join('channel_data', 'channel_titles.entry_id = channel_data.entry_id');

			$params = array(
				'select' 	=> $select,
				'where' 	=> $where,
				'order_by' 	=> $order_by,
				'sort' 		=> $sort,
				'limit' 	=> $limit,
				'offset'	=> $offset
			);
			
			foreach(array('join', 'having', 'group_by') as $keyword)
			{
				if(isset($$keyword))
				{
					$params[$keyword] = $$keyword;
				}
			}

			// Converts the params into active record methods

			$this->convert_params($params, FALSE, FALSE, FALSE, FALSE, FALSE, $debug);


			return $this->EE->db->get('channel_titles');
		}

		/**
		 * Get a single member by specifying a member_id. The custom fields
		 * are automatically joined in the query just like entries.
		 *
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	object
		 */

		public function get_member($member_id, $select = array('member_data.*', 'members.*'))
		{
			return $this->get_members(array(
				'select' 	=> $select,
				'where'		=> array(
					'members.member_id' => $member_id
				)
			));
		}

		/**
		 * Get members using the standard polymorphic parameters. The custom
		 * fields are automatically joined in the query just like entries.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_members($select = array('members.member_id', 'members.group_id', 'members.email', 'members.username', 'members.screen_name'), $where = array(), $order_by = 'member_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{

			$fields 		= $this->get_member_fields()->result();
			$field_array	= array();
			$field_select 	= array();

			//Default fields to select

			$default_select = array('members.*');

			// If the parameter is polymorphic, then the variables are extracted

			if($this->is_polymorphic($select) && $polymorphic = $select)
			{
				extract($select);

				foreach($this->reserved_terms as $term)
				{
					if(!isset($polymorphic[$term]) && isset($$term) || isset($polymorphic[$term]))
					{
                        $var_term = $$term;

						if($term == 'select' && !isset($var_term['select']))
							$$term = $default_select;
						else
							$$term = isset($polymorphic[$term]) ? $polymorphic[$term] : $$term;
					}
				}
			}

			// Selects the appropriate field name and converts where converts
			// where parameters to their corresponding m_field_id's
			foreach($fields as $field)
			{
				if(is_array($select))
					$select[] = 'm_field_id_'.$field->m_field_id.' as \''.$field->m_field_name.'\'';

				foreach($where as $index => $value)
				{
					$index = $this->check_ambiguity($index);

					if($field->m_field_name == $index)
					{
						unset($where[$index]);
						$where['m_field_id_'.$field->m_field_id] = $value;
					}
				}
			}

			// Joins the channel_data table

			$this->EE->db->join('member_data', 'members.member_id = member_data.member_id');

			$params = array(
				'select' 	=> $select,
				'where' 	=> $where,
				'order_by' 	=> $order_by,
				'sort' 		=> $sort,
				'limit' 	=> $limit,
				'offset'	=> $offset
			);

			// Converts the params into active record methods

			$this->convert_params($params, FALSE, FALSE, FALSE, FALSE, FALSE, TRUE);

			return $this->EE->db->get('members');


			foreach($fields as $field)
			{
				$field_array[$field->m_field_name] = $field;
				$field_select[] = 'm_field_'.$field->m_field_id.' as \''.$field->m_field_name.'\'';
			}

			if($this->is_polymorphic($select))
			{
				if(isset($select['select']))
				{
					$select_array = $select['select'];

					if(!is_array($select['select']))
					{
						$select_array = array($select['select']);
					}

					$select['select'] = array_merge($select_array, $field_select);
				}
			}
			else
			{
				if(!is_array($select))
				{
					$select = array($select);
				}

				$select = array_merge($select, $field_select);
			}

			$this->EE->db->join('member_data', 'members.member_id = member_data.member_id');

			return $this->get('members', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Get members directly using the standard polymorphic parameters.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_member_data($select = array(), $where = array(), $order_by = 'rel_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('member_data', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Get members fields using the standard polymorphic parameters.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_member_fields($select = array(), $where = array(), $order_by = 'm_field_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('member_fields', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Get a single member field by specifying a field_id
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	object
		 */

		public function get_member_field($field_id, $select = array('*'))
		{
			return $this->get_member_fields(array(
				'select' 	=> $select,
				'where'		=> array(
					'm_field_id' => $field_id
				)
			));
		}

		/**
		 * Get a single member field by specifying a field_name
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	object
		 */

		public function get_member_field_by_name($field_name, $select = array('*'))
		{
			return $this->get_member_fields(array(
				'select' 	=> $select,
				'where'		=> array(
					'm_field_name' => $field_name
				)
			));
		}

		/**
		 * Get member groups using the standard polymorphic parameters.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_member_groups($select = array(), $where = array(), $order_by = 'group_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('member_groups', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Get a member group by specifying a group_id
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	object
		 */

		public function get_member_group($group_id, $select = array('*'))
		{
			return $this->get_member_groups(array(
				'select' 	=> $select,
				'where'		=> array(
					'group_id' => $group_id
				)
			));
		}

		/**
		 * Get a member group by specifying a group_title
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	object
		 */

		public function get_member_group_by_title($title, $select = array('*'))
		{
			return $this->get_member_groups(array(
				'select' 	=> $select,
				'where'		=> array(
					'group_title' => $title
				)
			));
		}

		/**
		 * Get a single relationship by specifying an relationship id
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	object
		 */

		public function get_relationship($rel_id, $select = array('*'))
		{
			return $this->get_relationships($select, array('rel_id' => $rel_id));
		}

		/**
		 * Get relationships by using on a series of polymorphic parameters
		 * that returns an active record object.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_relationships($select = array(), $where = array(), $order_by = 'rel_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('relationships', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Get child relationships by specifying an entry_id
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	object
		 */

		public function get_related_child_entries($entry_id, $select = '*')
		{
			return $this->get_relationships(array(
				'select' => $select,
				'where'	 => array(
					'rel_child_id' => $entry_id
				)
			));
		}

		/**
		 * Get parent relationships by specifying an entry_id
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	object
		 */

		public function get_related_entries($entry_id, $select = '*')
		{
			return $this->get_relationships(array(
				'select' => $select,
				'where'	 => array(
					'rel_parent_id' => $entry_id
				)
			));
		}

		/**
		 * Get related parent entries by specifying an entry_id
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	object
		 */

		public function get_related_parent_entries($entry_id, $select = '*')
		{
			return $this->get_related_entries($entry_id, $select);
		}

		/**
		 * Get status group by specifying a group_id
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	object
		 */

		public function get_status_group($group_id, $select = '*')
		{
			return $this->get_status_groups($select, array(
				'group_id' => $group_id
			));
		}

		/**
		 * Get status groups the standard polymorphic parameters.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixeds
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_status_groups($select = array(), $where = array(), $order_by = 'group_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('status_groups', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Get status by specifying a status_id
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	object
		 */

		public function get_status($status_id, $select = '*')
		{
			return $this->get_statuses($select, array(
				'status_id' => $status_id
			));
		}

		/**
		 * Get statuses by specifying a group_id
		 *
		 * @access	public
		 * @param	int
		 * @param	mixed
		 * @return	object
		 */

		public function get_statuses_by_group($group_id, $select = '*', $where = array(), $order_by = 'status_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{

			if($this->is_polymorphic($select))
			{
				$select['where']['group_id'] = $group_id;
			}
			else
			{
				$where['group_id'] = $group_id;
			}

			return $this->get_statuses($select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Get statuses by using on a series of polymorphic parameters
		 * that returns an active record object.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	object
		 */

		public function get_statuses($select = array(), $where = array(), $order_by = 'status_id', $sort = 'DESC', $limit = FALSE, $offset = 0)
		{
			return $this->get('statuses', $select, $where, $order_by, $sort, $limit, $offset);
		}

		/**
		 * Return TRUE if category is a parent.
		 *
		 * @access	public
		 * @param	int		Category id
		 * @return  bool
		 */

		public function is_parent_category($cat_id)
		{
			$category = $this->get_category($cat_id);

			if($category->num_rows() == 0)
			{
				return FALSE;
			}

			if($category->row('parent_id') == 0)
			{
				return TRUE;
			}

			return FALSE;
		}

		/**
		 * Adds a set prefix to an ambigious database field
		 *
		 * @access	public
		 * @param	string	A database field name
		 * @param	string	A prefix to add if the field is indeed ambiguous
		 * @return	object
		 */

		public function check_ambiguity($field, $prefix = 'channel_titles.')
		{
			foreach($this->ambigious_fields as $fields)
			{
				if(strpos($field, $fields) !== FALSE)
				{
					$field = $prefix.$field;
				}
			}

			return $field;
		}

		/**
		 * Convert Params
		 *
		 * Converts polymorphic parameters into an array that is used to
		 * build the active record sequence.
		 *
		 * Example:
		 *
		 * 	$this->convert_params(array(
		 * 		'select' 	=> array('channel_id', 'channel_name'),
		 *		'where'	 	=> array('channel_id' => 1),
		 *		'order_by'	=> 'channel_id'
		 * 	));
		 *
		 * 	$this->convert_params(array(
		 * 		'select' 	=> '*',
		 *		'order_by'	=> array('channel_description', 'channel_name'),
		 *		'sort'		=> 'ASC'
		 * 	));
		 *
		 * $this->convert_params(array('*'), array('channel_id' => 1), 'channel_id', 'DESC', 1, 1);
		 *
		 * There are number of combinations possible that aren't even shown.
		 *
		 * @access	public
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @param	mixed
		 * @return	array
		 */

		public function convert_params($select, $where, $order_by, $sort, $limit, $offset, $debug = FALSE)
		{
			if($this->is_polymorphic($select))
			{
				$subject = $select;
				unset($select);

				$keywords = $this->reserved_terms;

				foreach($keywords as $keyword)
				{
					$$keyword = isset($subject[$keyword]) ? $subject[$keyword] : (isset($$keyword) ? $$keyword : NULL);
				}

				if(isset($subject['select']))
					$select = $subject['select'];
				else
					if(!isset($select))
						$select = array('*');
			}

			$params	= array(
				'select' 	=> $select,
				'where'		=> $where,
				'order_by'	=> $order_by,
				'sort'		=> $sort,
				'limit'		=> $limit,
				'offset'	=> $offset
			);

			foreach(array('join', 'having', 'group_by') as $keyword)
			{
				if(isset($$keyword))
				{
					$params[$keyword] = $$keyword;
				}
			}

			foreach($this->reserved_terms as $term)
			{
				if(isset($params[$term]) && $params[$term] !== FALSE)
				{
					$param = $params[$term];

					switch ($term)
					{
						case 'select':


							if(!is_array($param))
								$param = array($param);


							foreach($param as $select)
							{
								//$select = $this->check_ambiguity($select);

								$this->EE->db->select($select);
							}


							break;

						case 'where':
							$where_sql = array();

							foreach($param as $field => $value)
							{
								$field = preg_replace('/\{+\d+\}/', '', $field);
								if(!is_array($value)) $value = array($value);

								foreach($value as $where_val)
								{
									$where_field = trim($field);

									$concat = ' AND ';

									if(preg_match("/(^or.+)|(^OR.+)/", $where_field))
									{
										unset($params['where'][$field]);

										//$where_field 	=  preg_replace("/^or.+/", "", $field);

										$where_field 	= trim(str_replace(array("or ", "OR "), '', $field));
										$concat 		= ' OR ';
									}

									$where_sql[] =  $concat . $this->remove_conditionals($this->EE->db->protect_identifiers($where_field)) . $this->assign_conditional($where_field) . $this->EE->db->escape($where_val) ;

								}
							}

							$sql = trim(implode(' ', $where_sql));
							$sql = trim(ltrim(ltrim($sql, 'AND'), 'OR'));

							if(!empty($sql)) $this->EE->db->where($sql, FALSE, FALSE);

							break;

						case 'order_by':
							if(!is_array($param))
								$param = array($param);

							foreach($param as $param)
							{
								$sort = isset($sort) ? $sort : 'DESC';

								$this->EE->db->order_by($param, $sort);
							}

							break;

						case 'limit':
							if(!is_array($param))
								$param = array($param);

							$offset = isset($param['offset']) ? $param['offset'] : $offset;
							$offset	= $offset !== FALSE ? $offset : 0;

							foreach($param as $param)
								$this->EE->db->limit($param, $offset);


							break;

						case 'join':

							if(is_array($param))
							{
								foreach($param as $table => $on)
								{
									$this->EE->db->join($table, $on);
								}
							}

							break;

						case 'having':

							if(is_array($param))
							{
								$having_sql = array();

								foreach($param as $field => $value)
								{
									$field = preg_replace('/\{+\d+\}/', '', $field);
									if(!is_array($value)) $value = array($value);

									foreach($value as $where_val)
									{
										$where_field = trim($field);

										$concat = ' AND ';

										if(preg_match("/(^or.+)|(^OR.+)/", $where_field))
										{
											unset($params['where'][$field]);

											//$where_field 	=  preg_replace("/^or.+/", "", $field);

											$where_field 	= trim(str_replace(array("or ", "OR "), '', $field));
											$concat 		= ' OR ';
										}

										$having_sql[] =  $concat . $this->remove_conditionals($this->EE->db->protect_identifiers($where_field)) . $this->assign_conditional($where_field)  .  $this->EE->db->escape($where_val);

									}
								}

								$sql = trim(implode(' ', $having_sql));
								$sql = trim(ltrim(ltrim($sql, 'AND'), 'OR'));

								if(!empty($sql)) $this->EE->db->having($sql, FALSE, FALSE);
							}

							break;

						case 'group_by':

							if(is_string($param))
							{
								$this->EE->db->group_by($param);
							}

							break;
					}
				}
			}

			return $params;
		}

		/**
		 * Strips conditionals from a string
		 *
		 * @access	public
		 * @param	string
		 * @return	object
		 */

		public function remove_conditionals($subject)
		{
			foreach($this->conditionals as $condition)
			{
				$match = str_replace('\\', '', $condition);
				$subject = str_replace($match, '', $subject);
			}

			return $subject;
		}

		/**
		 * Assign a conditional based on the presence of a logical operator
		 *
		 * @access	public
		 * @param	string
		 * @return	object
		 */

		public function assign_conditional($str, $debug = FALSE)
		{
			$conditionals 	= $this->conditionals;
			$match			= FALSE;

			foreach($conditionals as $condition)
			{
				$matches = array();

				if(preg_match('/^([a-zA-Z0-9]).+'.$condition.'/', $str, $matches))
				{
					$match = TRUE;
					$return = $condition;


					break;
				}


				if(!$match) $return = '=';
			}

			return ' '.str_replace('\\', '', $return).' ';
		}

		/**
		 * Is Polymorphic
		 *
		 * Determines if a parameter is polymorphic
		 *
		 * @access	public
		 * @param	mixed	A mixed value variable
		 * @return	bool
		 */

		public function is_polymorphic($param)
		{
			if(is_array($param))
			{
				foreach($this->reserved_terms as $term)
				{
					if(isset($param[$term]))
					{
						return TRUE;
					}
				}

			}

			return FALSE;
		}
	}
}