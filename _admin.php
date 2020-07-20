<?php
require_once '_setup.php';
# this file is for /admin/* URL handlers

//following is using addarticletest while following video about addperson and tristate form
// STATE 1: first display  -- this is a get  -- just need to render the template
$app->get('/additem', function ($request, $response, $args) {
    return $this->view->render($response, '/admin/additem.html.twig');
});

//STATE 2&3 receiving submission
$app->post('/additem', function ($request, $response, $args) {
    
    // $app->post will display the form, following code will handle the form
    $name = $request->getParam('name');
    $description = $request->getParam('description');
    $categoryCode = $request->getParam('categoryCode');
    $inventoryFlag = $request->getParam('inventoryFlag');
    $quantityOnHand = $request->getParam('quantityOnHand');
    $price = $request->getParam('price');
    
    //
    // FIXME: sanitize body - 1) only allow certain HTML tags, 2) make sure it is valid html
    // WARNING: If you forget to sanitize the body bad things may happen such as JavaScript injection
    $description = strip_tags($description, "<p><ul><li><em><strong><i><b><ol><h3><h4><h5><span>");
               
    $errorList = array();

    if (strlen($name) < 10 || (strlen($name) >100) ) {
        $errorList[] = "Name must be between 10  and 100 characters long";
    }
    if (strlen($description) < 10  || strlen($description) > 250) {
        $errorList[] = "Description must be between 10 and 250 characters long";
    }


    
    if ($errorList) {
        return $this->view->render($response, '/admin/additem.html.twig',
                ['errorList' => $errorList ]);
    } else {
        // response write is only allowed to be used with ajax
        //return $response->write("<p>Success</p>");

        // insert not working foreign key author id not found
       // DB::insert( 'articles', ['id' => NULL, 'authorId' => 3, 'title' => $title, 'body' => $body ]);
       // render the success template
       // no params to pass, but there could be... like an id or something that was
       // created
        return $this->view->render($response, '/admin/additem_success.html.twig');
    }
});





?>