<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Channel_select_ft extends EE_Fieldtype
{
    public $info = array(
        'name' => 'Channel Select',
        'version' => '1.0.0',
    );

    public $has_array_data = TRUE;

    public function __construct()
    {
        parent::__construct();

        $this->EE->load->add_package_path(PATH_THIRD.'channel_select/');

        $this->EE->load->model('channel_select_model');
    }

    /**
     * Display channel entry field
     *
     * @param   $data the entry data
     * @return  string
     *
     */
    public function display_field($data)
    {
        return $this->EE->channel_select_model->display_field($data, $this->field_name, ! empty($this->settings['channel_select_multiple']));
    }

    /**
     * Save
     *
     * format the data for the database by pipe delimiting
     *
     * @param $data a channel ID or an array of channel IDs
     * @return string
     */
    public function save($data)
    {
        if ( ! is_array($data))
        {
            return $data ? $data : '';
        }

        return $data ? implode('|', $data) : '';
    }

    /**
     * Pre Process
     *
     * convert the pipe delimited string into an array of channel IDs
     *
     * @param $data channel ids
     * @return array
     */
    public function pre_process($data)
    {
        return $data ? explode('|', $data) : array();
    }
    
    /**
     * Replace tag
     * 
     * returns pipe deliimted string of channel ids if single variable
     * displays channel info if tag pair
     *
     * @param $data array of channel ids
     * @param $params tag params array
     * @param $tagdata the tagdata string
     * @return string
     */
    public function replace_tag($data, $params = array(), $tagdata = FALSE)
    {
        if ($tagdata && isset($params['entries']) && $params['entries'] === 'yes')
        {
            return $this->replace_entries($data, $params, $tagdata);
        }

        if ( ! $tagdata)
        {
            //convert back to pipe delimited, it's an array now because of pre_process
            return $this->save($data);
        }

        //the model gives an array keyed by channel id, parse_variables wants a zero-indexed array
        $variables = array_values($this->EE->channel_select_model->get_channels($data));

        if (empty($variables))
        {
            return '';
        }

        return $this->EE->TMPL->parse_variables($tagdata, $variables);
    }

    /**
     * Replace Name
     *
     * display the first channel name in a set of channel ids
     *
     * @param array $data
     * @param array $params
     * @param bool|string $tagdata
     * @return string
     */
    public function replace_name($data, $params = array(), $tagdata = false)
    {
        $channel_id = array_shift($data);

        $channels = $this->EE->channel_select_model->get_channels();

        return $this->EE->channel_select_model->get_channel_info($channel_id, 'channel_name');
    }

    /**
     * Replace Names
     *
     * a pipe delimited list of channel names
     *
     * @param array $data
     * @param array $params
     * @param bool|string $tagdata
     * @return string
     */
    public function replace_names($data, $params = array(), $tagdata = false)
    {
        $channels = $this->EE->channel_select_model->get_channels($data);

        $names = array();

        foreach ($channels as $channel_id => $channels)
        {
            $names[] = $channels[$channel_id]['channel_name'];
        }

        return implode('|', $names);
    }

    /**
     * Replates Titles
     * 
     * a pipe delimited list of channel titles
     * 
     * @param $data
     * @param array $params
     * @param bool $tagdata
     * @return string
     */
    public function replace_titles($data, $params = array(), $tagdata = false)
    {
        $channels = $this->EE->channel_select_model->get_channels($data);

        $separator = isset($params['separator']) ? $params['separator'] : '|';

        $last_separator = isset($params['last_separator']) ? $params['last_separator'] : '|';

        $titles = array();

        foreach ($channels as $channel_id => $channel_info)
        {
            $titles[] = $channel_info['channel_title'];
        }

        if ( ! $titles)
        {
            return '';
        }

        $last_title = array_pop($titles);

        return implode($separator, $titles).$last_separator.$last_title;
    }

    /**
     * Replate Title
     * 
     * display the first channel name in a set of channel ids
     * 
     * @param $data
     * @param array $params
     * @param bool $tagdata
     * @return string
     */
    public function replace_title($data, $params = array(), $tagdata = false)
    {
        $channel_id = array_shift($data);

        $channels = $this->EE->channel_select_model->get_channels();

        return $this->EE->channel_select_model->get_channel_info($channel_id, 'channel_name');
    }

    
    /**
     * Replace Entries
     * 
     * run a channel:entries loop from the fieldtype
     * 
     * {your_field_name:entries disable="pagination"} {/your_field_name}
     *
     * @param $data
     * @param array $params
     * @param bool $tagdata
     * @return string
     */
    public function replace_entries($data, $params = array(), $tagdata = FALSE)
    {
        if ( ! $data || ! $tagdata)
        {
            return '';
        }

        require_once APPPATH.'modules/channel/mod.channel.php';

        $original_tagdata = $this->EE->TMPL->tagdata;
        $original_tagparams = $this->EE->TMPL->tagparams;

        $channels = $this->EE->channel_select_model->get_channels($data);

        $channel_names = array();

        foreach ($channels as $channel)
        {
            $channel_names[] = $channel['channel_name'];
        }

        $this->EE->TMPL->tagdata = $tagdata;
        $this->EE->TMPL->tagparams = $params;
        $this->EE->TMPL->tagparams['channel'] = implode('|', $channel_names);
        $this->EE->TMPL->tagparams['dynamic'] = 'no';
    
        $channel = new Channel;
        
        $output = $channel->entries();

        $this->EE->TMPL->tagdata = $original_tagdata;
        $this->EE->TMPL->tagparams = $original_tagparams;

        return $output;
    }

    /**
     * Display channel field settings
     **/
    public function display_settings($data)
    {
        foreach ($this->EE->channel_select_model->display_settings($data) as $row)
        {
            $this->EE->table->add_row($row[0], $row[1]);
        }
    }

    /**
     * Display Matrix cell settings
     **/
    public function display_cell_settings($data)
    {
        return $this->EE->channel_select_model->display_settings($data);
    }

    /**
     * Display Low Variable settings
     **/
    public function display_var_settings($data)
    {
        return $this->EE->channel_select_model->display_settings($data);
    }

    /**
     * Save channel field settings
     **/
    public function save_settings()
    {
        return array_merge($this->EE->channel_select_model->save_settings($_POST), array(
            'field_fmt' => 'none',
            'field_show_fmt' => 'n',
        ));
    }

    /**
     * Save Matrix cell settings
     **/
    public function save_cell_settings($data)
    {
        return $this->EE->channel_select_model->save_settings($data);
    }

    /**
     * Save Low Variable settings
     **/
    public function save_var_settings($data)
    {
        return $this->EE->channel_select_model->save_settings($data);
    }

    /**
     * Save Low Variable
     **/
    public function save_var_field($data)
    {
        return $this->EE->channel_select_model->save($data);
    }

    /**
     * Save Matrix cell
     **/
    public function save_cell($data)
    {
        return $this->EE->channel_select_model->save($data);
    }

    /**
     * Display Low Variable field
     **/
    public function display_var_field($data)
    {
        return $this->EE->channel_select_model->display_field($data, $this->field_name, ! empty($this->settings['channel_select_multiple']));
    }

    /**
     * Display Matrix cell field
     **/
    public function display_cell($data)
    {
        return $this->EE->channel_select_model->display_field($data, $this->cell_name, ! empty($this->settings['channel_select_multiple']));
    }

    /**
     * Replace Low Variable tag
     **/
    public function display_var_tag($data, $params = array(), $tagdata = false)
    {
        return $this->replace_tag($data, $params, $tagdata);
    }
}

/* End of file ft.channel_select.php */
/* Location: ./system/expressionengine/third_party/channel_select/ft.channel_select.php */