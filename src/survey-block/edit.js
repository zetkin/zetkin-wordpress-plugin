import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { useEffect, useState, useCallback, useRef } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { SelectControl, Spinner, Notice } from '@wordpress/components';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const [ surveys, setSurveys ] = useState( [] );
	const [ surveyQuestions, setSurveyQuestions ] = useState( [] );
	const [ loadingSurveys, setLoadingSurveys ] = useState( false );
	const [ loadingSurvey, setLoadingSurvey ] = useState( false );
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
		setSurveyQuestions( [] );
		try {
			const data = await apiFetch( {
				path: `/zetkin/surveys/${ attributes.surveyId }`,
			} );
			const questions = ( data?.elements || [] ).filter(
				( s ) => s.type === 'question'
			);
			setSurveyQuestions( questions );
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

	return (
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
						...surveys.map( ( survey ) => ( {
							label: survey.title,
							value: survey.id,
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

			{ /* Display survey invalid message if no questions found */ }
			{ attributes.surveyId &&
			! loading &&
			! error &&
			! surveyQuestions.length ? (
				<Notice status="error" isDismissible={ false }>
					{ __(
						'The selected survey has no questions, so is not a valid option.',
						'zetkin'
					) }
				</Notice>
			) : (
				''
			) }

			{ surveyQuestions.length ? (
				<ol>
					{ surveyQuestions.map( ( q ) => (
						<li key={ q.id }>{ q.question.question }</li>
					) ) }
				</ol>
			) : (
				''
			) }

			{ ! loading && ! surveyQuestions.length ? (
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
	);
}
