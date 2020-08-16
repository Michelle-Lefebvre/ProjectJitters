<?php
require_once '_setup.php';

// STATE 1: first display
$app->get('/register', function ($request, $response, $args) {
    return $this->view->render($response, 'register.html.twig');
});

// STATE 2&3: receiving submission  
$app->post('/register', function ($request, $response, $args) {
    
    $firstName = $request->getParam('firstName');
    $lastName = $request->getParam('lastName');
    $nickname = $request->getParam('nickname');
    $email = $request->getParam('email');
    $pass1 = $request->getParam('pass1');
    $pass2 = $request->getParam('pass2');
    //
    $errorList = array();
    if (preg_match('/^[a-zA-Z0-9\ \\._\'"-]{4,50}$/', $firstName) != 1) { // no match
        array_push($errorList, "Names and nickname must be 4-50 characters long and consist of letters, digits, "
            . "spaces, dots, underscores, apostrophies, or minus sign.");
        $firstName = "";
    }
    if (preg_match('/^[a-zA-Z0-9\ \\._\'"-]{4,50}$/', $lastName) != 1) { // no match
        array_push($errorList, "Names and nickname must be 4-50 characters long and consist of letters, digits, "
            . "spaces, dots, underscores, apostrophies, or minus sign.");
        $lastName = "";
    }
    if (preg_match('/^[a-zA-Z0-9\ \\._\'"-]{4,50}$/', $nickname) != 1) { // no match
        array_push($errorList, "Nickname must be 4-50 characters long and consist of letters, digits, "
            . "spaces, dots, underscores, apostrophies, or minus sign.");
      
        $nickname = "";
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) == FALSE) {
        array_push($errorList, "Email does not look valid");
        $email = "";
    } else {
        // is email already in use?
        $record = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
        if ($record) {
            array_push($errorList, "This email is already registered");
            $email = "";
        }
    }
    if ($pass1 != $pass2) {
        array_push($errorList, "Passwords do not match");
    } else {
        if ((strlen($pass1) < 6) || (strlen($pass1) > 100)
                || (preg_match("/[A-Z]/", $pass1) == FALSE )
                || (preg_match("/[a-z]/", $pass1) == FALSE )
                || (preg_match("/[0-9]/", $pass1) == FALSE )) {
            array_push($errorList, "Password must be 6-100 characters long, "
                . "with at least one uppercase, one lowercase, and one digit in it");
        }
    }
    //
    if ($errorList) {
        return $this->view->render($response, 'register.html.twig',
                [ 'errorList' => $errorList, 'v' => [$firstName, 'lastName' => $lastName,  'nickname' => $nickname, 'email' => $email ]  ]);
    } else {
        DB::insert('users', ['firstName' => $firstName, 'lastName' => $lastName, 'nickname' => $nickname, 'email' => $email, 'password' => $pass1]);
        return $this->view->render($response, 'register_success.html.twig');
    }
});

// used via AJAX
$app->get('/isemailtaken/[{email}]', function ($request, $response, $args) {
    $email = isset($args['email']) ? $args['email'] : "";
    $record = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
    if ($record) {
        return $response->write("Email already in use");
    } else {
        return $response->write("");
    }
});

// STATE 1: first display
$app->get('/login', function ($request, $response, $args) {
    return $this->view->render($response, 'login.html.twig');
});

// $app->get('/login', function ($request, $response, $args) use ($userSession) {
//     if($userSession = $_SESSION['user'] ) {
//         unset($_SESSION['user']);
//         return $this->view->render($response, 'login.html.twig', ['userSession' => null ]);
//     }
//     return $this->view->render($response, 'login.html.twig');
// });

// STATE 2&3: receiving submission
$app->post('/login', function ($request, $response, $args) use ($log) {
    $email = $request->getParam('email');
    $password = $request->getParam('password');
    //
    $record = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
    $loginSuccess = false;
    if ($record) {
        if ($record['password'] == $password) {
            $loginSuccess = true;
        }        
    }
    //
    if (!$loginSuccess) {
        $log->info(sprintf("Login failed for email %s from %s", $email, $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'login.html.twig', [ 'error' => true ]);
    } else {
        unset($record['password']); // for security reasons remove password from session
        $_SESSION['user'] = $record; // remember user logged in
        //print_r($_SESSION['user']);
        $log->debug(sprintf("Login successful for email %s, uid=%d, from %s", $email, $record['userId'], $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'login_success.html.twig', ['userSession' => $_SESSION['user'] ] );
        
    }
});


// STATE 1: first display
$app->get('/logout', function ($request, $response, $args) use ($log) {
    $log->debug(sprintf("Logout successful for uid=%d, from %s", @$_SESSION['user']['userId'], $_SERVER['REMOTE_ADDR']));
    unset($_SESSION['user']);
    return $this->view->render($response, 'logout.html.twig', ['userSession' => null ]);
});


/* ********************     USER PROFILE VIEW   ******************** */
$app->get('/profile/{userId:[0-9]+}', function ($request, $response, $args) {
    $user = DB::queryFirstRow("SELECT * FROM users WHERE userId=%d", $args['userId']);
    if (!$user) {
        $response = $response->withStatus(404);
        return $this->view->render($response, 'error_not_found.html.twig');
    }
    return $this->view->render($response, 'profile.html.twig', ['v' => $user] );
});

$app->get('/profile/edit[/{userId:[0-9]+}]', function ($request, $response, $args) {
    // to view profile user must have log and id must be given
    if ( ($args['op'] == 'profile' && !empty($args['userId'])) || ($args['op'] == 'edit' && empty($args['userId'])) ) {
        $response = $response->withStatus(404);
        return $this->view->render($response, 'error_not_found.html.twig');
    }
    if ($args['op'] == 'edit') {
        $user = DB::queryFirstRow("SELECT * FROM users WHERE userId=%d", $args['userId']);
        if (!$user) {
            $response = $response->withStatus(404);
            return $this->view->render($response, 'error_not_found.html.twig');
        }
    } else {
        $user = [];
    }
    return $this->view->render($response, 'profile.html.twig', ['v' => $user, 'op' => $args['op']]);
});

// STATE 2&3: receiving submission
$app->post('/profile/edit[/{userId:[0-9]+}]', function ($request, $response, $args) {
    $op = $args['op'];
    // either op is add and id is not given OR op is edit and id must be given
    if ( ($op == 'profile' && !empty($args['userId'])) || ($op == 'edit' && empty($args['userId'])) ) {
        $response = $response->withStatus(404);
        return $this->view->render($response, 'error_not_found.html.twig');
    }
    
    $userId = $request->getParam('userId');
    $name = $request->getParam('firstName'. " " . 'lastName');
    $firstName = $request->getParam('firstName');
    $lastName = $request->getParam('lastName');
    $nickname = $request->getParam('nickname');
    $adminuser = $request->getParam('adminuser') ?? '0';
    $email = $request->getParam('email');
    $pass1 = $request->getParam('password');
    $pass2 = $request->getParam('pass2');
    $phone = $request->getParam('mobilePhone');
    $address = $request->getParam('address');
    $city = $request->getParam('city');
    $province = $request->getParam('province');
    $postalCode = $request->getParam('postalCode');
    $promotionalEmails = $request->getParam('promotionalEmails');
    $emailFromPartners = $request->getParam('emailFromPartners');
    $postalMailFromJitters = $request->getParam('postalMailFromJitters');
    $rewards = $request->getParam('rewards');
    $photofilepath = $request->getParam('photofilepath');
    $creationTS = $request->getParam('creationTS');

    $errorList = array();

    $result = verifyUserName( $name);
    if ($result != TRUE) { $errorList[] = $result; }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) == FALSE) {
        array_push($errorList, "Email does not look valid");
        $email = "";
    } else {
        // is email already in use BY ANOTHER ACCOUNT???
        if ($op == 'edit') {
            $record = DB::queryFirstRow("SELECT * FROM users WHERE email=%s AND userId != %d", $email, $args['id'] );
        } else { // add has no id yet
            $record = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
        }
        if ($record) {
            array_push($errorList, "This email is already registered");
            $email = "";
        }
    }
    // verify password always on profile, and on edit/delete only if it was given
    if ($op == 'profile' || $pass1 != '') {
        $result = verifyPasswordQuailty($pass1, $pass2);
        if ($result != TRUE) { $errorList[] = $result; }
    }
    //
    if ($errorList) {
        return $this->view->render($response, 'profile_edit.html.twig',
                [ 'errorList' => $errorList, 'v' => ['name' => $name, 'email' => $email ]  ]);
    } else {
        if ($op == 'profile') { 
            DB::insert('users', ['firstName' => $firstName, 'lastName' => $lastName, 'nickname' => $nickname, 'email' => $email, 'password' => $pass1, 'phone' => $phone, 'address' => $address, 'city' => $city, 'province' => $province, 'postalCode' => $postalCode, 'promotionalEmails' => $promotionalEmails, 'emailFromPartners' => $emailFromPartners, 'postalMailFromJitters' => $postalMailFromJitters, 'rewards' => $rewards, 'photofilepath' => $photofilepath]);

            return $this->view->render($response, 'profile_edit_success.html.twig', ['op' => $op ]);
        } else {
            $data = ['name' => $name, 'email' => $email, 'adminuser' => $adminuser];
            if ($pass1 != '') { // only update the password if it was provided
                $data['password'] = $pass1;
            }
            DB::update('users', $data, "userId=%d", $args['id']);
            return $this->view->render($response, 'profile_edit_success.html.twig', ['op' => $op ]);
        }
    }
});


// STATE 1: first display
$app->get('/profile/delete/{userId:[0-9]+}', function ($request, $response, $args) {
    $user = DB::queryFirstRow("SELECT * FROM users WHERE userId=%d", $args['userId']);
    if (!$user) {
        $response = $response->withStatus(404);
        return $this->view->render($response, 'error_not_found.html.twig');
    }
    return $this->view->render($response, 'profile_delete.html.twig', ['v' => $user] );
});

// STATE 1: first display
$app->post('/profile/delete/{userId:[0-9]+}', function ($request, $response, $args) {
    DB::delete('users', "userId=%d", $args['id']);
    return $this->view->render($response, 'profile_delete_success.html.twig' );
});

// Attach middleware that verifies only Admin can access /admin... URLs

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// Function to check string starting 
// with given substring 
// function startsWith($string, $startString) 
// { 
//     $len = strlen($startString); 
//     return (substr($string, 0, $len) === $startString); 
// } 


/* ********** TODO FIXME!!!!! *********** */
$app->add(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    $url = $request->getUri()->getPath();
    if (startsWith($url, "/admin")) {
        if (!isset($_SESSION['user']) || $_SESSION['user']['adminuser'] == 0) { // refuse if user not logged in AS ADMIN
            $response = $response->withStatus(403);
            return $this->view->render($response, 'error_access_denied.html.twig');
        }
    }
    return $next($request, $response);
});

/* ********** TODO FIXME!!!!! FUNCTIONS DECLARED IN _admin.php  *********** */
// these functions return TRUE on success and string describing an issue on failure
// function verifyUserName($name) {
//     if (preg_match('/^[a-zA-Z0-9\ \\._\'"-]{4,50}$/', $name) != 1) { // no match
//         return "Name must be 4-50 characters long and consist of letters, digits, "
//             . "spaces, dots, underscores, apostrophies, or minus sign.";
//     }
//     return TRUE;
// }

// function verifyPasswordQuailty($pass1, $pass2) {
//     if ($pass1 != $pass2) {
//         return "Passwords do not match";
//     } else {
//         /*
//         // FIXME: figure out how to use case-sensitive regexps with Validator
//         if (!Validator::length(6,100)->regex('/[A-Z]/')->validate($pass1)) {
//             return "VALIDATOR. Password must be 6-100 characters long, "
//                 . "with at least one uppercase, one lowercase, and one digit in it";
//         } */
//         if ((strlen($pass1) < 6) || (strlen($pass1) > 100)
//                 || (preg_match("/[A-Z]/", $pass1) == FALSE )
//                 || (preg_match("/[a-z]/", $pass1) == FALSE )
//                 || (preg_match("/[0-9]/", $pass1) == FALSE )) {
//             return "Password must be 6-100 characters long, "
//                 . "with at least one uppercase, one lowercase, and one digit in it";
//         }
//     }
//     return TRUE;
// }