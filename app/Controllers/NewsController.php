<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class NewsController extends ResourceController
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

    $categoryFilter = $this->request->getGet('category_id');

    $queryNews = $db->query("SELECT id, user_id, content, title, banner, category_id, created_at, updated_at FROM news WHERE deleted_at is NULL ORDER BY id DESC");
    
    if($userData->role_id == 1) {
      $queryNews = $db->query("SELECT id, user_id, content, title, banner, category_id, created_at, updated_at FROM news WHERE deleted_at is NULL AND user_id = ? ORDER BY id DESC", [$userData->id]);
    }
    
    if($categoryFilter) {
      $queryNews = $db->query("SELECT id, user_id, content, title, banner, category_id, created_at, updated_at FROM news WHERE deleted_at is NULL AND category_id = ? ORDER BY id DESC", [$categoryFilter]);
    }
    
    $news = $queryNews->getResult('object');

    $imgURL = "https://insandev.com/public";
    if ($_SERVER['CI_ENVIRONMENT'] == "development") {
      if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER)) {
        $imgURL = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['HTTP_HOST'];
      } else {
        $imgURL = "http://localhost:8080";
      }
    }

    $arr_news = [];

    foreach ($news as $newsData) {
      $shortContent = substr($newsData->content, 0, 50);
      if (strlen($newsData->content) >= 50) {
        $shortContent .= " [...]";
      }

      $queryCategory = $db->query("SELECT id, name FROM news_categories WHERE id = ?", [$newsData->category_id]);

      $category = $queryCategory->getRow();

      // $gambar = "1670317191_5bb946a545f0a5263d24.jpg";

      $category_name = "";

      if ($category) {
        $category_name = $category->name;
      }

      $queryAuthor = $db->query("SELECT id, name FROM user_profiles WHERE id = ?", [$newsData->user_id]);

      $author = $queryAuthor->getRow();

      $author_name = "";

      if ($author) {
        $author_name = $author->name;
      }

      $temp = [
        'type' => 'news',
        'id' => (int)$newsData->id,
        'attributes' => [
          'category_id' => (int)$newsData->category_id,
          'category_name' => $category_name,
          'author_id' => $newsData->user_id,
          'author_name' => $author_name,
          'title' => $newsData->title,
          'content' => $shortContent,
          'banner' => $imgURL . $newsData->banner,
          'created_at' => $newsData->created_at,
          'updated_at' => $newsData->updated_at
        ]
      ];

      array_push($arr_news, $temp);
    }

    $db->close();

    $data = [
      'data' => $arr_news,
      'error' => null
    ];

    return $this->respond($data, 200);
  }

  public function show($news_id = null)
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

    if (!$news_id) {
      return $this->respond('News ID Required', 400);
    }

    $queryNews = $db->query("SELECT id, user_id, content, title, banner, category_id, created_at, updated_at FROM news WHERE id = ? AND deleted_at IS NULL", [$news_id]);

    $news = $queryNews->getRow();

    $queryCategory = $db->query("SELECT id, name FROM news_categories WHERE id = ?", [$news->category_id]);

    $category = $queryCategory->getRow();

    $category_name = "";

    if ($category) {
      $category_name = $category->name;
    }

    $queryAuthor = $db->query("SELECT id, name FROM user_profiles WHERE id = ?", [$news->user_id]);

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

    if ($news) {
      // $gambar = "1670317191_5bb946a545f0a5263d24.jpg";

      $data = [
        'type' => 'news',
        'id' => (int)$news->id,
        'attributes' => [
          'category_id' => (int)$news->category_id,
          'category_name' => $category_name,
          'author_id' => $news->user_id,
          'author_name' => $author_name,
          'title' => $news->title,
          'content' => $news->content,
          'banner' => $imgURL . $news->banner,
          'created_at' => $news->created_at,
          'updated_at' => $news->updated_at,
        ]
      ];
    } else {
      $error = [
        'message' => 'ID tidak ditemukan pada tabel berita'
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

    $queryNews = $db->query("INSERT INTO news (title, content, category_id, user_id) VALUES (?, ?, ?, ?)", [$jsonData->title, $jsonData->content, $jsonData->category_id, $user_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryNews) {
      $error = [
        'message' => 'Gagal menambahkan data berita'
      ];
    } else {
      $data = [
        'id' => $db->insertID(),
        'message' => 'Berhasil menambahkan data berita'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function remove($news_id)
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

    $queryNews = $db->query("UPDATE news SET deleted_at = ? WHERE id = ?", [$currentDatetime, $news_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryNews) {
      $error = [
        'message' => 'Gagal menghapus data berita'
      ];
    } else {
      $data = [
        'id' => (int) $news_id,
        'message' => 'Berhasil menghapus data berita'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function change($news_id = null)
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

    $queryNews = $db->query("UPDATE news SET title = ?, content = ?, category_id = ? WHERE id = ?", [$jsonData->title, $jsonData->content, $jsonData->category_id, $news_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryNews) {
      $error = [
        'message' => 'Gagal merubah data berita'
      ];
    } else {
      $data = [
        'id' => (int) $news_id,
        'content' => $jsonData->content,
        'message' => 'Berhasil merubah data berita'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function upload_banner($news_id)
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
      $queryExistingPicture = $db->query("SELECT id, banner FROM news WHERE id = ?", [$news_id]);

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

      $queryPicture = $db->query("UPDATE news SET banner = ? WHERE id = ?", [$filepath, $news_id]);

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
