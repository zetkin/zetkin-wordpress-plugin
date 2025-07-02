import { __ } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";
import { useEffect, useState } from "react";
import apiFetch from "@wordpress/api-fetch";
import { SelectControl, Spinner, Notice } from "@wordpress/components";
import "./editor.scss";

export default function Edit({ attributes, setAttributes }) {
	const [surveys, setSurveys] = useState([]);
	const [surveyQuestions, setSurveyQuestions] = useState([]);
	const [loadingSurveys, setLoadingSurveys] = useState(true);
	const [loadingSurvey, setLoadingSurvey] = useState(true);
	const [error, setError] = useState("");

	console.log("attributes", attributes);

	const loadSurveys = async () => {
		setError("");
		setLoadingSurveys(true);
		setSurveys([]);
		try {
			const data = await apiFetch({ path: "/zetkin/surveys" });
			const surveys = (data || []).map((s) => ({
				id: s.id,
				title: s.title,
			}));
			setSurveys(surveys);
		} catch {
			setError(
				__(
					"Could not load your surveys. Please check your organization ID in the main Zetkin settings page.",
					"zetkin",
				),
			);
		} finally {
			setLoadingSurveys(false);
		}
	};

	const loadSurvey = async () => {
		if (!attributes.surveyId) {
			setLoadingSurvey(false);
			return;
		}
		setError("");
		setLoadingSurvey(true);
		setSurveyQuestions([]);
		try {
			const data = await apiFetch({
				path: `/zetkin/surveys/${attributes.surveyId}`,
			});
			const questions = (data?.elements || []).filter(
				(s) => s.type === "question",
			);
			setSurveyQuestions(questions);
		} catch {
			setError(
				__(
					"Could not load this survey. Please try again later or try a different survey.",
					"zetkin",
				),
			);
		} finally {
			setLoadingSurvey(false);
		}
	};

	useEffect(() => {
		loadSurveys();
		loadSurvey();
	}, []);

	useEffect(() => {
		loadSurvey();
	}, [attributes.surveyId]);

	const loading = loadingSurveys || loadingSurvey;

	return (
		<div {...useBlockProps({ className: "zetkin-survey-block" })}>
			{loading ? <Spinner /> : ""}

			{error ? (
				<Notice status="error" isDismissible={false}>
					{error}
				</Notice>
			) : (
				""
			)}

			{!loading && surveys.length ? (
				<SelectControl
					label={__("Choose a survey", "zetkin")}
					value={attributes.surveyId}
					options={[
						{ label: __("Choose a survey", ""), value: 0 },
						...surveys.map((survey) => ({
							label: survey.title,
							value: survey.id,
						})),
					]}
					onChange={(value) => setAttributes({ surveyId: Number(value) })}
					disabled={loading}
				/>
			) : (
				""
			)}

			{!loading && !surveys.length ? (
				<Notice status="error" isDismissible={false}>
					{__(
						"No surveys found. Please make sure you have created one in your Zetkin organization admin.",
						"zetkin",
					)}
				</Notice>
			) : (
				""
			)}

			{/* Display survey invalid message if no questions found */}
			{attributes.surveyId && !loading && !error && !surveyQuestions.length ? (
				<Notice status="error" isDismissible={false}>
					{__(
						"The selected survey has no questions, so is not a valid option.",
						"zetkin",
					)}
				</Notice>
			) : (
				""
			)}

			{surveyQuestions.length ? (
				<ol>
					{surveyQuestions.map((q) => (
						<li key={q.id}>{q.question.question}</li>
					))}
				</ol>
			) : (
				""
			)}

			{!loading && !surveyQuestions.length ? (
				<Notice isDismissible={false}>
					{__(
						"Choose a valid survey before saving this page, or remove this block.",
						"zetkin",
					)}
				</Notice>
			) : (
				""
			)}
		</div>
	);
}
