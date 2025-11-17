<?php

use Zetkin\ZetkinWordPressPlugin\HTML\Element;
use Zetkin\ZetkinWordPressPlugin\HTML\Renderer;
use Zetkin\ZetkinWordPressPlugin\JoinForms;
use Zetkin\ZetkinWordPressPlugin\Utils;
use Zetkin\ZetkinWordPressPlugin\ZetkinAPI;

if (!function_exists('renderZetkinJoinForm')) {
	function renderZetkinJoinForm($attributes)
	{
		$formId = $attributes["formId"] ?? null;
		$formSubmitToken = $attributes["formSubmitToken"] ?? null;
		$result = $_GET[JoinForms::RESULT_QUERY_ARG_PREFIX . $formId] ?? "";

		if ($result === "success") { ?>
			<div <?php echo get_block_wrapper_attributes(["class" => "zetkin-join-form-block"]); ?>>
				<?php Renderer::renderElement(new Element("p", ["class" => "zetkin-join-form-success"], __("Thanks for signing up!", "zetkin"))) ?>
			</div>
		<?php
			return;
		}

		$form = null;
		if ($formId) {
			$form = ZetkinAPI::getJoinForm($formId);
		}

		if (!$form || !$formSubmitToken ): ?>
			<?php echo '<p class="zetkin-invalid-join-form">' . esc_html__("Invalid join form - check the block settings.", "zetkin") . '</p>'; ?>
		<?php endif; ?>

		<div <?php echo get_block_wrapper_attributes(["class" => "zetkin-join-form-block"]); ?> style="<?php echo Utils::getBlockStyle($attributes) ?>">
			<?php JoinForms::renderJoinForm($form, $attributes, $result); ?>
		</div>
<?php
	}
}

renderZetkinJoinForm($attributes);

?>