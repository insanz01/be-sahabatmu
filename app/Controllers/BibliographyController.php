<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class BibliographyController extends ResourceController
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

    $topic_id = $this->request->getGet("topic_id");

    if (!$topic_id) {
      return $this->respond("Topic ID must not be empty", 403);
    }

    $queryBibliography = $db->query("SELECT id, content, topic_id FROM bibliographies WHERE user_id = ? AND topic_id = ? ORDER BY id", [$user_id, $topic_id]);

    $bibliographies = $queryBibliography->getResult('object');

    $arr_bibliographies = [];

    foreach ($bibliographies as $bibliography) {
      $temp = [
        'type' => 'bibliography',
        'id' => (int)$bibliography->id,
        'attributes' => [
          'content' => $bibliography->content,
          'topic_id' => (int)$bibliography->topic_id
        ]
      ];

      array_push($arr_bibliographies, $temp);
    }

    $db->close();

    $data = [
      'data' => $arr_bibliographies,
      'error' => null
    ];

    return $this->respond($data, 200);
  }

  public function show($bibliography_id = null)
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

    if (!$bibliography_id) {
      return $this->respond('Bibliography ID Required', 400);
    }

    $topic_id = $this->request->getGet("topic_id");

    if (!$topic_id) {
      return $this->respond("Topic ID must not be empty", 403);
    }

    $queryBibliography = $db->query("SELECT id, content, topic_id FROM bibliographies WHERE user_id = ? AND id = ? AND topic_id = ?", [$user_id, $bibliography_id, $topic_id]);

    $bibliography = $queryBibliography->getRow();

    $data = null;
    $error = null;

    if ($bibliography) {
      $data = [
        'type' => 'bibliography',
        'id' => (int)$bibliography->id,
        'attributes' => [
          'content' => $bibliography->content,
          'topic_id' => (int)$bibliography->topic_id
        ]
      ];
    } else {
      $error = [
        'message' => 'ID tidak ditemukan pada tabel daftar pustaka'
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

    $queryBibliography = $db->query("INSERT INTO bibliographies (user_id, content, topic_id) VALUES (?, ?, ?)", [$user_id, $jsonData->content, $topic_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryBibliography) {
      $error = [
        'message' => 'Gagal menambahkan data glosarium'
      ];
    } else {
      $data = [
        'id' => $db->insertID(),
        'message' => 'Berhasil menambahkan data glosarium'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function remove($bibliography_id)
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

    $queryBibliography = $db->query("DELETE FROM bibliographies WHERE id = ? AND user_id = ? AND topic_id = ?", [$bibliography_id, $user_id, $topic_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryBibliography) {
      $error = [
        'message' => 'Gagal menghapus data glosarium'
      ];
    } else {
      $data = [
        'id' => (int) $bibliography_id,
        'message' => 'Berhasil menghapus data glosarium'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function change($bibliography_id = null)
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

    $queryBibliography = $db->query("UPDATE bibliographies SET content = ? WHERE user_id = ? AND id = ? AND topic_id = ?", [$jsonData->content, $user_id, $bibliography_id, $topic_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryBibliography) {
      $error = [
        'message' => 'Gagal merubah data glosarium'
      ];
    } else {
      $data = [
        'id' => (int) $bibliography_id,
        'content' => $jsonData->content,
        'message' => 'Berhasil merubah data glosarium'
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
