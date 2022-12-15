<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class MaterialController extends ResourceController
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
      return $this->respond('Topic ID Required', 400);
    }

    $queryParentMenu = $db->query("SELECT id FROM menus WHERE user_id = ? AND parent_menu_id is NULL AND `path` = '/topic' AND topic_id = ?", [$user_id, $topic_id]);

    $parentMenu = $queryParentMenu->getRow();

    if (!$parentMenu) {
      $data_result = [
        'menus' => [],
        'super_parent_id' => 0
      ];

      $data = [
        'data' => $data_result,
        'error' => null
      ];

      return $this->respond($data, 200);
    }

    $queryMenus = $db->query("SELECT id, topic_id, parent_menu_id, title, icon, `path` FROM menus WHERE user_id = ? AND parent_menu_id = ? AND topic_id = ?", [$user_id, $parentMenu->id, $topic_id]);

    $menus = $queryMenus->getResult('object');

    $arr_menu = [];

    foreach ($menus as $menu) {
      $temp = [
        'type' => 'menu',
        'id' => (int) $menu->id,
        'attributes' => [
          'topic_id' => (int) $topic_id,
          'parent_menu_id' => (int)$menu->parent_menu_id,
          'title' => $menu->title,
          'path' => $menu->path,
          'icon' => $menu->icon
        ]
      ];

      array_push($arr_menu, $temp);
    }

    $data_result = [
      'menus' => $arr_menu,
      'super_parent_id' => 0
    ];

    if ($parentMenu) {
      $data_result = [
        'menus' => $arr_menu,
        'super_parent_id' => (int) $parentMenu->id
      ];
    }

    $db->close();

    $data = [
      'data' => $data_result,
      'error' => null
    ];

    return $this->respond($data, 200);
  }

  public function show($parent_menu_id = null)
  {
    if ($parent_menu_id == null) {
      return $this->respond("Bad Request", 400);
    }

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

    $queryParentMenu = $db->query("SELECT id, title, parent_menu_id FROM menus WHERE user_id = ? AND id = ?", [$user_id, $parent_menu_id]);

    $parentMenu = $queryParentMenu->getRow();

    $queryMenus = $db->query("SELECT id, parent_menu_id, title, icon, `path` FROM menus WHERE user_id = ? AND parent_menu_id = ?", [$user_id, $parent_menu_id]);

    $menus = $queryMenus->getResult('object');

    $queryMaterial = $db->query("SELECT id, menu_id, banner, content, created_at, updated_at FROM materials WHERE user_id = ? AND menu_id = ? AND deleted_at is NULL AND is_archive = 0", [$user_id, $parent_menu_id]);

    // $materials = $queryMaterials->getResult('object');
    $material = $queryMaterial->getRow();

    $arr_menu = [];

    $menu_data = [];
    $material_data = null;

    foreach ($menus as $menu) {
      $temp = [
        'type' => 'menu',
        'id' => (int)$menu->id,
        'attributes' => [
          'parent_menu_id' => (int)$menu->parent_menu_id,
          'title' => $menu->title,
          'path' => $menu->path,
          'icon' => $menu->icon
        ]
      ];

      array_push($menu_data, $temp);
    }

    // foreach ($materials as $material) {
    //   $temp = [
    //     'type' => 'material',
    //     'id' => (int)$material->id,
    //     'attributes' => [
    //       'menu_id' => $material->menu_id,
    //       'content' => $material->content,
    //       'created_at' => $material->created_at,
    //       'updated_at' => $material->updated_at
    //     ]
    //   ];

    //   array_push($material_data, $temp);
    // }

    $imgURL = "";

    if ($_SERVER['CI_ENVIRONMENT'] == "development") {
      if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER)) {
        $imgURL = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['HTTP_HOST'];
      } else {
        $imgURL = "http://localhost:8080";
      }
    } else {
      $imgURL = "https://memofy.net/public";
    }

    if ($material) {
      $modifyContent = $material->content;
      $imageDisplayURL = "";

      if ($material->banner != "") {
        $imageDisplayURL = $imgURL . $material->banner;
      }

      $material_data = [
        'type' => 'material',
        'id' => (int)$material->id,
        'attributes' => [
          'menu_id' => (int)$material->menu_id,
          'parent_menu_id' => (int)$parentMenu->parent_menu_id,
          'banner' => $imageDisplayURL,
          'title' => $parentMenu->title,
          'content' => $modifyContent,
          'created_at' => $material->created_at,
          'updated_at' => $material->updated_at
        ]
      ];
    }

    $arr_menu = [
      'menus' => $menu_data,
      'material' => $material_data
    ];

    $db->close();

    $data = [
      'data' => $arr_menu,
      'error' => null
    ];

    return $this->respond($data, 200);

    return $parent_menu_id;
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

    $queryMaterial = $db->query("INSERT INTO materials (user_id, menu_id, content, is_archive) VALUES (?, ?, ?, ?)", [$user_id, $jsonData->menu_id, $jsonData->content, $jsonData->is_archive]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryMaterial) {
      $error = [
        'message' => 'Gagal menambahkan data materi'
      ];
    } else {
      $data = [
        'id' => $db->insertID(),
        'message' => 'Berhasil menambahkan data materi'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function change($menu_id = null)
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

    $queryMenu = $db->query("UPDATE materials SET content = ? WHERE user_id = ? AND menu_id = ?", [$jsonData->content, $user_id, $menu_id]);

    $data = null;
    $error = null;
    $code = 400;

    if (!$queryMenu) {
      $error = [
        'message' => 'Gagal merubah data materi'
      ];
    } else {
      $data = [
        'menu_id' => (int) $menu_id,
        'content' => $jsonData->content,
        'message' => 'Berhasil merubah data materi'
      ];

      $code = 200;
    }

    $data = [
      'data' => $data,
      'error' => $error
    ];

    return $this->respond($data, $code);
  }

  public function change_banner($menu_id = NULL)
  {
    $api_key = $this->request->getGet('api_key');

    if (!$api_key) {
      return $this->respond('API Key Required', 403);
    }

    if (!$menu_id) {
      return $this->respond("Parameter Menu ID must not be empty", 400);
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
      $queryExistingPicture = $db->query("SELECT id, menu_id, banner FROM materials WHERE user_id = ? AND menu_id = ?", [$user_id, $menu_id]);

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

      $queryPicture = $db->query("UPDATE materials SET banner = ? WHERE user_id = ? AND menu_id = ?", [$filepath, $user_id, $menu_id]);

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
