<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class TopicController extends ResourceController
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

    // $queryVoucher = $db->query("SELECT status FROM purchased WHERE user_id = $user_id");

    // $voucher = $queryVoucher->getRow();

    $queryTopic = $db->query("SELECT id, name FROM topics WHERE user_id = ? ORDER BY id", [$user_id]);

    $topics = $queryTopic->getResult('object');

    $arr_topics = [];

    foreach ($topics as $topic) {
      $temp = [
        'type' => 'topic',
        'id' => (int)$topic->id,
        'attributes' => [
          'name' => $topic->name
        ]
      ];

      array_push($arr_topics, $temp);
    }

    $db->close();

    $data = [
      'data' => $arr_topics,
      'error' => null
    ];

    return $this->respond($data, 200);
  }

  public function show($topic_id = null)
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

    if (!$topic_id) {
      return $this->respond('Objective ID Required', 400);
    }

    $queryTopic = $db->query("SELECT id, name FROM topics WHERE user_id = ? AND id = ?", [$user_id, $topic_id]);

    $topic = $queryTopic->getRow();

    $data = null;
    $error = null;

    if ($topic) {
      $data = [
        'type' => 'topic',
        'id' => (int)$topic->id,
        'attributes' => [
          'name' => $topic->name
        ]
      ];
    } else {
      $error = [
        'message' => 'ID tidak ditemukan pada tabel topik'
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

    $user_id = $user->user_id;

    $jsonData = $this->request->getJSON();

    if ($jsonData->name == "") {
      $data = [
        'data' => null,
        'error' => [
          "message" => `Value "name" tidak boleh kosong`
        ]
      ];

      return $this->respond($data, 400);
    }

    $queryTopic = $db->query("insert into topics(user_id, name) VALUES(?, ?)", [$user_id, $jsonData->name]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryTopic) {
      $error = [
        'message' => 'Gagal menambahkan data topik'
      ];
    } else {
      $topic_id = $db->insertID();

      $ok = $this->insert_menu_data($user_id, $topic_id);

      if (!$ok) {
        $error = [
          'message' => 'Gagal menambahkan data menu topik'
        ];
      } else {
        $data = [
          'message' => 'Berhasil menambahkan data topik'
        ];

        $code = 200;
      }
    }

    $db->close();

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function remove($topic_id)
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

    $queryTopic = $db->query("DELETE FROM topics WHERE id = ? AND user_id = ?", [$topic_id, $user_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryTopic) {
      $error = [
        'message' => 'Gagal menghapus data topik'
      ];
    } else {
      if ($this->delete_menu_data($user_id, $topic_id)) {
        $data = [
          'id' => (int) $topic_id,
          'message' => 'Berhasil menghapus data topik'
        ];

        $code = 200;
      } else {
        $error = [
          'message' => 'Gagal menghapus data menu'
        ];
      }
    }

    $db->close();

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function change($topic_id = null)
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

    $queryTopic = $db->query("UPDATE topics SET name = ? WHERE user_id = ? AND id = ?", [$jsonData->name, $user_id, $topic_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryTopic) {
      $error = [
        'message' => 'Gagal merubah data topik'
      ];
    } else {
      $data = [
        'id' => (int) $topic_id,
        'name' => $jsonData->name,
        'message' => 'Berhasil merubah data topik'
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

  // helper
  private function delete_menu_data($user_id = null, $topic_id = null)
  {
    if ($topic_id == null || $user_id == null) {
      return false;
    }

    $db = \Config\Database::connect();

    $queryTopic = $db->query("DELETE FROM menus WHERE topic_id = ? AND user_id = ?", [$topic_id, $user_id]);

    $db->close();

    return $queryTopic;
  }

  private function insert_menu_data($user_id = null, $topic_id = null)
  {
    if ($topic_id == null || $user_id == null) {
      return false;
    }

    $menuData = [
      [
        'title' => 'Materi',
        'is_mandatory' => true,
        'path' => '/topic',
        'icon' => 'assets/img/menus/content.svg'
      ],
      [
        'title' => 'Glosarium',
        'is_mandatory' => true,
        'path' => '/glossary',
        'icon' => 'assets/img/menus/glosarium.svg'
      ],
      [
        'title' => 'Indikator',
        'is_mandatory' => true,
        'path' => '/indicator',
        'icon' => 'assets/img/menus/indikator.svg'
      ],
      [
        'title' => 'Tujuan',
        'is_mandatory' => true,
        'path' => '/objective',
        'icon' => 'assets/img/menus/tujuan.svg'
      ],
      [
        'title' => 'Daftar Pustaka',
        'is_mandatory' => true,
        'path' => '/bibliography',
        'icon' => 'assets/img/menus/daftar-pustaka.svg'
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
}
