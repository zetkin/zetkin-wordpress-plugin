import { __ } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";
import { useState } from "react";
import { TextControl, Button, Spinner, Notice } from "@wordpress/components";
import "./editor.scss";

export default function Edit({ attributes, setAttributes }) {
	const [formId, setFormId] = useState("");
	const [formSubmitToken, setFormSubmitToken] = useState("");
	const [loading, setLoading] = useState(false);
	const [error, setError] = useState("");
	const [formFields, setFormFields] = useState([]);

	const onSubmit = async (e) => {
		e.preventDefault();
		if (!formId || !formSubmitToken) {
			return;
		}
		setError("");
		setLoading(true);
		setFormFields([]);
		try {
			const baseUrl = window.zetkinSettings.stagingEnvironment
				? "http://api.dev.zetkin.org"
				: "https://api.zetkin.org";
			const orgId = window.zetkinSettings.organizationId
			const url = `${baseUrl}/v2/${orgId}/join_forms/${formId}`;
			const response = await fetch(url);
			if (!response.ok) {
				throw new Error();
			}
			const responseData = await response.json();
			setFormFields(responseData.data.fields);
			setAttributes({
				formId,
				formSubmitToken,
			});
		} catch {
			setError(__("Could not load the form, please check your details.", "zetkin"));
		} finally {
			setLoading(false);
		}
	};

	return (
		<div {...useBlockProps({ className: "zetkin-join-form-block" })}>
			<form onSubmit={onSubmit}>
				<TextControl
					label={__("Form ID", "zetkin")}
					type="number"
					value={formId}
					onChange={(value) => setFormId(value)}
					required
				/>

				<TextControl
					label={__("Join Form Submit Token", "zetkin")}
					value={formSubmitToken}
					onChange={(value) => setFormSubmitToken(value)}
					required
				/>

				{(!attributes.formId || !attributes.formFields) ? (
					<Notice isDismissible={false}>
						{__("Load a form before saving this page, or remove this block.", "zetkin")}
					</Notice>
				) : ""}

				<Button variant="primary" type="submit">
					{__("Load form", "zetkin")}
				</Button>

				{loading ? <Spinner /> : ""}

				{error ? (
					<Notice status="error" isDismissible={false}>
						{error}
					</Notice>
				) : ""}

				{formFields.map((f) => (
					<TextControl label={f.label} readOnly />
				))}
			</form>
		</div>
	);
}
