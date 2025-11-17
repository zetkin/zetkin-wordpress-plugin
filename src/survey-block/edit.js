import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { useEffect, useState, useCallback, useRef } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { SelectControl, Spinner, Notice } from '@wordpress/components';
import SidebarControls from '../shared/SidebarControls';
import './editor.scss';

const INPUT_ID_PREFIX = 'zetkin-survey-question-';

export default function Edit( { attributes, setAttributes } ) {
	const [ surveys, setSurveys ] = useState( [] );
	const [ survey, setSurvey ] = useState( null );
	const [ loadingSurveys, setLoadingSurveys ] = useState( false );
	const [ loadingSurvey, setLoadingSurvey ] = useState(
		Boolean( attributes.surveyId )
	);
	const [ error, setError ] = useState( '' );
	const loadingSurveysRef = useRef( false );
	const loadingSurveyRef = useRef( null );

	const loadSurveys = useCallback( async () => {
		if ( surveys.length || loadingSurveysRef.current ) {
			return;
		}

		loadingSurveysRef.current = true;

		setError( '' );
		setLoadingSurveys( true );
		setSurveys( [] );
		try {
			const data = await apiFetch( { path: '/zetkin/surveys' } );
			const fetchedSurveys = ( data || [] ).map( ( s ) => ( {
				id: s.id,
				title: s.title,
			} ) );
			setSurveys( fetchedSurveys );
		} catch {
			setError(
				__(
					'Could not load your surveys. Please check your organization ID in the main Zetkin settings page.',
					'zetkin'
				)
			);
		} finally {
			setLoadingSurveys( false );
			loadingSurveysRef.current = false;
		}
	}, [ surveys.length ] );

	const loadSurvey = useCallback( async () => {
		if (
			! attributes.surveyId ||
			loadingSurveyRef.current === attributes.surveyId
		) {
			setLoadingSurvey( false );
			return;
		}

		loadingSurveyRef.current = attributes.surveyId;

		setError( '' );
		setLoadingSurvey( true );
		setSurvey( null );
		try {
			const data = await apiFetch( {
				path: `/zetkin/surveys/${ attributes.surveyId }`,
			} );
			setSurvey( data );
		} catch {
			setError(
				__(
					'Could not load this survey. Please try again later or try a different survey.',
					'zetkin'
				)
			);
		} finally {
			setLoadingSurvey( false );
			loadingSurveyRef.current = null;
		}
	}, [ attributes.surveyId ] );

	useEffect( () => {
		loadSurveys();
	}, [ loadSurveys ] );

	useEffect( () => {
		loadSurvey();
	}, [ loadSurvey, attributes.surveyId ] );

	const loading = loadingSurveys || loadingSurvey;

	const blockStyle = {
		paddingTop: attributes.paddingTop,
		paddingRight: attributes.paddingRight,
		paddingBottom: attributes.paddingBottom,
		paddingLeft: attributes.paddingLeft,
	};
	if ( attributes.textColor ) {
		blockStyle.color = attributes.textColor;
	}
	if ( attributes.backgroundColor ) {
		blockStyle.backgroundColor = attributes.backgroundColor;
	}

	const buttonStyle = {};
	if ( attributes.buttonColor ) {
		buttonStyle.backgroundColor = attributes.buttonColor;
	}
	if ( attributes.buttonTextColor ) {
		buttonStyle.color = attributes.buttonTextColor;
	}

	return (
		<>
			<SidebarControls
				attributes={ attributes }
				setAttributes={ setAttributes }
			/>

			<div { ...useBlockProps( { className: 'zetkin-survey-block' } ) }>
				{ loading ? <Spinner /> : '' }

				{ error ? (
					<Notice status="error" isDismissible={ false }>
						{ error }
					</Notice>
				) : (
					''
				) }

				{ ! loading && surveys.length ? (
					<SelectControl
						label={ __( 'Choose a survey', 'zetkin' ) }
						value={ attributes.surveyId }
						options={ [
							{ label: __( 'Choose a survey', '' ), value: 0 },
							...surveys.map( ( s ) => ( {
								label: s.title,
								value: s.id,
							} ) ),
						] }
						onChange={ ( value ) =>
							setAttributes( { surveyId: Number( value ) } )
						}
						disabled={ loading }
					/>
				) : (
					''
				) }

				{ ! loading && ! surveys.length ? (
					<Notice status="error" isDismissible={ false }>
						{ __(
							'No surveys found. Please make sure you have created one in your Zetkin organization admin.',
							'zetkin'
						) }
					</Notice>
				) : (
					''
				) }

				{ survey && (
					<div style={ blockStyle }>
						{ survey.title && (
							<h2
								className="zetkin-survey-title"
								style={
									attributes.textColor
										? {
												color: attributes.textColor,
												marginBottom:
													attributes.spacing,
										  }
										: { marginBottom: attributes.spacing }
								}
							>
								{ survey.title }
							</h2>
						) }
						<div
							className="zetkin-survey"
							style={ { gap: attributes.spacing } }
						>
							{ survey.elements.map( ( e ) => (
								<SurveyElement
									key={ e.id }
									element={ e }
									textColor={ attributes.textColor }
								/>
							) ) }
							<button
								className="zetkin-survey-submit zetkin-submit-button"
								style={ buttonStyle }
							>
								{ __( 'Submit', 'zetkin' ) }
							</button>
						</div>
					</div>
				) }

				{ ! loading && ! survey ? (
					<Notice isDismissible={ false }>
						{ __(
							'Choose a valid survey before saving this page, or remove this block.',
							'zetkin'
						) }
					</Notice>
				) : (
					''
				) }
			</div>
		</>
	);
}

function SurveyElement( { element, textColor } ) {
	const type = element.type;

	if ( type === 'text' ) {
		return <TextElement element={ element } />;
	}

	const question = element.question;
	const responseType = question.response_type;
	const responseConfig = question.response_config;
	const widgetType = responseConfig?.widget_type;

	if ( responseType === 'text' ) {
		if ( responseConfig?.multiline ) {
			return (
				<TextAreaElement element={ element } textColor={ textColor } />
			);
		}
		return <TextInputElement element={ element } textColor={ textColor } />;
	}

	if ( widgetType === 'checkbox' || widgetType === 'radio' ) {
		return <OptionsElement element={ element } />;
	}

	if ( widgetType === 'select' ) {
		return <SelectElement element={ element } textColor={ textColor } />;
	}

	return null;
}

function TextElement( { element } ) {
	const header = element.text_block?.header;
	const content = element.text_block?.content;

	return (
		<div className="zetkin-survey-text">
			{ header && (
				<p className="zetkin-survey-text__header">{ header }</p>
			) }
			{ content && (
				<p className="zetkin-survey-text__content">{ content }</p>
			) }
		</div>
	);
}

function TextInputElement( { element, textColor } ) {
	const id = INPUT_ID_PREFIX + element.id;
	const name = element.id + '_text';
	const question = element.question;

	return (
		<div className="zetkin-survey-question zetkin-survey-question--single-line">
			<Label element={ element } id={ id } />
			<input
				id={ id }
				className="zetkin-survey-question__input zetkin-input"
				name={ name }
				type={ question.response_config?.type || 'text' }
				required={ question.required }
				style={ textColor ? { borderColor: textColor } : {} }
			/>
		</div>
	);
}

function TextAreaElement( { element, textColor } ) {
	const id = INPUT_ID_PREFIX + element.id;
	const name = element.id + '_text';

	return (
		<div className="zetkin-survey-question zetkin-survey-question--multi-line">
			<Label element={ element } id={ id } />
			<textarea
				id={ id }
				className="zetkin-survey-question__textarea zetkin-input"
				name={ name }
				required={ element.question.required }
				rows={ 4 }
				style={ textColor ? { borderColor: textColor } : {} }
			/>
		</div>
	);
}

function OptionsElement( { element } ) {
	const question = element.question;
	const name = element.id + '_options';
	const widgetType = question.response_config.widget_type;

	return (
		<div
			className={ `zetkin-survey-question zetkin-survey-question--${ widgetType }` }
		>
			<Label element={ element } />
			<ol className="zetkin-survey-question__options">
				{ ( question.options || [] ).map( ( option ) => {
					const optionId = `zetkin-survey-option-${ option.id }`;
					return (
						<label
							key={ option.id }
							className="zetkin-survey-question__option"
							htmlFor={ optionId }
						>
							<input
								id={ optionId }
								name={ `${ name }[]` }
								type={ widgetType }
								value={ option.id }
							/>
							<span>{ option.text }</span>
						</label>
					);
				} ) }
			</ol>
		</div>
	);
}

function SelectElement( { element, textColor } ) {
	const id = INPUT_ID_PREFIX + element.id;
	const name = element.id + '_options';
	const question = element.question;

	return (
		<div className="zetkin-survey-question zetkin-survey-question--select">
			<Label element={ element } id={ id } />
			<select
				className="zetkin-survey-question__select zetkin-select"
				id={ id }
				name={ name }
				style={
					textColor
						? { borderColor: textColor, color: textColor }
						: {}
				}
			>
				<option value="">{ __( 'Select an option', 'zetkin' ) }</option>
				{ ( question.options || [] ).map( ( option ) => (
					<option key={ option.id } value={ option.id }>
						{ option.text }
					</option>
				) ) }
			</select>
		</div>
	);
}

function Label( { element, id } ) {
	const question = element.question;
	const widgetType = question.response_config?.widget_type;
	const isRealLabel = widgetType !== 'checkbox' && widgetType !== 'radio';
	const questionText = question.question || '';
	const description = question.description;

	return (
		<>
			{ isRealLabel ? (
				<label className="zetkin-survey-question__label" htmlFor={ id }>
					{ questionText }
				</label>
			) : (
				<span className="zetkin-survey-question__label">
					{ questionText }
				</span>
			) }
			{ description && (
				<p className="zetkin-survey-question__description">
					{ description }
				</p>
			) }
		</>
	);
}
