<?php
	if( !isset($_REQUEST['quote']) or empty($_REQUEST['quote']) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'No se ingreso quote';
		die(json_encode($result));
	} else if ( !isset($_REQUEST['quoted']) or empty($_REQUEST['quoted']) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'Debe elegirse al menos un quoteado';
		die(json_encode($result));
	}
	$quote = utf8_decode( $_REQUEST['quote'] );
	$unknown = array( );
	$known = array( );

	foreach( $_REQUEST['quoted'] as $id ) {
		if( (int) $id <= 0 ) {
			array_push( $unknown, $id );
		} else {
			array_push( $known, $id );
		}
	}

	if( !empty($unknown) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'QuotedId invalidos';
		$result['invalid'] = $unknown;
	}
	if( empty( $known ) ) {
		$result['error'] = TRUE;
		$result['errorDescription'] = 'Debe elegirse al menos un quoteado valido';
		die(json_encode($result));
	}

	require_once('header.php');

	$qdr = new QuotedRepository( $db );
	$qr = new QuoteRepository( $db, $qdr );

	$quoted = array( );

	foreach( array_keys($known) as $key) {
		$id = $known[$key];
		try {
			$q = $qdr->getQuotedWithId( $id );
			array_push( $quoted, $q );
		} catch (OutOfRangeException $e) {
			array_push( $unknown, $id );
			unset( $known[$key] );
		}
	}

	$qr->addQuote( $quote, $quoted );
	$result['success'] = TRUE;

	$quote = 'Nueva quote: <br/>&nbsp;&nbsp;&nbsp;&nbsp;<i>'.$quote.'</i>';
	$result['mail'] = sendMail( $quote,  $newQuoteSubject );
	echo json_encode($result);
?>

