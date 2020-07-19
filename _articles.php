<?php

require_once '_setup.php';

$app->map(['GET', 'POST'],'/article/{id:[0-9]+}', function ($request, $response, $args) {
    // step 1: fetch article and author info
    $article = DB::queryFirstRow("SELECT a.id, a.authorId, a.creationTS, a.title, a.body, u.name "
            . "FROM articles as a, users as u WHERE a.authorId = u.id AND a.id = %d", $args['id']);
    if (!$article) { // TODO: use Slim's default 404 page instead of our custom one
        $response = $response->withStatus(404);
        return $this->view->render($response, 'article_not_found.html.twig');
    }
    $datetime = strtotime($article['creationTS']);
    $postedDate = date('M d, Y \a\t H:i:s', $datetime );
    $article['postedDate'] = $postedDate;
    // step 2: handle comment submission if there is one
    if ($request->getMethod() == "POST" ) {
        // is user authenticated?
        if (!isset($_SESSION['user'])) { // refuse if user not logged in
            $response = $response->withStatus(403);
            return $this->view->render($response, 'error_access_denied.html.twig');
        }
        $authorId = $_SESSION['user']['id'];
        $body = $request->getParam('body');
        // TODO: we could check other things, like banned words
        if (strlen($body) > 0) {
            DB::insert('comments', [
                'articleId' => $args['id'],
                'authorId' => $authorId,
                'body' => $body
            ]);
        }
    }
    // step 3: fetch article comments
    $commentsList = DB::query("SELECT c.id, u.name as authorName, c.creationTS, c.body FROM comments c, users u WHERE c.authorId=u.id ORDER BY c.id");
    foreach ($commentsList as &$comment) {
        $datetime = strtotime($comment['creationTS']);
        $postedDate = date('M d, Y \a\t H:i:s', $datetime );
        $comment['postedDate'] = $postedDate;
    }
    //
    return $this->view->render($response, 'article.html.twig', ['a' => $article, 'commentsList' => $commentsList]);
});

// STATE 1: first display
$app->get('/addarticle', function ($request, $response, $args) {
    if (!isset($_SESSION['user'])) { // refuse if user not logged in
        $response = $response->withStatus(403);
        return $this->view->render($response, 'error_access_denied.html.twig');
    }
    return $this->view->render($response, 'addarticle.html.twig');
});

// STATE 2&3: receiving submission
$app->post('/addarticle', function ($request, $response, $args) {
    if (!isset($_SESSION['user'])) { // refuse if user not logged in
        $response = $response->withStatus(403);
        return $this->view->render($response, 'error_access_denied.html.twig');
    }
    $title = $request->getParam('title');
    $body = $request->getParam('body');
    // FIXME: sanitize body - 1) only allow certain HTML tags, 2) make sure it is valid html
    // WARNING: If you forget to sanitize the body bad things may happen such as JavaScript injection
    $body = strip_tags($body, "<p><ul><li><em><strong><i><b><ol><h3><h4><h5><span>");
    //
    $errorList = array();
    if (strlen($title) < 2 || strlen($title) > 100) {
        array_push($errorList, "Title must be 2-100 characters long");
        // keep the title even if invalid
    }
    if (strlen($body) < 2 || strlen($body) > 10000) {
        array_push($errorList, "Body must be 2-10000 characters long");
        // keep the body even if invalid
    }
    //
    if ($errorList) {
        return $this->view->render($response, 'addarticle.html.twig',
                [ 'errorList' => $errorList, 'v' => ['title' => $title, 'body' => $body ]  ]);
    } else {
        $authorId = $_SESSION['user']['id'];
        DB::insert('articles', ['authorId' => $authorId, 'title' => $title, 'body' => $body]);
        $articleId = DB::insertId();
        return $this->view->render($response, 'addarticle_success.html.twig', ['id' => $articleId]);
    }
});