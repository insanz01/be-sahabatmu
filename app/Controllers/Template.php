<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class Template extends ResourceController
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

		$query = $db->query("SELECT * FROM user_templates WHERE user_id = $user_id");

		$template = $query->getRow();

		$db->close();

		$temp = [];

		if ($template) {
			$landing_menu_num = (int) $template->landing_menu_num;
			switch ($landing_menu_num) {
				case 1:
					$landing_menu_num = 12;
					break;
				case 2:
					$landing_menu_num = 6;
					break;
				case 3:
					$landing_menu_num = 4;
					break;
				case 4:
					$landing_menu_num = 3;
					break;
			}

			$materi_grid_num = (int) $template->materi_grid_num;
			switch ($materi_grid_num) {
				case 1:
					$materi_grid_num = 12;
					break;
				case 2:
					$materi_grid_num = 6;
					break;
				case 3:
					$materi_grid_num = 4;
					break;
				case 4:
					$materi_grid_num = 3;
					break;
			}

			$video_grid_num = (int) $template->video_grid_num;
			switch ($video_grid_num) {
				case 1:
					$video_grid_num = 12;
					break;
				case 2:
					$video_grid_num = 6;
					break;
				case 3:
					$video_grid_num = 4;
					break;
				case 4:
					$video_grid_num = 3;
					break;
			}

			$temp = [
				'type' => 'template',
				'id' => (int)$template->id,
				'attributes' => [
					'name' => $template->name,
					'landing_menu_mode' => $template->landing_menu_mode,
					'landing_menu_num' => $landing_menu_num,
					'materi_mode' => $template->materi_mode,
					'materi_grid_num' => $materi_grid_num,
					'video_mode' => $template->video_mode,
					'video_grid_num' => $video_grid_num,
					'bottom_nav_num' => (int) $template->bottom_nav_num,
					'use_banner' => (bool) $template->use_banner,
					'thumbnail' => $template->thumbnail,
					'created_at' => $template->created_at,
					'updated_at' => $template->updated_at
				]
			];
		}

		$data = [
			'data' => $temp,
			'error' => null
		];

		return $this->respond($data, 200);
	}

	public function bottomNav()
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

		$query = $db->query("SELECT * FROM user_templates_bottom_nav WHERE user_id = $user_id");

		$templates = $query->getResult();

		$db->close();

		$arr_template = [];

		foreach ($templates as $template) {
			$temp = [
				'type' => 'bottom navigation',
				'id' => (int)$template->id,
				'attributes' => [
					'name' => $template->name,
					'link' => $template->link,
					'icon' => $template->icon,
					'created_at' => $template->created_at,
					'updated_at' => $template->updated_at
				]
			];

			array_push($arr_template, $temp);
		}

		$data = [
			'data' => $arr_template,
			'error' => null
		];

		return $this->respond($data, 200);
	}

	public function banner()
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

		$query = $db->query("SELECT * FROM user_templates_banner WHERE user_id = $user_id");

		$templates = $query->getResult();

		$db->close();

		$arr_template = [];

		foreach ($templates as $template) {
			$temp = [
				'type' => 'banner',
				'id' => (int)$template->id,
				'attributes' => [
					'name' => $template->name,
					'photo_url' => $template->photo_url,
					'link' => $template->link,
					'created_at' => $template->created_at,
					'updated_at' => $template->updated_at
				]
			];

			array_push($arr_template, $temp);
		}

		$data = [
			'data' => $arr_template,
			'error' => null
		];

		return $this->respond($data, 200);
	}

	public function landingMenu()
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

		$queryVoucher = $db->query("SELECT status FROM purchased WHERE user_id = $user_id");

		$voucher = $queryVoucher->getRow();

		$custom_menu = [
			[
				'id' => '1',
				'nama' => 'Indikator',
				'path' => '/indikator',
				'icon' => '',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time())
			],
			[
				'id' => '2',
				'nama' => 'Materi',
				'path' => '/materi',
				'icon' => '',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time())
			],
			[
				'id' => '3',
				'nama' => 'Profil',
				'path' => '/about',
				'icon' => '',
				'created_at' => date('Y-m-d H:i:s', time()),
				'updated_at' => date('Y-m-d H:i:s', time())
			]
		];

		// Petunjuk -> KI/KD -> Indikator -> Tujuan -> Materi -> Daftar Pustaka -> Glosarium -> Profil

		if (property_exists($voucher, 'status')) {
			if ($voucher->status == "paid") {
				$custom_menu = [
					[
						'id' => '1',
						'nama' => 'Petunjuk',
						'path' => '/petunjuk',
						'icon' => '',
						'created_at' => date('Y-m-d H:i:s', time()),
						'updated_at' => date('Y-m-d H:i:s', time())
					],
					[
						'id' => '2',
						'nama' => 'Indikator',
						'path' => '/indikator',
						'icon' => '',
						'created_at' => date('Y-m-d H:i:s', time()),
						'updated_at' => date('Y-m-d H:i:s', time())
					],
					[
						'id' => '3',
						'nama' => 'KI / KD',
						'path' => '/kikd',
						'icon' => '',
						'created_at' => date('Y-m-d H:i:s', time()),
						'updated_at' => date('Y-m-d H:i:s', time())
					],
					[
						'id' => '4',
						'nama' => 'Tujuan',
						'path' => '/tujuan',
						'icon' => '',
						'created_at' => date('Y-m-d H:i:s', time()),
						'updated_at' => date('Y-m-d H:i:s', time())
					],
					[
						'id' => '5',
						'nama' => 'Materi',
						'path' => '/materi',
						'icon' => '',
						'created_at' => date('Y-m-d H:i:s', time()),
						'updated_at' => date('Y-m-d H:i:s', time())
					],
					[
						'id' => '6',
						'nama' => 'Daftar Pustaka',
						'path' => '/pustaka',
						'icon' => '',
						'created_at' => date('Y-m-d H:i:s', time()),
						'updated_at' => date('Y-m-d H:i:s', time())
					],
					[
						'id' => '7',
						'nama' => 'Glosarium',
						'path' => '/glosarium',
						'icon' => '',
						'created_at' => date('Y-m-d H:i:s', time()),
						'updated_at' => date('Y-m-d H:i:s', time())
					],
					[
						'id' => '8',
						'nama' => 'Profil',
						'path' => '/about',
						'icon' => '',
						'created_at' => date('Y-m-d H:i:s', time()),
						'updated_at' => date('Y-m-d H:i:s', time())
					]
				];
			}
		}

		$arr_menu = [];

		foreach ($custom_menu as $menu) {
			$temp = [
				'type' => 'menu',
				'id' => (int)$menu['id'],
				'attributes' => [
					'nama' => $menu['nama'],
					'path' => $menu['path'],
					'icon' => $menu['icon'],
					'created_at' => $menu['created_at'],
					'updated_at' => $menu['updated_at']
				]
			];

			array_push($arr_menu, $temp);
		}

		$data = [
			'data' => $arr_menu,
			'error' => null
		];

		return $this->respond($data, 200);
	}
}
