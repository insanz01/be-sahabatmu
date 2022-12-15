<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;
use CodeIgniter\Files\File;
use CodeIgniter\HTTP\Files\UploadedFile;


class AboutController extends ResourceController
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

    $queryUser = $db->query("SELECT id, username FROM users WHERE id = ?", [$user_id]);

    $user = $queryUser->getRow();

    $queryAbout = $db->query("SELECT id, title, description, short_description, banner, updated_at FROM abouts WHERE id = $user_id");

    $about = $queryAbout->getRow();

    $queryProfile = $db->query("SELECT id, name, phone_number, email, photo FROM user_profiles WHERE id = ?", [$user_id]);

    $profile = $queryProfile->getRow();

    if ($_SERVER['CI_ENVIRONMENT'] == "development") {
      if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER)) {
        $imgURL = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['HTTP_HOST'];
      } else {
        $imgURL = "http://localhost:8080";
      }
    } else {
      $imgURL = "https://memofy.net/public";
    }

    $result = [
      'type' => 'about',
      'id' => (int)$user_id,
      'attributes' => [
        'username' => $user->username,
        'name' => $profile->name,
        'phone_number' => $profile->phone_number,
        'email' => $profile->email,
        'photo' => $imgURL . $profile->photo,
        'title' => $about->title,
        'description' => $about->description,
        'banner' => $imgURL . $about->banner,
        'short_description' => $about->short_description,
        'updated_at' => $about->updated_at
      ]
    ];

    $db->close();

    $data = [
      'data' => $result,
      'error' => null
    ];

    return $this->respond($data, 200);
  }

  public function change_photo()
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
      'profile_picture' => [
        'label' => 'Image File',
        'rules' => 'uploaded[profile_picture]'
          . '|is_image[profile_picture]'
          . '|mime_in[profile_picture,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
          . '|max_size[profile_picture,5000]',
      ],
    ];

    if (!$this->validate($validationRule)) {
      $data = [
        'data' => null,
        'error' => $this->validator->getErrors()
      ];

      return $this->respond($data, 400);
    }

    $img = $this->request->getFile('profile_picture');

    if (!$img->hasMoved()) {
      $queryExistingPicture = $db->query("SELECT id, photo FROM user_profiles WHERE id = ?", [$user_id]);

      $existingPicture = $queryExistingPicture->getRow();

      if ($existingPicture) {
        if ($existingPicture->photo != "") {
          $file = new \CodeIgniter\Files\File($existingPicture->photo);

          if ($_SERVER['CI_ENVIRONMENT'] == "development") {
            // var_dump($_SERVER["DOCUMENT_ROOT"] . "/uploads/" . $file->getFilename());
            unlink($_SERVER["DOCUMENT_ROOT"] . "/uploads/" . $file->getFilename());
          } else {

            unlink("/home/memofyne/public_html/public/uploads/" . $file->getFilename());
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
        $display_image = "https://memofy.net/public/uploads/" . $new_name;
      }

      $filepath = "/uploads/" . $new_name;

      $queryPicture = $db->query("UPDATE user_profiles SET photo = ? WHERE id = ?", [$filepath, $user_id]);

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

  public function change_banner()
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
      'banner_picture' => [
        'label' => 'Image File',
        'rules' => 'uploaded[banner_picture]'
          . '|is_image[banner_picture]'
          . '|mime_in[banner_picture,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
          . '|max_size[banner_picture,5000]',
      ],
    ];

    if (!$this->validate($validationRule)) {
      $data = [
        'data' => null,
        'error' => $this->validator->getErrors()
      ];

      return $this->respond($data, 400);
    }

    $img = $this->request->getFile('banner_picture');

    if (!$img->hasMoved()) {
      $queryExistingPicture = $db->query("SELECT id, banner FROM abouts WHERE id = ?", [$user_id]);

      $existingPicture = $queryExistingPicture->getRow();

      if ($existingPicture) {
        if ($existingPicture->banner != "") {
          $file = new \CodeIgniter\Files\File($existingPicture->banner);

          if ($_SERVER['CI_ENVIRONMENT'] == "development") {
            // var_dump($_SERVER["DOCUMENT_ROOT"] . "/uploads/" . $file->getFilename());
            unlink($_SERVER["DOCUMENT_ROOT"] . "/uploads/" . $file->getFilename());
          } else {

            unlink("/home/memofyne/public_html/public/uploads/" . $file->getFilename());
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
        $display_image = "https://memofy.net/public/uploads/" . $new_name;
      }

      $filepath = "/uploads/" . $new_name;

      $queryPicture = $db->query("UPDATE abouts SET banner = ? WHERE id = ?", [$filepath, $user_id]);

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

  public function change_profile()
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

    $jsonData = $this->request->getJSON();

    $error_message = "";
    $update_query  = "";
    $update_value = [];

    if (property_exists($jsonData, "email")) {
      if (!$jsonData->email) {
        $error_message .= "Email tidak boleh kosong";
      } else {
        $update_query .= "email = ?";
        array_push($update_value, $jsonData->email);
      }
    }

    if (property_exists($jsonData, "phone_number")) {
      if (!$jsonData->phone_number) {
        $error_message .= "Nomor HP tidak boleh kosong";
      } else {
        if ($update_query == "") {
          $update_query .= "phone_number = ?";
        } else {
          $update_query .= ", phone_number = ?";
        }
        array_push($update_value, $jsonData->phone_number);
      }
    }

    if ($error_message != "") {
      $data = [
        "data" => null,
        "error" => [
          "message" => $error_message
        ]
      ];

      $db->close();

      return $this->respond($data, 400);
    }

    $user_id = (int) $user->user_id;

    $currentTimestamp = date('Y-m-d H:i:s', time());

    array_push($update_value, $currentTimestamp);
    array_push($update_value, $user_id);

    $queryAbout = $db->query("UPDATE user_profiles SET " . $update_query . ", updated_at = ? WHERE id = ?", $update_value);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryAbout) {
      $error = [
        'message' => 'Gagal merubah data profil'
      ];
    } else {
      $data = [
        'user_id' => (int) $user_id,
        'message' => 'Berhasil merubah data profil'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function change_about()
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

    $jsonData = $this->request->getJSON();

    $error_message = "";
    $update_query  = "";
    $update_value = [];

    if (property_exists($jsonData, "description")) {
      if (!$jsonData->description) {
        $error_message .= "Deskripsi tidak boleh kosong";
      } else {
        $update_query .= "description = ?";
        array_push($update_value, $jsonData->description);
      }
    }

    if (property_exists($jsonData, "short_description")) {
      if (!$jsonData->short_description) {
        $error_message .= "Deskripsi singkat tidak boleh kosong";
      } else {
        if ($update_query == "") {
          $update_query .= "short_description = ?";
        } else {
          $update_query .= ", short_description = ?";
        }
        array_push($update_value, $jsonData->short_description);
      }
    }

    if ($error_message != "") {
      $data = [
        "data" => null,
        "error" => [
          "message" => $error_message
        ]
      ];

      return $this->respond($data, 400);
    }

    $user_id = (int) $user->user_id;

    $currentTimestamp = date('Y-m-d H:i:s', time());

    array_push($update_value, $currentTimestamp);
    array_push($update_value, $user_id);

    $queryAbout = $db->query("UPDATE abouts SET " . $update_query . ", updated_at = ? WHERE id = ?", $update_value);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryAbout) {
      $error = [
        'message' => 'Gagal merubah data tentang'
      ];
    } else {
      $data = [
        'user_id' => (int) $user_id,
        'message' => 'Berhasil merubah data tentang'
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
