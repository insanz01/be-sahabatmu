<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class CategoriesController extends ResourceController
{
  protected $format = 'json';

  use RequestTrait;

  public function get_category($category_type)
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

    $queryCategory = null;
    $table_name = "";

    switch ($category_type) {
      case "news":
        $table_name = "news_categories";
        break;
      case "counselor":
        $table_name = "counselor_categories";
        break;
    }

    $queryCategory = $db->query("SELECT id, name, created_at, updated_at FROM $table_name WHERE deleted_at is NULL");
    $categories = $queryCategory->getResult('object');

    $arr_category = [];

    if ($queryCategory == null) {
      return $this->respond("Bad request", 400);
    }

    foreach ($categories as $category) {
      $temp = [
        'type' => 'category',
        'id' => (int)$category->id,
        'attributes' => [
          'name' => $category->name,
          'created_at' => $category->created_at,
          'updated_at' => $category->updated_at
        ]
      ];

      array_push($arr_category, $temp);
    }

    $db->close();

    $data = [
      'data' => $arr_category,
      'error' => null
    ];

    return $this->respond($data, 200);
  }

  public function create_category($category_type)
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

    $queryCategory = null;
    $table_name = "";

    switch ($category_type) {
      case "news":
        $table_name = "news_categories";
        break;
      case "counselor":
        $table_name = "counselor_categories";
        break;
    }

    $queryCategory = $db->query("INSERT INTO $table_name (name) VALUES (?)", [$jsonData->name]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryCategory) {
      $error = [
        'message' => 'Gagal menambahkan data kategori'
      ];
    } else {
      $data = [
        'id' => $db->insertID(),
        'message' => 'Berhasil menambahkan data kategori'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function show_category($category_type, $category_id = null)
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

    if (!$category_id) {
      return $this->respond('Category ID Required', 400);
    }

    $queryCategory = null;
    $table_name = "";

    switch ($category_type) {
      case "news":
        $table_name = "news_categories";
        break;
      case "counselor":
        $table_name = "counselor_categories";
        break;
    }

    $queryCategory = $db->query("SELECT id, name, created_at, updated_at FROM $table_name WHERE id = ? AND deleted_at is NULL", [$category_id]);

    $category = $queryCategory->getRow();

    $data = null;
    $error = null;

    if ($category) {
      $data = [
        'type' => 'category',
        'id' => (int)$category->id,
        'attributes' => [
          'name' => $category->name,
          'created_at' => $category->created_at,
          'updated_at' => $category->updated_at
        ]
      ];
    } else {
      $error = [
        'message' => 'ID tidak ditemukan pada tabel kategori'
      ];
    }

    $db->close();

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, 200);
  }

  public function remove_category($category_type, $category_id)
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

    $queryCategory = null;
    $table_name = "";

    switch ($category_type) {
      case "news":
        $table_name = "news_categories";
        break;
      case "counselor":
        $table_name = "counselor_categories";
        break;
    }

    $queryCategory = $db->query("DELETE FROM $table_name WHERE id = ?", [$category_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryCategory) {
      $error = [
        'message' => 'Gagal menghapus data kategori'
      ];
    } else {
      $data = [
        'id' => (int) $category_id,
        'message' => 'Berhasil menghapus data kategori'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function change_category($category_type, $category_id = null)
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

    $queryCategory = null;
    $table_name = "";

    switch ($category_type) {
      case "news":
        $table_name = "news_categories";
        break;
      case "counselor":
        $table_name = "counselor_categories";
        break;
    }

    $queryCategory = $db->query("UPDATE $table_name SET name = ? WHERE id = ?", [$jsonData->name, $category_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryCategory) {
      $error = [
        'message' => 'Gagal merubah data kategori'
      ];
    } else {
      $data = [
        'id' => (int) $category_id,
        'name' => $jsonData->name,
        'message' => 'Berhasil merubah data kategori'
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
