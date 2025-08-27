import { easepick, RangePlugin } from '@easepick/bundle';
import '@easepick/bundle/dist/index.css';

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
	const $datePickerInput = $filter.querySelector(
		'.zetkin-events-filter__datepicker-input'
	);
	if (
		! $clear ||
		! $today ||
		! $tomorrow ||
		! $thisWeek ||
		! $datePicker ||
		! $datePickerInput
	) {
		return;
	}

	const $events = $calendar.querySelectorAll( '.zetkin-event' );

	const selectedClass = 'zetkin-events-filter__button--selected';
	const clearVisibleClass = 'zetkin-events-filter__clear-button--visible';

	$clear.addEventListener( 'click', () => {
		$events.forEach( ( $event ) => {
			$event.style.display = 'block';
		} );
		$clear.classList.remove( clearVisibleClass );
		$today.classList.remove( selectedClass );
		$tomorrow.classList.remove( selectedClass );
		$thisWeek.classList.remove( selectedClass );
		$datePicker.classList.remove( selectedClass );
	} );

	$today.addEventListener( 'click', () => {
		$events.forEach( ( $event ) => {
			const startTime = $event.getAttribute( 'data-starttime' );
			if ( startTime && isToday( new Date( startTime ) ) ) {
				$event.style.display = 'block';
			} else {
				$event.style.display = 'none';
			}
		} );
		$clear.classList.add( clearVisibleClass );
		$today.classList.add( selectedClass );
		$tomorrow.classList.remove( selectedClass );
		$thisWeek.classList.remove( selectedClass );
		$datePicker.classList.remove( selectedClass );
	} );

	$tomorrow.addEventListener( 'click', () => {
		$events.forEach( ( $event ) => {
			const startTime = $event.getAttribute( 'data-starttime' );
			if ( startTime && isTomorrow( new Date( startTime ) ) ) {
				$event.style.display = 'block';
			} else {
				$event.style.display = 'none';
			}
		} );
		$clear.classList.add( clearVisibleClass );
		$today.classList.remove( selectedClass );
		$tomorrow.classList.add( selectedClass );
		$thisWeek.classList.remove( selectedClass );
		$datePicker.classList.remove( selectedClass );
	} );

	$thisWeek.addEventListener( 'click', () => {
		$events.forEach( ( $event ) => {
			const startTime = $event.getAttribute( 'data-starttime' );
			if ( startTime && isThisWeek( new Date( startTime ) ) ) {
				$event.style.display = 'block';
			} else {
				$event.style.display = 'none';
			}
		} );
		$clear.classList.add( clearVisibleClass );
		$today.classList.remove( selectedClass );
		$tomorrow.classList.remove( selectedClass );
		$thisWeek.classList.add( selectedClass );
		$datePicker.classList.remove( selectedClass );
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
				$event.style.display = 'block';
			} else {
				$event.style.display = 'none';
			}
		} );

		$clear.classList.add( clearVisibleClass );
		$today.classList.remove( selectedClass );
		$tomorrow.classList.remove( selectedClass );
		$thisWeek.classList.remove( selectedClass );
		$datePicker.classList.add( selectedClass );
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
		onSelectDateRange( picker.getStartDate(), picker.getEndDate() );
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
