<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class ObjectiveController extends ResourceController
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

    $queryObjective = $db->query("SELECT id, content, topic_id FROM objectives WHERE user_id = ? AND topic_id = ?", [$user_id, $topic_id]);

    $objectives = $queryObjective->getResult('object');

    $arr_objective = [];

    foreach ($objectives as $objective) {
      $temp = [
        'type' => 'objective',
        'id' => (int)$objective->id,
        'attributes' => [
          'content' => $objective->content,
          'topic_id' => $objective->topic_id
        ]
      ];

      array_push($arr_objective, $temp);
    }

    $db->close();

    $data = [
      'data' => $arr_objective,
      'error' => null
    ];

    return $this->respond($data, 200);
  }

  public function show($objective_id = null)
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

    if (!$objective_id) {
      return $this->respond('Objective ID Required', 400);
    }

    $topic_id = $this->request->getGet("topic_id");

    if (!$topic_id) {
      return $this->respond("Topic ID must not be empty", 403);
    }

    $queryObjective = $db->query("SELECT id, content, topic_id FROM objectives WHERE user_id = ? AND id = ? AND topic_id = ?", [$user_id, $objective_id, $topic_id]);

    $objective = $queryObjective->getRow();

    $data = null;
    $error = null;

    if ($objective) {
      $data = [
        'type' => 'objective',
        'id' => (int)$objective->id,
        'attributes' => [
          'content' => $objective->content,
          'topic_id' => $objective->topic_id
        ]
      ];
    } else {
      $error = [
        'message' => 'ID tidak ditemukan pada tabel tujuan'
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

    $queryObjective = $db->query("INSERT INTO objectives (user_id, content, topic_id) VALUES (?, ?, ?)", [$user_id, $jsonData->content, $topic_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryObjective) {
      $error = [
        'message' => 'Gagal menambahkan data tujuan'
      ];
    } else {
      $data = [
        'id' => $db->insertID(),
        'message' => 'Berhasil menambahkan data tujuan'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function remove($objective_id)
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

    $queryObjective = $db->query("DELETE FROM objectives WHERE id = ? AND user_id = ? AND topic_id = ?", [$objective_id, $user_id, $topic_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryObjective) {
      $error = [
        'message' => 'Gagal menghapus data tujuan'
      ];
    } else {
      $data = [
        'id' => (int) $objective_id,
        'message' => 'Berhasil menghapus data tujuan'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function change($objective_id = null)
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

    $queryObjective = $db->query("UPDATE objectives SET content = ? WHERE user_id = ? AND id = ? AND topic_id = ?", [$jsonData->content, $user_id, $objective_id, $topic_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryObjective) {
      $error = [
        'message' => 'Gagal merubah data tujuan'
      ];
    } else {
      $data = [
        'id' => (int) $objective_id,
        'content' => $jsonData->content,
        'message' => 'Berhasil merubah data tujuan'
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
