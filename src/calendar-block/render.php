<?php

use Zetkin\ZetkinWordPressPlugin\Utils;
use Zetkin\ZetkinWordPressPlugin\ZetkinAPI;

$events = ZetkinAPI::getEvents();

if (!$events) {
	echo '<p class="zetkin-no-events">' . __("No events.", "zetkin") . '</p>';
}

?>
<ol <?php echo get_block_wrapper_attributes(["class" => "zetkin-events"]); ?>>
	<?php foreach ($events as $event) : ?>
		<?php
			$organisation = $event['organization']['title'] ?? __("Unknown organisation", "zetkin");
			$startTime = $event['start_time'];
			$endTime = $event['end_time'];
			$time = Utils::getFormattedEventTime($startTime, $endTime);
		?>
		<li class="zetkin-event">
			<h2 className="zetkin-event__title"><?php echo esc_html($event['title']) ?></h2>
			
			<?php if (!empty($event['campaign']['title'])): ?>
				<p className="zetkin-event__project"><?php echo esc_html($event['campaign']['title']) ?> / <?php echo esc_html($organisation) ?></p>
			<?php else: ?>
				<p className="zetkin-event__project"><?php echo esc_html($organisation) ?></p>
			<?php endif; ?>
			
			<?php if ($time): ?>
				<p className="zetkin-event__time"><?php echo esc_html($time) ?></p>
			<?php endif; ?>
			
			<?php if (!empty($event['location']['title'])): ?>
				<p className="zetkin-event__location"><?php echo esc_html($event['location']['title']) ?></p>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ol>