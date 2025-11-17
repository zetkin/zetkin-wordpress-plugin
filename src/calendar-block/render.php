<?php

use Zetkin\ZetkinWordPressPlugin\Events;
use Zetkin\ZetkinWordPressPlugin\Utils;
use Zetkin\ZetkinWordPressPlugin\ZetkinAPI;

if (!function_exists('renderZetkinCalendarBlock')) {
	function renderZetkinCalendarBlock($attributes)
	{
		try {
		$events = ZetkinAPI::getEvents();

?>
		<div <?php echo get_block_wrapper_attributes(["class" => "zetkin-calendar-block"]); ?>  style="<?php echo Utils::getBlockStyle($attributes) ?>">
			<script>window.ZETKIN_CALENDAR_BLOCK_BASE_URL = "<? echo plugin_dir_url(__FILE__); ?>"</script>
			<?php Events::renderCalendar($events, $attributes); ?>
		</div>
<?php
		} catch (\Exception $e) {
			echo '<p class="zetkin-calendar-error">' . esc_html__("Error loading calendar, please check the block settings.", "zetkin") . '</p>';
		}
	}
}

renderZetkinCalendarBlock($attributes);

?>