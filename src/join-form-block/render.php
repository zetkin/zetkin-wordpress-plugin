<?php

use Zetkin\ZetkinWordPressPlugin\ZetkinAPI;

$formId = $attributes["formId"] ?? null;
$formSubmitToken = $attributes["formSubmitToken"] ?? null;

if (!$formId || !$formSubmitToken || $formId < 1) {
?>
	<?php echo '<p class="zetkin-invalid-join-form">' . esc_html__("Invalid join form - check the block settings.", "zetkin") . '</p>'; ?>
<?php
}

$formFields = ZetkinAPI::getJoinFormFields($formId);

?>
<div <?php echo get_block_wrapper_attributes(["class" => "zetkin-join-form-block"]); ?>>
	<form class="zetkin-join-form">
		<?php foreach ($formFields as $formField): ?>
			<div class="zetkin-input">
				<label for="<?php echo esc_attr($formField['slug']) ?>"><?php echo esc_html($formField['label']) ?></label>
				<input id="<?php echo esc_attr($formField['slug']) ?>" name="<?php echo esc_html($formField['slug']) ?>" type="text" />
			</div>
		<?php endforeach; ?>
	</form>
</div>