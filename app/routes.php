<?php

use App\RouteResolver;

$app->map(
	['POST','GET','PUT','DELETE','OPTIONS'],
	'/[{args:.*}]',
	function ($request, $response, $args) {
    	if ($request->isOptions()) {
        	return $response;
    	}
    	$objCtrl = new RouteResolver($this);
    	return $objCtrl->call($request, $response, $args);
	}
);
