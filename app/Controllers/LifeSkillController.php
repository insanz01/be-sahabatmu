<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class LifeSkillController extends ResourceController
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

    
    $user_id = $user->user_id;
    
    $queryLifeSkill = $db->query("SELECT id, user_id, title, video_url, created_at, updated_at FROM lifeskill WHERE deleted_at is NULL");
    
    if($userData->role_id == 1) {
      $queryLifeSkill = $db->query("SELECT id, user_id, title, video_url, created_at, updated_at FROM lifeskill WHERE deleted_at is NULL AND user_id = ?", [$userData->id]); 
    }
    
    $lifeSkills = $queryLifeSkill->getResult('object');

    $imgURL = "https://insandev.com/public";
    if ($_SERVER['CI_ENVIRONMENT'] == "development") {
      if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER)) {
        $imgURL = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['HTTP_HOST'];
      } else {
        $imgURL = "http://localhost:8080";
      }
    }

    $arr_lifeSkills = [];

    foreach ($lifeSkills as $lifeSkill) {
      $queryAuthor = $db->query("SELECT id, name FROM user_profiles WHERE id = ?", [$lifeSkill->user_id]);

      $author = $queryAuthor->getRow();

      $author_name = "";

      if ($author) {
        $author_name = $author->name;
      }

      $temp = [
        'type' => 'life_skill',
        'id' => (int)$lifeSkill->id,
        'attributes' => [
          'user_id' => (int)$lifeSkill->user_id,
          'author' => $author_name,
          'title' => $lifeSkill->title,
          'video_url' => $lifeSkill->video_url,
          'created_at' => $lifeSkill->created_at,
          'updated_at' => $lifeSkill->updated_at
        ]
      ];

      array_push($arr_lifeSkills, $temp);
    }

    $db->close();

    $data = [
      'data' => $arr_lifeSkills,
      'error' => null
    ];

    return $this->respond($data, 200);
  }

  public function show($lifeSkill_id = null)
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

    if (!$lifeSkill_id) {
      return $this->respond('Guidance ID Required', 400);
    }

    $queryLifeSkill = $db->query("SELECT id, user_id, title, video_url, created_at, updated_at FROM lifeskill WHERE id = ? AND deleted_at is NULL", [$lifeSkill_id]);

    $lifeSkill = $queryLifeSkill->getRow();

    $imgURL = "https://insandev.com/public";
    if ($_SERVER['CI_ENVIRONMENT'] == "development") {
      if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER)) {
        $imgURL = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['HTTP_HOST'];
      } else {
        $imgURL = "http://localhost:8080";
      }
    }

    $queryAuthor = $db->query("SELECT id, name FROM user_profiles WHERE id = ?", [$lifeSkill->user_id]);

    $author = $queryAuthor->getRow();

    $author_name = "";

    if ($author) {
      $author_name = $author->name;
    }

    $data = null;
    $error = null;

    if ($lifeSkill) {
      $data = [
        'type' => 'life_skill',
        'id' => (int)$lifeSkill->id,
        'attributes' => [
          'user_id' => (int)$lifeSkill->user_id,
          'author' => $author_name,
          'title' => $lifeSkill->title,
          'video_url' => $lifeSkill->video_url,
          'created_at' => $lifeSkill->created_at,
          'updated_at' => $lifeSkill->updated_at
        ]
      ];
    } else {
      $error = [
        'message' => 'ID tidak ditemukan pada tabel life skill'
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

    $queryLifeSkill = $db->query("INSERT INTO lifeskill (user_id, title, video_url) VALUES (?, ?, ?)", [$user_id, $jsonData->title, $jsonData->video_url]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryLifeSkill) {
      $error = [
        'message' => 'Gagal menambahkan data life skill'
      ];
    } else {
      $data = [
        'id' => $db->insertID(),
        'message' => 'Berhasil menambahkan data life skill'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function remove($lifeskill_id)
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

    // $queryGuidances = $db->query("DELETE FROM guidances WHERE id = ? AND user_id = ?", [$lifeskill_id, $user_id]);
    $queryLifeSkill = $db->query("UPDATE lifeskill SET deleted_at = ? WHERE id = ? AND user_id = ?", [$currentTime, $lifeskill_id, $user_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryLifeSkill) {
      $error = [
        'message' => 'Gagal menghapus data lifeskill'
      ];
    } else {
      $data = [
        'id' => (int) $lifeskill_id,
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

  public function change($lifeSkill_id = null)
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

    $queryExistingLifeSkill = $db->query("SELECT id FROM lifeskill WHERE user_id = ? AND id = ?", [$user_id, $lifeSkill_id]);

    $existingLifeSkill = $queryExistingLifeSkill->getRow();

    if (!$existingLifeSkill) {
      return $this->respond("User ID dan ID Lifeskill tidak cocok", 404);
    }

    $queryLifeSkill = $db->query("UPDATE lifeskill SET title = ?, video_url = ? WHERE user_id = ? AND id = ?", [$jsonData->title, $jsonData->video_url, $user_id, $lifeSkill_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryLifeSkill) {
      $error = [
        'message' => 'Gagal merubah data life skill'
      ];
    } else {
      $data = [
        'id' => (int) $lifeSkill_id,
        'title' => $jsonData->title,
        'video_url' => $jsonData->video_url,
        'message' => 'Berhasil merubah data life skill'
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
