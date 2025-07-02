// import apiFetch from "@wordpress/api-fetch";

// const $surveyBlocks = document.querySelectorAll(".zetkin-survey-block");

// $surveyBlocks.forEach(($surveyBlock) => {
// 	const surveyId = $surveyBlock.getAttribute("data-survey-id");
// 	const $form = $surveyBlock.querySelector("form");
// 	const $submit = $form.querySelector(".zetkin-survey-submit");
// 	const $error = $form.querySelector(".zetkin-survey-error");
// 	$form.addEventListener("submit", async (e) => {
// 		e.preventDefault();
// 		$submit.setAttribute("disabled", true);
// 		$error.setAttribute("style", "display:none");

// 		const formData = new FormData(e.target);
// 		const submission = {
// 			responses: [],
// 			signature: {},
// 		};

// 		for (const [key, value] of formData.entries()) {
// 			if (key.startsWith("sig.")) {
// 				const field = key.split(".")[1];
// 				submission.signature[field] = formData.get(key);
// 				continue;
// 			}
// 			if (key.endsWith(".text")) {
// 				const field = key.split(".")[0];
// 				submission.responses.push({
// 					question_id: Number(field),
// 					response: formData.get(key),
// 				});
// 				continue;
// 			}
// 			if (key.endsWith(".options")) {
// 				const field = key.split(".")[0];
// 				const values = formData.getAll(key);
// 				submission.responses.push({
// 					question_id: Number(field),
// 					options: values.filter((v) => v !== "").map(Number),
// 				});
// 				continue;
// 			}
// 		}
// 		try {
// 			const response = await apiFetch({
// 				body: JSON.stringify(submission),
// 				headers: {
// 					"Content-type": "application/json",
// 				},
// 				method: "POST",
// 				path: `/zetkin/surveys/${surveyId}/submissions`,
// 			});
// 			if (!response.ok) {
// 				throw new Error();
// 			}
// 		} catch (e) {
// 			$error.setAttribute("style", "");
// 		} finally {
// 			$submit.removeAttribute("disabled");
// 		}
// 	});
// });
