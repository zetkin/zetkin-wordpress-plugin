<?php

use Zetkin\ZetkinWordPressPlugin\Events;
use Zetkin\ZetkinWordPressPlugin\ZetkinAPI;

if (!function_exists('renderZetkinCalendarBlock')) {
	function renderZetkinCalendarBlock($attributes)
	{
		$events = ZetkinAPI::getEvents();

		if (!$events) {
			echo '<p class="zetkin-no-events">' . esc_html__("No events.", "zetkin") . '</p>';
		}

?>
		<div <?php echo get_block_wrapper_attributes(["class" => "zetkin-calendar-block"]); ?>>
			<script>window.ZETKIN_CALENDAR_BLOCK_BASE_URL = "<? echo plugin_dir_url(__FILE__); ?>"</script>
			<?php Events::renderCalendar($events); ?>
		</div>
<?php
	}
}

renderZetkinCalendarBlock($attributes);

?>