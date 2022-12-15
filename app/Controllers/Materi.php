<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class Materi extends ResourceController
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

		$customQuery = "SELECT * FROM materi WHERE user_id = $user_id";

		if ($bab_id) {
			$customQuery = "SELECT * FROM materi WHERE user_id = $user_id AND bab_id = $bab_id";
		}

		$query = $db->query($customQuery);

		$materi = $query->getResult();

		$db->close();

		$arr_materi = [];

		foreach ($materi as $materi) {
			$temp = [
				'type'	=> 'materi',
				'id'		=> (int)$materi->id,
				'attributes' => [
					'judul'				=> $materi->judul,
					'bab_id'			=> $materi->bab_id,
					'deskripsi'		=> $materi->deskripsi,
					'thumbnail'		=> $materi->thumbnail,
					'konten' 			=> $materi->konten,
					'created_at'	=> $materi->created_at,
					'updated_at'	=> $materi->updated_at
				]
			];

			array_push($arr_materi, $temp);
		}

		$data = [
			'data'	=> $arr_materi,
			'error'	=> null
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
				'data'	=> null,
				'error'	=> [
					'type'		=> 'Bad Request',
					'message'	=> 'You must enter id'
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

		$query = $db->query("SELECT * FROM materi WHERE id = " . $id . " AND user_id = " . $user_id);

		$materi = $query->getRow();

		$db->close();

		if (!$materi) {
			$data = [
				'data'	=> [],
				'error'	=> [
					'type'		=> 'Not Found',
					'message'	=> "Tidak ada ID yang ditemukan pada materi"
				]
			];

			return $this->respond($data, 404);
		} else {
			$konten = $materi->konten;
			$konten = str_replace('width="640"', 'width="100%"', $konten);
			$konten = str_replace('height="360"', '', $konten);

			$data = [
				'data' => [
					'type'	=> 'video',
					'id'		=> (int)$materi->id,
					'attributes' => [
						'judul' 			=> $materi->judul,
						'bab_id' 			=> $materi->bab_id,
						'deskripsi' 	=> $materi->deskripsi,
						'thumbnail'		=> $materi->thumbnail,
						'konten' 			=> $konten,
						'created_at'	=> $materi->created_at,
						'updated_at'	=> $materi->updated_at
					]
				],
				'error' => null
			];
		}

		return $this->respond($data, 200);
	}
}
