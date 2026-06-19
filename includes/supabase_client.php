<?php
// C:\Users\RISHON OOMMEN TOGY\Downloads\MindCare\php-app\includes\supabase_client.php
require_once __DIR__ . '/config.php';

class SupabaseClient {
    private $url;
    private $key;

    public function __construct($url, $key) {
        $this->url = $url;
        $this->key = $key;
    }

    private function request($method, $path, $data = null, $headers = []) {
        $ch = curl_init($this->url . $path);
        
        $defaultHeaders = [
            'apikey: ' . $this->key,
            'Content-Type: application/json'
        ];

        if (isset($_SESSION['supabase_token'])) {
            $defaultHeaders[] = 'Authorization: Bearer ' . $_SESSION['supabase_token'];
        }
        
        $allHeaders = array_merge($defaultHeaders, $headers);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['status' => $status, 'data' => json_decode($response, true)];
    }

    // Auth methods
    public function signUp($email, $password, $data = []) {
        return $this->request('POST', '/auth/v1/signup', [
            'email' => $email,
            'password' => $password,
            'data' => $data
        ]);
    }

    public function signIn($email, $password) {
        return $this->request('POST', '/auth/v1/token?grant_type=password', [
            'email' => $email,
            'password' => $password
        ]);
    }

    public function getUser($token) {
        return $this->request('GET', '/auth/v1/user', null, [
            'Authorization: Bearer ' . $token
        ]);
    }

    public function rpc($function, $params = []) {
        return $this->request('POST', '/rest/v1/rpc/' . $function, $params);
    }

    // Database methods (PostgREST)
    public function from($table) {
        return new SupabaseQueryBuilder($this, $table);
    }

    public function executeQuery($method, $path, $data = null, $headers = []) {
        return $this->request($method, '/rest/v1' . $path, $data, $headers);
    }
}

class SupabaseQueryBuilder {
    private $client;
    private $table;
    private $query = [];

    public function __construct($client, $table) {
        $this->client = $client;
        $this->table = $table;
    }

    public function select($columns = '*') {
        $this->query['select'] = $columns;
        return $this;
    }

    public function insert($data) {
        return $this->client->executeQuery('POST', '/' . $this->table, $data, ['Prefer: return=representation']);
    }

    public function upsert($data) {
        return $this->client->executeQuery('POST', '/' . $this->table, $data, ['Prefer: resolution=merge-duplicates, return=representation']);
    }

    public function update($data) {
        return $this->client->executeQuery('PATCH', '/' . $this->table . $this->buildQueryString(), $data);
    }

    public function eq($column, $value) {
        $this->query['filter'][] = $column . '=eq.' . urlencode($value);
        return $this;
    }

    public function order($column, $ascending = true) {
        $this->query['order'] = $column . '.' . ($ascending ? 'asc' : 'desc');
        return $this;
    }

    public function limit($count) {
        $this->query['limit'] = $count;
        return $this;
    }

    public function get() {
        return $this->client->executeQuery('GET', '/' . $this->table . $this->buildQueryString());
    }

    private function buildQueryString() {
        $parts = [];
        if (isset($this->query['select'])) $parts[] = 'select=' . $this->query['select'];
        if (isset($this->query['filter'])) {
            foreach ($this->query['filter'] as $filter) {
                $parts[] = $filter;
            }
        }
        if (isset($this->query['order'])) $parts[] = 'order=' . $this->query['order'];
        if (isset($this->query['limit'])) $parts[] = 'limit=' . $this->query['limit'];

        return $parts ? '?' . implode('&', $parts) : '';
    }
}

$supabase = new SupabaseClient(SUPABASE_URL, SUPABASE_ANON_KEY);
?>
