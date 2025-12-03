import { easepick, RangePlugin } from '@easepick/bundle';
import '@easepick/bundle/dist/index.css';

const selectedClass = 'zetkin-events-filter__button--selected';

const $filters = document.querySelectorAll( '.zetkin-events-filter' );

$filters.forEach( ( $filter ) => {
	const $calendar = $filter.closest( '.zetkin-calendar' );
	if ( ! $calendar ) {
		return;
	}
	const $buttons = $filter.querySelectorAll(
		'.zetkin-events-filter__button'
	);
	const $clear = $buttons[ 0 ];
	const $today = $buttons[ 1 ];
	const $tomorrow = $buttons[ 2 ];
	const $thisWeek = $buttons[ 3 ];
	const $datePicker = $buttons[ 4 ];
	const $eventType = $filter.querySelector( '.zetkin-event-types-filter' );

	const $datePickerInput = $filter.querySelector(
		'.zetkin-events-filter__datepicker-input'
	);
	const $noEvents = $calendar.querySelector( '.zetkin-no-events' );

	if (
		! $clear ||
		! $today ||
		! $tomorrow ||
		! $thisWeek ||
		! $datePicker ||
		! $datePickerInput ||
		! $eventType ||
		! $noEvents
	) {
		return;
	}

	const $events = $calendar.querySelectorAll( '.zetkin-event' );

	const clearVisibleClass = 'zetkin-events-filter__clear-button--visible';

	const $eventTypes = $eventType.querySelector( '.zetkin-event-types' );
	const $eventTypeButton = $eventType.querySelector( 'button' );
	const $pagination = $calendar.querySelector( '.zetkin-pagination' );
	const $pageButtons = $calendar.querySelectorAll(
		'.zetkin-pagination-button'
	);

	$clear.addEventListener( 'click', () => {
		$events.forEach( ( $event ) => {
			$event.removeAttribute( 'data-hidden-date' );
			$event.removeAttribute( 'data-hidden-type' );
		} );
		$clear.classList.remove( clearVisibleClass );
		$today.classList.remove( selectedClass );
		$tomorrow.classList.remove( selectedClass );
		$thisWeek.classList.remove( selectedClass );
		$datePicker.classList.remove( selectedClass );
		$datePicker.querySelector( 'svg' ).style.display = 'block';
		$datePicker.querySelector( 'span' ).innerHTML = '';
		const $eventTypeButtonTexts =
			$eventTypeButton.querySelectorAll( 'span' );
		$eventTypeButtonTexts[ 0 ].style.display = 'inline';
		$eventTypeButtonTexts[ 1 ].innerText = '';
		$eventTypeButton.classList.remove( selectedClass );
		$eventTypes.querySelectorAll( 'input' ).forEach( ( $checkbox ) => {
			$checkbox.checked = false;
		} );
		reorderButtons();
		updatePagination( $pagination, $noEvents );
	} );

	$today.addEventListener( 'click', () => {
		$events.forEach( ( $event ) => {
			const startTime = $event.getAttribute( 'data-starttime' );
			if ( startTime && isToday( new Date( startTime ) ) ) {
				$event.removeAttribute( 'data-hidden-date' );
			} else {
				$event.setAttribute( 'data-hidden-date', 'true' );
			}
		} );
		$clear.classList.add( clearVisibleClass );
		$today.classList.add( selectedClass );
		$tomorrow.classList.remove( selectedClass );
		$thisWeek.classList.remove( selectedClass );
		$datePicker.classList.remove( selectedClass );
		reorderButtons();
		updatePagination( $pagination, $noEvents );
	} );

	$tomorrow.addEventListener( 'click', () => {
		$events.forEach( ( $event ) => {
			const startTime = $event.getAttribute( 'data-starttime' );
			if ( startTime && isTomorrow( new Date( startTime ) ) ) {
				$event.removeAttribute( 'data-hidden-date' );
			} else {
				$event.setAttribute( 'data-hidden-date', 'true' );
			}
		} );
		$clear.classList.add( clearVisibleClass );
		$today.classList.remove( selectedClass );
		$tomorrow.classList.add( selectedClass );
		$thisWeek.classList.remove( selectedClass );
		$datePicker.classList.remove( selectedClass );
		reorderButtons();
		updatePagination( $pagination, $noEvents );
	} );

	$thisWeek.addEventListener( 'click', () => {
		$events.forEach( ( $event ) => {
			const startTime = $event.getAttribute( 'data-starttime' );
			if ( startTime && isThisWeek( new Date( startTime ) ) ) {
				$event.removeAttribute( 'data-hidden-date' );
			} else {
				$event.setAttribute( 'data-hidden-date', 'true' );
			}
		} );
		$clear.classList.add( clearVisibleClass );
		$today.classList.remove( selectedClass );
		$tomorrow.classList.remove( selectedClass );
		$thisWeek.classList.add( selectedClass );
		$datePicker.classList.remove( selectedClass );
		reorderButtons();
		updatePagination( $pagination, $noEvents );
	} );

	const onSelectDateRange = ( start, end ) => {
		if ( ! start || ! end ) {
			return;
		}

		$events.forEach( ( $event ) => {
			const startTime = $event.getAttribute( 'data-starttime' );
			if (
				startTime &&
				isWithinDates( start, end, new Date( startTime ) )
			) {
				$event.removeAttribute( 'data-hidden-date' );
			} else {
				$event.setAttribute( 'data-hidden-date', 'true' );
			}
		} );

		const $calendarSvg = $datePicker.querySelector( 'svg' );
		$calendarSvg.style.display = 'none';
		const $dates = $datePicker.querySelector( 'span' );
		$dates.innerText = getDateRangeStr( start, end );

		$clear.classList.add( clearVisibleClass );
		$today.classList.remove( selectedClass );
		$tomorrow.classList.remove( selectedClass );
		$thisWeek.classList.remove( selectedClass );
		$datePicker.classList.add( selectedClass );
		reorderButtons();
		updatePagination( $pagination, $noEvents );
	};

	const picker = new easepick.create( {
		element: $datePickerInput,
		css: [ window.ZETKIN_CALENDAR_BLOCK_BASE_URL + '/view.css' ],
		plugins: [ RangePlugin ],
		RangePlugin: {
			tooltip: true,
		},
		zIndex: 10,
		setup( p ) {
			p.on( 'select', ( e ) => {
				onSelectDateRange( e.detail.start, e.detail.end );
			} );
		},
	} );

	$datePicker.addEventListener( 'click', () => {
		picker.show();
	} );

	$eventTypeButton.addEventListener( 'click', () => {
		$eventTypes.classList.add( 'zetkin-event-types--visible' );
	} );

	const $eventTypeItems =
		$eventTypes.querySelectorAll( '.zetkin-event-type' );
	$eventTypeItems.forEach( ( $item ) =>
		$item.querySelector( 'input' ).addEventListener( 'change', () => {
			const eventTypes = [];
			$eventTypeItems.forEach( ( $eventTypeItem ) => {
				if ( $eventTypeItem.querySelector( 'input' ).checked ) {
					eventTypes.push(
						$eventTypeItem.querySelector( 'label' ).innerText
					);
				}
			} );

			$events.forEach( ( $event ) => {
				const eventType = $event.getAttribute( 'data-event-type' );
				if (
					eventTypes.includes( eventType ) ||
					eventTypes.length === 0
				) {
					$event.removeAttribute( 'data-hidden-type' );
				} else {
					$event.setAttribute( 'data-hidden-type', 'true' );
				}
			} );

			const $eventTypeButtonTexts =
				$eventTypeButton.querySelectorAll( 'span' );
			if ( eventTypes.length === 0 ) {
				$eventTypeButtonTexts[ 0 ].style.display = 'inline';
				$eventTypeButtonTexts[ 1 ].innerText = '';
				$eventTypeButton.classList.remove( selectedClass );
			} else if ( eventTypes.length === 1 ) {
				$eventTypeButtonTexts[ 0 ].style.display = 'none';
				$eventTypeButtonTexts[ 1 ].innerText = eventTypes[ 0 ];
				$eventTypeButton.classList.add( selectedClass );
				$clear.classList.add( clearVisibleClass );
			} else {
				$eventTypeButtonTexts[ 0 ].style.display = 'none';
				$eventTypeButtonTexts[ 1 ].innerText =
					eventTypes.length +
					' ' +
					$eventTypeButtonTexts[ 0 ].innerText.toLowerCase();
				$eventTypeButton.classList.add( selectedClass );
				$clear.classList.add( clearVisibleClass );
			}

			updatePagination( $pagination, $noEvents );
		} )
	);

	$pageButtons.forEach( ( $button ) => {
		$button.addEventListener( 'click', () => {
			handlePageButtonClick( $button );
		} );
	} );
} );

const isToday = ( date ) => {
	const today = new Date();
	return (
		today.getDate() === date.getDate() &&
		today.getMonth() === date.getMonth() &&
		today.getFullYear() === date.getFullYear()
	);
};

const isTomorrow = ( date ) => {
	const today = new Date();
	const tomorrow = new Date( today );
	tomorrow.setDate( today.getDate() + 1 );

	return (
		tomorrow.getDate() === date.getDate() &&
		tomorrow.getMonth() === date.getMonth() &&
		tomorrow.getFullYear() === date.getFullYear()
	);
};

const isThisWeek = ( date ) => {
	const today = new Date();
	today.setHours( 0, 0, 0, 0 );

	const sixDaysFromNow = new Date( today );
	sixDaysFromNow.setDate( today.getDate() + 6 );
	sixDaysFromNow.setHours( 23, 59, 59, 999 );

	return date >= today && date <= sixDaysFromNow;
};

const isWithinDates = ( start, end, date ) => {
	start.setHours( 0, 0, 0, 0 );
	end.setHours( 23, 59, 59, 999 );
	return date >= start && date <= end;
};

const reorderButtons = () => {
	$filters.forEach( ( $filter ) => {
		const $clear = $filter.querySelector(
			'.zetkin-events-filter__clear-button'
		);
		const $today = $filter.querySelector(
			'.zetkin-events-filter__today-button'
		);
		const $tomorrow = $filter.querySelector(
			'.zetkin-events-filter__tomorrow-button'
		);
		const $thisWeek = $filter.querySelector(
			'.zetkin-events-filter__this-week-button'
		);
		const $datePicker = $filter.querySelector(
			'.zetkin-events-filter__datepicker-button'
		);
		const $eventType = $filter.querySelector(
			'.zetkin-event-types-filter'
		);

		const $items = [ $today, $tomorrow, $thisWeek, $datePicker ];
		const $selectedItems = $items.filter( ( $item ) =>
			$item.classList.contains( selectedClass )
		);
		const $unselectedItems = $items.filter(
			( $item ) => ! $item.classList.contains( selectedClass )
		);

		$items.push( $eventType );

		if ( $eventType.querySelector( `.${ selectedClass }` ) ) {
			$selectedItems.push( $eventType );
		} else {
			$unselectedItems.push( $eventType );
		}

		const $parent = $clear.parentElement;
		for ( const $item of $items ) {
			$parent.removeChild( $item );
		}
		for ( const $item of $selectedItems ) {
			$parent.appendChild( $item );
		}
		for ( const $item of $unselectedItems ) {
			$parent.appendChild( $item );
		}
	} );
};

const updatePagination = ( $pagination, $noEvents ) => {
	const $calendar = $noEvents.closest( '.zetkin-calendar' );
	const $visibleEvents = $calendar.querySelectorAll(
		'.zetkin-event:not([data-hidden-type]):not([data-hidden-date])'
	);
	$noEvents.style.display = $visibleEvents.length ? 'none' : 'block';

	const pageSize = Number( $calendar.getAttribute( 'data-page-size' ) );
	const pages = Math.ceil( $visibleEvents.length / pageSize );
	const $pageItems = $pagination.querySelectorAll(
		'.zetkin-pagination-page'
	);
	$pageItems.forEach( ( $item, i ) => {
		if ( i < pages ) {
			$item.style.display = 'block';
		} else {
			$item.style.display = 'none';
		}
	} );
	if ( $pageItems.length ) {
		handlePageButtonClick( $pageItems[ 0 ] );
	}
};

const getDateRangeStr = ( start, end ) => {
	const startDay = start.getDate();
	const endDay = end.getDate();

	const startMonth = start.toLocaleString( 'en-US', { month: 'short' } );
	const endMonth = end.toLocaleString( 'en-US', { month: 'short' } );

	const startYear = start.getFullYear();
	const endYear = end.getFullYear();

	// Same month and year
	if ( startMonth === endMonth && startYear === endYear ) {
		return `${ startDay } ${ startMonth } - ${ endDay }`;
	}

	// Different months, same year
	if ( startYear === endYear ) {
		return `${ startDay } ${ startMonth } - ${ endDay } ${ endMonth }`;
	}

	// Different years
	return `${ startDay } ${ startMonth }, ${ startYear } - ${ endDay } ${ endMonth }, ${ endYear }`;
};

const handlePageButtonClick = ( $button ) => {
	const $calendar = $button.closest( '.zetkin-calendar' );
	const $pageButtons = $calendar.querySelectorAll(
		'.zetkin-pagination-button'
	);
	const pageSize = Number( $calendar.getAttribute( 'data-page-size' ) );

	const page = Number( $button.innerText - 1 );
	$pageButtons.forEach( ( $p ) => {
		const p = Number( $p.innerText - 1 );
		if ( p === page ) {
			$p.classList.add( 'zetkin-pagination-button--selected' );
		} else {
			$p.classList.remove( 'zetkin-pagination-button--selected' );
		}
	} );
	const start = pageSize * page;
	const end = pageSize * ( page + 1 );
	const $visibleEvents = $calendar.querySelectorAll(
		'.zetkin-event:not([data-hidden-type]):not([data-hidden-date])'
	);
	$visibleEvents.forEach( ( $event, i ) => {
		if ( i >= start && i < end ) {
			$event.removeAttribute( 'data-hidden-page' );
		} else {
			$event.setAttribute( 'data-hidden-page', 'true' );
		}
	} );
};

window.addEventListener( 'click', ( event ) => {
	if ( ! event.target.closest( '.zetkin-event-types-filter' ) ) {
		const $eventTypeLists = document.querySelectorAll(
			'.zetkin-event-types.zetkin-event-types--visible'
		);
		let reorderRequired = false;
		for ( const $eventTypeList of $eventTypeLists ) {
			$eventTypeList.classList.remove( 'zetkin-event-types--visible' );
			reorderRequired = true;
		}
		if ( reorderRequired ) {
			reorderButtons();
		}
	}
} );
