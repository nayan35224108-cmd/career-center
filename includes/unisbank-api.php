<?php
// Unisbank API Helper Functions

/**
 * Get Bearer Token for API authentication
 */
function unisbank_get_bearer_token() {
  if (empty(UNISBANK_API_TOKEN)) {
    return null;
  }
  return UNISBANK_API_TOKEN;
}

/**
 * Make API call to Unisbank
 */
function unisbank_api_call($action, $data) {
  $bearer_token = unisbank_get_bearer_token();
  if (!$bearer_token) {
    return ['success' => false, 'error' => 'API credentials not configured'];
  }

  $payload = json_encode([
    'action' => $action,
    'data' => $data
  ]);

  $ch = curl_init(UNISBANK_API_URL);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
      'Content-Type: application/json',
      'Authorization: Bearer ' . $bearer_token
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true
  ]);

  $response = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $error = curl_error($ch);
  curl_close($ch);

  if ($error) {
    return ['success' => false, 'error' => 'Connection error: ' . $error];
  }

  $result = json_decode($response, true);
  if (!$result) {
    return ['success' => false, 'error' => 'Invalid response from API'];
  }

  return ['success' => true, 'data' => $result];
}

/**
 * Login to Unisbank API
 * @param string $username Username (e.g., NIM for students)
 * @param string $password Password
 * @return array Result with success status and data or error
 */
function unisbank_login($username, $password) {
  $result = unisbank_api_call('Login', [
    'Login' => $username,
    'Password' => $password
  ]);

  if (!$result['success']) {
    return $result;
  }

  $data = $result['data'];

  // Check for success
  if (isset($data['info']['InfoCode']) && $data['info']['InfoCode'] === '000') {
    return [
      'success' => true,
      'data' => [
        'login' => $data['data']['Login'] ?? $username,
        'nama_lengkap' => $data['data']['NamaLengkap'] ?? '',
        'role' => $data['data']['Role'] ?? '',
        'struktural' => $data['data']['Struktural'] ?? '',
        'auth_key' => $data['data']['AuthKey'] ?? ''
      ]
    ];
  }

  return [
    'success' => false,
    'error' => $data['info']['CodeDesc'] ?? 'Login failed'
  ];
}

/**
 * Get User Token from AuthKey
 */
function unisbank_get_token($auth_key, $username) {
  $result = unisbank_api_call('GetToken', [
    'AuthKey' => $auth_key,
    'Login' => $username
  ]);

  if (!$result['success']) {
    return $result;
  }

  $data = $result['data'];

  if (isset($data['info']['InfoCode']) && $data['info']['InfoCode'] === '020') {
    return [
      'success' => true,
      'user_token' => $data['data']['UserToken'] ?? ''
    ];
  }

  return [
    'success' => false,
    'error' => $data['info']['CodeDesc'] ?? 'Failed to get token'
  ];
}

/**
 * Get User Profile
 */
function unisbank_get_profile($username, $user_token) {
  $result = unisbank_api_call('MyProfile', [
    'UserToken' => $user_token,
    'Login' => $username
  ]);

  if (!$result['success']) {
    return $result;
  }

  $data = $result['data'];

  if (isset($data['info']['InfoCode']) && $data['info']['InfoCode'] === '00') {
    $profile = $data['data'];

    // Handle different response formats for Dosen vs Mahasiswa
    // Dosen: NamaLengkap, NIY, NIDN, Homebase, Jafung
    // Mahasiswa: Nama, MHSWID, ProdiID, Foto
    $nama_lengkap = $profile['NamaLengkap'] ?? $profile['Nama'] ?? '';
    $nim = $profile['MHSWID'] ?? $profile['NIM'] ?? '';
    $niy = $profile['NIY'] ?? '';
    $homebase = $profile['Homebase'] ?? $profile['Prodi'] ?? '';

    return [
      'success' => true,
      'profile' => [
        'niy' => $niy,
        'nik' => $profile['NIK'] ?? '',
        'nidn' => $profile['NIDN'] ?? '',
        'nim' => $nim,
        'nama_lengkap' => $nama_lengkap,
        'homebase' => $homebase,
        'fakultas' => $profile['Fakultas'] ?? '',
        'prodi' => $profile['Prodi'] ?? '',
        'email' => $profile['Email'] ?? '',
        'handphone' => $profile['Handphone'] ?? $profile['Telepon'] ?? '',
        'jafung' => $profile['Jafung'] ?? '',
        'foto' => $profile['Foto'] ?? ''
      ]
    ];
  }

  return [
    'success' => false,
    'error' => $data['info']['CodeDesc'] ?? 'Failed to get profile'
  ];
}

/**
 * Authenticate user via Unisbank API and get full profile
 */
function unisbank_authenticate($username, $password) {
  // Step 1: Login to get AuthKey
  $login_result = unisbank_login($username, $password);
  if (!$login_result['success']) {
    return $login_result;
  }

  $auth_key = $login_result['data']['auth_key'];
  $login_name = $login_result['data']['login'];
  $role = $login_result['data']['role'];

  // Step 2: Get User Token
  $token_result = unisbank_get_token($auth_key, $login_name);
  if (!$token_result['success']) {
    return $token_result;
  }

  $user_token = $token_result['user_token'];

  // Step 3: Get Profile
  $profile_result = unisbank_get_profile($login_name, $user_token);
  if (!$profile_result['success']) {
    return $profile_result;
  }

  return [
    'success' => true,
    'user' => array_merge($login_result['data'], $profile_result['profile'])
  ];
}