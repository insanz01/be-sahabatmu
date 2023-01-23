<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class CounselorController extends ResourceController
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

    $queryUserData = $db->query("SELECT role_id FROM users WHERE user_id = ?", [$user->user_id]);
    $userData = $queryUserData->getRow();

    $categoryFilter = $this->request->getGet("category_id");
    
    $queryCounselor = $db->query("SELECT id, user_id, image, category_id, created_at, updated_at FROM counselors WHERE deleted_at is NULL");
    
    if($userData->role_id == 1) {
      $queryCounselor = $db->query("SELECT id, user_id, image, category_id, created_at, updated_at FROM counselors WHERE deleted_at is NULL AND user_id = ?", [$userData->id]);
    }
    
    if($categoryFilter) {
      $queryCounselor = $db->query("SELECT id, user_id, image, category_id, created_at, updated_at FROM counselors WHERE deleted_at is NULL AND category_id = ?", [$categoryFilter]);
    }

    $counselors = $queryCounselor->getResult('object');

    $imgURL = "https://insandev.com/public";
    if ($_SERVER['CI_ENVIRONMENT'] == "development") {
      if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER)) {
        $imgURL = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['HTTP_HOST'];
      } else {
        $imgURL = "http://localhost:8080";
      }
    }

    $arr_counselors = [];

    foreach ($counselors as $counselor) {
      $queryCategory = $db->query("SELECT id, name FROM counselor_categories WHERE id = ?", [$counselor->category_id]);

      $category = $queryCategory->getRow();

      $category_name = "";

      if ($category) {
        $category_name = $category->name;
      }

      $queryAuthor = $db->query("SELECT id, name FROM user_profiles WHERE id = ?", [$counselor->user_id]);

      $author = $queryAuthor->getRow();

      $author_name = "";

      if ($author) {
        $author_name = $author->name;
      }

      // $gambar = "1670317191_5bb946a545f0a5263d24.jpg";

      $temp = [
        'type' => 'counselor',
        'id' => (int)$counselor->id,
        'attributes' => [
          'category_id' => (int)$counselor->category_id,
          'category_name' => $category_name,
          'user_id' => $counselor->user_id,
          'author' => $author_name,
          'image' => $imgURL . $counselor->image,
          'created_at' => $counselor->created_at,
          'updated_at' => $counselor->updated_at
        ]
      ];

      array_push($arr_counselors, $temp);
    }

    $db->close();

    $data = [
      'data' => $arr_counselors,
      'error' => null
    ];

    return $this->respond($data, 200);
  }

  public function show($counselor_id = null)
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

    if (!$counselor_id) {
      return $this->respond('News ID Required', 400);
    }

    $queryCounselor = $db->query("SELECT id, user_id, image, category_id, created_at, updated_at FROM counselors WHERE id = ? AND deleted_at IS NULL", [$counselor_id]);

    $counselor = $queryCounselor->getRow();

    $queryCategory = $db->query("SELECT id, name FROM counselor_categories WHERE id = ?", [$counselor->category_id]);

    $category = $queryCategory->getRow();

    $category_name = "";

    if ($category) {
      $category_name = $category->name;
    }

    $queryAuthor = $db->query("SELECT id, name FROM user_profiles WHERE id = ?", [$counselor->user_id]);

    $author = $queryAuthor->getRow();

    $author_name = "";

    if ($author) {
      $author_name = $author->name;
    }

    $data = null;
    $error = null;

    $imgURL = "https://insandev.com/public";
    if ($_SERVER['CI_ENVIRONMENT'] == "development") {
      if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER)) {
        $imgURL = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['HTTP_HOST'];
      } else {
        $imgURL = "http://localhost:8080";
      }
    }

    if ($counselor) {
      // $gambar = "1670317191_5bb946a545f0a5263d24.jpg";

      $data = [
        'type' => 'counselor',
        'id' => (int)$counselor->id,
        'attributes' => [
          'category_id' => (int)$counselor->category_id,
          'category_name' => $category_name,
          'user_id' => $counselor->user_id,
          'author' => $author_name,
          'image' => $imgURL . $counselor->image,
          'created_at' => $counselor->created_at,
          'updated_at' => $counselor->updated_at
        ]
      ];
    } else {
      $error = [
        'message' => 'ID tidak ditemukan pada tabel konselor'
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

    $queryCounselor = $db->query("INSERT INTO counselors (user_id, category_id) VALUES (?, ?)", [$jsonData->user_id, $jsonData->category_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryCounselor) {
      $error = [
        'message' => 'Gagal menambahkan data konselor'
      ];
    } else {
      $data = [
        'id' => $db->insertID(),
        'message' => 'Berhasil menambahkan data konselor'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function remove($counselor_id)
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

    $currentDatetime = date('Y-m-d H:i:s', time());

    $queryCounselor = $db->query("UPDATE counselors SET deleted_at = ? WHERE id = ?", [$currentDatetime, $counselor_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryCounselor) {
      $error = [
        'message' => 'Gagal menghapus data konselor'
      ];
    } else {
      $data = [
        'id' => (int) $counselor_id,
        'message' => 'Berhasil menghapus data konselor'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function change($counselor_id = null)
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

    $queryCounselor = $db->query("UPDATE counselors SET category_id = ?, user_id = ? WHERE id = ?", [$jsonData->category_id, $jsonData->user_id, $counselor_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryCounselor) {
      $error = [
        'message' => 'Gagal merubah data konselor'
      ];
    } else {
      $data = [
        'id' => (int) $counselor_id,
        'message' => 'Berhasil merubah data konselor'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function upload_image($counselor_id)
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
      $queryExistingPicture = $db->query("SELECT id, image FROM counselors WHERE id = ?", [$counselor_id]);

      $existingPicture = $queryExistingPicture->getRow();

      if ($existingPicture) {
        if ($existingPicture->image != "") {
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

      $queryPicture = $db->query("UPDATE counselors SET image = ? WHERE id = ?", [$filepath, $counselor_id]);

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
