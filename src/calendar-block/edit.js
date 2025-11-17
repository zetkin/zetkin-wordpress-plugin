import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { Spinner } from '@wordpress/components';
import { useEffect, useState } from 'react';
import { dateI18n } from '@wordpress/date';
import SidebarControls from '../shared/SidebarControls';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const [ events, setEvents ] = useState( null );
	useEffect( () => {
		const fetch = async () => {
			const data = await apiFetch( {
				path: '/zetkin/events',
			} );
			setEvents(
				data.length
					? data
					: [
							{
								id: 1,
								activity: { title: 'Demonstration' },
								campaign: { title: 'Climate Justice' },
								title: 'March for Justice (Example Event)',
								start_time: new Date().toISOString(),
								end_time: new Date(
									new Date().getTime() + 1000 * 60 * 60 * 4
								).toISOString(),
								organization: { title: 'Your Organization' },
								location: { title: 'City Centre' },
							},
					  ]
			);
		};
		fetch();
	}, [] );

	const [ isStaging ] = useEntityProp(
		'root',
		'site',
		'zetkin_staging_environment'
	);

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

	return (
		<>
			<SidebarControls
				attributes={ attributes }
				setAttributes={ setAttributes }
				extraColorSettings={ [
					{
						value: attributes.eventColor,
						onChange: ( color ) =>
							setAttributes( { eventColor: color } ),
						label: 'Event Color',
					},
				] }
			/>
			<div
				{ ...useBlockProps( { className: 'zetkin-calendar-block' } ) }
				style={ blockStyle }
			>
				{ events === null ? (
					<Spinner />
				) : (
					<>
						<ol className="zetkin-calendar-events">
							{ events.slice( 0, 3 ).map( ( e ) => (
								<Event
									key={ e.id }
									event={ e }
									isStaging={ isStaging }
									attributes={ attributes }
								/>
							) ) }
						</ol>
						{ events.length > 3 ? (
							<em
								className="zetkin-editor-more-events"
								style={ { color: 'initial' } }
							>
								...
								{ events.length - 3 === 1
									? __(
											'One more event will be displayed',
											'zetkin'
									  )
									: events.length -
									  3 +
									  ' ' +
									  __(
											'more events will be displayed',
											'zetkin'
									  ) }
								.
							</em>
						) : null }
					</>
				) }
			</div>
		</>
	);
}

function Event( {
	event: {
		id,
		activity,
		campaign,
		title,
		start_time: startTime,
		end_time: endTime,
		organization,
		location,
	},
	isStaging,
	attributes,
} ) {
	const safeTitle =
		title || activity?.title || __( 'Untitled event', 'zetkin' );
	const baseURL = isStaging
		? 'https://app.dev.zetkin.org'
		: 'https://app.zetkin.org';
	const url = organization?.id
		? `${ baseURL }/o/${ organization.id }/events/${ id }`
		: '';
	const organizationTitle =
		organization?.title ?? __( 'Unknown organization', 'zetkin' );
	const project = campaign?.title
		? `${ campaign.title } / ${ organizationTitle }`
		: organizationTitle;
	const dates = getFormattedEventTime( startTime, endTime );

	const buttonStyle = {};
	if ( attributes.buttonColor ) {
		buttonStyle.backgroundColor = attributes.buttonColor;
	}
	if ( attributes.buttonTextColor ) {
		buttonStyle.color = attributes.buttonTextColor;
	}

	const eventStyle = { marginBottom: attributes.spacing };
	if ( attributes.eventColor ) {
		eventStyle.backgroundColor = attributes.eventColor;
	}

	return (
		<li
			className="zetkin-event"
			data-starttime="2025-06-23T08:00:00+00:00"
			data-event-type="Uncategorized"
			style={ eventStyle }
		>
			<h2 className="zetkin-event__title">
				<a
					href={ url }
					target="_blank"
					style={
						attributes.textColor
							? { color: attributes.textColor }
							: {}
					}
					rel="noreferrer"
				>
					{ safeTitle }
				</a>
			</h2>
			<div className="zetkin-event__row">
				<svg viewBox="0 0 24 24" fill={ attributes.textColor }>
					<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8"></path>
					<circle cx="8" cy="14" r="2"></circle>
					<circle cx="12" cy="8" r="2"></circle>
					<circle cx="16" cy="14" r="2"></circle>
				</svg>
				<p className="zetkin-event__project">{ project }</p>
			</div>
			{ dates ? (
				<div className="zetkin-event__row">
					<svg viewBox="0 0 24 24" fill={ attributes.textColor }>
						<path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8m.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7z"></path>
					</svg>
					<p className="zetkin-event__time">{ dates }</p>
				</div>
			) : null }
			{ location?.title ? (
				<div className="zetkin-event__row">
					<svg viewBox="0 0 24 24" fill={ attributes.textColor }>
						<path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7M7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9"></path>
						<circle cx="12" cy="9" r="2.5"></circle>
					</svg>
					<p className="zetkin-event__location">{ location.title }</p>
				</div>
			) : null }
			<a
				className="zetkin-event__sign-up zetkin-submit-button"
				href={ url }
				target="_blank"
				style={ buttonStyle }
				rel="noreferrer"
			>
				Sign up
			</a>
		</li>
	);
}

/**
 * Parameters should be strings formatted Zetkin API style
 * e.g. 2025-05-21T15:00:00+00:00
 *
 * Returns Zetkin-style formatted dates
 * e.g. June 11, 4:40 PM - 6:40 PM
 * @param {string} startTime
 * @param {string} endTime
 */
export function getFormattedEventTime( startTime, endTime ) {
	if ( ! startTime && ! endTime ) {
		return null;
	}

	// If no start time is provided, use the end time instead
	if ( ! startTime ) {
		return getFormattedTime( endTime );
	}

	if ( ! endTime ) {
		return getFormattedTime( startTime );
	}

	return getFormattedTimes( startTime, endTime );
}

function getFormattedTime( startTime ) {
	const date = new Date( startTime );
	const now = new Date();

	if ( date.getFullYear() === now.getFullYear() ) {
		return dateI18n( 'F j, g:i A', startTime );
	}
	return dateI18n( 'F j Y, g:i A', startTime );
}

function getFormattedTimes( startTime, endTime ) {
	const startDateStr = dateI18n( 'F j Y', startTime );
	const endDateStr = dateI18n( 'F j Y', endTime );

	if ( startDateStr === endDateStr ) {
		return (
			getFormattedTime( startTime ) + ' - ' + dateI18n( 'g:i A', endTime )
		);
	}
	return (
		getFormattedTime( startTime ) +
		' - ' +
		dateI18n( 'F j, g:i A', endTime )
	);
}
