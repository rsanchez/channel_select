<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Channel_select_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        //load up all channels into the cache
        if ($this->session->cache(__CLASS__, 'channels') === FALSE)
        {
            $query = $this->db->where('site_id', $this->config->item('site_id'))
                                    ->order_by('channel_title', 'asc')
                                    ->get('channels');

            $channels = array();

            foreach ($query->result_array() as $row)
            {
                $this->session->set_cache(__CLASS__, 'channel:'.$row['channel_id'], $row);

                $channels[$row['channel_id']] = $row;
            }

            $query->free_result();

            $this->session->set_cache(__CLASS__, 'channels', $channels);
        }
    }

    /**
     * Get all or some channels from the cache
     * 
     * @param array|bool $channel_ids (optional) array of channel ids to include
     */
    public function get_channels($channel_ids = FALSE)
    {
        $all_channels = $this->session->cache(__CLASS__, 'channels');

        if ($channel_ids === FALSE)
        {
            return $all_channels;
        }

        $channels = array();

        foreach ($channel_ids as $channel_id)
        {
            if (isset($all_channels[$channel_id]))
            {
                $channels[$channel_id] = $all_channels[$channel_id];
            }
        }

        return $channels;
    }

    /**
     * Get channel info
     * 
     * @param $channel_id
     * @param $key the column from the exp_channels table data to fetch
     * @return array if a $key is not provided
     * @return mixed if a $key is provieded
     **/
    public function get_channel_info($channel_id, $key = FALSE)
    {
        $channel_info = $this->session->cache(__CLASS__, 'channel:'.$channel_id);

        if (func_num_args() === 0)
        {
            return $channel_info;
        }

        return isset($channel_info[$key]) ? $channel_info[$key] : FALSE;
    }
    
    public function save($data)
    {
        if ($data && ! is_array($data))
        {
            $data = array($data);
        }

        return $data ? implode('|', $data) : '';
    }

    /**
     * Display Field
     *
     * @param $data the field's saved data
     * @param $field_name the field name to use when outputting the form input
     * @return string
     */
    public function display_field($data, $field_name, $multiple = FALSE)
    {
        if ( ! $this->session->cache(__CLASS__, __FUNCTION__))
        {
            $this->load->library('javascript');
            
            $this->cp->add_to_head('<script type="text/javascript" src="'.URL_THIRD_THEMES.'channel_select/select2/select2.min.js"></script>');
            $this->cp->add_to_head('<link rel="stylesheet" media="all" href="'.URL_THIRD_THEMES.'channel_select/select2/select2.css">');
            $this->cp->add_to_head('
            <style type="text/css">
            input.select2-input {
                -webkit-box-sizing: content-box;
                -moz-box-sizing: content-box;
                -o-box-sizing: content-box;
                box-sizing: content-box;
            }
            ul.select2-choices.ui-sortable li {
                cursor: move !important;
            }
            </style>
            ');
            
            $this->javascript->output('
                $(".channel-select").select2({
                    minimumResultsForSearch: 12
                });
            ');

            $this->session->set_cache(__CLASS__, __FUNCTION__, true);
        }

        $options = array();

        foreach ($this->channel_select_model->get_channels() as $channel)
        {
            $options[$channel['channel_id']] = $channel['channel_title'];
        }
        
        if ( ! $options)
        {
            return lang('no_channels');
        }

        $this->load->helper('form');

        if ($multiple)
        {
            $data = $data ? explode('|', $data) : array();

            return form_multiselect($field_name.'[]', $options, $data, 'class="channel-select" style="width:100%;"');
        }

        return form_dropdown($field_name, array('' => '---') + $options, $data, 'class="channel-select" style="width:100%;"');
    }

    /**
     * Display settings
     * 
     * @param $data
     * @return array
     */
    public function display_settings($data)
    {
        $defaults = array(
            'channel_select_multiple' => 0,
        );
        
        $data = array_merge($defaults, $data);

        $this->load->helper('form');
        
        return array(
            array(
                form_label('Allow selection of multiple channels?'),
                form_label(form_checkbox('channel_select_multiple', '1', $data['channel_select_multiple']).' Yes'),
            ),
        );
    }

    /**
     * Save settings
     * 
     * @param $data
     * @return array
     */
    public function save_settings($data)
    {
        return array(
            'channel_select_multiple' => empty($data['channel_select_multiple']) ? 0 : 1,
        );
    }
}