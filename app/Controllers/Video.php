<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class Video extends ResourceController
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

		$query = $db->query("SELECT * FROM video WHERE user_id = $user_id");

		$video = $query->getResult();

		$db->close();

		$arr_video = [];

		foreach ($video as $video) {
			$temp = [
				'type' => 'video',
				'id' => (int)$video->id,
				'attributes' => [
					'judul' => $video->judul,
					'deskripsi' => $video->deskripsi,
					'video_url' => $video->video_url,
					'created_at' => $video->created_at,
					'updated_at' => $video->updated_at
				]
			];

			array_push($arr_video, $temp);
		}

		$data = [
			'data' => $arr_video,
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

		$query = $db->query("SELECT * FROM video WHERE id = " . $id . " AND user_id = " . $user_id);

		$video = $query->getRow();

		$db->close();

		if (!$video) {
			$data = [
				'data' => [],
				'error' => [
					'type' => 'Not Found',
					'message' => "Tidak ada ID yang ditemukan pada video"
				]
			];

			return $this->respond($data, 404);
		} else {
			$data = [
				'data' => [
					'type' => 'video',
					'id' => (int)$video->id,
					'attributes' => [
						'judul' => $video->judul,
						'deskripsi' => $video->deskripsi,
						'video_url' => $video->video_url,
						'created_at' => $video->created_at,
						'updated_at' => $video->updated_at
					],
				],
				'error' => null
			];
		}

		return $this->respond($data, 200);
	}
}
