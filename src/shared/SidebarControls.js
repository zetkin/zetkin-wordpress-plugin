import { InspectorControls, PanelColorSettings } from '@wordpress/block-editor';
import { PanelBody, RangeControl } from '@wordpress/components';

export default function SidebarControls( {
	attributes,
	setAttributes,
	extraColorSettings,
} ) {
	return (
		<InspectorControls>
			{ /* Color Settings Panel */ }
			<PanelColorSettings
				title="Color Settings"
				colorSettings={ [
					{
						value: attributes.textColor,
						onChange: ( color ) =>
							setAttributes( { textColor: color } ),
						label: 'Text Color',
					},
					{
						value: attributes.backgroundColor,
						onChange: ( color ) =>
							setAttributes( { backgroundColor: color } ),
						label: 'Background Color',
					},
					{
						value: attributes.buttonColor,
						onChange: ( color ) =>
							setAttributes( { buttonColor: color } ),
						label: 'Button Color',
					},
					{
						value: attributes.buttonTextColor,
						onChange: ( color ) =>
							setAttributes( { buttonTextColor: color } ),
						label: 'Button Text Color',
					},
					...( extraColorSettings || [] ),
				] }
			/>

			{ /* Spacing Settings Panel */ }
			<PanelBody title="Spacing Settings" initialOpen={ false }>
				<RangeControl
					label="Padding Top"
					value={ attributes.paddingTop }
					onChange={ ( value ) =>
						setAttributes( { paddingTop: value } )
					}
					min={ 0 }
					max={ 100 }
				/>
				<RangeControl
					label="Padding Bottom"
					value={ attributes.paddingBottom }
					onChange={ ( value ) =>
						setAttributes( { paddingBottom: value } )
					}
					min={ 0 }
					max={ 100 }
				/>
				<RangeControl
					label="Padding Left"
					value={ attributes.paddingLeft }
					onChange={ ( value ) =>
						setAttributes( { paddingLeft: value } )
					}
					min={ 0 }
					max={ 100 }
				/>
				<RangeControl
					label="Padding Right"
					value={ attributes.paddingRight }
					onChange={ ( value ) =>
						setAttributes( { paddingRight: value } )
					}
					min={ 0 }
					max={ 100 }
				/>
				<RangeControl
					label="Spacing"
					value={ attributes.spacing }
					onChange={ ( value ) =>
						setAttributes( { spacing: value } )
					}
					min={ 0 }
					max={ 100 }
				/>
			</PanelBody>
		</InspectorControls>
	);
}
