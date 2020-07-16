<?php

require_once '_setup.php';
// renders template
$app->get('/internalerror', function ($request, $response, $args) {
    return $this->view->render($response, 'error_internal.html.twig');
});

// TODO: Define app routes
// Define app routes

$app->get('/', function ($request, $response, $args) {
    return $response->write("this is index");
});