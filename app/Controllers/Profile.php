<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestTrait;
use CodeIgniter\RESTful\ResourceController;
// use Firebase\JWT\JWT;

class Profile extends ResourceController
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

		$query = $db->query("SELECT * FROM profiles WHERE user_id = $user_id");

		$profile = $query->getRow();

		$db->close();

		$temp = [];

		if ($profile) {
			$temp = [
				'type' => 'profile',
				'id' => (int)$profile->id,
				'attributes' => [
					'name' => $profile->name,
					'email' => $profile->email,
					'phone_number' => $profile->phone_number,
					'topic' => $profile->topik,
					'address' => $profile->address,
					'photo_url' => $profile->photo_url,
					'created_at' => $profile->created_at,
					'updated_at' => $profile->updated_at
				]
			];
		}

		$data = [
			'data' => $temp,
			'error' => null
		];

		return $this->respond($data, 200);
	}
}
