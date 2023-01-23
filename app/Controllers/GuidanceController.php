<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class GuidanceController extends ResourceController
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

    $queryUserData = $db->query("SELECT role_id FROM users WHERE user_id = ?", [$user->user_id]);
    $userData = $queryUserData->getRow();
    
    $queryGuidance = $db->query("SELECT id, user_id, title, meeting_url, banner FROM guidances WHERE deleted_at is NULL");
    
    if($userData->role_id == 1) {
      $queryGuidance = $db->query("SELECT id, user_id, title, meeting_url, banner FROM guidances WHERE deleted_at is NULL AND user_id = ?", [$userData->id]);
    }
    
    $guidances = $queryGuidance->getResult('object');

    $imgURL = "https://insandev.com/public";
    if ($_SERVER['CI_ENVIRONMENT'] == "development") {
      if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER)) {
        $imgURL = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['HTTP_HOST'];
      } else {
        $imgURL = "http://localhost:8080";
      }
    }

    $arr_guidances = [];

    foreach ($guidances as $guidance) {
      $queryAuthor = $db->query("SELECT id, name FROM user_profiles WHERE id = ?", [$guidance->user_id]);

      $author = $queryAuthor->getRow();

      $author_name = "";

      if ($author) {
        $author_name = $author->name;
      }

      $temp = [
        'type' => 'guidance',
        'id' => (int)$guidance->id,
        'attributes' => [
          'user_id' => (int)$guidance->user_id,
          'author_name' => $author_name,
          'title' => $guidance->title,
          'meeting_url' => $guidance->meeting_url
          // 'banner' => $imgURL . $guidance->banner
        ]
      ];

      array_push($arr_guidances, $temp);
    }

    $db->close();

    $data = [
      'data' => $arr_guidances,
      'error' => null
    ];

    return $this->respond($data, 200);
  }

  public function show($guidance_id = null)
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

    if (!$guidance_id) {
      return $this->respond('Guidance ID Required', 400);
    }

    $queryGuidance = $db->query("SELECT id, user_id, title, meeting_url, banner FROM guidances WHERE id = ? AND deleted_at is NULL", [$guidance_id]);

    $guidance = $queryGuidance->getRow();

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
    
    if ($guidance) {
      $queryAuthor = $db->query("SELECT id, name FROM user_profiles WHERE id = ?", [$guidance->user_id]);
  
      $author = $queryAuthor->getRow();
  
      $author_name = "";
  
      if ($author) {
        $author_name = $author->name;
      }

      $data = [
        'type' => 'guidance',
        'id' => (int)$guidance->id,
        'attributes' => [
          'user_id' => (int)$guidance->user_id,
          'author_name' => $author_name,
          'title' => $guidance->title,
          'meeting_url' => $guidance->meeting_url
          // 'banner' => $imgURL . $guidance->banner
        ]
      ];
    } else {
      $error = [
        'message' => 'ID tidak ditemukan pada tabel guidances'
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

    $queryGuidance = $db->query("INSERT INTO guidances (user_id, title, meeting_url) VALUES (?, ?, ?)", [$user_id, $jsonData->title, $jsonData->meeting_url]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryGuidance) {
      $error = [
        'message' => 'Gagal menambahkan data guidances'
      ];
    } else {
      $data = [
        'id' => $db->insertID(),
        'message' => 'Berhasil menambahkan data guidances'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function remove($guidance_id)
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

    $currentTime = date('Y-m-d H:i:s', time());

    $user_id = $user->user_id;

    // $queryGuidances = $db->query("DELETE FROM guidances WHERE id = ? AND user_id = ?", [$guidance_id, $user_id]);
    $queryGuidances = $db->query("UPDATE guidances SET deleted_at = ? WHERE id = ? AND user_id = ?", [$currentTime, $guidance_id, $user_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryGuidances) {
      $error = [
        'message' => 'Gagal menghapus data guidances'
      ];
    } else {
      $data = [
        'id' => (int) $guidance_id,
        'message' => 'Berhasil menghapus data guidances'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function change($guidance_id = null)
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

    $queryGuidance = $db->query("UPDATE guidances SET title = ?, meeting_url = ? WHERE user_id = ? AND id = ?", [$jsonData->title, $jsonData->meeting_url, $user_id, $guidance_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryGuidance) {
      $error = [
        'message' => 'Gagal merubah data guidance'
      ];
    } else {
      $data = [
        'id' => (int) $guidance_id,
        'title' => $jsonData->title,
        'meeting_url' => $jsonData->meeting_url,
        'message' => 'Berhasil merubah data guidance'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function upload_image($guidance_id)
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

    $validationRule = [
      'banner_image' => [
        'label' => 'Image File',
        'rules' => 'uploaded[banner_image]'
          . '|is_image[banner_image]'
          . '|mime_in[banner_image,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
          . '|max_size[banner_image,5000]',
      ],
    ];

    if (!$this->validate($validationRule)) {
      $data = [
        'data' => null,
        'error' => $this->validator->getErrors()
      ];

      return $this->respond($data, 400);
    }

    $img = $this->request->getFile('banner_image');

    if (!$img->hasMoved()) {
      $queryExistingPicture = $db->query("SELECT id, banner FROM guidances WHERE id = ?", [$guidance_id]);

      $existingPicture = $queryExistingPicture->getRow();

      if ($existingPicture) {
        if ($existingPicture->banner != "") {
          $file = new \CodeIgniter\Files\File($existingPicture->banner);

          if ($_SERVER['CI_ENVIRONMENT'] == "development") {
            // var_dump($_SERVER["DOCUMENT_ROOT"] . "/uploads/" . $file->getFilename());
            unlink($_SERVER["DOCUMENT_ROOT"] . "/uploads/" . $file->getFilename());
          } else {

            unlink("/home/n1572959/public_html/public/uploads/" . $file->getFilename());
          }
        }
      }

      $new_name = $img->getRandomName();

      $img->move('../public/uploads', $new_name);

      $display_image = "";

      if ($_SERVER['CI_ENVIRONMENT'] == "development") {
        if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER)) {
          $display_image = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['HTTP_HOST'] . "/uploads/" . $new_name;
        } else {
          $display_image = 'http://' . $_SERVER['HTTP_HOST'] . "/uploads/" . $new_name;
        }
      } else {
        $display_image = "https://insandev.com/public/uploads/" . $new_name;
      }

      $filepath = "/uploads/" . $new_name;

      $queryPicture = $db->query("UPDATE guidances SET banner = ? WHERE id = ?", [$filepath, $guidance_id]);

      if ($queryPicture) {
        $data = [
          'data' => [
            'image_preview' => $display_image,
            'filepath' => $filepath,
          ],
          'error' => null
        ];

        $db->close();

        return $this->respond($data, 200);
      }
    }

    $data = [
      'data' => null,
      'error' => [
        'message' => 'The file has already been moved.'
      ]
    ];

    return $this->respond($data, 500);
  }
}
