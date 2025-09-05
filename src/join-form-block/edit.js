import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { useState } from 'react';
import { TextControl, Button, Spinner, Notice } from '@wordpress/components';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const [ formId, setFormId ] = useState( attributes.formId );
	const [ formSubmitToken, setFormSubmitToken ] = useState(
		attributes.formSubmitToken
	);
	const [ loading, setLoading ] = useState( false );
	const [ error, setError ] = useState( '' );
	const [ formFields, setFormFields ] = useState( [] );

	const onSubmit = async ( e ) => {
		e.preventDefault();
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

	return (
		<div { ...useBlockProps( { className: 'zetkin-join-form-block' } ) }>
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

				{ ! attributes.formId || ! attributes.formFields ? (
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

				<div style={ { marginTop: '1rem' } }>
					{ formFields.map( ( f ) => (
						<TextControl
							key={ f.slug }
							label={ f.slug.replace( /_/g, ' ' ) }
							readOnly
						/>
					) ) }
				</div>
			</form>
		</div>
	);
}
