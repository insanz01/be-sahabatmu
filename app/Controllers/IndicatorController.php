<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class IndicatorController extends ResourceController
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

    $queryIndicator = $db->query("SELECT id, content, topic_id FROM indicators WHERE user_id = ? AND topic_id = ? ORDER BY id", [$user_id, $topic_id]);

    $indicators = $queryIndicator->getResult('object');

    $arr_indicators = [];

    foreach ($indicators as $indicator) {
      $temp = [
        'type' => 'indicator',
        'id' => (int)$indicator->id,
        'attributes' => [
          'content' => $indicator->content,
          'topic_id' => (int)$indicator->topic_id
        ]
      ];

      array_push($arr_indicators, $temp);
    }

    $db->close();

    $data = [
      'data' => $arr_indicators,
      'error' => null
    ];

    return $this->respond($data, 200);
  }

  public function show($indicator_id = null)
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

    if (!$indicator_id) {
      return $this->respond('Indicator ID Required', 400);
    }

    $topic_id = $this->request->getGet("topic_id");

    if (!$topic_id) {
      return $this->respond("Topic ID must not be empty", 403);
    }

    $queryIndicator = $db->query("SELECT id, content, topic_id FROM indicators WHERE user_id = ? AND id = ? AND topic_id = ?", [$user_id, $indicator_id, $topic_id]);

    $indicator = $queryIndicator->getRow();

    $data = null;
    $error = null;

    if ($indicator) {
      $data = [
        'type' => 'indicator',
        'id' => (int)$indicator->id,
        'attributes' => [
          'content' => $indicator->content,
          'topic_id' => (int)$indicator->topic_id
        ]
      ];
    } else {
      $error = [
        'message' => 'ID tidak ditemukan pada tabel indikator'
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

    $topic_id = $this->request->getGet("topic_id");

    if (!$topic_id) {
      return $this->respond("Topic ID must not be empty", 403);
    }

    $jsonData = $this->request->getJSON();

    $queryIndicator = $db->query("INSERT INTO indicators (user_id, content, topic_id) VALUES (?, ?, ?)", [$user_id, $jsonData->content, $topic_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryIndicator) {
      $error = [
        'message' => 'Gagal menambahkan data indikator'
      ];
    } else {
      $data = [
        'id' => $db->insertID(),
        'message' => 'Berhasil menambahkan data indikator'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function remove($indicator_id)
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

    $queryIndicator = $db->query("DELETE FROM indicators WHERE id = ? AND user_id = ? AND topic_id = ?", [$indicator_id, $user_id, $topic_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryIndicator) {
      $error = [
        'message' => 'Gagal menghapus data indikator'
      ];
    } else {
      $data = [
        'id' => (int) $indicator_id,
        'message' => 'Berhasil menghapus data indikator'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function change($indicator_id = null)
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

    $queryIndicator = $db->query("UPDATE indicators SET content = ? WHERE user_id = ? AND id = ? AND topic_id = ?", [$jsonData->content, $user_id, $indicator_id, $topic_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryIndicator) {
      $error = [
        'message' => 'Gagal merubah data indikator'
      ];
    } else {
      $data = [
        'id' => (int) $indicator_id,
        'content' => $jsonData->content,
        'message' => 'Berhasil merubah data indikator'
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
