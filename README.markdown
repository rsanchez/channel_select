# Channel Select

Channel Select is an ExpressionEngine fieldtype for selecting one or more channels.

## Installation

* Copy the /system/expressionengine/third_party/channel_select/ folder to your /system/expressionengine/third_party/ folder
* Install the fieldtype

## Usage

### Single Variables

	{your_field_name}

Outputs the selected channel ids, separated by pipe. Ex. 1|3|4
	
	{your_field_name:title}

Outputs the selected channel title, when using single channel select mode.
	
	{your_field_name:titles separator=", " last_separator=" and "}

Outputs a list of titles of selected channels. Ex. News, Events and Blog
	
	{your_field_name:name}

Outputs the selected channel title, when using single channel select mode. Ex. calendar_events

	{your_field_name:names}

Outputs a list of names of selected channels, separated by pipe. Ex. news|calendar_events|blog

### Variable Pairs

	{your_field_name}
		{channel_id}

Output data about the selected channels.
	{your_field_name:entries}
		<li><a href="{url_title_path=blog/post}">{title}</a></li>
	{/your_field_name}

Runs a channel:entries loop for the selected channels. This is the same as doing `{exp:channel:entries channel="{your_field_name:names}" dynamic="no"}`