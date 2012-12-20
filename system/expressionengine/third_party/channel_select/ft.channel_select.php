<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Channel_select_ft extends EE_Fieldtype
{
    public $info = array(
        'name' => 'Channel Select',
        'version' => '1.0.0'
    );

    public $has_array_data = TRUE;
    
    /**
     * Display Field on Publish
     *
     * @access  public
     * @param   $data
     * @return  field html
     *
     */
    public function display_field($data, $cell = FALSE)
    {
        static $options;
        
        if (is_null($options))
        {
            $this->EE->load->library('javascript');
            
            $this->EE->cp->add_to_head('<script type="text/javascript" src="'.URL_THIRD_THEMES.'channel_select/select2/select2.min.js"></script>');
            $this->EE->cp->add_to_head('<link rel="stylesheet" media="all" href="'.URL_THIRD_THEMES.'channel_select/select2/select2.css">');
            $this->EE->cp->add_to_head('
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
            
            $this->EE->javascript->output('
                $(".channel-select").select2({
                    minimumResultsForSearch: 12
                });
            ');
            
            $options = array();
            
            foreach ($this->channels() as $channel)
            {
                $options[$channel['channel_id']] = $channel['channel_title'];
            }
        }
        
        if ( ! $options)
        {
            return lang('no_channels');
        }

        $field_name = $cell ? $this->cell_name : $this->field_name;

        if ( ! empty($this->settings['channel_select_multiple']))
        {
            $data = $data ? explode('|', $data) : array();

            return form_multiselect($field_name.'[]', $options, $data, 'class="channel-select" style="width:100%;"');
        }

        return form_dropdown($field_name, $options, $data, 'class="channel-select" style="width:100%;"');
    }

    private function channels()
    {
        static $cache;

        if ( ! is_null($cache))
        {
            return $cache;
        }

        $query = $this->EE->db->where('site_id', $this->EE->config->item('site_id'))
                                ->order_by('channel_title', 'asc')
                                ->get('channels');

        $cache = array();

        foreach ($query->result_array() as $row)
        {
            $cache[$row['channel_id']] = $row;
        }

        $query->free_result();

        return $cache;
    }
    
    public function save($data)
    {
        if ( ! is_array($data))
        {
            return $data ? $data : '';
        }

        return $data ? implode('|', $data) : '';
    }

    public function pre_process($data)
    {
        return $data ? explode('|', $data) : array();
    }
    
    /**
     * Replace tag
     *
     * @access  public
     * @param   field contents
     * @return  replacement text
     *
     */
    public function replace_tag($data, $params = array(), $tagdata = FALSE)
    {
        if ( ! $tagdata)
        {
            //convert back to pipe delimited, it's an array now because of pre_process
            return $this->save($data);
        }

        $channels = $this->channels();

        $variables = array();

        foreach ($data as $channel_id)
        {
            if ( ! isset($channels[$channel_id]))
            {
                continue;
            }

            array_push($variables, $channels[$channel_id]);
        }

        if (empty($variables))
        {
            return '';
        }

        return $this->EE->TMPL->parse_variables($tagdata, $variables);
    }

    public function replace_name($data, $params = array(), $tagdata = FALSE)
    {
        return $this->replace_names($data, $params, $data);
    }

    public function replace_names($data, $params = array(), $tagdata = FALSE)
    {
        $channels = $this->channels();

        $result = '';

        foreach ($data as $channel_id)
        {
            if ( ! isset($channels[$channel_id]))
            {
                continue;
            }

            $result .= $result ? '|'.$channels[$channel_id]['channel_name'] : $channels[$channel_id]['channel_name'];
        }

        return $result;
    }

    public function replace_titles($data, $params = array(), $tagdata = FALSE)
    {
        $channels = $this->channels();

        $result = '';

        $separator = isset($params['separator']) ? $params['separator'] : '|';

        $last_separator = isset($params['last_separator']) ? $params['last_separator'] : '|';

        $count = 0;

        $total_results = count($data);

        foreach ($data as $channel_id)
        {
            $count++;

            if ( ! isset($channels[$channel_id]))
            {
                continue;
            }

            $current_separator = $count === $total_results ? $last_separator : $separator;

            $result .= $result ? $current_separator.$channels[$channel_id]['channel_title'] : $channels[$channel_id]['channel_title'];
        }

        return $result;
    }

    public function replace_title($data, $params = array(), $tagdata = FALSE)
    {
        return $this->replace_titles($data, $params, $data);
    }

    public function display_settings($data)
    {
        foreach ($this->_display_settings($data) as $row)
        {
            $this->EE->table->add_row($row[0], $row[1]);
        }
    }

    public function display_cell_settings($data)
    {
        return $this->_display_settings($data);
    }

    public function display_var_settings($data)
    {
        return $this->_display_settings($data);
    }

    protected function _display_settings($data)
    {
        $defaults = array(
            'channel_select_multiple' => 0,
        );
        
        $data = array_merge($defaults, $data);
        
        return array(
            array(
                form_label('Allow selection of multiple channels?'),
                form_label(form_checkbox('channel_select_multiple', '1', $data['channel_select_multiple']).' Yes'),
            ),
        );
    }

    public function save_settings()
    {
        return array_merge($this->_save_settings($_POST), array(
            'field_fmt' => 'none',
            'field_show_fmt' => 'n',
        ));
    }

    public function save_cell_settings($data)
    {
        return $this->_save_settings($data);
    }

    public function save_var_settings($data)
    {
        return $this->_save_settings($data);
    }

    protected function _save_settings($data)
    {
        return array(
            'channel_select_multiple' => empty($data['channel_select_multiple']) ? 0 : 1,
        );
    }

    public function save_var_field($data)
    {
        return $this->save($data);
    }

    public function save_cell($data)
    {
        return $this->save($data);
    }

    public function display_var_field($data)
    {
        return $this->display_field($data);
    }

    public function display_cell($data)
    {
        return $this->display_field($data, TRUE);
    }

    public function display_var_tag($data, $params = array(), $tagdata = FALSE)
    {
        return $this->replace_tag($data, $params, $tagdata);
    }
}

/* End of file ft.google_maps.php */
/* Location: ./system/expressionengine/third_party/google_maps/ft.google_maps.php */