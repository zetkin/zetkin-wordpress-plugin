import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { useEffect, useState } from 'react';
import { TextControl, Button, Spinner, Notice } from '@wordpress/components';
import SidebarControls from '../shared/SidebarControls';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const [ formId, setFormId ] = useState( attributes.formId );
	const [ formSubmitToken, setFormSubmitToken ] = useState(
		attributes.formSubmitToken
	);
	const [ loading, setLoading ] = useState( false );
	const [ error, setError ] = useState( '' );
	const [ formFields, setFormFields ] = useState( [] );

	useEffect( () => {
		if ( attributes.formId ) {
			onSubmit();
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	const onSubmit = async ( e ) => {
		e?.preventDefault();
		if ( ! formId || ! formSubmitToken ) {
			return;
		}
		setError( '' );
		setLoading( true );
		setFormFields( [] );
		try {
			const data = await apiFetch( {
				path: addQueryArgs( '/zetkin/join-form', {
					form_id: formId,
				} ),
			} );
			setFormFields( data.fields );
			setAttributes( {
				formId: Number( formId ),
				formSubmitToken,
			} );
		} catch {
			setError(
				__(
					'Could not load the form, please check your details.',
					'zetkin'
				)
			);
		} finally {
			setLoading( false );
		}
	};

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

	const formStyle = { marginTop: '1rem', gap: attributes.spacing };

	return (
		<>
			<SidebarControls
				attributes={ attributes }
				setAttributes={ setAttributes }
			/>
			<div
				{ ...useBlockProps( { className: 'zetkin-join-form-block' } ) }
				style={ blockStyle }
			>
				<form onSubmit={ onSubmit }>
					<TextControl
						label={ __( 'Form ID', 'zetkin' ) }
						type="number"
						value={ formId }
						onChange={ ( value ) => setFormId( value ) }
						required
					/>

					<TextControl
						label={ __( 'Join Form Submit Token', 'zetkin' ) }
						value={ formSubmitToken }
						onChange={ ( value ) => setFormSubmitToken( value ) }
						required
					/>

					{ ! attributes.formId || ! attributes.formSubmitToken ? (
						<Notice isDismissible={ false }>
							{ __(
								'Load a form before saving this page, or remove this block.',
								'zetkin'
							) }
						</Notice>
					) : (
						''
					) }

					<Button variant="primary" type="submit">
						{ __( 'Load form', 'zetkin' ) }
					</Button>

					{ loading ? <Spinner /> : '' }

					{ error ? (
						<Notice status="error" isDismissible={ false }>
							{ error }
						</Notice>
					) : (
						''
					) }

					<div style={ formStyle } className="zetkin-join-form">
						{ formFields.map( ( f ) => (
							<FieldElement
								key={ f.slug }
								field={ f }
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
				</form>
			</div>
		</>
	);
}

function FieldElement( { field, textColor } ) {
	const formatLabel = ( slug ) => {
		return slug
			.split( '_' )
			.map(
				( word ) =>
					word.charAt( 0 ).toUpperCase() +
					word.slice( 1 ).toLowerCase()
			)
			.join( ' ' );
	};

	if ( field.slug === 'gender' ) {
		return (
			<div className="zetkin-join-form-input">
				<label htmlFor={ field.slug }>
					{ __( 'Gender', 'zetkin' ) }
				</label>
				<select
					id={ field.slug }
					className="zetkin-select"
					name={ field.slug }
					style={
						textColor
							? { color: textColor, borderColor: textColor }
							: {}
					}
				>
					<option value="unspecified">Unspecified</option>
					<option value="m">Male</option>
					<option value="f">Female</option>
					<option value="o">Other</option>
				</select>
			</div>
		);
	}

	const label = formatLabel( field.slug );

	return (
		<div className="zetkin-join-form-input">
			<label htmlFor={ field.slug }>{ label }</label>
			<input
				id={ field.slug }
				className="zetkin-input"
				name={ field.slug }
				style={ textColor ? { borderColor: textColor } : {} }
			/>
		</div>
	);
}
