<?php
require_once '_setup.php';
# this file is for /admin/* URL handlers

// menu for administration functions
$app->get('/adminmenu', function ($request, $response, $args) {
    return $this->view->render($response, '/admin/adminmenu.html.twig');
});





//following is using addarticletest while following video about addperson and tristate form
// STATE 1: first display  -- this is a get  -- just need to render the template
$app->get('/additem', function ($request, $response, $args) {
    return $this->view->render($response, '/admin/additem.html.twig');
});

//STATE 2&3 receiving submission
$app->post('/additem', function ($request, $response, $args) {
    
    // $app->post will display the form, following code will handle the form
    $itemName = $request->getParam('itemName');
    $description = $request->getParam('description');
    $categoryCode = $request->getParam('categoryCode');
    $inventoryFlag = $request->getParam('inventoryFlag');
    $quantityOnHand = $request->getParam('quantityOnHand');
    $price = $request->getParam('price');
    $photofilepath = $request->getParam('photofilepath');
    
    //
    // FIXME: sanitize body - 1) only allow certain HTML tags, 2) make sure it is valid html
    // WARNING: If you forget to sanitize the body bad things may happen such as JavaScript injection
    $description = strip_tags($description, "<p><ul><li><em><strong><i><b><ol><h3><h4><h5><span>");
               
    $errorList = array();

    if (strlen($itemName) < 10 || (strlen($itemName) > 100) ) {
        $errorList[] = "Name must be between 10  and 100 characters long";
    }
    if (strlen($description) < 10  || strlen($description) > 250) {
        $errorList[] = "Description must be between 10 and 250 characters long";
    }

    //print_r($categoryCode);
    

        // validate category code
        $categoryList = DB::queryFirstRow("SELECT categoryCode FROM categorycodes
         WHERE categoryCode = '$categoryCode'");  

    if (!$categoryList) { 
        $errorList[] = "Category Code does not exist";
    } 
    
    if ($errorList) {
        return $this->view->render($response, '/admin/additem.html.twig',
                ['errorList' => $errorList ]);

    } else {
        // response write is only allowed to be used with ajax
        //return $response->write("<p>Success</p>");

        // insert not working foreign key author id not found
       DB::insert( 'items', ['itemid' => NULL, 'itemName' => $itemName,  'description' => $description,
                   'categoryCode' => $categoryCode, 'inventoryFlag' => $inventoryFlag, 
                   'quantityOnHand' => $quantityOnHand, 'price' => $price, 'photofilepath' => $photofilepath
       
       ]);
       // render the success template
       // no params to pass, but there could be... like an id or something that was
       // created
        return $this->view->render($response, '/admin/additem_success.html.twig');
    }
});





?>