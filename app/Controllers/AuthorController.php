<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class AuthorController extends ResourceController
{
  protected $format = 'json';

  use RequestTrait;

  public function index()
  {
    $api_key = $this->request->getGet('api_key');

    if (!$api_key) {
      return $this->respond('API Key Required', 403);
    }

    $db = \Config\Database::connect();

    $queryUser = $db->query("SELECT user_id FROM tokens WHERE secret_key = '$api_key'");

    $user = $queryUser->getRow();

    if (!$user) {
      return $this->respond("Invalid API Key", 403);
    }

    $user_id = $user->user_id;

    $queryAuthor = $db->query("SELECT id, name, phone_number, email, photo FROM user_profiles");

    $authors = $queryAuthor->getResult('object');

    $imgURL = "https://insandev.com/public";
    if ($_SERVER['CI_ENVIRONMENT'] == "development") {
      if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER)) {
        $imgURL = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['HTTP_HOST'];
      } else {
        $imgURL = "http://localhost:8080";
      }
    }

    $arr_authors = [];

    foreach ($authors as $author) {
      $temp = [
        'type' => 'author',
        'id' => (int)$author->id,
        'attributes' => [
          'name' => $author->name,
          'phone_number' => $author->phone_number,
          'email' => $author->email,
          'photo' => $imgURL . $author->photo
        ]
      ];

      array_push($arr_authors, $temp);
    }

    $db->close();

    $data = [
      'data' => $arr_authors,
      'error' => null
    ];

    return $this->respond($data, 200);
  }

  public function show($user_id = null)
  {
    $api_key = $this->request->getGet('api_key');

    if (!$api_key) {
      return $this->respond('API Key Required', 403);
    }

    $db = \Config\Database::connect();

    $queryUser = $db->query("SELECT user_id FROM tokens WHERE secret_key = '$api_key'");

    $user = $queryUser->getRow();

    if (!$user) {
      return $this->respond("Invalid API Key", 403);
    }

    // $user_id = (int) $user->user_id;

    if (!$user_id) {
      return $this->respond('Guidance ID Required', 400);
    }

    $queryAuthor = $db->query("SELECT id, name, phone_number, email, photo FROM user_profiles WHERE id = ?", [$user_id]);

    $author = $queryAuthor->getRow();

    $imgURL = "https://insandev.com/public";
    if ($_SERVER['CI_ENVIRONMENT'] == "development") {
      if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER)) {
        $imgURL = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['HTTP_HOST'];
      } else {
        $imgURL = "http://localhost:8080";
      }
    }

    $data = null;
    $error = null;

    if ($author) {
      $data = [
        'type' => 'author',
        'id' => (int)$author->id,
        'attributes' => [
          'name' => $author->name,
          'phone_number' => $author->phone_number,
          'email' => $author->email,
          'photo' => $imgURL . $author->photo
        ]
      ];
    } else {
      $error = [
        'message' => 'ID tidak ditemukan pada tabel user profile'
      ];
    }

    $db->close();

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, 200);
  }
}
