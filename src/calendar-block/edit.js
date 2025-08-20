import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import './editor.scss';

export default function Edit() {
	return (
		<div { ...useBlockProps( { className: 'zetkin-calendar-block' } ) }>
			<ol className="zetkin-events">
				<li className="zetkin-event">
					<h2 className="zetkin-event__title">
						{ __( 'Example Event Title', 'zetkin' ) }
					</h2>
					<p className="zetkin-event__project">
						{ __( 'Example Project', 'zetkin' ) } /{ ' ' }
						{ __( 'Example Organization', 'zetkin' ) }
					</p>
					<p className="zetkin-event__time">
						June 11, 4:40 PM - 7:00 PM
					</p>
					<p className="zetkin-event__location">
						{ __( 'Example Location', 'zetkin' ) }
					</p>
				</li>
			</ol>
		</div>
	);
}
