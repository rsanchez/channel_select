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
		{channel_id}		{site_id}		{channel_name}		{channel_title}		{channel_url}		{channel_description}		{channel_lang}		{total_entries}		{total_comments}		{last_entry_date}		{last_comment_date}		{cat_group}		{status_group}		{deft_status}		{field_group}		{search_excerpt}		{deft_category}		{deft_comments}		{channel_require_membership}		{channel_max_chars}		{channel_html_formatting}		{channel_allow_img_urls}		{channel_auto_link_urls}		{channel_notify}		{channel_notify_emails}		{comment_url}		{comment_system_enabled}		{comment_require_membership}		{comment_use_captcha}		{comment_moderate}		{comment_max_chars}		{comment_timelock}		{comment_require_email}		{comment_text_formatting}		{comment_html_formatting}		{comment_allow_img_urls}		{comment_auto_link_urls}		{comment_notify}		{comment_notify_authors}		{comment_notify_emails}		{comment_expiration}		{search_results_url}		{ping_return_url}		{show_button_cluster}		{rss_url}		{enable_versioning}		{max_revisions}		{default_entry_title}		{url_title_prefix}		{live_look_template}	{/your_field_name}

Output data about the selected channels.
	{your_field_name:entries}
		<li><a href="{url_title_path=blog/post}">{title}</a></li>
	{/your_field_name}

Runs a channel:entries loop for the selected channels. This is the same as doing `{exp:channel:entries channel="{your_field_name:names}" dynamic="no"}`