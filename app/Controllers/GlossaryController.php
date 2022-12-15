<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class GlossaryController extends ResourceController
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

    $queryGlossary = $db->query("SELECT id, term, `definition`, topic_id FROM glossaries WHERE user_id = ? AND topic_id = ?", [$user_id, $topic_id]);

    $glossaries = $queryGlossary->getResult('object');

    $arr_glossary = [];

    foreach ($glossaries as $glossary) {
      $temp = [
        'type' => 'glossary',
        'id' => (int)$glossary->id,
        'attributes' => [
          'term' => $glossary->term,
          'definition' => $glossary->definition,
          'topic_id' => (int)$glossary->topic_id
        ]
      ];

      array_push($arr_glossary, $temp);
    }

    $db->close();

    $data = [
      'data' => $arr_glossary,
      'error' => null
    ];

    return $this->respond($data, 200);
  }

  public function show($glossary_id = null)
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

    if (!$glossary_id) {
      return $this->respond('Glossary ID Required', 400);
    }

    $topic_id = $this->request->getGet("topic_id");

    if (!$topic_id) {
      return $this->respond("Topic ID must not be empty", 403);
    }

    $queryGlossary = $db->query("SELECT id, term, definition, topic_id FROM glossaries WHERE user_id = ? AND id = ? AND topic_id = ?", [$user_id, $glossary_id, $topic_id]);

    $glossary = $queryGlossary->getRow();

    $data = null;
    $error = null;

    if ($glossary) {
      $data = [
        'type' => 'glossary',
        'id' => (int)$glossary->id,
        'attributes' => [
          'term' => $glossary->term,
          'definition' => $glossary->definition,
          'topic_id' => (int)$glossary->topic_id
        ]
      ];
    } else {
      $error = [
        'message' => 'ID tidak ditemukan pada tabel glosarium'
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

    $queryGlossary = $db->query("INSERT INTO glossaries (user_id, term, definition, topic_id) VALUES (?, ?, ?, ?)", [$user_id, $jsonData->term, $jsonData->definition, $topic_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryGlossary) {
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

  public function remove($glossary_id)
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

    $queryGlossary = $db->query("DELETE FROM glossaries WHERE id = ? AND user_id = ? AND topic_id = ?", [$glossary_id, $user_id, $topic_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryGlossary) {
      $error = [
        'message' => 'Gagal menghapus data glosarium'
      ];
    } else {
      $data = [
        'id' => (int) $glossary_id,
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

  public function change($glossary_id = null)
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

    $queryGlossary = $db->query("UPDATE glossaries SET term = ?, definition = ? WHERE user_id = ? AND id = ? AND topic_id = ?", [$jsonData->term, $jsonData->definition, $user_id, $glossary_id, $topic_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryGlossary) {
      $error = [
        'message' => 'Gagal merubah data glosarium'
      ];
    } else {
      $data = [
        'id' => (int) $glossary_id,
        'term' => $jsonData->term,
        'definition' => $jsonData->definition,
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
