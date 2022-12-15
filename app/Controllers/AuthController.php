<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class AuthController extends ResourceController
{
  protected $format = 'json';

  use RequestTrait;

  public function login()
  {
    $db = \Config\Database::connect();

    $jsonData = $this->request->getJSON();

    $password = $jsonData->password;

    $queryUser = $db->query("SELECT id, username, password FROM users WHERE username = ?", [$jsonData->username]);

    $user = $queryUser->getRow();

    if (!$user) {
      $data = [
        'data' => null,
        'error' => [
          'message' => 'Username tidak ditemukan'
        ]
      ];

      return $this->respond($data, 200);
    }

    $data = null;
    $error = null;
    $code = 200;

    if (password_verify($password, $user->password)) {
      $queryToken = $db->query("SELECT id, user_id, token, secret_key FROM tokens WHERE user_id = ?", [$user->id]);

      $token = $queryToken->getRow();

      $data = [
        'type' => 'user',
        'id' => (int)$user->id,
        'attributes' => [
          'username' => $user->username,
          'token' => $token->token,
          'api_key' => $token->secret_key
        ]
      ];
    } else {
      $error = [
        'message' => 'Username dan password tidak cocok!'
      ];
      $code = 200;
    }


    $db->close();

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function register()
  {
    $db = \Config\Database::connect();

    $jsonData = $this->request->getJSON();

    $password = password_hash($jsonData->password, PASSWORD_DEFAULT);

    $queryUser = $db->query("INSERT INTO users (username, password, is_active) VALUES (?, ?, 1)", [$jsonData->username, $password]);

    $data = null;
    $error = null;
    $code = 200;

    if ($queryUser) {
      $insertID = $db->insertID();

      helper('text');

      $generateKey = random_string('alnum', 60);
      $generateToken = random_string('alnum', 60);

      $queryToken = $db->query("INSERT INTO tokens (user_id, token, secret_key) VALUES (?, ?, ?)", [$insertID, $generateToken, $generateKey]);

      $userData = [
        "id" => $insertID,
        "name" => $jsonData->name,
        "phone_number" => $jsonData->phone_number,
        "email" => $jsonData->email,
        "photo" => ""
      ];

      $queryUserProfile = $this->create_user_profile($userData);

      $userAbout = [
        "id" => $insertID,
        "title" => $jsonData->title,
        "description" => "",
        "short_description" => "",
        "banner" => ""
      ];

      $queryUserAbout = $this->create_user_about($userAbout);

      if ($queryToken && $queryUserProfile && $queryUserAbout) {
        if ($this->insert_menu_data($insertID)) {
          $data = [
            'message' => 'Akun, menu, tentang dan token berhasil dibuat!'
          ];
        } else {
          $error = [
            'message' => 'Akun dan token berhasil dibuat! Menu gagal dibuat 😭'
          ];
        }
      } else {
        $error = [
          'message' => 'Akun berhasil dibuat! Token gagal dibuat 😭'
        ];
      }
    } else {
      $error = [
        'message' => 'Akun, menu dan token gagal dibuat! 😭'
      ];
      $code = 400;
    }


    $db->close();

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  // helper
  private function insert_menu_data($user_id = null, $topic_id = null)
  {
    if ($user_id == null) {
      return false;
    }

    $menuData = [
      [
        'title' => 'Tentang',
        'is_mandatory' => true,
        'path' => '/about',
        'icon' => 'assets/img/menus/profil.svg'
      ]
    ];

    $db = \Config\Database::connect();

    foreach ($menuData as $menu) {
      $queryUser = $db->query("insert into menus (user_id, topic_id, title, icon, is_mandatory, path) values (?, ?, ?, ?, ?, ?)", [$user_id, $topic_id, $menu['title'], $menu['icon'], $menu['is_mandatory'], $menu['path']]);

      if (!$queryUser) {
        $db->close();
        return false;
      }
    }

    $db->close();

    return true;
  }

  private function create_user_profile($userData = [])
  {
    $db = \Config\Database::connect();

    $queryUser = $db->query("insert into user_profiles (id, name, phone_number, email, photo) values (?, ?, ?, ?, ?)", [$userData["id"], $userData["name"], $userData["phone_number"], $userData["email"], $userData["photo"]]);

    $db->close();

    return $queryUser;
  }

  private function create_user_about($userAbout = [])
  {
    $db = \Config\Database::connect();

    $queryUser = $db->query("insert into abouts (id, title, description, short_description, banner) values (?, ?, ?, ?, ?)", [$userAbout["id"], $userAbout["title"], $userAbout["description"], $userAbout["short_description"], $userAbout["banner"]]);

    $db->close();

    return $queryUser;
  }
}
