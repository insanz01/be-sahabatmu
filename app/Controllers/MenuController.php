<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class MenuController extends ResourceController
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

    $topic_id = $this->request->getGet('topic_id');

    if (!$topic_id) {
      return $this->respond('Topic ID Required', 400);
    }

    $queryMenus = $db->query("SELECT id, title, icon, `path`, topic_id FROM menus WHERE user_id = ? AND parent_menu_id is NULL AND topic_id = ?", [$user_id, $topic_id]);

    $menus = $queryMenus->getResult('object');

    $arr_menu = [];

    foreach ($menus as $menu) {
      $temp = [
        'type' => 'menu',
        'id' => (int)$menu->id,
        'attributes' => [
          'title' => $menu->title,
          'path' => $menu->path,
          'icon' => $menu->icon,
          'topic_id' => (int)$menu->topic_id
        ]
      ];

      array_push($arr_menu, $temp);
    }

    $db->close();

    $data = [
      'data' => $arr_menu,
      'error' => null
    ];

    return $this->respond($data, 200);
  }

  public function show($menu_id = null)
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

    $user_id = (int) $user->user_id;

    if (!$menu_id) {
      return $this->respond('Menu ID Required', 400);
    }

    $topic_id = $this->request->getGet("topic_id");

    if (!$topic_id) {
      return $this->respond("Topic ID must not be empty", 403);
    }

    $queryMenu = $db->query("SELECT id, title, icon, `path`, topic_id FROM menus WHERE user_id = ? AND id = ? AND topic_id = ?", [$user_id, $menu_id, $topic_id]);

    $menu = $queryMenu->getRow();

    $data = null;
    $error = null;

    if ($menu) {
      $data = [
        'type' => 'menu',
        'id' => (int)$menu->id,
        'attributes' => [
          'title' => $menu->title,
          'icon' => $menu->icon,
          'path' => $menu->path,
          'topic_id' => (int) $menu->topic_id
        ]
      ];
    } else {
      $error = [
        'message' => 'ID tidak ditemukan pada tabel menu'
      ];
    }

    $db->close();

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, 200);
  }

  public function create()
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

    $user_id = (int) $user->user_id;

    $jsonData = $this->request->getJSON();

    $topic_id = $this->request->getGet("topic_id");

    if (!$topic_id) {
      return $this->respond("Topic ID must not be empty", 403);
    }

    $staticData = [
      "icon" => "assets/img/menus/content.svg",
      "path" => "material/:id",
      "is_mandatory" => 0
    ];

    $queryMenu = $db->query("INSERT INTO menus (user_id, parent_menu_id, title, is_mandatory, icon, `path`, topic_id) VALUES (?, ?, ?, ?, ?, ?, ?)", [$user_id, $jsonData->parent_menu_id, $jsonData->title, $staticData['is_mandatory'], $staticData['icon'], $staticData['path'], $topic_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryMenu) {
      $error = [
        'message' => 'Gagal menambahkan data menu'
      ];
    } else {
      $data = [
        'id' => $db->insertID(),
        'message' => 'Berhasil menambahkan data menu'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function remove($menu_id)
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

    $topic_id = $this->request->getGet("topic_id");

    if (!$topic_id) {
      return $this->respond("Topic ID must not be empty", 403);
    }

    $queryMenu = $db->query("DELETE FROM menus WHERE id = ? AND user_id = ? AND topic_id = ?", [$menu_id, $user_id, $topic_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryMenu) {
      $error = [
        'message' => 'Gagal menghapus data menu'
      ];
    } else {
      $data = [
        'id' => (int) $menu_id,
        'message' => 'Berhasil menghapus data menu'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function change($menu_id = null)
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

    $jsonData = $this->request->getJSON();

    $topic_id = $this->request->getGet("topic_id");

    if (!$topic_id) {
      return $this->respond("Topic ID must not be empty", 403);
    }

    $queryMenu = $db->query("UPDATE menus SET title = ? WHERE user_id = ? AND id = ? AND topic_id = ?", [$jsonData->title, $user_id, $menu_id, $topic_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryMenu) {
      $error = [
        'message' => 'Gagal merubah data menu'
      ];
    } else {
      $data = [
        'id' => (int) $menu_id,
        'content' => $jsonData->content,
        'message' => 'Berhasil merubah data menu'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }
}
