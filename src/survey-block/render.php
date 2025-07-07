<?php

use Zetkin\ZetkinWordPressPlugin\HTML\Element;
use Zetkin\ZetkinWordPressPlugin\HTML\Renderer;
use Zetkin\ZetkinWordPressPlugin\Surveys;
use Zetkin\ZetkinWordPressPlugin\ZetkinAPI;

if (!function_exists('renderZetkinSurveyBlock')) {
	function renderZetkinSurveyBlock($attributes)
	{
		$survey = null;
		$surveyId = $attributes["surveyId"] ?? null;
		$result = $_GET[Surveys::RESULT_QUERY_ARG_PREFIX . $surveyId] ?? "";

		if ($result === "success") { ?>
			<div <?php echo get_block_wrapper_attributes(["class" => "zetkin-survey-block"]); ?>>
				<?php Renderer::renderElement(new Element("p", ["class" => "zetkin-survey-success"], __("Success!", "zetkin"))) ?>
			</div>
		<?php
			return;
		}

		if ($surveyId && $surveyId > 0) {
			$survey = ZetkinAPI::getSurvey($surveyId);
		}

		if (!$survey) {
			echo '<p class="zetkin-invalid-survey">' . esc_html__("Invalid survey - check the block settings.", "zetkin") . '</p>';
			return;
		}

		?>
		<div <?php echo get_block_wrapper_attributes(["class" => "zetkin-survey-block"]); ?>>
			<?php Surveys::renderSurvey($survey, $result) ?>
		</div>
<?php
	}
}

renderZetkinSurveyBlock($attributes);
