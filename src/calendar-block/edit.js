/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit() {
	return (
		<ol { ...useBlockProps({ className: "zetkin-events" }) }>
			<li className="zetkin-event">
				<h2 className="zetkin-event__title">{__('Example Event Title', 'zetkin')}</h2>
				<p className="zetkin-event__project">{__('Example Project', 'zetkin')} / {__('Example Organisation', 'zetkin')}</p>
				<p className="zetkin-event__time">June 11, 4:40 PM - 7:00 PM</p>
				<p className="zetkin-event__location">{__('Example Location', 'zetkin')}</p>
			</li>
		</ol>
	);
}
