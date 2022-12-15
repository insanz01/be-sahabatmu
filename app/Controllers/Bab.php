<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class Bab extends ResourceController
{
	protected $format = 'json';

	use RequestTrait;

	public function index()
	{

		$api_key = $this->request->getGet('api_key');

		$bab_id = $this->request->getGet('bab_id');

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

		$query = $db->query("SELECT * FROM bab WHERE user_id = $user_id");

		$bab = $query->getResult();

		$db->close();

		$arr_bab = [];

		foreach ($bab as $bab) {
			$temp = [
				'type' => 'bab',
				'id' => (int)$bab->id,
				'attributes' => [
					'judul' => $bab->nama,
					'created_at' => $bab->created_at,
					'updated_at' => $bab->updated_at
				]
			];

			array_push($arr_bab, $temp);
		}

		$data = [
			'data' => $arr_bab,
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
