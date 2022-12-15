<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class Landing extends ResourceController
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

	public function show($id = null)
	{

		$api_key = $this->request->getGet('api_key');

		if (!$api_key) {
			return $this->respond('API Key Required', 403);
		}

		if ($id == null) {
			$data = [
				'data' => null,
				'error' => [
					'type' => 'Bad Request',
					'message' => 'You must enter id'
				]
			];

			return $this->respond($data, 400);
		}

		$db = \Config\Database::connect();

		$queryUser = $db->query("SELECT user_id FROM tokens WHERE secret_key = '$api_key'");

		$user = $queryUser->getRow();

		if (!$user) {
			return $this->respond("Invalid API Key", 403);
		}

		$user_id = $user->user_id;

		$query = $db->query("SELECT * FROM bab WHERE id = " . $id . " AND user_id = " . $user_id);

		$bab = $query->getRow();

		$queryMateri = $db->query("SELECT * FROM materi WHERE bab_id = $bab->id");

		$materi = $query->getResult();

		$db->close();

		if (!$bab) {
			$data = [
				'data' => [],
				'error' => [
					'type' => 'Not Found',
					'message' => "Tidak ada ID yang ditemukan pada bab"
				]
			];

			return $this->respond($data, 404);
		} else {
			$arr_materi = [];

			foreach ($materi as $materi) {
				$arr_materi = [
					"id" => $materi->id,
					"judul" => $materi->judul,
					"deskripsi" => $materi->deskripsi,
					"konten" => $materi->konten,
					"thumbnail" => $materi->thumbnail,
					"created_at" => $materi->created_at,
					"updated_at" => $materi->updated_at
				];
			}

			$data = [
				'data' => [
					'type' => 'video',
					'id' => (int)$bab->id,
					'attributes' => [
						'judul' => $bab->nama,
						'materi' => $arr_materi,
						'created_at' => $bab->created_at,
						'updated_at' => $bab->updated_at
					]
				],
				'error' => null
			];
		}

		return $this->respond($data, 200);
	}
}
