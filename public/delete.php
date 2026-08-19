<?php
require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : false;
    if ($id) {
        $contact = new Contact($PDO);
        if ($contact->find($id)) {
            $contact->delete();
        }
    }
}

redirect('/');
